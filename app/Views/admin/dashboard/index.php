<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card border-danger">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Cảnh báo tồn kho</h3>
                <?php if (($lowStockCount ?? 0) > 0): ?>
                    <span class="badge text-bg-danger"><?= (int) $lowStockCount ?> SP &lt; 5</span>
                <?php else: ?>
                    <span class="badge text-bg-success">Ổn định</span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStockRows)): ?>
                    <p class="p-3 mb-0 text-body-secondary small">Không có sản phẩm nào có tồn kho dưới 5.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($lowStockRows as $p): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?= esc((string) $p['name']) ?> <small class="text-body-secondary"><?= esc((string) ($p['sku'] ?? '')) ?></small></span>
                                <span class="badge text-bg-danger"><?= (int) $p['stock_quantity'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="card-footer small text-body-secondary">Hiển thị tối đa 30 dòng — xem đầy đủ tại <a href="<?= site_url('admin/view/products') ?>">Sản phẩm</a>.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Giai đoạn 5</h3>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Phân quyền:</strong> <span class="badge text-bg-secondary">Nhân viên</span> dùng đủ nghiệp vụ; <span class="badge text-bg-primary">Quản trị viên</span> thêm được người dùng<?php if (session()->get('role') === 'admin'): ?> tại <a href="<?= site_url('admin/view/users') ?>">Người dùng</a><?php else: ?> (menu <em>Người dùng</em> chỉ dành cho admin)<?php endif; ?>.</p>
                <p class="mb-2"><strong>Xuất Excel:</strong> <a href="<?= site_url('admin/export') ?>">admin/export</a> — chọn loại (đơn hàng, nhập, khách), khoảng ngày, tải <code>.xlsx</code> (header in đậm, cột tiền VND, gộp ô theo đơn).</p>
                <p class="mb-0 text-body-secondary small">Nếu tải file lỗi: bật <code>extension=zip</code> trong php.ini (PhpSpreadsheet cần ext-zip).</p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Đã triển khai</h3>
            </div>
            <div class="card-body">
                <p class="mb-2">SSR <code>admin/view/...</code>, REST <code>admin/...</code>, API Select2 / giá dòng.</p>
                <p class="mb-0 text-body-secondary small">Mockup: <code>data_working/Thiet Ke</code></p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-outline card-primary">
            <div class="card-header"><span class="card-title">Tiến độ</span></div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Giai đoạn 2–4: <span class="text-success">xong cốt lõi</span></li>
                    <li>Giai đoạn 5: <span class="text-success">Excel + cảnh báo tồn</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
