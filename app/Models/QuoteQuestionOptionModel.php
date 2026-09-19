<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteQuestionOptionModel extends Model
{
    protected $table = 'quote_question_options';
    protected $returnType = 'array';
    protected $allowedFields = ['question_id', 'label', 'sort_order'];
}
