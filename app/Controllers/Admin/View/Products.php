<?php

declare(strict_types=1);

namespace App\Controllers\Admin\View;

use App\Services\ProductCatalogService;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Validation;

class Products extends BasePageController
{
    public function index(): string
    {
        $page   = max(1, (int) $this->request->getGet('page') ?: 1);
        $search = $this->request->getGet('q');
        $search = is_string($search) ? $search : null;
        $svc    = ProductCatalogService::make();
        $result = $svc->paginate($page, 20, $search);

        return $this->page('admin/products/index', [
            'title'     => 'Sản phẩm',
            'navActive' => 'products',
            'rows'      => $result['data'],
            'pager'     => $result['pager'],
            'search'    => $search,
        ]);
    }

    public function formNew(): string
    {
        return $this->page('admin/products/form', [
            'title'     => 'Thêm sản phẩm',
            'navActive' => 'products',
            'record'    => null,
        ]);
    }

    public function formEdit(?string $id = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        $row = ProductCatalogService::make()->find($id);
        if ($row === null) {
            return redirect()->to(site_url('admin/view/products'))->with('error', 'Không tìm thấy sản phẩm.');
        }

        return $this->page('admin/products/form', [
            'title'     => 'Sửa sản phẩm',
            'navActive' => 'products',
            'record'    => $row,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $payload = $this->request->getPost();
        $v       = config(Validation::class);
        if (! $this->validateData($payload, $v->productCreate)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $insert = [
            'name'            => $payload['name'],
            'sku'             => $payload['sku'],
            'purchase_price'  => (string) ($payload['purchase_price'] ?? '0'),
            'selling_price'   => (string) ($payload['selling_price'] ?? '0'),
            'stock_quantity'  => (int) ($payload['stock_quantity'] ?? 0),
            'display_order'   => (int) ($payload['display_order'] ?? 0),
            'status'          => $payload['status'] ?? 'active',
        ];

        try {
            ProductCatalogService::make()->create($insert);
        } catch (DatabaseException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/view/products'))->with('success', 'Đã thêm sản phẩm.');
    }

    public function update(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id  = (int) $id;
        $svc = ProductCatalogService::make();
        if ($svc->find($id) === null) {
            return redirect()->to(site_url('admin/view/products'))->with('error', 'Không tìm thấy sản phẩm.');
        }

        $payload = $this->request->getPost();
        $rules   = [
            'name'            => 'permit_empty|max_length[191]',
            'sku'             => "permit_empty|max_length[100]|is_unique[products.sku,id,{$id}]",
            'purchase_price'  => 'permit_empty|decimal|greater_than_equal_to[0]',
            'selling_price'   => 'permit_empty|decimal|greater_than_equal_to[0]',
            'stock_quantity'  => 'permit_empty|integer',
            'display_order'   => 'permit_empty|integer',
            'status'          => 'permit_empty|in_list[active,inactive]',
        ];
        if (! $this->validateData($payload, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $update = [];
        foreach (['name', 'sku', 'purchase_price', 'selling_price', 'stock_quantity', 'display_order', 'status'] as $f) {
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

        return redirect()->to(site_url('admin/view/products'))->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        try {
            ProductCatalogService::make()->delete($id);
        } catch (DatabaseException $e) {
            return redirect()->back()->with('error', 'Không xóa được (còn tham chiếu).');
        }

        return redirect()->to(site_url('admin/view/products'))->with('success', 'Đã xóa sản phẩm.');
    }
}
