<?php

namespace App\Models;

use CodeIgniter\Model;

class PortfolioMediaModel extends Model
{
    protected $table = 'portfolio_project_media';
    protected $returnType = 'array';
    protected $allowedFields = ['project_id', 'media_id', 'sort_order'];
}
