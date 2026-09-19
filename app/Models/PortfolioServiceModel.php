<?php

namespace App\Models;

use CodeIgniter\Model;

class PortfolioServiceModel extends Model
{
    protected $table = 'portfolio_project_services';
    protected $returnType = 'array';
    protected $allowedFields = ['project_id', 'service_id'];
}
