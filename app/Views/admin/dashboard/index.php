<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Giai đoạn 0 + 1</h3>
            </div>
            <div class="card-body">
                <p class="mb-2">Dự án đã khởi tạo CodeIgniter 4, layout AdminLTE (asset trong <code>public/assets/adminlte</code>), schema CSDL và <strong>lớp Services giai đoạn 2</strong> (tồn kho, khách, đơn, nhập, thu tiền, tài xế).</p>
                <p class="mb-0 text-body-secondary small">Dùng <code>service('orderService')</code> / <code>\Config\Services::orderService()</code> từ controller sau bước 3.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-outline card-primary">
            <div class="card-header"><span class="card-title">Bước tiếp theo</span></div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Giai đoạn 2: <span class="text-success">đã có Services</span> — tiếp tục Controllers &amp; API</li>
                    <li>Giai đoạn 3: Controllers &amp; API Select2</li>
                    <li>Giai đoạn 4: Giao diện module + bám mockup <code>data_working/Thiet Ke</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
