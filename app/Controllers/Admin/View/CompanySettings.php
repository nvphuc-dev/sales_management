<?php

declare(strict_types=1);

namespace App\Controllers\Admin\View;

use App\Models\CompanySettingModel;
use Config\Validation;

class CompanySettings extends BasePageController
{
    public function edit(): string
    {
        $row = model(CompanySettingModel::class)->getSingletonRow();

        return $this->page('admin/company_settings/form', [
            'title'     => 'Thông tin công ty / cửa hàng',
            'navActive' => 'company-settings',
            'row'       => $row,
        ]);
    }

    public function update(): \CodeIgniter\HTTP\RedirectResponse
    {
        $payload = $this->request->getPost();
        if (! $this->validateData($payload, config(Validation::class)->companySettings)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        model(CompanySettingModel::class)->saveSingleton([
            'shop_name'     => (string) $payload['shop_name'],
            'phone'         => (string) ($payload['phone'] ?? ''),
            'email'         => (string) ($payload['email'] ?? ''),
            'address_line1' => (string) ($payload['address_line1'] ?? ''),
            'address_line2' => (string) ($payload['address_line2'] ?? ''),
            'tax_code'      => (string) ($payload['tax_code'] ?? ''),
            'website'       => (string) ($payload['website'] ?? ''),
        ]);

        return redirect()->to(site_url('admin/view/company-settings'))->with('success', 'Đã lưu thông tin công ty.');
    }
}
