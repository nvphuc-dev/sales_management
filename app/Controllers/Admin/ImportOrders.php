<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\NoHtmlResourceForms;
use App\Controllers\BaseController;
use App\Exceptions\BusinessRuleException;
use App\Services\ImportOrderReadService;
use CodeIgniter\API\ResponseTrait;
use Config\Services;
use Config\Validation;

class ImportOrders extends BaseController
{
    use NoHtmlResourceForms;
    use ResponseTrait;

    protected $format = 'json';

    private ImportOrderReadService $read;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->read = ImportOrderReadService::make();
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
            return $this->failNotFound('Không tìm thấy phiếu nhập.');
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
        if (! $this->validateData($payload, $validation->importOrderCreate)) {
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
                'unit_price' => (string) $line['unit_price'],
            ];
        }

        try {
            $newId = Services::importOrderService()->createImportOrder([
                'code'        => (string) $payload['code'],
                'supplier_id' => (int) $payload['supplier_id'],
                'notes'       => $payload['notes'] ?? null,
                'items'       => $lines,
            ]);
        } catch (BusinessRuleException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        $bundle = $this->read->findWithItems($newId);

        return $this->respondCreated($bundle ?? ['id' => $newId]);
    }

    public function update(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->fail('Phiếu nhập không cho sửa/xóa qua API (giai đoạn 3).', 405);
    }

    public function delete(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->fail('Phiếu nhập không cho sửa/xóa qua API (giai đoạn 3).', 405);
    }
}
