<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'slug', 'name', 'summary', 'description', 'starting_price',
        'currency_code', 'show_price', 'status', 'sort_order',
    ];

    public function published(): self
    {
        return $this->where('status', 'published')->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC');
    }

    public function withImage(): self
    {
        return $this->select('services.*, media_assets.path AS image_path, media_assets.alt_text AS image_alt')
            ->join('service_media', 'service_media.service_id = services.id', 'left')
            ->join('media_assets', 'media_assets.id = service_media.media_id', 'left');
    }

    /** @return list<array<string, mixed>> */
    public function relatedProjects(int $serviceId): array
    {
        return db_connect()->table('portfolio_projects')
            ->select('portfolio_projects.*, media_assets.path AS image_path, media_assets.alt_text AS image_alt')
            ->join('portfolio_project_services', 'portfolio_project_services.project_id = portfolio_projects.id')
            ->join('media_assets', 'media_assets.id = portfolio_projects.featured_media_id', 'left')
            ->where('portfolio_project_services.service_id', $serviceId)
            ->where('portfolio_projects.status', 'published')
            ->orderBy('portfolio_projects.sort_order', 'ASC')
            ->orderBy('portfolio_projects.id', 'DESC')
            ->get()->getResultArray();
    }
}
