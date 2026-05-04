<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Thông tin công ty / cửa hàng</h3>
                <p class="text-body-secondary small mb-0 mt-1">Dùng cho in đơn hàng và tài liệu.</p>
            </div>
            <div class="card-body">
                <?= $this->include('admin/partials/validation_errors') ?>
                <form method="post" action="<?= site_url('admin/view/company-settings') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Tên công ty / cửa hàng <span class="text-danger">*</span></label>
                        <input type="text" name="shop_name" class="form-control" required maxlength="255" value="<?= esc(old('shop_name', $row['shop_name'] ?? '')) ?>">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Điện thoại</label>
                            <input type="text" name="phone" class="form-control" maxlength="64" value="<?= esc(old('phone', $row['phone'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" maxlength="191" value="<?= esc(old('email', $row['email'] ?? '')) ?>">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Địa chỉ 1</label>
                        <textarea name="address_line1" class="form-control" rows="2" maxlength="2000"><?= esc(old('address_line1', $row['address_line1'] ?? '')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ 2</label>
                        <textarea name="address_line2" class="form-control" rows="2" maxlength="2000"><?= esc(old('address_line2', $row['address_line2'] ?? '')) ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã số thuế</label>
                            <input type="text" name="tax_code" class="form-control" maxlength="64" value="<?= esc(old('tax_code', $row['tax_code'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website / Fanpage</label>
                            <input type="text" name="website" class="form-control" maxlength="255" placeholder="https://..." value="<?= esc(old('website', $row['website'] ?? '')) ?>">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
