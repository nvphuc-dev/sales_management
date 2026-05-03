<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes): void {
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');

    $routes->resource('products', ['controller' => 'Products']);
    $routes->resource('customers', ['controller' => 'Customers']);
    $routes->resource('drivers', ['controller' => 'Drivers']);
    $routes->resource('import-orders', ['controller' => 'ImportOrders']);
    $routes->resource('orders', ['controller' => 'Orders']);

    $routes->post('orders/(:num)/cancel', 'Orders::cancel/$1');
    $routes->post('orders/(:num)/complete', 'Orders::complete/$1');
    $routes->post('orders/(:num)/assign-driver', 'Orders::assignDriver/$1');
    $routes->post('orders/(:num)/payments', 'Orders::addPayment/$1');
});

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes): void {
    $routes->get('search-products', 'Search::products');
    $routes->get('search-customers', 'Search::customers');
    $routes->get('search-drivers', 'Search::drivers');
});
