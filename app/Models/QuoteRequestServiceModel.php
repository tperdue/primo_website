<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteRequestServiceModel extends Model
{
    protected $table = 'quote_request_services';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'quote_request_id', 'service_id', 'service_name', 'service_slug', 'service_summary',
        'service_description', 'starting_price', 'currency_code', 'show_price', 'sort_order',
    ];
}
