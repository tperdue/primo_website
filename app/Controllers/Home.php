<?php

namespace App\Controllers;

use App\Models\BusinessSettingsModel;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home', ['business' => (new BusinessSettingsModel())->find(1)]);
    }
}
