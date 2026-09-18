<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessSettingsModel;
use App\Models\ServiceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class Services extends BaseController
{
    public function index(): string
    {
        return view('admin/services/index', [
            'services' => (new ServiceModel())->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll(),
            'businessName' => $this->businessName(),
        ]);
    }

    public function create(): string
    {
        return view('admin/services/form', ['service' => null, 'businessName' => $this->businessName()]);
    }

    public function edit(int $id): string
    {
        return view('admin/services/form', ['service' => $this->findService($id), 'businessName' => $this->businessName()]);
    }

    public function store(): RedirectResponse
    {
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        helper('url');
        $slug = url_title($data['name'], '-', true);
        if ($slug === '' || (new ServiceModel())->where('slug', $slug)->first() !== null) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'Choose a distinct service name.']);
        }

        $model = new ServiceModel();
        $model->insert(['slug' => $slug] + $data);

        return redirect()->to('/admin/services')->with('message', 'Service created.');
    }

    public function update(int $id): RedirectResponse
    {
        $this->findService($id);
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new ServiceModel())->update($id, $data);

        return redirect()->to('/admin/services')->with('message', 'Service saved.');
    }

    private function findService(int $id): array
    {
        $service = (new ServiceModel())->find($id);
        if ($service === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $service;
    }

    private function businessName(): string
    {
        return (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio';
    }

    private function validatedInput(): ?array
    {
        $rules = [
            'name' => 'required|max_length[120]',
            'summary' => 'required|max_length[300]',
            'description' => 'required|max_length[10000]',
            'starting_price' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[99999999.99]|max_length[11]',
            'currency_code' => 'required|regex_match[/^[A-Za-z]{3}$/]',
            'status' => 'required|in_list[draft,published,archived]',
            'sort_order' => 'required|integer|greater_than_equal_to[0]',
        ];
        $data = [];
        foreach (array_keys($rules) as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }
        $data['show_price'] = $this->request->getPost('show_price') === '1' ? 1 : 0;

        if (! $this->validateData($data, $rules)) {
            return null;
        }

        $data['starting_price'] = $data['starting_price'] === '' ? null : $data['starting_price'];
        $data['currency_code'] = strtoupper($data['currency_code']);
        $data['sort_order'] = (int) $data['sort_order'];
        if ($data['show_price'] && $data['starting_price'] === null) {
            $this->validator->setError('starting_price', 'Enter a starting price before showing it publicly.');
            return null;
        }

        return $data;
    }
}
