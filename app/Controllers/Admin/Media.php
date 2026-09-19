<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MediaImageUpload;
use App\Models\BusinessSettingsModel;
use App\Models\MediaAssetModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class Media extends BaseController
{
    public function index(): string
    {
        $model = new MediaAssetModel();
        $assets = $model->orderBy('created_at', 'DESC')->findAll();
        foreach ($assets as &$asset) {
            $asset['usage'] = $model->usage((int) $asset['id']);
        }

        return view('admin/media/index', [
            'businessName' => $this->businessName(),
            'assets' => $assets,
        ]);
    }

    public function store(): RedirectResponse
    {
        $altText = $this->request->getPost('alt_text');
        $altText = is_string($altText) ? trim($altText) : '';
        if (! $this->validateData(['alt_text' => $altText], ['alt_text' => 'required|max_length[255]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('image');
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return redirect()->back()->withInput()->with('errors', ['image' => 'Choose an image to upload.']);
        }

        try {
            (new MediaImageUpload())->store($file, $altText);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return redirect()->back()->withInput()->with('errors', ['image' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            log_message('error', 'Media upload failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->back()->withInput()->with('errors', ['image' => 'The image could not be saved.']);
        }

        return redirect()->to('/admin/media')->with('message', 'Image uploaded.');
    }

    public function edit(int $id): string
    {
        $model = new MediaAssetModel();

        return view('admin/media/edit', [
            'businessName' => $this->businessName(),
            'asset' => $this->findAsset($id),
            'usage' => $model->usage($id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $this->findAsset($id);
        $altText = $this->request->getPost('alt_text');
        $data = ['alt_text' => is_string($altText) ? trim($altText) : $altText];
        if (! $this->validateData($data, ['alt_text' => 'required|max_length[255]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new MediaAssetModel())->update($id, $data);

        return redirect()->to('/admin/media/' . $id . '/edit')->with('message', 'Image details saved.');
    }

    public function delete(int $id): RedirectResponse
    {
        $model = new MediaAssetModel();
        $asset = $this->findAsset($id);
        if ($model->usage($id)['total'] > 0) {
            return redirect()->to('/admin/media/' . $id . '/edit')->with('errors', ['delete' => 'Remove this image from every service and project before deleting it.']);
        }

        try {
            if (! $model->delete($id)) {
                throw new RuntimeException('The image record could not be deleted.');
            }
        } catch (Throwable $exception) {
            log_message('error', 'Media deletion failed for asset {id}: {message}', ['id' => $id, 'message' => $exception->getMessage()]);

            return redirect()->to('/admin/media/' . $id . '/edit')->with('errors', ['delete' => 'The image could not be deleted. Check its current usage and try again.']);
        }
        $this->deleteManagedFile($asset['path']);

        return redirect()->to('/admin/media')->with('message', 'Image deleted.');
    }

    /** @return array<string, mixed> */
    private function findAsset(int $id): array
    {
        $asset = (new MediaAssetModel())->find($id);
        if ($asset === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $asset;
    }

    private function deleteManagedFile(string $path): void
    {
        $path = str_replace('\\', '/', $path);
        if (! str_starts_with($path, 'uploads/') || str_contains($path, '..')) {
            return;
        }

        $uploadsRoot = realpath(FCPATH . 'uploads');
        $file = realpath(FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $path));
        if ($uploadsRoot !== false && $file !== false && str_starts_with($file, $uploadsRoot . DIRECTORY_SEPARATOR)) {
            @unlink($file);
        }
    }

    private function businessName(): string
    {
        return (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio';
    }
}
