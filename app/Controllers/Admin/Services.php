<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessSettingsModel;
use App\Models\MediaAssetModel;
use App\Models\ServiceMediaModel;
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
        return $this->form(null);
    }

    public function edit(int $id): string
    {
        return $this->form($this->findService($id));
    }

    public function store(): RedirectResponse
    {
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $mediaId = $this->selectedMediaId();
        if ($mediaId === false) {
            return redirect()->back()->withInput()->with('errors', ['featured_media_id' => 'Choose an image from the media library.']);
        }

        helper('url');
        $slug = url_title($data['name'], '-', true);
        if ($slug === '' || (new ServiceModel())->where('slug', $slug)->first() !== null) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'Choose a distinct service name.']);
        }

        $db = db_connect();
        $db->transStart();
        $model = new ServiceModel();
        $id = (int) $model->insert(['slug' => $slug] + $data);
        $this->syncMedia($id, $mediaId);
        $db->transComplete();

        return redirect()->to('/admin/services')->with('message', 'Service created.');
    }

    public function update(int $id): RedirectResponse
    {
        $this->findService($id);
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $mediaId = $this->selectedMediaId();
        if ($mediaId === false) {
            return redirect()->back()->withInput()->with('errors', ['featured_media_id' => 'Choose an image from the media library.']);
        }

        $db = db_connect();
        $db->transStart();
        (new ServiceModel())->update($id, $data);
        $this->syncMedia($id, $mediaId);
        $db->transComplete();

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

    private function form(?array $service): string
    {
        $currentMediaId = null;
        if ($service !== null) {
            $relation = (new ServiceMediaModel())->find($service['id']);
            $currentMediaId = $relation['media_id'] ?? null;
        }

        return view('admin/services/form', [
            'service' => $service,
            'businessName' => $this->businessName(),
            'media' => (new MediaAssetModel())->orderBy('created_at', 'DESC')->findAll(),
            'currentMediaId' => $currentMediaId,
            'currentImage' => $currentMediaId === null ? null : (new MediaAssetModel())->find($currentMediaId),
        ]);
    }

    private function selectedMediaId(): int|false|null
    {
        $selected = $this->request->getPost('featured_media_id');
        if ($selected === null || $selected === '') {
            return null;
        }
        if (! is_string($selected) || ! ctype_digit($selected) || (int) $selected < 1 || (new MediaAssetModel())->find((int) $selected) === null) {
            return false;
        }

        return (int) $selected;
    }

    private function syncMedia(int $serviceId, ?int $mediaId): void
    {
        $model = new ServiceMediaModel();
        $model->where('service_id', $serviceId)->delete();
        if ($mediaId !== null) {
            $model->insert(['service_id' => $serviceId, 'media_id' => $mediaId]);
        }
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
