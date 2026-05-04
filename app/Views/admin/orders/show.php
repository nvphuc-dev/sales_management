<?= $this->extend('admin/layouts/main') ?>
<?php $h = $bundle['header']; $items = $bundle['items']; $paid = $bundle['paid']; ?>
<?= $this->section('content') ?>
<div class="row mb-3">
    <div class="col d-flex flex-wrap gap-2 align-items-center">
        <a href="<?= site_url('admin/view/orders') ?>" class="btn btn-outline-secondary btn-sm">← Danh sách</a>
        <a href="<?= site_url('admin/view/orders/' . (int) $h['id'] . '/print') ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">In đơn</a>
    </div>
</div>
<div class="card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span>Đơn <strong><?= esc($h['order_code']) ?></strong></span>
        <div class="btn-group btn-group-sm">
            <?php if ($h['status'] !== 'cancelled' && $h['status'] !== 'completed'): ?>
                <form class="d-inline js-confirm" method="post" action="<?= site_url('admin/view/orders/' . (int) $h['id'] . '/cancel') ?>" data-title="Hủy đơn hàng?" data-text="Hoàn kho và đảo sổ theo nghiệp vụ.">
                    <?= csrf_field() ?><button type="submit" class="btn btn-warning">Hủy đơn</button>
                </form>
                <form class="d-inline js-confirm" method="post" action="<?= site_url('admin/view/orders/' . (int) $h['id'] . '/complete') ?>" data-title="Hoàn thành đơn?">
                    <?= csrf_field() ?><button type="submit" class="btn btn-success">Hoàn thành</button>
                </form>
            <?php endif; ?>
            <form class="d-inline js-confirm" method="post" action="<?= site_url('admin/view/orders/' . (int) $h['id'] . '/delete') ?>" data-title="Xóa vĩnh viễn đơn?" data-text="Không hoàn tác.">
                <?= csrf_field() ?><button type="submit" class="btn btn-danger">Xóa đơn</button>
            </form>
        </div>
    </div>
    <div class="card-body row g-2 small">
        <div class="col-md-4"><strong>Khách:</strong> <?= esc((string) ($h['customer_name'] ?? '')) ?> <?= esc((string) ($h['customer_phone'] ?? '')) ?></div>
        <div class="col-md-4"><strong>TT đơn:</strong> <?= esc($h['status']) ?> · <strong>Thanh toán:</strong> <?= esc($h['payment_status']) ?></div>
        <div class="col-md-4"><strong>Tổng:</strong> <?= esc($h['total_amount']) ?> · <strong>Đã thu:</strong> <?= esc($paid) ?> · <strong>Còn lại:</strong> <?= esc($remaining) ?></div>
        <?php if (! empty($h['delivery_notes'])): ?>
            <div class="col-12"><strong>Ghi chú:</strong> <?= esc((string) $h['delivery_notes']) ?></div>
        <?php endif; ?>
    </div>
</div>

<?php if ($h['payment_status'] !== 'paid' && $h['status'] !== 'cancelled'): ?>
<div class="card mb-3">
    <div class="card-header">Thu tiền</div>
    <div class="card-body">
        <?= $this->include('admin/partials/validation_errors') ?>
        <form method="post" action="<?= site_url('admin/view/orders/' . (int) $h['id'] . '/payment') ?>" class="row g-2 align-items-end">
            <?= csrf_field() ?>
            <input type="hidden" name="customer_id" value="<?= (int) $h['customer_id'] ?>">
            <div class="col-md-4">
                <label class="form-label">Số tiền thu</label>
                <input type="text" name="amount" class="form-control" required placeholder="VD: 100000">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Ghi nhận thu</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Chi tiết dòng</div>
    <div class="card-body p-0 table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Sản phẩm</th><th>SKU</th><th class="text-end">SL</th><th class="text-end">Đơn giá</th><th class="text-end">Thành tiền</th></tr></thead>
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
<?= $this->section('scripts') ?>
<script>
(function () {
  document.querySelectorAll('.js-confirm').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var t = form.getAttribute('data-title') || 'Xác nhận';
      var x = form.getAttribute('data-text') || '';
      Swal.fire({ title: t, text: x, icon: 'warning', showCancelButton: true, confirmButtonText: 'Đồng ý', cancelButtonText: 'Hủy' }).then(function (r) {
        if (r.isConfirmed) form.submit();
      });
    });
  });
})();
</script>
<?= $this->endSection() ?>
