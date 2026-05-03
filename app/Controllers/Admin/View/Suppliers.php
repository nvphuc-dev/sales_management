<?php

declare(strict_types=1);

namespace App\Controllers\Admin\View;

use App\Services\SupplierCatalogService;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Validation;

class Suppliers extends BasePageController
{
    public function index(): string
    {
        $page   = max(1, (int) $this->request->getGet('page') ?: 1);
        $search = $this->request->getGet('q');
        $search = is_string($search) ? $search : null;
        $svc    = SupplierCatalogService::make();
        $result = $svc->paginate($page, 20, $search);

        return $this->page('admin/suppliers/index', [
            'title'     => 'Nhà cung cấp',
            'navActive' => 'suppliers',
            'rows'      => $result['data'],
            'pager'     => $result['pager'],
            'search'    => $search,
        ]);
    }

    public function formNew(): string
    {
        return $this->page('admin/suppliers/form', [
            'title'     => 'Thêm NCC',
            'navActive' => 'suppliers',
            'record'    => null,
        ]);
    }

    public function formEdit(?string $id = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id  = (int) $id;
        $row = SupplierCatalogService::make()->find($id);
        if ($row === null) {
            return redirect()->to(site_url('admin/view/suppliers'))->with('error', 'Không tìm thấy.');
        }

        return $this->page('admin/suppliers/form', [
            'title'     => 'Sửa NCC',
            'navActive' => 'suppliers',
            'record'    => $row,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, config(Validation::class)->supplierCreate)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            SupplierCatalogService::make()->create([
                'name'         => $payload['name'],
                'contact_info' => $payload['contact_info'] ?? null,
            ]);
        } catch (DatabaseException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/view/suppliers'))->with('success', 'Đã thêm NCC.');
    }

    public function update(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id  = (int) $id;
        $svc = SupplierCatalogService::make();
        if ($svc->find($id) === null) {
            return redirect()->to(site_url('admin/view/suppliers'))->with('error', 'Không tìm thấy.');
        }

        $payload = $this->request->getPost();
        $rules   = [
            'name'         => 'permit_empty|max_length[191]',
            'contact_info' => 'permit_empty|max_length[2000]',
        ];
        if (! $this->validateData($payload, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $update = [];
        foreach (['name', 'contact_info'] as $f) {
            if (array_key_exists($f, $payload)) {
                $update[$f] = $payload[$f];
            }
        }

        if ($update !== []) {
            $svc->update($id, $update);
        }

        return redirect()->to(site_url('admin/view/suppliers'))->with('success', 'Đã cập nhật.');
    }

    public function destroy(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        try {
            SupplierCatalogService::make()->delete($id);
        } catch (DatabaseException $e) {
            return redirect()->back()->with('error', 'Không xóa được (còn phiếu nhập).');
        }

        return redirect()->to(site_url('admin/view/suppliers'))->with('success', 'Đã xóa.');
    }
}
