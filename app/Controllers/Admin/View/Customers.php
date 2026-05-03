<?php

declare(strict_types=1);

namespace App\Controllers\Admin\View;

use App\Services\CustomerCrudService;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Validation;

class Customers extends BasePageController
{
    public function index(): string
    {
        $page   = max(1, (int) $this->request->getGet('page') ?: 1);
        $search = $this->request->getGet('q');
        $search = is_string($search) ? $search : null;
        $svc    = CustomerCrudService::make();
        $result = $svc->paginate($page, 20, $search);

        return $this->page('admin/customers/index', [
            'title'     => 'Khách hàng',
            'navActive' => 'customers',
            'rows'      => $result['data'],
            'pager'     => $result['pager'],
            'search'    => $search,
        ]);
    }

    public function formNew(): string
    {
        return $this->page('admin/customers/form', [
            'title'     => 'Thêm khách hàng',
            'navActive' => 'customers',
            'record'    => null,
        ]);
    }

    public function formEdit(?string $id = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id  = (int) $id;
        $row = CustomerCrudService::make()->find($id);
        if ($row === null) {
            return redirect()->to(site_url('admin/view/customers'))->with('error', 'Không tìm thấy khách hàng.');
        }

        return $this->page('admin/customers/form', [
            'title'     => 'Sửa khách hàng',
            'navActive' => 'customers',
            'record'    => $row,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, config(Validation::class)->customerCreate)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            CustomerCrudService::make()->create([
                'name'    => $payload['name'],
                'phone'   => $payload['phone'] ?? null,
                'email'   => $payload['email'] ?? null,
                'address' => $payload['address'] ?? null,
            ]);
        } catch (DatabaseException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/view/customers'))->with('success', 'Đã thêm khách hàng.');
    }

    public function update(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id  = (int) $id;
        $svc = CustomerCrudService::make();
        if ($svc->find($id) === null) {
            return redirect()->to(site_url('admin/view/customers'))->with('error', 'Không tìm thấy.');
        }

        $payload = $this->request->getPost();
        $rules   = [
            'name'    => 'permit_empty|max_length[191]',
            'phone'   => 'permit_empty|vn_phone10',
            'email'   => 'permit_empty|valid_email|max_length[191]',
            'address' => 'permit_empty|max_length[2000]',
        ];
        if (! $this->validateData($payload, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $update = [];
        foreach (['name', 'phone', 'email', 'address'] as $f) {
            if (array_key_exists($f, $payload)) {
                $update[$f] = $payload[$f];
            }
        }

        if ($update !== []) {
            try {
                $svc->update($id, $update);
            } catch (DatabaseException $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        return redirect()->to(site_url('admin/view/customers'))->with('success', 'Đã cập nhật khách hàng.');
    }

    public function destroy(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        try {
            CustomerCrudService::make()->delete($id);
        } catch (DatabaseException $e) {
            return redirect()->back()->with('error', 'Không xóa được (còn đơn hàng).');
        }

        return redirect()->to(site_url('admin/view/customers'))->with('success', 'Đã xóa khách hàng.');
    }
}
