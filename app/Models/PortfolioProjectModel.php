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

    /** @return list<array<string, mixed>> */
    public function gallery(int $projectId): array
    {
        return db_connect()->table('portfolio_project_media')
            ->select('media_assets.*, portfolio_project_media.sort_order')
            ->join('media_assets', 'media_assets.id = portfolio_project_media.media_id')
            ->where('portfolio_project_media.project_id', $projectId)
            ->orderBy('portfolio_project_media.sort_order', 'ASC')
            ->orderBy('media_assets.id', 'ASC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function relatedServices(int $projectId): array
    {
        return db_connect()->table('portfolio_project_services')
            ->select('services.*')
            ->join('services', 'services.id = portfolio_project_services.service_id')
            ->where('portfolio_project_services.project_id', $projectId)
            ->where('services.status', 'published')
            ->orderBy('services.sort_order', 'ASC')
            ->orderBy('services.id', 'ASC')
            ->get()->getResultArray();
    }
}
