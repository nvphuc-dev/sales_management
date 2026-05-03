<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;

class Dashboard extends BaseController
{
    protected $helpers = ['url'];

    public function index(): string
    {
        $productModel = model(ProductModel::class);
        $lowStockCount = (int) $productModel->where('stock_quantity <', 5)->countAllResults();
        $lowStockRows  = $productModel->where('stock_quantity <', 5)
            ->orderBy('stock_quantity', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll(30);

        return view('admin/dashboard/index', [
            'title'         => 'Tổng quan',
            'navActive'     => 'dashboard',
            'lowStockRows'  => $lowStockRows,
            'lowStockCount' => $lowStockCount,
        ]);
    }
}
