<?php

declare(strict_types=1);

namespace App\Controllers\Admin\View;

use App\Models\UserModel;
use Config\Validation;

class Users extends BasePageController
{
    public function index(): string
    {
        $rows = model(UserModel::class)->orderBy('id', 'ASC')->findAll();

        return $this->page('admin/users/index', [
            'title'     => 'Người dùng',
            'navActive' => 'users',
            'rows'      => $rows,
        ]);
    }

    public function formNew(): string
    {
        return $this->page('admin/users/form', [
            'title'     => 'Thêm người dùng',
            'navActive' => 'users',
            'edit'      => null,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, config(Validation::class)->userAccountCreate)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(UserModel::class)->insert([
            'username'       => strtolower(trim((string) $payload['username'])),
            'password_hash'  => password_hash((string) $payload['password'], PASSWORD_DEFAULT),
            'full_name'      => (string) $payload['full_name'],
            'role'           => (string) $payload['role'],
            'is_active'      => 1,
        ]);

        return redirect()->to(site_url('admin/view/users'))->with('success', 'Đã tạo người dùng.');
    }

    public function formEdit(?string $id = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        $row = model(UserModel::class)->find($id);
        if ($row === null) {
            return redirect()->to(site_url('admin/view/users'))->with('error', 'Không tìm thấy người dùng.');
        }

        return $this->page('admin/users/form', [
            'title'     => 'Sửa người dùng',
            'navActive' => 'users',
            'edit'      => $row,
        ]);
    }

    public function update(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        $row = model(UserModel::class)->find($id);
        if ($row === null) {
            return redirect()->to(site_url('admin/view/users'))->with('error', 'Không tìm thấy người dùng.');
        }

        $payload = $this->request->getPost();
        if (! $this->validateData($payload, config(Validation::class)->userAccountUpdate)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newRole     = (string) $payload['role'];
        $newActive   = (int) ($payload['is_active'] ?? 0) === 1 ? 1 : 0;
        $soloAdmin   = $this->isSoleActiveAdmin($row, $id);
        $demoteAdmin = $soloAdmin && (
            $newRole !== UserModel::ROLE_ADMIN
            || $newActive === 0
        );
        if ($demoteAdmin) {
            return redirect()->back()->withInput()->with('error', 'Không thể bỏ vai trò hoặc khóa quản trị viên cuối cùng.');
        }

        $data = [
            'full_name' => (string) $payload['full_name'],
            'role'      => $newRole,
            'is_active' => $newActive,
        ];

        $pwd = (string) ($payload['password'] ?? '');
        if ($pwd !== '') {
            $data['password_hash'] = password_hash($pwd, PASSWORD_DEFAULT);
        }

        model(UserModel::class)->update($id, $data);

        return redirect()->to(site_url('admin/view/users'))->with('success', 'Đã cập nhật người dùng.');
    }

    public function deactivate(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        $uid = (int) session()->get('user_id');
        if ($id === $uid) {
            return redirect()->back()->with('error', 'Không thể vô hiệu hóa chính mình.');
        }

        $row = model(UserModel::class)->find($id);
        if ($row === null) {
            return redirect()->back()->with('error', 'Không tìm thấy người dùng.');
        }

        if ($this->isSoleActiveAdmin($row, $id)) {
            return redirect()->back()->with('error', 'Không thể vô hiệu quản trị viên cuối cùng.');
        }

        model(UserModel::class)->update($id, ['is_active' => 0]);

        return redirect()->to(site_url('admin/view/users'))->with('success', 'Đã vô hiệu hóa tài khoản.');
    }

    /**
     * @param array<string, mixed> $row
     */
    private function isSoleActiveAdmin(array $row, int $id): bool
    {
        if (($row['role'] ?? '') !== UserModel::ROLE_ADMIN || (int) ($row['is_active'] ?? 0) !== 1) {
            return false;
        }

        $others = (int) model(UserModel::class)
            ->where('role', UserModel::ROLE_ADMIN)
            ->where('is_active', 1)
            ->where('id !=', $id)
            ->countAllResults();

        return $others === 0;
    }
}
