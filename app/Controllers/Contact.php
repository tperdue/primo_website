<?php

namespace App\Controllers;

use App\Libraries\ContactNotifier;
use App\Models\ContactSubmissionModel;
use App\Models\ContentPageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Contact extends BaseController
{
    public function index(): string
    {
        return view('contact/index', $this->pageData());
    }

    public function submit(): RedirectResponse|ResponseInterface
    {
        $key = 'contact-submit-' . hash('sha256', $this->request->getIPAddress());
        if (! service('throttler')->check($key, 5, 900, 1)) {
            return $this->response
                ->setStatusCode(429)
                ->setHeader('Retry-After', '900')
                ->setBody(view('contact/index', $this->pageData() + ['rateLimited' => true]));
        }

        $rules = [
            'name' => 'required|max_length[120]',
            'email' => 'required|valid_email|max_length[254]',
            'phone' => 'permit_empty|max_length[40]',
            'subject' => 'required|max_length[160]',
            'message' => 'required|min_length[20]|max_length[5000]',
        ];
        $data = [];
        foreach (array_keys($rules) as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }

        if (! $this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new ContactSubmissionModel();
        $id = (int) $model->insert($data + ['status' => 'new', 'notification_status' => 'pending']);
        $submission = $model->find($id);
        $business = $this->publicSiteData()['business'] ?? [];
        try {
            $notificationStatus = (new ContactNotifier())->notify($submission, $business);
        } catch (Throwable $exception) {
            log_message('error', 'Contact notification failed for submission {id}: {message}', [
                'id' => $id,
                'message' => $exception->getMessage(),
            ]);
            $notificationStatus = 'failed';
        }
        $model->update($id, ['notification_status' => $notificationStatus]);

        return redirect()->to('/contact')->with('contact_success', true);
    }

    /** @return array<string, mixed> */
    private function pageData(): array
    {
        $page = (new ContentPageModel())->published()->where('slug', 'contact')->first();
        if ($page === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->publicSiteData() + ['page' => $page];
    }
}
