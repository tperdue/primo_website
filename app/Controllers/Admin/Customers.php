<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CustomerRelationships;
use App\Models\BusinessSettingsModel;
use App\Models\CustomerModel;
use App\Models\ProjectModel;
use App\Models\QuoteModel;
use App\Models\QuoteRequestModel;
use App\Models\QuoteStatusHistoryModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Customers extends BaseController
{
    public function index(): string
    {
        $query = trim((string) $this->request->getGet('q'));
        $model = new CustomerModel();
        if ($query !== '') {
            $model->groupStart()
                ->like('name', $query)
                ->orLike('business_name', $query)
                ->orLike('email', $query)
                ->orLike('phone', $query)
                ->groupEnd();
        }

        $customers = $model->orderBy('updated_at', 'DESC')->orderBy('id', 'DESC')->findAll(100);
        foreach ($customers as &$customer) {
            $customer += $this->relationshipSummary((int) $customer['id']);
        }
        unset($customer);

        return view('admin/customers/index', [
            'businessName' => $this->businessName(),
            'customers' => $customers,
            'query' => $query,
        ]);
    }

    public function show(int $id): string
    {
        $customer = $this->findCustomer($id);
        $requests = (new QuoteRequestModel())->where('customer_id', $id)->orderBy('created_at', 'DESC')->findAll();
        $quotes = (new QuoteModel())->where('customer_id', $id)->orderBy('created_at', 'DESC')->findAll();
        $projects = (new ProjectModel())->where('customer_id', $id)->orderBy('created_at', 'DESC')->findAll();
        $timeline = [];
        foreach ($requests as $request) {
            $timeline[] = [
                'type' => 'request',
                'date' => (string) ($request['created_at'] ?? ''),
                'title' => $request['reference_number'],
                'status' => $request['status'],
                'statusLabel' => QuoteRequestModel::STATUSES[$request['status']] ?? ucfirst($request['status']),
                'summary' => $request['project_summary'],
                'url' => '/admin/quote-requests/' . $request['id'],
            ];
        }
        foreach ($quotes as $quote) {
            $timeline[] = [
                'type' => 'quote',
                'date' => (string) ($quote['created_at'] ?? ''),
                'title' => $quote['quote_number'],
                'status' => $quote['status'],
                'statusLabel' => QuoteModel::STATUSES[$quote['status']] ?? ucfirst($quote['status']),
                'summary' => $quote['currency_code'] . ' ' . number_format((float) $quote['total'], 2),
                'url' => '/admin/quotes/' . $quote['id'] . '/edit',
            ];
            foreach ((new QuoteStatusHistoryModel())->where('quote_id', $quote['id'])->orderBy('created_at', 'DESC')->findAll() as $history) {
                $from = $history['from_status'] ? (QuoteModel::STATUSES[$history['from_status']] ?? ucfirst($history['from_status'])) : null;
                $to = QuoteModel::STATUSES[$history['to_status']] ?? ucfirst($history['to_status']);
                $timeline[] = [
                    'type' => 'quote update',
                    'date' => (string) ($history['created_at'] ?? ''),
                    'title' => $quote['quote_number'],
                    'status' => $history['to_status'],
                    'statusLabel' => $to,
                    'summary' => ($from ? $from . ' to ' : '') . $to . ($history['note'] ? '. ' . $history['note'] : ''),
                    'url' => '/admin/quotes/' . $quote['id'] . '/edit',
                ];
            }
        }
        foreach ($projects as $project) {
            $timeline[] = [
                'type' => 'project',
                'date' => (string) ($project['created_at'] ?? ''),
                'title' => $project['project_number'],
                'status' => $project['status'],
                'statusLabel' => ProjectModel::STATUSES[$project['status']] ?? ucfirst($project['status']),
                'summary' => $project['name'],
                'url' => '/admin/projects/' . $project['id'] . '/edit',
            ];
        }
        usort($timeline, static fn (array $left, array $right): int => strcmp($right['date'], $left['date']));

        return view('admin/customers/show', [
            'businessName' => $this->businessName(),
            'customer' => $customer,
            'requests' => $requests,
            'quotes' => $quotes,
            'projects' => $projects,
            'timeline' => $timeline,
            'acceptedCount' => count(array_filter($quotes, static fn (array $quote): bool => $quote['status'] === 'accepted')),
        ]);
    }

    public function create(): string
    {
        return $this->form(null);
    }

    public function edit(int $id): string
    {
        return $this->form($this->findCustomer($id));
    }

    public function store(): RedirectResponse
    {
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ((new CustomerRelationships())->emailInUse($data['normalized_email'])) {
            return redirect()->back()->withInput()->with('errors', ['email' => 'A customer already uses this email address.']);
        }

        $customerId = (int) (new CustomerModel())->insert($data);
        (new CustomerRelationships())->linkMatchingRequests($customerId, $data['normalized_email']);

        return redirect()->to('/admin/customers/' . $customerId)->with('message', 'Customer created.');
    }

    public function update(int $id): RedirectResponse
    {
        $this->findCustomer($id);
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ((new CustomerRelationships())->emailInUse($data['normalized_email'], $id)) {
            return redirect()->back()->withInput()->with('errors', ['email' => 'A customer already uses this email address.']);
        }

        (new CustomerModel())->update($id, $data);
        (new CustomerRelationships())->linkMatchingRequests($id, $data['normalized_email']);

        return redirect()->to('/admin/customers/' . $id)->with('message', 'Customer profile saved.');
    }

    /** @return array<string, mixed> */
    private function findCustomer(int $id): array
    {
        $customer = (new CustomerModel())->find($id);
        if ($customer === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $customer;
    }

    private function form(?array $customer): string
    {
        return view('admin/customers/form', [
            'businessName' => $this->businessName(),
            'customer' => $customer,
        ]);
    }

    /** @return array<string, mixed>|null */
    private function validatedInput(): ?array
    {
        $rules = [
            'name' => 'required|max_length[120]',
            'business_name' => 'permit_empty|max_length[160]',
            'email' => 'required|valid_email|max_length[254]',
            'phone' => 'permit_empty|max_length[40]',
            'address_line_1' => 'permit_empty|max_length[180]',
            'address_line_2' => 'permit_empty|max_length[180]',
            'city' => 'permit_empty|max_length[120]',
            'region' => 'permit_empty|max_length[120]',
            'postal_code' => 'permit_empty|max_length[30]',
            'country' => 'permit_empty|max_length[120]',
            'notes' => 'permit_empty|max_length[5000]',
        ];
        $data = [];
        foreach (array_keys($rules) as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }
        if (! $this->validateData($data, $rules)) {
            return null;
        }

        $data['email'] = CustomerRelationships::normalizeEmail($data['email']);
        $data['normalized_email'] = $data['email'];
        foreach (['business_name', 'phone', 'address_line_1', 'address_line_2', 'city', 'region', 'postal_code', 'country', 'notes'] as $nullable) {
            $data[$nullable] = $data[$nullable] === '' ? null : $data[$nullable];
        }

        return $data;
    }

    /** @return array{request_count: int, quote_count: int, last_activity: string} */
    private function relationshipSummary(int $customerId): array
    {
        $requestModel = new QuoteRequestModel();
        $quoteModel = new QuoteModel();
        $lastRequest = $requestModel->where('customer_id', $customerId)->orderBy('updated_at', 'DESC')->first();
        $lastQuote = $quoteModel->where('customer_id', $customerId)->orderBy('updated_at', 'DESC')->first();
        $lastActivity = max((string) ($lastRequest['updated_at'] ?? ''), (string) ($lastQuote['updated_at'] ?? ''));

        return [
            'request_count' => (new QuoteRequestModel())->where('customer_id', $customerId)->countAllResults(),
            'quote_count' => (new QuoteModel())->where('customer_id', $customerId)->countAllResults(),
            'last_activity' => $lastActivity,
        ];
    }

    private function businessName(): string
    {
        return (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio';
    }
}
