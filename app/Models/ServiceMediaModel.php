<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceMediaModel extends Model
{
    protected $table = 'service_media';
    protected $primaryKey = 'service_id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $allowedFields = ['service_id', 'media_id'];
}
