<?php

namespace App\Models;

use CodeIgniter\Model;

class MediaAssetModel extends Model
{
    protected $table = 'media_assets';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = ['path', 'alt_text', 'mime_type', 'byte_size'];

    /** @return array{featured_projects: int, project_galleries: int, services: int, total: int} */
    public function usage(int $id): array
    {
        $db = db_connect();
        $featuredProjects = $db->table('portfolio_projects')->where('featured_media_id', $id)->countAllResults();
        $projectGalleries = $db->table('portfolio_project_media')->where('media_id', $id)->countAllResults();
        $services = $db->table('service_media')->where('media_id', $id)->countAllResults();

        return [
            'featured_projects' => $featuredProjects,
            'project_galleries' => $projectGalleries,
            'services' => $services,
            'total' => $featuredProjects + $projectGalleries + $services,
        ];
    }
}
