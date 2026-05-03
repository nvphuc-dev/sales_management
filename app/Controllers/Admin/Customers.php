<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\NoHtmlResourceForms;
use App\Controllers\BaseController;
use App\Services\CustomerCrudService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Validation;

class Customers extends BaseController
{
    use NoHtmlResourceForms;
    use ResponseTrait;

    protected $format = 'json';

    private CustomerCrudService $service;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->service = CustomerCrudService::make();
    }

    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $page   = max(1, (int) $this->request->getGet('page') ?: 1);
        $search = $this->request->getGet('q');
        $search = is_string($search) ? $search : null;
        $per    = min(100, max(1, (int) $this->request->getGet('per_page') ?: 20));

        $result = $this->service->paginate($page, $per, $search);
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
        $row = $this->service->find((int) $id);
        if ($row === null) {
            return $this->failNotFound('Không tìm thấy khách hàng.');
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
        if (! $this->validateData($payload, $validation->customerCreate)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $insert = [
            'name'    => $payload['name'],
            'phone'   => $payload['phone'] ?? null,
            'email'   => $payload['email'] ?? null,
            'address' => $payload['address'] ?? null,
        ];

        try {
            $newId = $this->service->create($insert);
        } catch (DatabaseException $e) {
            return $this->fail($e->getMessage(), 409);
        }

        return $this->respondCreated(['id' => $newId, 'data' => $this->service->find($newId)]);
    }

    public function update(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $idInt = (int) $id;
        if ($idInt < 1 || $this->service->find($idInt) === null) {
            return $this->failNotFound('Không tìm thấy khách hàng.');
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        if (! is_array($payload)) {
            return $this->fail('Payload không hợp lệ.', 400);
        }

        $rules = [
            'name'    => 'permit_empty|max_length[191]',
            'phone'   => 'permit_empty|vn_phone10',
            'email'   => 'permit_empty|valid_email|max_length[191]',
            'address' => 'permit_empty|max_length[2000]',
        ];
        if (! $this->validateData($payload, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $update = [];
        foreach (['name', 'phone', 'email', 'address'] as $f) {
            if (array_key_exists($f, $payload)) {
                $update[$f] = $payload[$f];
            }
        }

        if ($update === []) {
            return $this->respond(['data' => $this->service->find($idInt)]);
        }

        try {
            $this->service->update($idInt, $update);
        } catch (DatabaseException $e) {
            return $this->fail($e->getMessage(), 409);
        }

        return $this->respond(['data' => $this->service->find($idInt)]);
    }

    public function delete(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $idInt = (int) $id;
        if ($idInt < 1 || $this->service->find($idInt) === null) {
            return $this->failNotFound('Không tìm thấy khách hàng.');
        }

        try {
            $this->service->delete($idInt);
        } catch (DatabaseException $e) {
            return $this->fail('Không xóa được (còn đơn hàng hoặc tham chiếu khác).', 409);
        }

        return $this->respondDeleted(['id' => $idInt]);
    }
}
