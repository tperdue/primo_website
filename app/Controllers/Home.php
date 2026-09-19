<?php

namespace App\Controllers;

use App\Models\PortfolioProjectModel;
use App\Models\ServiceModel;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home', $this->publicSiteData() + [
            'services' => (new ServiceModel())->withImage()->published()->findAll(3),
            'featuredProjects' => (new PortfolioProjectModel())->published()->where('is_featured', 1)->withImage()->findAll(2),
        ]);
    }
}
