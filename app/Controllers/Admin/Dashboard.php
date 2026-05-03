<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    protected $helpers = ['url'];

    public function index(): string
    {
        return view('admin/dashboard/index', [
            'title'     => 'Tổng quan',
            'navActive' => 'dashboard',
        ]);
    }
}
