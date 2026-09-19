<?php

namespace App\Controllers;

use App\Models\ContentPageModel;
use App\Models\PortfolioProjectModel;
use App\Models\ServiceModel;
use CodeIgniter\HTTP\ResponseInterface;

class SiteFiles extends BaseController
{
    public function sitemap(): ResponseInterface
    {
        $urls = [['location' => base_url(), 'updated_at' => null]];
        foreach ((new ContentPageModel())->published()->findAll() as $page) {
            $urls[] = ['location' => base_url($page['slug']), 'updated_at' => $page['updated_at']];
        }
        foreach ((new ServiceModel())->published()->findAll() as $service) {
            $urls[] = ['location' => base_url('services/' . $service['slug']), 'updated_at' => $service['updated_at']];
        }
        foreach ((new PortfolioProjectModel())->published()->findAll() as $project) {
            $urls[] = ['location' => base_url('work/' . $project['slug']), 'updated_at' => $project['updated_at']];
        }
        $urls[] = ['location' => base_url('services'), 'updated_at' => null];
        $urls[] = ['location' => base_url('work'), 'updated_at' => null];
        $urls[] = ['location' => base_url('quote'), 'updated_at' => null];

        return $this->response->setContentType('application/xml')->setBody(view('site/sitemap', ['urls' => $urls]));
    }

    public function robots(): ResponseInterface
    {
        $body = "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /login\nSitemap: " . base_url('sitemap.xml') . "\n";

        return $this->response->setContentType('text/plain')->setBody($body);
    }
}
