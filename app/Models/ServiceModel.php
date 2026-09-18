<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'slug', 'name', 'summary', 'description', 'starting_price',
        'currency_code', 'show_price', 'status', 'sort_order',
    ];

    public function published(): self
    {
        return $this->where('status', 'published')->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC');
    }
}
