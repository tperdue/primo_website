<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteRequestAnswerModel extends Model
{
    protected $table = 'quote_request_answers';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'quote_request_id', 'question_id', 'group_name', 'question_label', 'field_type',
        'answer_text', 'answer_json', 'sort_order',
    ];
}
