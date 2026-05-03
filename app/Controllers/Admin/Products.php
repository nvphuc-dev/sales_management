<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\NoHtmlResourceForms;
use App\Controllers\BaseController;
use App\Exceptions\BusinessRuleException;
use App\Services\ProductCatalogService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Validation;

class Products extends BaseController
{
    use NoHtmlResourceForms;
    use ResponseTrait;

    protected $format = 'json';

    private ProductCatalogService $catalog;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->catalog = ProductCatalogService::make();
    }

    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $page   = max(1, (int) $this->request->getGet('page') ?: 1);
        $search = $this->request->getGet('q');
        $search = is_string($search) ? $search : null;
        $per    = min(100, max(1, (int) $this->request->getGet('per_page') ?: 20));

        $result = $this->catalog->paginate($page, $per, $search);
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
        $row = $this->catalog->find((int) $id);
        if ($row === null) {
            return $this->failNotFound('Không tìm thấy sản phẩm.');
        }

        return $this->respond(['data' => $row]);
    }

    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        if (! is_array($payload)) {
            return $this->fail('Payload không hợp lệ.', 400);
        }

        $validation = config(Validation::class);
        if (! $this->validateData($payload, $validation->productCreate)) {
            return $this->failValidationErrors($this->validator->getErrors());
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
            $newId = $this->catalog->create($insert);
        } catch (DatabaseException $e) {
            return $this->fail($e->getMessage(), 409);
        }

        return $this->respondCreated(['id' => $newId, 'data' => $this->catalog->find($newId)]);
    }

    public function update(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $idInt = (int) $id;
        if ($idInt < 1 || $this->catalog->find($idInt) === null) {
            return $this->failNotFound('Không tìm thấy sản phẩm.');
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        if (! is_array($payload)) {
            return $this->fail('Payload không hợp lệ.', 400);
        }

        $rules = [
            'name'            => 'permit_empty|max_length[191]',
            'sku'             => "permit_empty|max_length[100]|is_unique[products.sku,id,{$idInt}]",
            'purchase_price'  => 'permit_empty|decimal|greater_than_equal_to[0]',
            'selling_price'   => 'permit_empty|decimal|greater_than_equal_to[0]',
            'stock_quantity'  => 'permit_empty|integer',
            'display_order'   => 'permit_empty|integer',
            'status'          => 'permit_empty|in_list[active,inactive]',
        ];

        if (! $this->validateData($payload, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $update = array_filter([
            'name'            => $payload['name'] ?? null,
            'sku'             => $payload['sku'] ?? null,
            'purchase_price'  => isset($payload['purchase_price']) ? (string) $payload['purchase_price'] : null,
            'selling_price'   => isset($payload['selling_price']) ? (string) $payload['selling_price'] : null,
            'stock_quantity'  => isset($payload['stock_quantity']) ? (int) $payload['stock_quantity'] : null,
            'display_order'   => isset($payload['display_order']) ? (int) $payload['display_order'] : null,
            'status'          => $payload['status'] ?? null,
        ], static fn ($v) => $v !== null);

        if ($update === []) {
            return $this->respond(['data' => $this->catalog->find($idInt)]);
        }

        try {
            $this->catalog->update($idInt, $update);
        } catch (DatabaseException $e) {
            return $this->fail($e->getMessage(), 409);
        }

        return $this->respond(['data' => $this->catalog->find($idInt)]);
    }

    public function delete(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $idInt = (int) $id;
        if ($idInt < 1 || $this->catalog->find($idInt) === null) {
            return $this->failNotFound('Không tìm thấy sản phẩm.');
        }

        try {
            $this->catalog->delete($idInt);
        } catch (DatabaseException $e) {
            return $this->fail('Không xóa được (còn tham chiếu trong hệ thống).', 409);
        }

        return $this->respondDeleted(['id' => $idInt]);
    }
}
