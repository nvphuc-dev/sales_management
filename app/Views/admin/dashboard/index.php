<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Giai đoạn 0 + 1</h3>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Giai đoạn 4:</strong> giao diện SSR dưới <code>admin/view/...</code> (sản phẩm, khách, NCC, nhập, đơn, tài xế). Đơn mới: Select2 + gọi <code>api/line-price</code> để hiển thị giá theo khách; SweetAlert2 xác nhận xóa/hủy.</p>
                <p class="mb-0 text-body-secondary small">API giai đoạn 3 vẫn dùng tại <code>admin/products</code> (JSON) và <code>api/search-*</code>.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-outline card-primary">
            <div class="card-header"><span class="card-title">Bước tiếp theo</span></div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Giai đoạn 2: <span class="text-success">Services</span></li>
                    <li>Giai đoạn 3: <span class="text-success">REST + API Select2</span></li>
                    <li>Giai đoạn 4: <span class="text-success">Form AdminLTE + Select2 + SweetAlert2</span> — tinh chỉnh bám mockup <code>data_working/Thiet Ke</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
