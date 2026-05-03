<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Exceptions\BusinessRuleException;
use CodeIgniter\API\ResponseTrait;
use Config\Services;

class Pricing extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    /**
     * Giá đơn vị áp dụng cho khách + SP (giá riêng hoặc giá bán mặc định).
     */
    public function line(): \CodeIgniter\HTTP\ResponseInterface
    {
        $cid = (int) $this->request->getGet('customer_id');
        $pid = (int) $this->request->getGet('product_id');
        if ($cid < 1 || $pid < 1) {
            return $this->failValidationErrors([
                'query' => 'Thiếu customer_id hoặc product_id hợp lệ.',
            ]);
        }

        try {
            $unit = Services::customerService()->resolveUnitPrice($cid, $pid);
        } catch (BusinessRuleException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->respond([
            'data' => [
                'unit_price' => $unit,
            ],
        ]);
    }
}
