<?php

namespace App\Controllers;

use App\Libraries\QuoteCart;
use App\Libraries\QuoteQuestionCatalog;
use App\Libraries\QuoteRequestNotifier;
use App\Libraries\QuoteRequestSubmission;
use App\Models\QuoteRequestModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Quote extends BaseController
{
    public function index(): string
    {
        $cart = new QuoteCart();
        $serviceIds = $cart->serviceIds();

        return view('quote/index', $this->publicSiteData() + [
            'services' => $cart->services(),
            'questionGroups' => (new QuoteQuestionCatalog())->forServices($serviceIds),
            'answers' => $cart->answers(),
        ]);
    }

    public function add(int $serviceId): RedirectResponse
    {
        if (! (new QuoteCart())->add($serviceId)) {
            return redirect()->to('/services')->with('quoteError', 'That service could not be added to your quote.');
        }

        return redirect()->to('/quote')->with('quoteMessage', 'Service added to your quote.');
    }

    public function remove(int $serviceId): RedirectResponse
    {
        $cart = new QuoteCart();
        $serviceIds = $cart->serviceIds();
        $cart->saveAnswers($this->request->getPost('answers'), (new QuoteQuestionCatalog())->forServices($serviceIds));
        $cart->remove($serviceId);

        return redirect()->to('/quote')->with('quoteMessage', 'Service removed from your quote.');
    }

    public function save(): RedirectResponse
    {
        $cart = new QuoteCart();
        $cart->saveAnswers(
            $this->request->getPost('answers'),
            (new QuoteQuestionCatalog())->forServices($cart->serviceIds()),
        );
        $destination = $this->request->getPost('next') === 'services' ? '/services' : '/quote';

        return redirect()->to($destination)->with('quoteMessage', 'Quote progress saved.');
    }

    public function submit(): RedirectResponse|ResponseInterface
    {
        $key = 'quote-submit-' . hash('sha256', $this->request->getIPAddress());
        if (! service('throttler')->check($key, 3, 900, 1)) {
            return $this->response
                ->setStatusCode(429)
                ->setHeader('Retry-After', '900')
                ->setBody(view('quote/index', $this->builderData() + ['rateLimited' => true]));
        }

        $cart = new QuoteCart();
        $services = $cart->services();
        if ($services === []) {
            return redirect()->to('/services')->with('quoteError', 'Add at least one available service before submitting a request.');
        }

        $catalog = (new QuoteQuestionCatalog())->forServices($cart->serviceIds());
        $cart->saveAnswers($this->request->getPost('answers'), $catalog);
        $answers = $cart->answers();
        $files = $this->questionFiles($catalog);

        $rules = [
            'name' => 'required|max_length[120]',
            'company' => 'permit_empty|max_length[160]',
            'email' => 'required|valid_email|max_length[254]',
            'phone' => 'permit_empty|max_length[40]',
            'preferred_contact' => 'required|in_list[email,phone,either]',
            'project_summary' => 'required|min_length[20]|max_length[5000]',
            'desired_completion_date' => 'permit_empty|valid_date[Y-m-d]',
            'budget_range' => 'permit_empty|max_length[120]',
            'additional_notes' => 'permit_empty|max_length[5000]',
            'consent' => 'required|in_list[1]',
        ];
        $input = [];
        foreach (array_keys($rules) as $field) {
            $value = $this->request->getPost($field);
            $input[$field] = is_string($value) ? trim($value) : $value;
        }

        $errors = [];
        if (! $this->validateData($input, $rules)) {
            $errors = $this->validator->getErrors();
        }
        if (in_array($input['preferred_contact'] ?? '', ['phone', 'either'], true) && ($input['phone'] ?? '') === '') {
            $errors['phone'] = 'Add a phone number when phone contact is preferred.';
        }
        $errors += (new QuoteRequestSubmission())->validate($catalog, $answers, $files);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        unset($input['consent']);
        $input['email'] = strtolower($input['email']);
        $input['normalized_email'] = $input['email'];
        foreach (['company', 'phone', 'desired_completion_date', 'budget_range', 'additional_notes'] as $nullable) {
            $input[$nullable] = $input[$nullable] === '' ? null : $input[$nullable];
        }

        try {
            $request = (new QuoteRequestSubmission())->create($input, $services, $catalog, $answers, $files);
        } catch (Throwable $exception) {
            log_message('error', 'Quote request submission failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->back()->withInput()->with('quoteError', 'We could not save your request. Please try again.');
        }

        $business = $this->publicSiteData()['business'] ?? [];
        $notifier = new QuoteRequestNotifier();
        try {
            $ownerStatus = $notifier->notifyOwner($request, $business);
        } catch (Throwable $exception) {
            log_message('error', 'Owner quote notification failed for {reference}: {message}', ['reference' => $request['reference_number'], 'message' => $exception->getMessage()]);
            $ownerStatus = 'failed';
        }
        try {
            $customerStatus = $notifier->confirmCustomer($request, $business);
        } catch (Throwable $exception) {
            log_message('error', 'Customer quote notification failed for {reference}: {message}', ['reference' => $request['reference_number'], 'message' => $exception->getMessage()]);
            $customerStatus = 'failed';
        }
        (new QuoteRequestModel())->update($request['id'], [
            'owner_notification_status' => $ownerStatus,
            'customer_notification_status' => $customerStatus,
        ]);
        $cart->clear();

        return redirect()->to('/quote/received/' . $request['reference_number']);
    }

    public function received(string $reference): string
    {
        $request = (new QuoteRequestModel())->where('reference_number', $reference)->first();
        if ($request === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('quote/received', $this->publicSiteData() + ['reference' => $request['reference_number']]);
    }

    /** @return array<string, mixed> */
    private function builderData(): array
    {
        $cart = new QuoteCart();

        return $this->publicSiteData() + [
            'services' => $cart->services(),
            'questionGroups' => (new QuoteQuestionCatalog())->forServices($cart->serviceIds()),
            'answers' => $cart->answers(),
        ];
    }

    /** @param list<array<string, mixed>> $catalog @return array<int, \CodeIgniter\HTTP\Files\UploadedFile|null> */
    private function questionFiles(array $catalog): array
    {
        $files = [];
        foreach ($catalog as $group) {
            foreach ($group['questions'] as $question) {
                if ($question['field_type'] === 'file') {
                    $files[(int) $question['id']] = $this->request->getFile('files.' . $question['id']);
                }
            }
        }

        return $files;
    }
}
