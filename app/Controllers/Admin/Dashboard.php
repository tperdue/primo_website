<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessSettingsModel;
use App\Models\ContactSubmissionModel;
use App\Models\PortfolioProjectModel;
use App\Models\ServiceModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $model = new ServiceModel();

        return view('admin/dashboard', [
            'businessName' => (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio',
            'totalServices' => $model->countAllResults(),
            'publishedServices' => (new ServiceModel())->where('status', 'published')->countAllResults(),
            'draftServices' => (new ServiceModel())->where('status', 'draft')->countAllResults(),
            'recentServices' => (new ServiceModel())->orderBy('updated_at', 'DESC')->findAll(5),
            'portfolioTotal' => (new PortfolioProjectModel())->countAllResults(),
            'portfolioPublished' => (new PortfolioProjectModel())->where('status', 'published')->countAllResults(),
            'recentProjects' => (new PortfolioProjectModel())->orderBy('updated_at', 'DESC')->findAll(5),
            'newContactCount' => (new ContactSubmissionModel())->where('status', 'new')->countAllResults(),
            'recentContacts' => (new ContactSubmissionModel())->orderBy('created_at', 'DESC')->findAll(5),
        ]);
    }
}
