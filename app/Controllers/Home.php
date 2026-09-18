<?php

namespace App\Controllers;

use App\Models\BusinessSettingsModel;
use App\Models\ServiceModel;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home', [
            'business' => (new BusinessSettingsModel())->find(1),
            'services' => (new ServiceModel())->published()->findAll(3),
        ]);
    }
}
