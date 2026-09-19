<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessSettingsModel;
use App\Models\QuoteRequestAnswerModel;
use App\Models\QuoteRequestFileModel;
use App\Models\QuoteRequestModel;
use App\Models\QuoteRequestServiceModel;
use App\Models\QuoteModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RedirectResponse;

class QuoteRequests extends BaseController
{
    public function index(): string
    {
        $status = $this->request->getGet('status');
        $model = new QuoteRequestModel();
        if (is_string($status) && isset(QuoteRequestModel::STATUSES[$status])) {
            $model->where('status', $status);
        } else {
            $status = '';
        }

        return view('admin/quote_requests/index', [
            'businessName' => $this->businessName(),
            'requests' => $model->orderBy('created_at', 'DESC')->findAll(),
            'statuses' => QuoteRequestModel::STATUSES,
            'selectedStatus' => $status,
        ]);
    }

    public function show(int $id): string
    {
        return view('admin/quote_requests/show', [
            'businessName' => $this->businessName(),
            'request' => $this->findRequest($id),
            'services' => (new QuoteRequestServiceModel())->where('quote_request_id', $id)->orderBy('sort_order', 'ASC')->findAll(),
            'answers' => (new QuoteRequestAnswerModel())->where('quote_request_id', $id)->orderBy('sort_order', 'ASC')->findAll(),
            'files' => (new QuoteRequestFileModel())->where('quote_request_id', $id)->findAll(),
            'statuses' => QuoteRequestModel::STATUSES,
            'quote' => (new QuoteModel())->where('quote_request_id', $id)->first(),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $this->findRequest($id);
        $status = $this->request->getPost('status');
        $notes = $this->request->getPost('internal_notes');
        $data = [
            'status' => is_string($status) ? trim($status) : $status,
            'internal_notes' => is_string($notes) ? trim($notes) : $notes,
        ];
        if (! $this->validateData($data, [
            'status' => 'required|in_list[' . implode(',', array_keys(QuoteRequestModel::STATUSES)) . ']',
            'internal_notes' => 'permit_empty|max_length[5000]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data['internal_notes'] = $data['internal_notes'] === '' ? null : $data['internal_notes'];
        (new QuoteRequestModel())->update($id, $data);

        return redirect()->to('/admin/quote-requests/' . $id)->with('message', 'Quote request updated.');
    }

    public function download(int $requestId, int $fileId): DownloadResponse
    {
        $this->findRequest($requestId);
        $file = (new QuoteRequestFileModel())->where('quote_request_id', $requestId)->find($fileId);
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

    /** @return array<string, mixed> */
    private function findRequest(int $id): array
    {
        $request = (new QuoteRequestModel())->find($id);
        if ($request === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $request;
    }

    private function businessName(): string
    {
        return (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio';
    }
}
