<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AdminAttentionDashboard;
use App\Models\BusinessSettingsModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        return view('admin/dashboard', (new AdminAttentionDashboard())->data() + [
            'businessName' => (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio',
        ]);
    }
}
