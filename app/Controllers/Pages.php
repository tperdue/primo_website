<?php

namespace App\Controllers;

use App\Models\ContentPageModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Pages extends BaseController
{
    public function show(string $slug): string
    {
        $page = (new ContentPageModel())->published()->where('slug', $slug)->first();
        if ($page === null || ! in_array($slug, ['about', 'privacy', 'terms'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/show', $this->publicSiteData() + ['page' => $page]);
    }
}
