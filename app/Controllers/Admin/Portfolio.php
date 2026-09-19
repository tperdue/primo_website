<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MediaImageUpload;
use App\Models\BusinessSettingsModel;
use App\Models\MediaAssetModel;
use App\Models\PortfolioMediaModel;
use App\Models\PortfolioProjectModel;
use App\Models\PortfolioServiceModel;
use App\Models\ServiceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use InvalidArgumentException;
use Throwable;

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
        $relationships = $this->validatedRelationships();
        if ($relationships === false) {
            return redirect()->back()->withInput()->with('errors', $this->mediaErrors);
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

        $db = db_connect();
        $db->transStart();
        $id = (int) (new PortfolioProjectModel())->insert(['slug' => $slug, 'featured_media_id' => $mediaId] + $data);
        $this->syncRelationships($id, $relationships);
        $db->transComplete();

        return redirect()->to('/admin/portfolio')->with('message', 'Project created.');
    }

    public function update(int $id): RedirectResponse
    {
        $project = $this->findProject($id);
        $data = $this->validatedInput();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $relationships = $this->validatedRelationships();
        if ($relationships === false) {
            return redirect()->back()->withInput()->with('errors', $this->mediaErrors);
        }

        $mediaId = $this->resolveMediaId($project['featured_media_id'] === null ? null : (int) $project['featured_media_id'], $data['status']);
        if ($mediaId === false) {
            return redirect()->back()->withInput()->with('errors', $this->mediaErrors);
        }

        $db = db_connect();
        $db->transStart();
        (new PortfolioProjectModel())->update($id, ['featured_media_id' => $mediaId] + $data);
        $this->syncRelationships($id, $relationships);
        $db->transComplete();

        return redirect()->to('/admin/portfolio')->with('message', 'Project saved.');
    }

    private function form(?array $project): string
    {
        $media = (new MediaAssetModel())->orderBy('id', 'DESC')->findAll();
        $currentImage = $project === null || $project['featured_media_id'] === null
            ? null : (new MediaAssetModel())->find($project['featured_media_id']);
        $gallery = $project === null ? [] : (new PortfolioProjectModel())->gallery((int) $project['id']);
        $projectServices = $project === null ? [] : (new PortfolioServiceModel())->where('project_id', $project['id'])->findAll();

        return view('admin/portfolio/form', [
            'businessName' => $this->businessName(),
            'project' => $project,
            'media' => $media,
            'currentImage' => $currentImage,
            'galleryMediaIds' => array_map('intval', array_column($gallery, 'id')),
            'gallerySortOrders' => array_column($gallery, 'sort_order', 'id'),
            'services' => (new ServiceModel())->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll(),
            'projectServiceIds' => array_map('intval', array_column($projectServices, 'service_id')),
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
                $mediaId = (new MediaImageUpload())->store($file, $altText);
            } catch (InvalidArgumentException $e) {
                $this->mediaErrors = ['featured_image' => $e->getMessage()];
                return false;
            } catch (Throwable $exception) {
                log_message('error', 'Portfolio media upload failed: {message}', ['message' => $exception->getMessage()]);
                $this->mediaErrors = ['featured_image' => 'The image could not be saved.'];
                return false;
            }
        }

        if ($status === 'published' && $mediaId === null) {
            $this->mediaErrors = ['featured_image' => 'Add or select an image before publishing.'];
            return false;
        }

        return $mediaId;
    }

    /** @return array{gallery: list<array{media_id: int, sort_order: int}>, services: list<int>}|false */
    private function validatedRelationships(): array|false
    {
        $galleryIds = $this->request->getPost('gallery_media_ids') ?? [];
        $galleryOrders = $this->request->getPost('gallery_sort_order') ?? [];
        $serviceIds = $this->request->getPost('service_ids') ?? [];
        if (! is_array($galleryIds) || ! is_array($galleryOrders) || ! is_array($serviceIds)) {
            $this->mediaErrors = ['relationships' => 'Choose valid media and service relationships.'];
            return false;
        }

        $gallery = [];
        $seenGallery = [];
        foreach ($galleryIds as $index => $id) {
            if (! is_string($id) || ! ctype_digit($id) || (int) $id < 1 || (new MediaAssetModel())->find((int) $id) === null) {
                $this->mediaErrors = ['gallery_media_ids' => 'Choose gallery images from the media library.'];
                return false;
            }
            if (isset($seenGallery[$id])) {
                continue;
            }
            $seenGallery[$id] = true;
            $order = $galleryOrders[$id] ?? $index;
            if (! is_scalar($order) || filter_var($order, FILTER_VALIDATE_INT) === false || (int) $order < 0) {
                $this->mediaErrors = ['gallery_media_ids' => 'Gallery display order must be zero or greater.'];
                return false;
            }
            $gallery[] = ['media_id' => (int) $id, 'sort_order' => (int) $order];
        }

        $services = [];
        $seenServices = [];
        foreach ($serviceIds as $id) {
            if (! is_string($id) || ! ctype_digit($id) || (int) $id < 1 || (new ServiceModel())->find((int) $id) === null) {
                $this->mediaErrors = ['service_ids' => 'Choose services from the catalog.'];
                return false;
            }
            if (isset($seenServices[$id])) {
                continue;
            }
            $seenServices[$id] = true;
            $services[] = (int) $id;
        }

        return ['gallery' => $gallery, 'services' => $services];
    }

    /** @param array{gallery: list<array{media_id: int, sort_order: int}>, services: list<int>} $relationships */
    private function syncRelationships(int $projectId, array $relationships): void
    {
        $galleryModel = new PortfolioMediaModel();
        $galleryModel->where('project_id', $projectId)->delete();
        foreach ($relationships['gallery'] as $image) {
            $galleryModel->insert(['project_id' => $projectId] + $image);
        }

        $serviceModel = new PortfolioServiceModel();
        $serviceModel->where('project_id', $projectId)->delete();
        foreach ($relationships['services'] as $serviceId) {
            $serviceModel->insert(['project_id' => $projectId, 'service_id' => $serviceId]);
        }
    }
}
