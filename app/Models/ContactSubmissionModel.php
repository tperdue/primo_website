<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactSubmissionModel extends Model
{
    protected $table = 'contact_submissions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'email', 'phone', 'subject', 'message', 'status', 'notification_status'];
}
