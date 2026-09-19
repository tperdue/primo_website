<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteRequestModel extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'needs_information' => 'Needs more information from client',
        'ready_to_quote' => 'Ready to quote',
        'closed_quote_created' => 'Closed, Quote Created',
        'closed_wont_pursue' => "Closed, Won't pursue.",
    ];

    public const QUOTEABLE_STATUSES = ['ready_to_quote', 'closed_quote_created'];

    protected $table = 'quote_requests';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'reference_number', 'customer_id', 'name', 'company', 'email', 'normalized_email', 'phone', 'preferred_contact',
        'project_summary', 'desired_completion_date', 'budget_range', 'additional_notes',
        'status', 'internal_notes', 'owner_notification_status', 'customer_notification_status',
        'submitted_at',
    ];
}
