<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Admin\Concerns\NoHtmlResourceForms;
use App\Controllers\BaseController;
use App\Services\DriverCrudService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Validation;

class Drivers extends BaseController
{
    use NoHtmlResourceForms;
    use ResponseTrait;

    protected $format = 'json';

    private DriverCrudService $service;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->service = DriverCrudService::make();
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
            return $this->failNotFound('Không tìm thấy tài xế.');
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
        if (! $this->validateData($payload, $validation->driverCreate)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $insert = [
            'name'          => $payload['name'],
            'license_plate' => $payload['license_plate'],
            'status'        => $payload['status'] ?? 'available',
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
            return $this->failNotFound('Không tìm thấy tài xế.');
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        if (! is_array($payload)) {
            return $this->fail('Payload không hợp lệ.', 400);
        }

        $rules = [
            'name'          => 'permit_empty|max_length[191]',
            'license_plate' => 'permit_empty|max_length[32]',
            'status'        => 'permit_empty|in_list[available,busy]',
        ];
        if (! $this->validateData($payload, $rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $update = [];
        foreach (['name', 'license_plate', 'status'] as $f) {
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
            return $this->failNotFound('Không tìm thấy tài xế.');
        }

        try {
            $this->service->delete($idInt);
        } catch (DatabaseException $e) {
            return $this->fail('Không xóa được (còn đơn hàng tham chiếu).', 409);
        }

        return $this->respondDeleted(['id' => $idInt]);
    }
}
