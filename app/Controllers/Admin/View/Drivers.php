<?php

declare(strict_types=1);

namespace App\Controllers\Admin\View;

use App\Services\DriverCrudService;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Validation;

class Drivers extends BasePageController
{
    public function index(): string
    {
        $page   = max(1, (int) $this->request->getGet('page') ?: 1);
        $search = $this->request->getGet('q');
        $search = is_string($search) ? $search : null;
        $svc    = DriverCrudService::make();
        $result = $svc->paginate($page, 20, $search);

        return $this->page('admin/drivers/index', [
            'title'     => 'Tài xế',
            'navActive' => 'drivers',
            'rows'      => $result['data'],
            'pager'     => $result['pager'],
            'search'    => $search,
        ]);
    }

    public function formNew(): string
    {
        return $this->page('admin/drivers/form', [
            'title'     => 'Thêm tài xế',
            'navActive' => 'drivers',
            'record'    => null,
        ]);
    }

    public function formEdit(?string $id = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id  = (int) $id;
        $row = DriverCrudService::make()->find($id);
        if ($row === null) {
            return redirect()->to(site_url('admin/view/drivers'))->with('error', 'Không tìm thấy.');
        }

        return $this->page('admin/drivers/form', [
            'title'     => 'Sửa tài xế',
            'navActive' => 'drivers',
            'record'    => $row,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, config(Validation::class)->driverCreate)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            DriverCrudService::make()->create([
                'name'          => $payload['name'],
                'license_plate' => $payload['license_plate'],
                'status'        => $payload['status'] ?? 'available',
            ]);
        } catch (DatabaseException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/view/drivers'))->with('success', 'Đã thêm tài xế.');
    }

    public function update(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id  = (int) $id;
        $svc = DriverCrudService::make();
        if ($svc->find($id) === null) {
            return redirect()->to(site_url('admin/view/drivers'))->with('error', 'Không tìm thấy.');
        }

        $payload = $this->request->getPost();
        $rules   = [
            'name'          => 'permit_empty|max_length[191]',
            'license_plate' => 'permit_empty|max_length[32]',
            'status'        => 'permit_empty|in_list[available,busy]',
        ];
        if (! $this->validateData($payload, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $update = [];
        foreach (['name', 'license_plate', 'status'] as $f) {
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

        return redirect()->to(site_url('admin/view/drivers'))->with('success', 'Đã cập nhật.');
    }

    public function destroy(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        try {
            DriverCrudService::make()->delete($id);
        } catch (DatabaseException $e) {
            return redirect()->back()->with('error', 'Không xóa được.');
        }

        return redirect()->to(site_url('admin/view/drivers'))->with('success', 'Đã xóa.');
    }
}
