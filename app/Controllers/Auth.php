<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use Config\Validation;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function loginForm(): \CodeIgniter\HTTP\RedirectResponse|string
    {
        if (session()->get('user_id')) {
            return redirect()->to(site_url('admin'));
        }

        return view('auth/login', [
            'title' => 'Đăng nhập',
        ]);
    }

    public function attemptLogin(): \CodeIgniter\HTTP\RedirectResponse
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, config(Validation::class)->loginAttempt)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = (string) $payload['username'];
        $password  = (string) $payload['password'];

        $user = model(UserModel::class)->findActiveByUsername($username);
        if ($user === null || ! password_verify($password, (string) $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Sai tên đăng nhập hoặc mật khẩu.');
        }

        session()->regenerate(true);
        session()->set([
            'user_id'   => (int) $user['id'],
            'username'  => (string) $user['username'],
            'full_name' => (string) $user['full_name'],
            'role'      => (string) $user['role'],
        ]);

        return redirect()->to(site_url('admin'))->with('success', 'Đăng nhập thành công.');
    }

    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->destroy();

        return redirect()->to(site_url('auth/login'))->with('success', 'Đã đăng xuất.');
    }
}
