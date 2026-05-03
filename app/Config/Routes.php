<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('admin', static function ($routes): void {
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->get('dashboard', 'Admin\Dashboard::index');
});
