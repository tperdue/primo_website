<?php

namespace App\Controllers;

use App\Models\BusinessSettingsModel;
use App\Models\CustomerModel;
use App\Models\ProjectModel;
use App\Models\QuoteLineItemModel;
use App\Models\QuoteModel;
use App\Models\QuoteRequestAnswerModel;
use App\Models\QuoteRequestFileModel;
use App\Models\QuoteRequestModel;
use App\Models\QuoteRequestServiceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\DownloadResponse;

class Portal extends BaseController
{
    private const VISIBLE_QUOTE_STATUSES = ['sent', 'accepted', 'declined', 'expired'];

    public function index(): string
    {
        $customer = $this->customer();
        $customerId = (int) $customer['id'];

        return view('portal/dashboard', $this->shared($customer) + [
            'requests' => (new QuoteRequestModel())->where('customer_id', $customerId)->orderBy('created_at', 'DESC')->findAll(5),
            'quotes' => (new QuoteModel())->where('customer_id', $customerId)->whereIn('status', self::VISIBLE_QUOTE_STATUSES)->orderBy('created_at', 'DESC')->findAll(5),
            'projects' => (new ProjectModel())->where('customer_id', $customerId)->orderBy('updated_at', 'DESC')->findAll(5),
            'requestCount' => (new QuoteRequestModel())->where('customer_id', $customerId)->countAllResults(),
            'quoteCount' => (new QuoteModel())->where('customer_id', $customerId)->whereIn('status', self::VISIBLE_QUOTE_STATUSES)->countAllResults(),
            'projectCount' => (new ProjectModel())->where('customer_id', $customerId)->countAllResults(),
        ]);
    }

    public function profile(): string
    {
        $customer = $this->customer();

        return view('portal/profile', $this->shared($customer));
    }

    public function request(int $id): string
    {
        $customer = $this->customer();
        $request = (new QuoteRequestModel())->where('customer_id', $customer['id'])->find($id);
        if ($request === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('portal/request', $this->shared($customer) + [
            'request' => $request,
            'services' => (new QuoteRequestServiceModel())->where('quote_request_id', $id)->orderBy('sort_order', 'ASC')->findAll(),
            'answers' => (new QuoteRequestAnswerModel())->where('quote_request_id', $id)->orderBy('sort_order', 'ASC')->findAll(),
            'files' => (new QuoteRequestFileModel())->where('quote_request_id', $id)->findAll(),
        ]);
    }

    public function download(int $requestId, int $fileId): DownloadResponse
    {
        $customer = $this->customer();
        $request = (new QuoteRequestModel())->where('customer_id', $customer['id'])->find($requestId);
        $file = $request === null ? null : (new QuoteRequestFileModel())->where('quote_request_id', $requestId)->find($fileId);
        if ($file === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $root = realpath(WRITEPATH . 'uploads/quote_requests');
        $path = realpath(WRITEPATH . 'uploads/' . $file['relative_path']);
        if ($root === false || $path === false || ! str_starts_with($path, $root . DIRECTORY_SEPARATOR) || ! is_file($path)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response->download($path, null)->setFileName($file['original_name']);
    }

    public function quote(int $id): string
    {
        $customer = $this->customer();
        $quote = (new QuoteModel())->where('customer_id', $customer['id'])->whereIn('status', self::VISIBLE_QUOTE_STATUSES)->find($id);
        if ($quote === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('portal/quote', $this->shared($customer) + [
            'quote' => $quote,
            'items' => (new QuoteLineItemModel())->where('quote_id', $id)->orderBy('sort_order', 'ASC')->findAll(),
        ]);
    }

    public function project(int $id): string
    {
        $customer = $this->customer();
        $project = (new ProjectModel())->where('customer_id', $customer['id'])->find($id);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('portal/project', $this->shared($customer) + [
            'project' => $project,
            'quote' => (new QuoteModel())->where('customer_id', $customer['id'])->find($project['quote_id']),
        ]);
    }

    /** @return array<string, mixed> */
    private function customer(): array
    {
        $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Referrer-Policy', 'no-referrer');
        $customer = (new CustomerModel())->where('user_id', auth()->id())->first();
        if ($customer === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $customer;
    }

    /** @param array<string, mixed> $customer @return array<string, mixed> */
    private function shared(array $customer): array
    {
        $business = (new BusinessSettingsModel())->find(1) ?? [];

        return [
            'businessName' => $business['business_name'] ?? 'Design studio',
            'contactEmail' => $business['contact_email'] ?? null,
            'customer' => $customer,
        ];
    }
}
