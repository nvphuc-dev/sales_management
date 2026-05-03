<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\NoHtmlResourceForms;
use App\Controllers\BaseController;
use App\Exceptions\BusinessRuleException;
use App\Services\OrderReadService;
use CodeIgniter\API\ResponseTrait;
use Config\Services;
use Config\Validation;

class Orders extends BaseController
{
    use NoHtmlResourceForms;
    use ResponseTrait;

    protected $format = 'json';

    private OrderReadService $read;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->read = OrderReadService::make();
    }

    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $page   = max(1, (int) $this->request->getGet('page') ?: 1);
        $search = $this->request->getGet('q');
        $search = is_string($search) ? $search : null;
        $per    = min(100, max(1, (int) $this->request->getGet('per_page') ?: 20));

        $result = $this->read->paginate($page, $per, $search);
        $pager  = $result['pager'];

        return $this->respond([
            'data'  => $result['data'],
            'pager' => [
                'currentPage' => $pager->getCurrentPage('default'),
                'pageCount'   => $pager->getPageCount('default'),
                'total'       => $pager->getTotal('default'),
                'perPage'     => $per,
            ],
        ]);
    }

    public function show(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $bundle = $this->read->findWithItems((int) $id);
        if ($bundle === null) {
            return $this->failNotFound('Không tìm thấy đơn hàng.');
        }

        return $this->respond($bundle);
    }

    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        if (! is_array($payload)) {
            return $this->fail('Payload không hợp lệ.', 400);
        }

        $validation = config(Validation::class);
        if (! $this->validateData($payload, $validation->orderCreate)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $items = $payload['items'];
        if (! is_array($items)) {
            return $this->fail('items phải là mảng.', 400);
        }

        $lines = [];
        foreach ($items as $line) {
            if (! is_array($line)) {
                return $this->fail('items: mỗi dòng phải là object.', 400);
            }

            $lines[] = [
                'product_id' => (int) $line['product_id'],
                'quantity'   => (int) $line['quantity'],
            ];
        }

        $driverId = isset($payload['driver_id']) ? (int) $payload['driver_id'] : 0;
        if ($driverId < 1) {
            $driverId = null;
        }

        try {
            $newId = Services::orderService()->createOrder([
                'order_code'     => (string) $payload['order_code'],
                'customer_id'    => (int) $payload['customer_id'],
                'driver_id'      => $driverId,
                'delivery_notes' => $payload['delivery_notes'] ?? null,
                'status'         => $payload['status'] ?? \App\Entities\SalesOrder::STATUS_PENDING,
                'items'          => $lines,
            ]);
        } catch (BusinessRuleException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        $bundle = $this->read->findWithItems($newId);

        return $this->respondCreated($bundle ?? ['id' => $newId]);
    }

    public function update(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $idInt = (int) $id;
        if ($idInt < 1) {
            return $this->failNotFound('Đơn không hợp lệ.');
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        if (! is_array($payload)) {
            return $this->fail('Payload không hợp lệ.', 400);
        }

        $validation = config(Validation::class);
        if (! $this->validateData($payload, $validation->orderItemsUpdate)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $items = $payload['items'];
        if (! is_array($items)) {
            return $this->fail('items phải là mảng.', 400);
        }

        $lines = [];
        foreach ($items as $line) {
            if (! is_array($line)) {
                return $this->fail('items: mỗi dòng phải là object.', 400);
            }

            $lines[] = [
                'product_id' => (int) $line['product_id'],
                'quantity'   => (int) $line['quantity'],
            ];
        }

        try {
            Services::orderService()->updateOrderItems($idInt, (int) $payload['customer_id'], $lines);
        } catch (BusinessRuleException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->respond($this->read->findWithItems($idInt));
    }

    public function delete(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $idInt = (int) $id;
        if ($idInt < 1) {
            return $this->failNotFound('Đơn không hợp lệ.');
        }

        try {
            Services::orderService()->deleteOrder($idInt);
        } catch (BusinessRuleException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->respondDeleted(['id' => $idInt]);
    }

    public function cancel(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $idInt = (int) $id;
        if ($idInt < 1) {
            return $this->failNotFound('Đơn không hợp lệ.');
        }

        try {
            Services::orderService()->cancelOrder($idInt);
        } catch (BusinessRuleException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->respond(['data' => $this->read->findWithItems($idInt)]);
    }

    public function complete(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $idInt = (int) $id;
        if ($idInt < 1) {
            return $this->failNotFound('Đơn không hợp lệ.');
        }

        try {
            Services::orderService()->markCompleted($idInt);
        } catch (BusinessRuleException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->respond(['data' => $this->read->findWithItems($idInt)]);
    }

    public function assignDriver(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $idInt = (int) $id;
        if ($idInt < 1) {
            return $this->failNotFound('Đơn không hợp lệ.');
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        if (! is_array($payload)) {
            return $this->fail('Payload không hợp lệ.', 400);
        }

        $rules = [
            'driver_id'      => 'required|is_natural_no_zero',
            'delivery_notes' => 'permit_empty|max_length[2000]',
        ];
        if (! $this->validateData($payload, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        try {
            Services::orderService()->assignDriver(
                $idInt,
                (int) $payload['driver_id'],
                isset($payload['delivery_notes']) ? (string) $payload['delivery_notes'] : null,
            );
        } catch (BusinessRuleException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->respond(['data' => $this->read->findWithItems($idInt)]);
    }

    public function addPayment(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $idInt = (int) $id;
        if ($idInt < 1) {
            return $this->failNotFound('Đơn không hợp lệ.');
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        if (! is_array($payload)) {
            return $this->fail('Payload không hợp lệ.', 400);
        }

        $validation = config(Validation::class);
        if (! $this->validateData($payload, $validation->orderPayment)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        if (empty($payload['customer_id'])) {
            return $this->failValidationErrors(['customer_id' => 'customer_id là bắt buộc.']);
        }

        try {
            Services::paymentService()->recordPayment(
                $idInt,
                (int) $payload['customer_id'],
                (string) $payload['amount'],
            );
        } catch (BusinessRuleException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->respond(['data' => $this->read->findWithItems($idInt)]);
    }
}
