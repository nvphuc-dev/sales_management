<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row mb-3">
    <div class="col-md-6">
        <h4 class="mb-0">Người dùng hệ thống</h4>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <a href="<?= site_url('admin/view/users/new') ?>" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Thêm người dùng</a>
    </div>
</div>
<div class="card">
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped mb-0">
            <thead>
            <tr>
                <th>Đăng nhập</th>
                <th>Họ tên</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= esc((string) $r['username']) ?></td>
                    <td><?= esc((string) $r['full_name']) ?></td>
                    <td>
                        <?php if (($r['role'] ?? '') === 'admin'): ?>
                            <span class="badge text-bg-primary">Quản trị viên</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Nhân viên</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int) ($r['is_active'] ?? 0) === 1): ?>
                            <span class="badge text-bg-success">Hoạt động</span>
                        <?php else: ?>
                            <span class="badge text-bg-danger">Đã khóa</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/view/users/' . (int) $r['id'] . '/edit') ?>">Sửa</a>
                        <?php if ((int) $r['id'] !== (int) session()->get('user_id') && (int) ($r['is_active'] ?? 0) === 1): ?>
                            <form class="d-inline js-confirm-user-deact" method="post" action="<?= site_url('admin/view/users/' . (int) $r['id'] . '/deactivate') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Vô hiệu</button>
                            </form>
                        <?php endif; ?>
                    </td>
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
  document.querySelectorAll('.js-confirm-user-deact').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      Swal.fire({ title: 'Vô hiệu tài khoản?', text: 'Người dùng không thể đăng nhập cho đến khi được kích hoạt lại.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Vô hiệu', cancelButtonText: 'Hủy' }).then(function (r) {
        if (r.isConfirmed) form.submit();
      });
    });
  });
})();
</script>
<?= $this->endSection() ?>
