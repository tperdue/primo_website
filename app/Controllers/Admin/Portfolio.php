<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\PortfolioImageUpload;
use App\Models\BusinessSettingsModel;
use App\Models\MediaAssetModel;
use App\Models\PortfolioProjectModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use InvalidArgumentException;

class Portfolio extends BaseController
{
    private array $mediaErrors = [];

    public function index(): string
    {
        return view('admin/portfolio/index', [
            'businessName' => $this->businessName(),
            'projects' => (new PortfolioProjectModel())->withImage()->orderBy('portfolio_projects.updated_at', 'DESC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return $this->form(null);
    }

    public function edit(int $id): string
    {
        return $this->form($this->findProject($id));
    }

    public function store(): RedirectResponse
    {
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        helper('url');
        $slug = url_title($data['title'], '-', true);
        if ($slug === '' || (new PortfolioProjectModel())->where('slug', $slug)->first() !== null) {
            return redirect()->back()->withInput()->with('errors', ['title' => 'Choose a distinct project title.']);
        }

        $mediaId = $this->resolveMediaId(null, $data['status']);
        if ($mediaId === false) {
            return redirect()->back()->withInput()->with('errors', $this->mediaErrors);
        }

        (new PortfolioProjectModel())->insert(['slug' => $slug, 'featured_media_id' => $mediaId] + $data);

        return redirect()->to('/admin/portfolio')->with('message', 'Project created.');
    }

    public function update(int $id): RedirectResponse
    {
        $project = $this->findProject($id);
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $mediaId = $this->resolveMediaId($project['featured_media_id'], $data['status']);
        if ($mediaId === false) {
            return redirect()->back()->withInput()->with('errors', $this->mediaErrors);
        }

        (new PortfolioProjectModel())->update($id, ['featured_media_id' => $mediaId] + $data);

        return redirect()->to('/admin/portfolio')->with('message', 'Project saved.');
    }

    private function form(?array $project): string
    {
        $media = (new MediaAssetModel())->orderBy('id', 'DESC')->findAll();
        $currentImage = $project === null || $project['featured_media_id'] === null
            ? null : (new MediaAssetModel())->find($project['featured_media_id']);

        return view('admin/portfolio/form', [
            'businessName' => $this->businessName(),
            'project' => $project,
            'media' => $media,
            'currentImage' => $currentImage,
        ]);
    }

    private function findProject(int $id): array
    {
        $project = (new PortfolioProjectModel())->find($id);
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $project;
    }

    private function businessName(): string
    {
        return (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio';
    }

    private function validatedInput(): ?array
    {
        $rules = [
            'title' => 'required|max_length[160]',
            'client_name' => 'permit_empty|max_length[160]',
            'summary' => 'required|max_length[320]',
            'challenge' => 'required|max_length[10000]',
            'solution' => 'required|max_length[10000]',
            'project_date' => 'permit_empty|valid_date[Y-m-d]',
            'status' => 'required|in_list[draft,published,archived]',
            'sort_order' => 'required|integer|greater_than_equal_to[0]',
        ];
        $data = [];
        foreach (array_keys($rules) as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }
        $data['is_featured'] = $this->request->getPost('is_featured') === '1' ? 1 : 0;

        if (! $this->validateData($data, $rules)) {
            return null;
        }

        $data['client_name'] = $data['client_name'] === '' ? null : $data['client_name'];
        $data['project_date'] = $data['project_date'] === '' ? null : $data['project_date'];
        $data['sort_order'] = (int) $data['sort_order'];

        return $data;
    }

    private function resolveMediaId(?int $currentId, string $status): int|false|null
    {
        $selected = $this->request->getPost('featured_media_id');
        $mediaId = $currentId;
        if ($selected !== null && $selected !== '') {
            if (! is_string($selected) || ! ctype_digit($selected) || (int) $selected < 1 || (new MediaAssetModel())->find((int) $selected) === null) {
                $this->mediaErrors = ['featured_image' => 'Choose an image from the library.'];
                return false;
            }
            $mediaId = (int) $selected;
        }

        $file = $this->request->getFile('featured_image');
        if ($file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            $altText = $this->request->getPost('image_alt');
            $altText = is_string($altText) ? trim($altText) : '';
            if ($altText === '' || mb_strlen($altText) > 255) {
                $this->mediaErrors = ['image_alt' => 'Enter image alt text (up to 255 characters).'];
                return false;
            }
            try {
                $mediaId = (new PortfolioImageUpload())->store($file, $altText);
            } catch (InvalidArgumentException $e) {
                $this->mediaErrors = ['featured_image' => $e->getMessage()];
                return false;
            }
        }

        if ($status === 'published' && $mediaId === null) {
            $this->mediaErrors = ['featured_image' => 'Add or select an image before publishing.'];
            return false;
        }

        return $mediaId;
    }
}
