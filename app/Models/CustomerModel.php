<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'business_name', 'email', 'phone', 'notes'];
}
