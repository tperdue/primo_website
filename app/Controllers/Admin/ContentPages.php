<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessSettingsModel;
use App\Models\ContentPageModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class ContentPages extends BaseController
{
    public function index(): string
    {
        return view('admin/pages/index', [
            'businessName' => $this->businessName(),
            'pages' => (new ContentPageModel())->orderBy('id')->findAll(),
        ]);
    }

    public function edit(int $id): string
    {
        return view('admin/pages/form', [
            'businessName' => $this->businessName(),
            'page' => $this->findPage($id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $this->findPage($id);
        $rules = [
            'title' => 'required|max_length[160]',
            'eyebrow' => 'permit_empty|max_length[80]',
            'summary' => 'required|max_length[320]',
            'body' => 'required|max_length[30000]',
            'meta_title' => 'required|max_length[160]',
            'meta_description' => 'required|max_length[320]',
            'status' => 'required|in_list[draft,published]',
        ];
        $data = [];
        foreach (array_keys($rules) as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }

        if (! $this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new ContentPageModel())->update($id, $data);

        return redirect()->to('/admin/pages')->with('message', 'Page saved.');
    }

    /** @return array<string, mixed> */
    private function findPage(int $id): array
    {
        $page = (new ContentPageModel())->find($id);
        if ($page === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $page;
    }

    private function businessName(): string
    {
        return (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio';
    }
}
