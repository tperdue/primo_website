<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessSettingsModel;
use App\Models\ContactSubmissionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class ContactSubmissions extends BaseController
{
    public function index(): string
    {
        return view('admin/contacts/index', [
            'businessName' => $this->businessName(),
            'submissions' => (new ContactSubmissionModel())->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function show(int $id): string
    {
        return view('admin/contacts/show', [
            'businessName' => $this->businessName(),
            'submission' => $this->findSubmission($id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $this->findSubmission($id);
        $status = $this->request->getPost('status');
        $data = ['status' => is_string($status) ? trim($status) : $status];
        if (! $this->validateData($data, ['status' => 'required|in_list[new,read,closed]'])) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        (new ContactSubmissionModel())->update($id, $data);

        return redirect()->to('/admin/contacts/' . $id)->with('message', 'Submission status updated.');
    }

    /** @return array<string, mixed> */
    private function findSubmission(int $id): array
    {
        $submission = (new ContactSubmissionModel())->find($id);
        if ($submission === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $submission;
    }

    private function businessName(): string
    {
        return (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio';
    }
}
