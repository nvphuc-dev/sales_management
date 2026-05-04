<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\CustomerCrudService;
use App\Services\DriverCrudService;
use App\Services\ProductCatalogService;
use CodeIgniter\API\ResponseTrait;

class Search extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';

    public function products(): \CodeIgniter\HTTP\ResponseInterface
    {
        $term = $this->request->getGet('q') ?? $this->request->getGet('term');
        $term = is_string($term) ? trim($term) : '';

        $rows = ProductCatalogService::make()->searchForSelect2($term, 30);

        return $this->respond([
            'results'    => $rows,
            'pagination' => ['more' => false],
        ]);
    }

    public function customers(): \CodeIgniter\HTTP\ResponseInterface
    {
        $term = $this->request->getGet('q') ?? $this->request->getGet('term');
        $term = is_string($term) ? trim($term) : '';

        $rows = CustomerCrudService::make()->searchForSelect2($term, 30);

        return $this->respond([
            'results'    => $rows,
            'pagination' => ['more' => false],
        ]);
    }

    public function drivers(): \CodeIgniter\HTTP\ResponseInterface
    {
        $term = $this->request->getGet('q') ?? $this->request->getGet('term');
        $term = is_string($term) ? trim($term) : '';

        $rows = DriverCrudService::make()->searchForSelect2($term, 30);

        return $this->respond([
            'results'    => $rows,
            'pagination' => ['more' => false],
        ]);
    }
}
