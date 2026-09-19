<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\QuoteDeliveryNotifier;
use App\Libraries\QuoteManager;
use App\Models\BusinessSettingsModel;
use App\Models\CustomerModel;
use App\Models\QuoteLineItemModel;
use App\Models\QuoteModel;
use App\Models\QuoteRequestModel;
use App\Models\QuoteStatusHistoryModel;
use App\Models\QuoteVersionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use RuntimeException;
use Throwable;

class Quotes extends BaseController
{
    public function index(): string
    {
        return view('admin/quotes/index', [
            'businessName' => $this->business()['business_name'] ?? 'Design studio',
            'quotes' => (new QuoteModel())->orderBy('created_at', 'DESC')->findAll(),
            'statuses' => QuoteModel::STATUSES,
        ]);
    }

    public function createFromRequest(int $requestId): RedirectResponse
    {
        try {
            $quote = (new QuoteManager())->createFromRequest($requestId, $this->business());
        } catch (Throwable $exception) {
            log_message('error', 'Quote creation failed for request {id}: {message}', ['id' => $requestId, 'message' => $exception->getMessage()]);
            $message = $exception instanceof RuntimeException ? $exception->getMessage() : 'The quote could not be created.';

            return redirect()->to('/admin/quote-requests/' . $requestId)->with('errors', [$message]);
        }

        return redirect()->to('/admin/quotes/' . $quote['id'] . '/edit')->with('message', 'Quote draft created from the request.');
    }

    public function edit(int $id): string
    {
        $quote = $this->findQuote($id);

        return view('admin/quotes/form', [
            'businessName' => $this->business()['business_name'] ?? 'Design studio',
            'quote' => $quote,
            'items' => (new QuoteLineItemModel())->where('quote_id', $id)->orderBy('sort_order', 'ASC')->findAll(),
            'history' => (new QuoteStatusHistoryModel())->where('quote_id', $id)->orderBy('created_at', 'DESC')->findAll(),
            'versions' => (new QuoteVersionModel())->where('quote_id', $id)->orderBy('version_number', 'DESC')->findAll(),
            'statuses' => QuoteModel::STATUSES,
            'request' => $quote['quote_request_id'] ? (new QuoteRequestModel())->find($quote['quote_request_id']) : null,
            'customer' => (new CustomerModel())->find($quote['customer_id']),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $this->findQuote($id);
        $fields = ['quote_date', 'expires_on', 'currency_code', 'status', 'discount_amount', 'tax_rate', 'deposit_percentage', 'terms', 'notes'];
        $data = [];
        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }
        $data['currency_code'] = strtoupper((string) $data['currency_code']);
        $items = $this->request->getPost('items');
        $manager = new QuoteManager();
        $errors = $manager->validate($data, $items);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        try {
            $manager->update($id, $data, array_values($items));
        } catch (Throwable $exception) {
            log_message('error', 'Quote update failed for quote {id}: {message}', ['id' => $id, 'message' => $exception->getMessage()]);
            $message = $exception instanceof RuntimeException ? $exception->getMessage() : 'The quote could not be saved.';

            return redirect()->back()->withInput()->with('errors', [$message]);
        }

        return redirect()->to('/admin/quotes/' . $id . '/edit')->with('message', 'Quote saved as a new revision.');
    }

    public function send(int $id): RedirectResponse
    {
        $quote = $this->findQuote($id);
        if ($quote['status'] !== 'ready') {
            return redirect()->to('/admin/quotes/' . $id . '/edit')->with('errors', ['Mark the quote ready before sending it.']);
        }

        try {
            $deliveryStatus = (new QuoteDeliveryNotifier())->send($quote, $this->business());
        } catch (Throwable $exception) {
            log_message('error', 'Quote delivery failed for {number}: {message}', ['number' => $quote['quote_number'], 'message' => $exception->getMessage()]);
            $deliveryStatus = 'failed';
        }
        (new QuoteManager())->recordDelivery($id, $deliveryStatus);
        $message = $deliveryStatus === 'sent'
            ? 'Quote sent to ' . $quote['customer_email'] . '.'
            : 'The quote is saved, but email delivery is ' . str_replace('_', ' ', $deliveryStatus) . '. You can share the secure preview link manually.';

        return redirect()->to('/admin/quotes/' . $id . '/edit')->with('message', $message);
    }

    /** @return array<string, mixed> */
    private function findQuote(int $id): array
    {
        $quote = (new QuoteModel())->find($id);
        if ($quote === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $quote;
    }

    /** @return array<string, mixed> */
    private function business(): array
    {
        return (new BusinessSettingsModel())->find(1) ?? [];
    }
}
