<?php

declare(strict_types=1);

namespace App\Controllers\Admin\View;

use App\Exceptions\BusinessRuleException;
use App\Services\ImportOrderReadService;
use App\Services\SupplierCatalogService;
use Config\Services;
use Config\Validation;

class ImportOrders extends BasePageController
{
    public function index(): string
    {
        $page   = max(1, (int) $this->request->getGet('page') ?: 1);
        $search = $this->request->getGet('q');
        $search = is_string($search) ? $search : null;
        $result = ImportOrderReadService::make()->paginate($page, 20, $search);

        return $this->page('admin/import_orders/index', [
            'title'     => 'Phiếu nhập',
            'navActive' => 'import-orders',
            'rows'      => $result['data'],
            'pager'     => $result['pager'],
            'search'    => $search,
        ]);
    }

    public function show(?string $id = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        $b  = ImportOrderReadService::make()->findWithItems($id);
        if ($b === null) {
            return redirect()->to(site_url('admin/view/import-orders'))->with('error', 'Không tìm thấy phiếu.');
        }

        return $this->page('admin/import_orders/show', [
            'title'     => 'Chi tiết phiếu nhập #' . $id,
            'navActive' => 'import-orders',
            'bundle'    => $b,
        ]);
    }

    public function formNew(): string
    {
        $suppliers = SupplierCatalogService::make()->allForDropdown();

        return $this->page('admin/import_orders/form', [
            'title'     => 'Tạo phiếu nhập',
            'navActive' => 'import-orders',
            'suppliers' => $suppliers,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, config(Validation::class)->importOrderWebHeader)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $raw = $this->request->getPost('items_json');
        if (! is_string($raw)) {
            return redirect()->back()->withInput()->with('error', 'Thiếu dữ liệu dòng hàng.');
        }

        $items = json_decode($raw, true);
        if (! is_array($items) || $items === []) {
            return redirect()->back()->withInput()->with('error', 'Dòng hàng không hợp lệ.');
        }

        $lines = [];
        foreach ($items as $line) {
            if (! is_array($line)) {
                continue;
            }

            $lines[] = [
                'product_id' => (int) ($line['product_id'] ?? 0),
                'quantity'   => (int) ($line['quantity'] ?? 0),
                'unit_price' => (string) ($line['unit_price'] ?? '0'),
            ];
        }

        if (! $this->validateData(['items' => $lines], ['items' => 'import_items_basic'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            Services::importOrderService()->createImportOrder([
                'code'        => (string) $payload['code'],
                'supplier_id' => (int) $payload['supplier_id'],
                'notes'       => $payload['notes'] ?? null,
                'items'       => $lines,
            ]);
        } catch (BusinessRuleException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/view/import-orders'))->with('success', 'Đã lưu phiếu nhập.');
    }
}
