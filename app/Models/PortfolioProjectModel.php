<?php

namespace App\Models;

use CodeIgniter\Model;

class PortfolioProjectModel extends Model
{
    protected $table = 'portfolio_projects';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'slug', 'title', 'client_name', 'summary', 'challenge', 'solution',
        'project_date', 'featured_media_id', 'status', 'is_featured', 'sort_order',
    ];

    public function published(): self
    {
        return $this->where('portfolio_projects.status', 'published')
            ->orderBy('portfolio_projects.sort_order', 'ASC')
            ->orderBy('portfolio_projects.id', 'DESC');
    }

    public function withImage(): self
    {
        return $this->select('portfolio_projects.*, media_assets.path AS image_path, media_assets.alt_text AS image_alt')
            ->join('media_assets', 'media_assets.id = portfolio_projects.featured_media_id', 'left');
    }
}
