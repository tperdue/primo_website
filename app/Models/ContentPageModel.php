<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentPageModel extends Model
{
    protected $table = 'content_pages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['title', 'eyebrow', 'summary', 'body', 'meta_title', 'meta_description', 'status'];

    public function published(): self
    {
        return $this->where('status', 'published');
    }
}
