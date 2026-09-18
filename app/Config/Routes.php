<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('services', 'Services::index');
$routes->get('services/(:segment)', 'Services::show/$1');
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
});
