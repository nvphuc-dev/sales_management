<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row mb-3">
    <div class="col-md-6">
        <form class="d-flex gap-2" method="get" action="<?= site_url('admin/view/customers') ?>">
            <input type="search" name="q" value="<?= esc($search ?? '') ?>" class="form-control" placeholder="Tên / SĐT / email">
            <button type="submit" class="btn btn-outline-secondary">Tìm</button>
        </form>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <a href="<?= site_url('admin/view/customers/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm khách</a>
    </div>
</div>
<div class="card d-none d-md-block">
    <div class="card-header">Danh sách (<?= esc((string) ($pager->getTotal('default') ?? 0)) ?>)</div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>ID</th><th>Tên</th><th>SĐT</th><th>Email</th><th class="text-end">Tổng mua</th><th class="text-end">Dư nợ</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['id'] ?></td>
                    <td><?= esc($r['name']) ?></td>
                    <td><?= esc((string) ($r['phone'] ?? '')) ?></td>
                    <td><?= esc((string) ($r['email'] ?? '')) ?></td>
                    <td class="text-end"><?= esc($r['total_purchase']) ?></td>
                    <td class="text-end"><?= esc($r['current_debt']) ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/view/customers/' . (int) $r['id'] . '/edit') ?>">Sửa</a>
                        <form class="d-inline js-confirm-delete" method="post" action="<?= site_url('admin/view/customers/' . (int) $r['id'] . '/delete') ?>">
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
    <div class="col-12 small">Tổng: <?= esc((string) ($pager->getTotal('default') ?? 0)) ?></div>
    <?php foreach ($rows as $r): ?>
        <div class="col-12">
            <div class="card card-outline card-secondary">
                <div class="card-body py-2">
                    <div class="fw-semibold"><?= esc($r['name']) ?></div>
                    <div class="small">Nợ: <?= esc($r['current_debt']) ?></div>
                    <div class="mt-2">
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/view/customers/' . (int) $r['id'] . '/edit') ?>">Sửa</a>
                        <form class="d-inline js-confirm-delete" method="post" action="<?= site_url('admin/view/customers/' . (int) $r['id'] . '/delete') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger" type="submit">Xóa</button></form>
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
(function(){document.querySelectorAll('.js-confirm-delete').forEach(function(f){f.addEventListener('submit',function(e){e.preventDefault();Swal.fire({title:'Xóa khách hàng?',icon:'warning',showCancelButton:true,confirmButtonText:'Xóa',cancelButtonText:'Hủy'}).then(function(r){if(r.isConfirmed)f.submit();});});});})();
</script>
<?= $this->endSection() ?>
