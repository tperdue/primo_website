<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerPortalInvitationModel extends Model
{
    protected $table = 'customer_portal_invitations';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['customer_id', 'token_hash', 'expires_at', 'used_at'];
}
