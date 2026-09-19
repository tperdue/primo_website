<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteQuestionGroupModel extends Model
{
    protected $table = 'quote_question_groups';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'description', 'is_active', 'sort_order'];
    protected $useTimestamps = true;
}
