<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var list<string>
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
        \App\Validation\AppRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    /** @var array<string, string|list<string>> */
    public array $productCreate = [
        'name'            => 'required|max_length[191]',
        'sku'             => 'required|max_length[100]|is_unique[products.sku]',
        'purchase_price'  => 'permit_empty|decimal|greater_than_equal_to[0]',
        'selling_price'   => 'permit_empty|decimal|greater_than_equal_to[0]',
        'stock_quantity'  => 'permit_empty|integer',
        'display_order'   => 'permit_empty|integer',
        'status'          => 'permit_empty|in_list[active,inactive]',
    ];

    /** @var array<string, string|list<string>> */
    public array $customerCreate = [
        'name'    => 'required|max_length[191]',
        'phone'   => 'permit_empty|vn_phone10',
        'email'   => 'permit_empty|valid_email|max_length[191]',
        'address' => 'permit_empty|max_length[2000]',
    ];

    /** @var array<string, string|list<string>> */
    public array $driverCreate = [
        'name'          => 'required|max_length[191]',
        'license_plate' => 'required|max_length[32]',
        'status'        => 'permit_empty|in_list[available,busy]',
    ];

    /** @var array<string, string|list<string>> */
    public array $importOrderCreate = [
        'code'          => 'required|max_length[64]|is_unique[import_orders.code]',
        'supplier_id'   => 'required|is_natural_no_zero',
        'notes'         => 'permit_empty|max_length[2000]',
        'items'         => 'import_items_basic',
    ];

    /** Phiếu nhập form web (dòng gửi qua items_json sau khi decode). */
    public array $importOrderWebHeader = [
        'code'          => 'required|max_length[64]|is_unique[import_orders.code]',
        'supplier_id'   => 'required|is_natural_no_zero',
        'notes'         => 'permit_empty|max_length[2000]',
    ];

    /** @var array<string, string|list<string>> */
    public array $orderCreate = [
        'order_code'     => 'required|max_length[64]|is_unique[orders.order_code]',
        'customer_id'    => 'required|is_natural_no_zero',
        'driver_id'      => 'permit_empty|is_natural_no_zero',
        'delivery_notes' => 'permit_empty|max_length[2000]',
        'status'         => 'permit_empty|in_list[pending,shipping]',
        'items'          => 'order_items_stock',
    ];

    /** @var array<string, string|list<string>> */
    public array $orderItemsUpdate = [
        'customer_id' => 'required|is_natural_no_zero',
        'items'       => 'order_items_stock',
    ];

    /** Đơn hàng form web (dòng gửi qua items_json). */
    public array $orderWebHeader = [
        'order_code'     => 'required|max_length[64]|is_unique[orders.order_code]',
        'customer_id'    => 'required|is_natural_no_zero',
        'driver_id'      => 'permit_empty|is_natural_no_zero',
        'delivery_notes' => 'permit_empty|max_length[2000]',
        'status'         => 'permit_empty|in_list[pending,shipping]',
    ];

    /** @var array<string, string|list<string>> */
    public array $orderPayment = [
        'amount' => 'required|decimal|greater_than[0]',
    ];

    /** @var array<string, string|list<string>> */
    public array $supplierCreate = [
        'name'         => 'required|max_length[191]',
        'contact_info' => 'permit_empty|max_length[2000]',
    ];

    /** @var array<string, string|list<string>> */
    public array $loginAttempt = [
        'username' => 'required|max_length[64]',
        'password' => 'required|max_length[255]',
    ];

    /** Tạo tài khoản (chỉ quản trị viên). */
    public array $userAccountCreate = [
        'username'  => 'required|min_length[3]|max_length[64]|regex_match[/^[a-zA-Z0-9._-]+$/]|is_unique[users.username]',
        'password'  => 'required|min_length[8]|max_length[255]',
        'full_name' => 'required|max_length[191]',
        'role'      => 'required|in_list[employee,admin]',
    ];

    /** Cập nhật tài khoản (quản trị viên). */
    public array $userAccountUpdate = [
        'full_name' => 'required|max_length[191]',
        'role'      => 'required|in_list[employee,admin]',
        'password'  => 'permit_empty|min_length[8]|max_length[255]',
        'is_active' => 'permit_empty|in_list[0,1]',
    ];

    /** Thông tin in trên đơn hàng (quản trị viên). */
    public array $companySettings = [
        'shop_name'     => 'required|max_length[255]',
        'phone'         => 'permit_empty|max_length[64]',
        'email'         => 'permit_empty|valid_email|max_length[191]',
        'address_line1' => 'permit_empty|max_length[2000]',
        'address_line2' => 'permit_empty|max_length[2000]',
        'tax_code'      => 'permit_empty|max_length[64]',
        'website'       => 'permit_empty|max_length[255]',
    ];
}
