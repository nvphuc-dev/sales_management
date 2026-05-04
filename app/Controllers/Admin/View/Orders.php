<?php

declare(strict_types=1);

namespace App\Controllers\Admin\View;

use App\Entities\SalesOrder;
use App\Exceptions\BusinessRuleException;
use App\Models\CompanySettingModel;
use App\Services\Money;
use App\Services\OrderReadService;
use Config\Services;
use Config\Validation;

class Orders extends BasePageController
{
    public function index(): string
    {
        $page   = max(1, (int) $this->request->getGet('page') ?: 1);
        $search = $this->request->getGet('q');
        $search = is_string($search) ? $search : null;
        $result = OrderReadService::make()->paginate($page, 20, $search);

        return $this->page('admin/orders/index', [
            'title'     => 'Đơn hàng',
            'navActive' => 'orders',
            'rows'      => $result['data'],
            'pager'     => $result['pager'],
            'search'    => $search,
        ]);
    }

    public function formNew(): string
    {
        return $this->page('admin/orders/form', [
            'title'     => 'Tạo đơn hàng',
            'navActive' => 'orders',
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, config(Validation::class)->orderWebHeader)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $raw = $this->request->getPost('items_json');
        if (! is_string($raw)) {
            return redirect()->back()->withInput()->with('error', 'Thiếu dòng sản phẩm.');
        }

        $items = json_decode($raw, true);
        if (! is_array($items) || $items === []) {
            return redirect()->back()->withInput()->with('error', 'Dòng đơn không hợp lệ.');
        }

        $lines = [];
        foreach ($items as $line) {
            if (! is_array($line)) {
                continue;
            }

            $lines[] = [
                'product_id' => (int) ($line['product_id'] ?? 0),
                'quantity'   => (int) ($line['quantity'] ?? 0),
            ];
        }

        if (! $this->validateData(['items' => $lines], ['items' => 'order_items_stock'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $driverId = (int) ($payload['driver_id'] ?? 0);
        if ($driverId < 1) {
            $driverId = null;
        }

        try {
            Services::orderService()->createOrder([
                'order_code'     => (string) $payload['order_code'],
                'customer_id'    => (int) $payload['customer_id'],
                'driver_id'      => $driverId,
                'delivery_notes' => $payload['delivery_notes'] ?? null,
                'status'         => $payload['status'] ?? SalesOrder::STATUS_PENDING,
                'items'          => $lines,
            ]);
        } catch (BusinessRuleException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/view/orders'))->with('success', 'Đã tạo đơn hàng.');
    }

    public function show(?string $id = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        $b  = OrderReadService::make()->findWithItems($id);
        if ($b === null) {
            return redirect()->to(site_url('admin/view/orders'))->with('error', 'Không tìm thấy đơn.');
        }

        $total    = Money::normalize($b['header']['total_amount']);
        $paid     = Money::normalize($b['paid']);
        $remain   = Money::sub($total, $paid);

        return $this->page('admin/orders/show', [
            'title'     => 'Đơn ' . $b['header']['order_code'],
            'navActive' => 'orders',
            'bundle'    => $b,
            'remaining' => $remain,
        ]);
    }

    /**
     * Trang in đơn (header lấy từ thông tin công ty đã cấu hình).
     */
    public function print(?string $id = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        $b  = OrderReadService::make()->findWithItems($id);
        if ($b === null) {
            return redirect()->to(site_url('admin/view/orders'))->with('error', 'Không tìm thấy đơn.');
        }

        $total  = Money::normalize($b['header']['total_amount']);
        $paid   = Money::normalize($b['paid']);
        $remain = Money::sub($total, $paid);

        return view('admin/orders/print', [
            'company'   => model(CompanySettingModel::class)->getSingletonRow(),
            'bundle'    => $b,
            'remaining' => $remain,
        ]);
    }

    public function payment(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, config(Validation::class)->orderPayment)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        if (empty($payload['customer_id'])) {
            return redirect()->back()->with('error', 'Thiếu customer_id.');
        }

        try {
            Services::paymentService()->recordPayment(
                $id,
                (int) $payload['customer_id'],
                (string) $payload['amount'],
            );
        } catch (BusinessRuleException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/view/orders/' . $id))->with('success', 'Đã ghi nhận thu tiền.');
    }

    public function cancel(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        try {
            Services::orderService()->cancelOrder($id);
        } catch (BusinessRuleException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/view/orders/' . $id))->with('success', 'Đã hủy đơn.');
    }

    public function complete(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        try {
            Services::orderService()->markCompleted($id);
        } catch (BusinessRuleException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/view/orders/' . $id))->with('success', 'Đã hoàn thành đơn.');
    }

    public function delete(?string $id = null): \CodeIgniter\HTTP\RedirectResponse
    {
        $id = (int) $id;
        try {
            Services::orderService()->deleteOrder($id);
        } catch (BusinessRuleException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(site_url('admin/view/orders'))->with('success', 'Đã xóa đơn.');
    }
}
