<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<?php $r = $record; $isEdit = $r !== null; ?>
<div class="card col-lg-8 mx-auto">
    <div class="card-header"><?= $isEdit ? 'Sửa khách hàng' : 'Thêm khách hàng' ?></div>
    <div class="card-body">
        <?= $this->include('admin/partials/validation_errors') ?>
        <form method="post" action="<?= $isEdit ? site_url('admin/view/customers/' . (int) $r['id']) : site_url('admin/view/customers') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Tên</label>
                <input type="text" name="name" class="form-control" required value="<?= esc(old('name', $r['name'] ?? '')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">SĐT (10 số, bắt đầu 0)</label>
                <input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $r['phone'] ?? '')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email', $r['email'] ?? '')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Địa chỉ</label>
                <textarea name="address" class="form-control" rows="2"><?= esc(old('address', $r['address'] ?? '')) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Lưu' ?></button>
            <a href="<?= site_url('admin/view/customers') ?>" class="btn btn-outline-secondary">Quay lại</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
