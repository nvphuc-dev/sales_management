<?= $this->extend('admin/layouts/main') ?>
<?php $h = $bundle['header']; $items = $bundle['items']; ?>
<?= $this->section('content') ?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Phiếu <strong><?= esc($h['code']) ?></strong></span>
        <a href="<?= site_url('admin/view/import-orders') ?>" class="btn btn-sm btn-outline-secondary">Danh sách</a>
    </div>
    <div class="card-body row">
        <div class="col-md-4"><strong>NCC:</strong> <?= esc((string) ($h['supplier_name'] ?? '')) ?></div>
        <div class="col-md-4"><strong>Tổng:</strong> <?= esc($h['total_amount']) ?></div>
        <div class="col-md-12 mt-2"><strong>Ghi chú:</strong> <?= esc((string) ($h['notes'] ?? '')) ?></div>
    </div>
</div>
<div class="card">
    <div class="card-header">Chi tiết dòng hàng</div>
    <div class="card-body p-0 table-responsive">
        <table class="table mb-0">
            <thead><tr><th>SP</th><th>SKU</th><th class="text-end">SL</th><th class="text-end">Đơn giá</th><th class="text-end">Thành tiền</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= esc((string) ($it['product_name'] ?? '')) ?></td>
                    <td><?= esc((string) ($it['sku'] ?? '')) ?></td>
                    <td class="text-end"><?= (int) $it['quantity'] ?></td>
                    <td class="text-end"><?= esc($it['unit_price']) ?></td>
                    <td class="text-end"><?= esc($it['line_total']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
