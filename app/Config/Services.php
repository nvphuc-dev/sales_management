<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function inventoryService(bool $getShared = true): \App\Services\InventoryService
    {
        if ($getShared) {
            return static::getSharedInstance('inventoryService');
        }

        return new \App\Services\InventoryService();
    }

    public static function customerService(bool $getShared = true): \App\Services\CustomerService
    {
        if ($getShared) {
            return static::getSharedInstance('customerService');
        }

        return new \App\Services\CustomerService();
    }

    public static function driverService(bool $getShared = true): \App\Services\DriverService
    {
        if ($getShared) {
            return static::getSharedInstance('driverService');
        }

        return new \App\Services\DriverService();
    }

    public static function paymentService(bool $getShared = true): \App\Services\PaymentService
    {
        if ($getShared) {
            return static::getSharedInstance('paymentService');
        }

        return new \App\Services\PaymentService();
    }

    public static function importOrderService(bool $getShared = true): \App\Services\ImportOrderService
    {
        if ($getShared) {
            return static::getSharedInstance('importOrderService');
        }

        return new \App\Services\ImportOrderService();
    }

    public static function orderService(bool $getShared = true): \App\Services\OrderService
    {
        if ($getShared) {
            return static::getSharedInstance('orderService');
        }

        return new \App\Services\OrderService();
    }
}
