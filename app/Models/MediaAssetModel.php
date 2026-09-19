<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaAssetModel extends Model
{
    protected $table = 'media_assets';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $allowedFields = ['path', 'alt_text', 'mime_type', 'byte_size'];
}
