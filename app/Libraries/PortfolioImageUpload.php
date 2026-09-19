<?php

namespace App\Libraries;

use App\Models\MediaAssetModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class PortfolioImageUpload
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
    ];

    public function store(UploadedFile $file, string $altText): int
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('Choose a valid image to upload.');
        }

        $size = $file->getSize();
        if ($size < 1 || $size > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Images must be 5 MB or smaller.');
        }

        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientExtension());
        if (! isset(self::MIME_EXTENSIONS[$mime]) || ! in_array($extension, self::MIME_EXTENSIONS[$mime], true)) {
            throw new InvalidArgumentException('Use a JPEG, PNG, or WebP image.');
        }

        $dimensions = @getimagesize($file->getTempName());
        if ($dimensions === false || $dimensions[0] > 8000 || $dimensions[1] > 8000) {
            throw new InvalidArgumentException('The image could not be read or is too large.');
        }

        $canonicalExtension = $mime === 'image/jpeg' ? 'jpg' : $extension;
        $name = bin2hex(random_bytes(16)) . '.' . $canonicalExtension;
        $directory = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'portfolio' . DIRECTORY_SEPARATOR;
        if ($file->move($directory, $name) !== true) {
            throw new RuntimeException('The image could not be uploaded.');
        }

        $path = 'uploads/portfolio/' . $name;
        try {
            $id = (new MediaAssetModel())->insert([
                'path' => $path,
                'alt_text' => $altText,
                'mime_type' => $mime,
                'byte_size' => $size,
            ]);
            if ($id === false) {
                throw new RuntimeException('The image could not be saved.');
            }
        } catch (Throwable $e) {
            @unlink($directory . $name);
            throw $e;
        }

        return (int) $id;
    }
}
