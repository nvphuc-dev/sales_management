<?php

declare(strict_types=1);

namespace App\Controllers\Admin\View;

use App\Controllers\BaseController;

abstract class BasePageController extends BaseController
{
    protected $helpers = ['form', 'url'];

    /**
     * @param array<string, mixed> $data
     */
    protected function page(string $view, array $data = []): string
    {
        $data['title']     = $data['title'] ?? '';
        $data['navActive'] = $data['navActive'] ?? '';

        return view($view, $data);
    }
}
