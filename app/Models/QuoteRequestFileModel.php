<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteRequestFileModel extends Model
{
    protected $table = 'quote_request_files';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $updatedField = '';
    protected $allowedFields = [
        'quote_request_id', 'question_id', 'original_name', 'stored_name', 'relative_path',
        'mime_type', 'extension', 'size_bytes',
    ];
}
