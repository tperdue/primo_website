<?php

namespace App\Models;

use CodeIgniter\Model;

class BusinessSettingsModel extends Model
{
    protected $table = 'business_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = false;
    protected $useTimestamps = true;
    protected $createdField = '';
    protected $updatedField = 'updated_at';
    protected $allowedFields = ['business_name', 'tagline', 'description', 'contact_email', 'notification_email'];
}
