<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionGroupServiceModel extends Model
{
    protected $table = 'quote_question_group_services';
    protected $primaryKey = 'group_id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $allowedFields = ['group_id', 'service_id'];
}
