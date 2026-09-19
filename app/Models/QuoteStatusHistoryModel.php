<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteStatusHistoryModel extends Model
{
    protected $table = 'quote_status_history';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $updatedField = '';
    protected $allowedFields = ['quote_id', 'from_status', 'to_status', 'note'];
}
