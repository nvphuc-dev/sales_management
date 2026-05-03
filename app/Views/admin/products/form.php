<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<?php $r = $record; $isEdit = $r !== null; ?>
<div class="card card-primary card-outline col-lg-8 mx-auto">
    <div class="card-header"><span class="card-title"><?= $isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm' ?></span></div>
    <div class="card-body">
        <?= $this->include('admin/partials/validation_errors') ?>
        <form method="post" action="<?= $isEdit ? site_url('admin/view/products/' . (int) $r['id']) : site_url('admin/view/products') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Tên</label>
                <input type="text" name="name" class="form-control" required value="<?= esc(old('name', $r['name'] ?? '')) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">SKU</label>
                <input type="text" name="sku" class="form-control" required value="<?= esc(old('sku', $r['sku'] ?? '')) ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giá nhập</label>
                    <input type="text" name="purchase_price" class="form-control" value="<?= esc(old('purchase_price', $r['purchase_price'] ?? '0')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giá bán</label>
                    <input type="text" name="selling_price" class="form-control" value="<?= esc(old('selling_price', $r['selling_price'] ?? '0')) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tồn kho</label>
                    <input type="number" name="stock_quantity" class="form-control" value="<?= esc((string) old('stock_quantity', $r['stock_quantity'] ?? 0)) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Thứ tự hiển thị</label>
                    <input type="number" name="display_order" class="form-control" value="<?= esc((string) old('display_order', $r['display_order'] ?? 0)) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <?php $st = old('status', $r['status'] ?? 'active'); ?>
                    <option value="active" <?= $st === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                    <option value="inactive" <?= $st === 'inactive' ? 'selected' : '' ?>>Ngừng kinh doanh</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Cập nhật' : 'Lưu' ?></button>
            <a href="<?= site_url('admin/view/products') ?>" class="btn btn-outline-secondary">Quay lại</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
