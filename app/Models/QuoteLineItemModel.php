<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteLineItemModel extends Model
{
    protected $table = 'quote_line_items';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['quote_id', 'description', 'details', 'quantity', 'unit_price', 'line_total', 'sort_order'];
}
