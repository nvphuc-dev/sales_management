<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<?php $r = $record; $isEdit = $r !== null; ?>
<div class="card col-lg-6 mx-auto">
    <div class="card-header"><?= $isEdit ? 'Sửa tài xế' : 'Thêm tài xế' ?></div>
    <div class="card-body">
        <?= $this->include('admin/partials/validation_errors') ?>
        <form method="post" action="<?= $isEdit ? site_url('admin/view/drivers/' . (int) $r['id']) : site_url('admin/view/drivers') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Tên</label>
                <input type="text" name="name" class="form-control" required value="<?= esc(old('name', $r['name'] ?? '')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Biển số xe</label>
                <input type="text" name="license_plate" class="form-control" required value="<?= esc(old('license_plate', $r['license_plate'] ?? '')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <?php $st = old('status', $r['status'] ?? 'available'); ?>
                    <option value="available" <?= $st === 'available' ? 'selected' : '' ?>>Rảnh</option>
                    <option value="busy" <?= $st === 'busy' ? 'selected' : '' ?>>Đang giao</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Lưu' ?></button>
            <a href="<?= site_url('admin/view/drivers') ?>" class="btn btn-outline-secondary">Quay lại</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
