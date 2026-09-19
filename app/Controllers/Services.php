<?php

namespace App\Controllers;

use App\Models\ServiceModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Services extends BaseController
{
    public function index(): string
    {
        return view('services/index', $this->publicSiteData() + [
            'services' => (new ServiceModel())->published()->findAll(),
        ]);
    }

    public function show(string $slug): string
    {
        $service = (new ServiceModel())->published()->where('slug', $slug)->first();
        if ($service === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('services/show', $this->publicSiteData() + [
            'service' => $service,
        ]);
    }
}
