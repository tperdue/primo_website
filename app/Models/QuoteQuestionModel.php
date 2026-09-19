<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteQuestionModel extends Model
{
    public const FIELD_TYPES = [
        'text' => 'Single-line text',
        'textarea' => 'Multi-line text',
        'number' => 'Number',
        'email' => 'Email',
        'phone' => 'Phone',
        'date' => 'Date',
        'select' => 'Dropdown',
        'radio' => 'Radio options',
        'checkboxes' => 'Checkboxes',
        'yes_no' => 'Yes / No',
        'file' => 'File upload',
    ];

    public const CHOICE_TYPES = ['select', 'radio', 'checkboxes'];

    protected $table = 'quote_questions';
    protected $returnType = 'array';
    protected $allowedFields = [
        'group_id', 'label', 'field_type', 'is_required', 'help_text', 'placeholder', 'is_active', 'sort_order',
    ];
    protected $useTimestamps = true;
}
