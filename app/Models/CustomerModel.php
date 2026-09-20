<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id', 'name', 'business_name', 'email', 'normalized_email', 'phone', 'address_line_1',
        'address_line_2', 'city', 'region', 'postal_code', 'country', 'notes',
    ];
}
