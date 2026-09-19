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

        return view('portfolio/show', $this->publicSiteData() + [
            'project' => $project,
        ]);
    }
}
