<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->setAutoRoute(false);
service('auth')->routes($routes, ['only' => ['login']]);
$routes->post('logout', '\CodeIgniter\Shield\Controllers\LoginController::logoutAction', ['as' => 'logout']);
$routes->group('admin', ['filter' => ['session', 'group:admin']], static function ($routes): void {
    $routes->get('/', 'Admin\BusinessSettings::edit');
    $routes->get('settings', 'Admin\BusinessSettings::edit');
    $routes->post('settings', 'Admin\BusinessSettings::update');
});
