<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    public const STATUSES = [
        'awaiting_deposit' => 'Awaiting deposit',
        'scheduled' => 'Scheduled',
        'in_progress' => 'In progress',
        'awaiting_client_feedback' => 'Awaiting client feedback',
        'revisions' => 'Revisions',
        'awaiting_final_payment' => 'Awaiting final payment',
        'complete' => 'Complete',
        'canceled' => 'Canceled',
    ];

    public const ACTIVE_STATUSES = [
        'awaiting_deposit', 'scheduled', 'in_progress', 'awaiting_client_feedback',
        'revisions', 'awaiting_final_payment',
    ];

    protected $table = 'projects';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'project_number', 'quote_id', 'customer_id', 'name', 'start_date', 'due_date',
        'status', 'notes', 'customer_update',
    ];

    public function withRelationships(): self
    {
        return $this->select('projects.*, customers.name AS customer_name, customers.business_name AS customer_business_name, quotes.quote_number, quotes.currency_code, quotes.total')
            ->join('customers', 'customers.id = projects.customer_id')
            ->join('quotes', 'quotes.id = projects.quote_id');
    }
}
