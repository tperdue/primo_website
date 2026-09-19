<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteVersionModel extends Model
{
    protected $table = 'quote_versions';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $updatedField = '';
    protected $allowedFields = ['quote_id', 'version_number', 'snapshot_json'];
}
