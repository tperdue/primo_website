<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteRequestModel extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'reviewing' => 'Reviewing',
        'needs_information' => 'Needs information',
        'qualified' => 'Qualified',
        'not_qualified' => 'Not qualified',
        'quote_preparing' => 'Quote preparing',
        'quote_sent' => 'Quote sent',
        'accepted' => 'Accepted',
        'declined' => 'Declined',
        'closed' => 'Closed',
    ];

    protected $table = 'quote_requests';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'reference_number', 'name', 'company', 'email', 'phone', 'preferred_contact',
        'project_summary', 'desired_completion_date', 'budget_range', 'additional_notes',
        'status', 'internal_notes', 'owner_notification_status', 'customer_notification_status',
        'submitted_at',
    ];
}
