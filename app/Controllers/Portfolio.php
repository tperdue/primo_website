<?php

namespace App\Controllers;

use App\Models\BusinessSettingsModel;
use App\Models\PortfolioProjectModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Portfolio extends BaseController
{
    public function index(): string
    {
        return view('portfolio/index', [
            'business' => (new BusinessSettingsModel())->find(1),
            'projects' => (new PortfolioProjectModel())->published()->withImage()->findAll(),
        ]);
    }

    public function show(string $slug): string
    {
        $project = (new PortfolioProjectModel())->published()->withImage()->where('portfolio_projects.slug', $slug)->first();
        if ($project === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('portfolio/show', [
            'business' => (new BusinessSettingsModel())->find(1),
            'project' => $project,
        ]);
    }
}
