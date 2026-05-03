<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Concerns;

/**
 * Resource route có GET .../new và .../edit — form HTML thuộc giai đoạn 4.
 */
trait NoHtmlResourceForms
{
    public function new(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->failNotFound('Form HTML sẽ có ở giai đoạn 4.');
    }

    public function edit(?string $id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->failNotFound('Form HTML sẽ có ở giai đoạn 4.');
    }
}
