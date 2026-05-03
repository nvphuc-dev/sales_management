<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<?php $r = $record; $isEdit = $r !== null; ?>
<div class="card col-lg-8 mx-auto">
    <div class="card-header"><?= $isEdit ? 'Sửa NCC' : 'Thêm NCC' ?></div>
    <div class="card-body">
        <?= $this->include('admin/partials/validation_errors') ?>
        <form method="post" action="<?= $isEdit ? site_url('admin/view/suppliers/' . (int) $r['id']) : site_url('admin/view/suppliers') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Tên</label>
                <input type="text" name="name" class="form-control" required value="<?= esc(old('name', $r['name'] ?? '')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Thông tin liên hệ</label>
                <textarea name="contact_info" class="form-control" rows="3"><?= esc(old('contact_info', $r['contact_info'] ?? '')) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Lưu' ?></button>
            <a href="<?= site_url('admin/view/suppliers') ?>" class="btn btn-outline-secondary">Quay lại</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
