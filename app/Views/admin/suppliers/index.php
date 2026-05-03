<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row mb-3">
    <div class="col-md-6">
        <form class="d-flex gap-2" method="get" action="<?= site_url('admin/view/suppliers') ?>">
            <input type="search" name="q" value="<?= esc($search ?? '') ?>" class="form-control" placeholder="Tên NCC">
            <button type="submit" class="btn btn-outline-secondary">Tìm</button>
        </form>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <a href="<?= site_url('admin/view/suppliers/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm NCC</a>
    </div>
</div>
<div class="card">
    <div class="card-header">Danh sách (<?= esc((string) ($pager->getTotal('default') ?? 0)) ?>)</div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>ID</th><th>Tên</th><th>Liên hệ</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int) $r['id'] ?></td>
                    <td><?= esc($r['name']) ?></td>
                    <td class="small"><?php $ci = (string) ($r['contact_info'] ?? ''); ?><?= esc(strlen($ci) > 100 ? substr($ci, 0, 97) . '...' : $ci) ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/view/suppliers/' . (int) $r['id'] . '/edit') ?>">Sửa</a>
                        <form class="d-inline js-confirm-delete" method="post" action="<?= site_url('admin/view/suppliers/' . (int) $r['id'] . '/delete') ?>"><?= csrf_field() ?><button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer"><?= $pager->links('default', 'default_full') ?></div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>(function(){document.querySelectorAll('.js-confirm-delete').forEach(function(f){f.addEventListener('submit',function(e){e.preventDefault();Swal.fire({title:'Xóa NCC?',icon:'warning',showCancelButton:true,confirmButtonText:'Xóa',cancelButtonText:'Hủy'}).then(function(r){if(r.isConfirmed)f.submit();});});});})();</script>
<?= $this->endSection() ?>
