<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Rule tùy chỉnh đăng ký trong Config\Validation::$ruleSets.
 */
class AppRules
{
    /**
     * Số điện thoại di động Việt Nam: 10 chữ số, bắt đầu bằng 0.
     */
    public function vn_phone10(?string $value, ?string &$error = null, array $data = []): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (preg_match('/^0\d{9}$/', $value) !== 1) {
            $error = 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 0.';

            return false;
        }

        return true;
    }

    /**
     * Mảng dòng đơn: mỗi dòng đủ tồn kho (product_id, quantity).
     *
     * @param list<array<string, mixed>>|null $value
     */
    public function order_items_stock(?array $value, ?string &$error = null, array $data = []): bool
    {
        if ($value === null || $value === []) {
            $error = 'Đơn hàng phải có ít nhất một dòng sản phẩm.';

            return false;
        }

        $db = db_connect();
        foreach ($value as $i => $line) {
            $pid = isset($line['product_id']) ? (int) $line['product_id'] : 0;
            $qty = isset($line['quantity']) ? (int) $line['quantity'] : 0;
            if ($pid < 1 || $qty < 1) {
                $error = 'Dòng ' . ($i + 1) . ': product_id và quantity không hợp lệ.';

                return false;
            }

            $row = $db->table('products')->select('stock_quantity, status')->where('id', $pid)->get()->getRowArray();
            if ($row === null) {
                $error = 'Dòng ' . ($i + 1) . ': sản phẩm không tồn tại.';

                return false;
            }

            if ($row['status'] !== 'active') {
                $error = 'Dòng ' . ($i + 1) . ': sản phẩm không kinh doanh.';

                return false;
            }

            if ((int) $row['stock_quantity'] < $qty) {
                $error = 'Dòng ' . ($i + 1) . ': không đủ tồn kho (còn ' . $row['stock_quantity'] . ').';

                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>>|null $value
     */
    public function import_items_basic(?array $value, ?string &$error = null, array $data = []): bool
    {
        if ($value === null || $value === []) {
            $error = 'Phiếu nhập phải có ít nhất một dòng hàng.';

            return false;
        }

        foreach ($value as $i => $line) {
            $pid = isset($line['product_id']) ? (int) $line['product_id'] : 0;
            $qty = isset($line['quantity']) ? (int) $line['quantity'] : 0;
            if ($pid < 1 || $qty < 1) {
                $error = 'Dòng ' . ($i + 1) . ': product_id và quantity không hợp lệ.';

                return false;
            }

            $price = $line['unit_price'] ?? null;
            if ($price === null || (float) $price <= 0) {
                $error = 'Dòng ' . ($i + 1) . ': unit_price phải > 0.';

                return false;
            }
        }

        return true;
    }
}
