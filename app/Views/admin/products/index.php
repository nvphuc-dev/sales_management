<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row mb-3">
    <div class="col-md-6">
        <form class="d-flex gap-2" method="get" action="<?= site_url('admin/view/products') ?>">
            <input type="search" name="q" value="<?= esc($search ?? '') ?>" class="form-control" placeholder="Tìm theo tên hoặc SKU">
            <button type="submit" class="btn btn-outline-secondary">Tìm</button>
        </form>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <a href="<?= site_url('admin/view/products/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm sản phẩm</a>
    </div>
</div>

<div class="card d-none d-md-block">
    <div class="card-header"><span class="card-title">Danh sách (<?= esc((string) ($pager->getTotal('default') ?? 0)) ?> bản ghi)</span></div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>SKU</th>
                <th class="text-end">Giá nhập</th>
                <th class="text-end">Giá bán</th>
                <th class="text-end">Tồn</th>
                <th>TT</th>
                <th class="text-end">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['id'] ?></td>
                    <td><?= esc($r['name']) ?></td>
                    <td><?= esc($r['sku']) ?></td>
                    <td class="text-end"><?= esc($r['purchase_price']) ?></td>
                    <td class="text-end"><?= esc($r['selling_price']) ?></td>
                    <td class="text-end"><?= (int) $r['stock_quantity'] ?></td>
                    <td><?= esc($r['status']) ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/view/products/' . (int) $r['id'] . '/edit') ?>">Sửa</a>
                        <form action="<?= site_url('admin/view/products/' . (int) $r['id'] . '/delete') ?>" method="post" class="d-inline js-confirm-delete" data-title="Xóa sản phẩm?" data-text="<?= esc($r['name'], 'attr') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer"><?= $pager->links('default', 'default_full') ?></div>
</div>

<div class="d-md-none row g-2">
    <div class="col-12 small text-body-secondary">Tổng: <?= esc((string) ($pager->getTotal('default') ?? 0)) ?> bản ghi</div>
    <?php foreach ($rows as $r): ?>
        <div class="col-12">
            <div class="card card-outline card-secondary">
                <div class="card-body py-2">
                    <div class="fw-semibold"><?= esc($r['name']) ?></div>
                    <div class="small">SKU: <?= esc($r['sku']) ?> · Tồn: <?= (int) $r['stock_quantity'] ?></div>
                    <div class="mt-2">
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/view/products/' . (int) $r['id'] . '/edit') ?>">Sửa</a>
                        <form action="<?= site_url('admin/view/products/' . (int) $r['id'] . '/delete') ?>" method="post" class="d-inline js-confirm-delete" data-title="Xóa sản phẩm?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="col-12"><?= $pager->links('default', 'default_full') ?></div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  document.querySelectorAll('.js-confirm-delete').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const t = form.getAttribute('data-title') || 'Xác nhận';
      const x = form.getAttribute('data-text') || '';
      Swal.fire({ title: t, text: x, icon: 'warning', showCancelButton: true, confirmButtonText: 'Xóa', cancelButtonText: 'Hủy' })
        .then(function (r) { if (r.isConfirmed) form.submit(); });
    });
  });
})();
</script>
<?= $this->endSection() ?>
