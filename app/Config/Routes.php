<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('admin/view', ['namespace' => 'App\Controllers\Admin\View'], static function ($routes): void {
    $routes->get('products', 'Products::index');
    $routes->get('products/new', 'Products::formNew');
    $routes->post('products', 'Products::store');
    $routes->get('products/(:num)/edit', 'Products::formEdit/$1');
    $routes->post('products/(:num)', 'Products::update/$1');
    $routes->post('products/(:num)/delete', 'Products::destroy/$1');

    $routes->get('customers', 'Customers::index');
    $routes->get('customers/new', 'Customers::formNew');
    $routes->post('customers', 'Customers::store');
    $routes->get('customers/(:num)/edit', 'Customers::formEdit/$1');
    $routes->post('customers/(:num)', 'Customers::update/$1');
    $routes->post('customers/(:num)/delete', 'Customers::destroy/$1');

    $routes->get('drivers', 'Drivers::index');
    $routes->get('drivers/new', 'Drivers::formNew');
    $routes->post('drivers', 'Drivers::store');
    $routes->get('drivers/(:num)/edit', 'Drivers::formEdit/$1');
    $routes->post('drivers/(:num)', 'Drivers::update/$1');
    $routes->post('drivers/(:num)/delete', 'Drivers::destroy/$1');

    $routes->get('suppliers', 'Suppliers::index');
    $routes->get('suppliers/new', 'Suppliers::formNew');
    $routes->post('suppliers', 'Suppliers::store');
    $routes->get('suppliers/(:num)/edit', 'Suppliers::formEdit/$1');
    $routes->post('suppliers/(:num)', 'Suppliers::update/$1');
    $routes->post('suppliers/(:num)/delete', 'Suppliers::destroy/$1');

    $routes->get('import-orders', 'ImportOrders::index');
    $routes->get('import-orders/new', 'ImportOrders::formNew');
    $routes->post('import-orders', 'ImportOrders::store');
    $routes->get('import-orders/(:num)', 'ImportOrders::show/$1');

    $routes->get('orders', 'Orders::index');
    $routes->get('orders/new', 'Orders::formNew');
    $routes->post('orders', 'Orders::store');
    $routes->get('orders/(:num)', 'Orders::show/$1');
    $routes->post('orders/(:num)/payment', 'Orders::payment/$1');
    $routes->post('orders/(:num)/cancel', 'Orders::cancel/$1');
    $routes->post('orders/(:num)/complete', 'Orders::complete/$1');
    $routes->post('orders/(:num)/delete', 'Orders::delete/$1');
});

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
    $routes->get('line-price', 'Pricing::line');
});
