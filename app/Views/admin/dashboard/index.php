<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Giai đoạn 0 + 1</h3>
            </div>
            <div class="card-body">
                <p class="mb-2">Đã có <strong>API JSON giai đoạn 3</strong>: resource dưới <code>admin/products</code>, <code>admin/customers</code>, <code>admin/drivers</code>, <code>admin/import-orders</code>, <code>admin/orders</code> (kèm <code>POST .../cancel|complete|assign-driver|payments</code>) và Select2 tại <code>api/search-*</code>.</p>
                <p class="mb-0 text-body-secondary small">Ví dụ: <code>GET .../api/search-products?q=ga</code> — gửi JSON (Content-Type: application/json) cho các lệnh POST.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-outline card-primary">
            <div class="card-header"><span class="card-title">Bước tiếp theo</span></div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Giai đoạn 2: <span class="text-success">Services</span></li>
                    <li>Giai đoạn 3: <span class="text-success">REST + API Select2</span> — giai đoạn 4: giao diện AdminLTE + form</li>
                    <li>Giai đoạn 4: Giao diện module + bám mockup <code>data_working/Thiet Ke</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
