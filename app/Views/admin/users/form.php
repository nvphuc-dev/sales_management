<?= $this->extend('admin/layouts/main') ?>
<?php $ed = $edit ?? null; ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="card-title mb-0"><?= $ed ? 'Sửa người dùng' : 'Thêm người dùng' ?></span>
                <a href="<?= site_url('admin/view/users') ?>" class="btn btn-sm btn-outline-secondary">← Danh sách</a>
            </div>
            <div class="card-body">
                <?= $this->include('admin/partials/validation_errors') ?>
                <form method="post" action="<?= $ed ? site_url('admin/view/users/' . (int) $ed['id']) : site_url('admin/view/users') ?>">
                    <?= csrf_field() ?>
                    <?php if (! $ed): ?>
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" name="username" class="form-control" required value="<?= esc(old('username')) ?>" pattern="[a-zA-Z0-9._-]{3,64}" autocomplete="off">
                            <div class="form-text">Chữ, số, dấu chấm, gạch dưới, gạch ngang (3–64 ký tự).</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" value="<?= esc((string) $ed['username']) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password">
                            <div class="form-text">Để trống nếu giữ mật khẩu cũ.</div>
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Họ tên</label>
                        <input type="text" name="full_name" class="form-control" required maxlength="191" value="<?= esc(old('full_name', $ed['full_name'] ?? '')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Vai trò</label>
                        <select name="role" class="form-select" required>
                            <?php
                            $r0 = old('role', $ed['role'] ?? 'employee');
                            ?>
                            <option value="employee" <?= $r0 === 'employee' ? 'selected' : '' ?>>Nhân viên</option>
                            <option value="admin" <?= $r0 === 'admin' ? 'selected' : '' ?>>Quản trị viên</option>
                        </select>
                    </div>
                    <?php if ($ed): ?>
                        <input type="hidden" name="is_active" value="0">
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" <?= (string) old('is_active', (string) ((int) ($ed['is_active'] ?? 1))) === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Tài khoản đang hoạt động</label>
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary"><?= $ed ? 'Cập nhật' : 'Tạo mới' ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
