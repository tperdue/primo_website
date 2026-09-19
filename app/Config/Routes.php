<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('services', 'Services::index');
$routes->get('services/(:segment)', 'Services::show/$1');
$routes->get('work', 'Portfolio::index');
$routes->get('work/(:segment)', 'Portfolio::show/$1');
$routes->get('about', 'Pages::show/about');
$routes->get('contact', 'Contact::index', ['filter' => 'honeypot']);
$routes->post('contact', 'Contact::submit', ['filter' => 'honeypot']);
$routes->get('privacy', 'Pages::show/privacy');
$routes->get('terms', 'Pages::show/terms');
$routes->get('sitemap.xml', 'SiteFiles::sitemap');
$routes->get('robots.txt', 'SiteFiles::robots');
$routes->setAutoRoute(false);
service('auth')->routes($routes, ['only' => ['login']]);
$routes->post('logout', '\CodeIgniter\Shield\Controllers\LoginController::logoutAction', ['as' => 'logout']);
$routes->group('admin', ['filter' => ['session', 'group:admin']], static function ($routes): void {
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->get('settings', 'Admin\BusinessSettings::edit');
    $routes->post('settings', 'Admin\BusinessSettings::update');
    $routes->get('services', 'Admin\Services::index');
    $routes->get('services/new', 'Admin\Services::create');
    $routes->post('services', 'Admin\Services::store');
    $routes->get('services/(:num)/edit', 'Admin\Services::edit/$1');
    $routes->post('services/(:num)', 'Admin\Services::update/$1');
    $routes->get('portfolio', 'Admin\Portfolio::index');
    $routes->get('portfolio/new', 'Admin\Portfolio::create');
    $routes->post('portfolio', 'Admin\Portfolio::store');
    $routes->get('portfolio/(:num)/edit', 'Admin\Portfolio::edit/$1');
    $routes->post('portfolio/(:num)', 'Admin\Portfolio::update/$1');
    $routes->get('media', 'Admin\Media::index');
    $routes->post('media', 'Admin\Media::store');
    $routes->get('media/(:num)/edit', 'Admin\Media::edit/$1');
    $routes->post('media/(:num)', 'Admin\Media::update/$1');
    $routes->post('media/(:num)/delete', 'Admin\Media::delete/$1');
    $routes->get('pages', 'Admin\ContentPages::index');
    $routes->get('pages/(:num)/edit', 'Admin\ContentPages::edit/$1');
    $routes->post('pages/(:num)', 'Admin\ContentPages::update/$1');
    $routes->get('contacts', 'Admin\ContactSubmissions::index');
    $routes->get('contacts/(:num)', 'Admin\ContactSubmissions::show/$1');
    $routes->post('contacts/(:num)', 'Admin\ContactSubmissions::update/$1');
});
