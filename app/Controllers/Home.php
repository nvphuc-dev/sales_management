<?php

declare(strict_types=1);

namespace App\Controllers;

class Home extends BaseController
{
    protected $helpers = ['url'];

    public function index(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (session()->get('user_id')) {
            return redirect()->to(site_url('admin'));
        }

        return redirect()->to(site_url('auth/login'));
    }
}
