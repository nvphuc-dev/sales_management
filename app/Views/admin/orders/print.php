<?php
$c = $company;
$h = $bundle['header'];
$items = $bundle['items'];
$paid = $bundle['paid'];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>In đơn <?= esc($h['order_code']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12pt; }
            a { color: inherit !important; text-decoration: none !important; }
        }
        .company-name { font-size: 1.35rem; font-weight: 700; }
    </style>
</head>
<body class="p-4">
<div class="no-print mb-3">
    <button type="button" class="btn btn-primary" onclick="window.print()">In</button>
    <a href="<?= site_url('admin/view/orders/' . (int) $h['id']) ?>" class="btn btn-outline-secondary">Quay lại chi tiết</a>
</div>

<div class="border-bottom pb-3 mb-4">
    <div class="company-name"><?= esc($c['shop_name'] !== '' ? $c['shop_name'] : '— Chưa cấu hình tên công ty —') ?></div>
    <?php if ($c['phone'] !== ''): ?><div>ĐT: <?= esc($c['phone']) ?></div><?php endif; ?>
    <?php if ($c['email'] !== ''): ?><div>Email: <?= esc($c['email']) ?></div><?php endif; ?>
    <?php if (! empty($c['address_line1'])): ?><div><?= nl2br(esc((string) $c['address_line1'])) ?></div><?php endif; ?>
    <?php if (! empty($c['address_line2'])): ?><div><?= nl2br(esc((string) $c['address_line2'])) ?></div><?php endif; ?>
    <?php if ($c['tax_code'] !== ''): ?><div>MST: <?= esc($c['tax_code']) ?></div><?php endif; ?>
    <?php if ($c['website'] !== ''): ?><div><?= esc($c['website']) ?></div><?php endif; ?>
</div>

<h1 class="h5 mb-3">Đơn hàng <strong><?= esc($h['order_code']) ?></strong></h1>
<div class="small mb-3">
    <div><strong>Khách:</strong> <?= esc((string) ($h['customer_name'] ?? '')) ?> <?= esc((string) ($h['customer_phone'] ?? '')) ?></div>
    <div><strong>Ngày:</strong> <?= esc((string) ($h['created_at'] ?? '')) ?> &nbsp; <strong>Trạng thái:</strong> <?= esc((string) ($h['status'] ?? '')) ?> &nbsp; <strong>Thanh toán:</strong> <?= esc((string) ($h['payment_status'] ?? '')) ?></div>
    <?php if (! empty($h['delivery_notes'])): ?>
        <div><strong>Ghi chú:</strong> <?= esc((string) $h['delivery_notes']) ?></div>
    <?php endif; ?>
</div>

<table class="table table-bordered table-sm">
    <thead class="table-light">
    <tr><th>Sản phẩm</th><th>SKU</th><th class="text-end">SL</th><th class="text-end">Đơn giá</th><th class="text-end">Thành tiền</th></tr>
    </thead>
    <tbody>
    <?php foreach ($items as $it): ?>
        <tr>
            <td><?= esc((string) ($it['product_name'] ?? '')) ?></td>
            <td><?= esc((string) ($it['sku'] ?? '')) ?></td>
            <td class="text-end"><?= (int) $it['quantity'] ?></td>
            <td class="text-end"><?= esc((string) $it['unit_price']) ?></td>
            <td class="text-end"><?= esc((string) $it['line_total']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="text-end small">
    <div><strong>Tổng đơn:</strong> <?= esc((string) $h['total_amount']) ?></div>
    <div><strong>Đã thu:</strong> <?= esc((string) $paid) ?></div>
    <div><strong>Còn lại:</strong> <?= esc((string) $remaining) ?></div>
</div>
</body>
</html>
