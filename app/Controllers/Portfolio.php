<?php

namespace App\Controllers;

use App\Models\PortfolioProjectModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Portfolio extends BaseController
{
    public function index(): string
    {
        return view('portfolio/index', $this->publicSiteData() + [
            'projects' => (new PortfolioProjectModel())->published()->withImage()->findAll(),
        ]);
    }

    public function show(string $slug): string
    {
        $project = (new PortfolioProjectModel())->published()->withImage()->where('portfolio_projects.slug', $slug)->first();
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = new PortfolioProjectModel();

        $gallery = array_values(array_filter(
            $model->gallery((int) $project['id']),
            static fn (array $image): bool => (int) $image['id'] !== (int) $project['featured_media_id'],
        ));

        return view('portfolio/show', $this->publicSiteData() + [
            'project' => $project,
            'gallery' => $gallery,
            'relatedServices' => $model->relatedServices((int) $project['id']),
        ]);
    }
}
