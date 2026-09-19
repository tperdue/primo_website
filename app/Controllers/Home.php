<?php

namespace App\Controllers;

use App\Models\BusinessSettingsModel;
use App\Models\PortfolioProjectModel;
use App\Models\ServiceModel;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home', [
            'business' => (new BusinessSettingsModel())->find(1),
            'services' => (new ServiceModel())->published()->findAll(3),
            'featuredProjects' => (new PortfolioProjectModel())->published()->where('is_featured', 1)->withImage()->findAll(2),
        ]);
    }
}
