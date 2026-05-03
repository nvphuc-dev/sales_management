<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Xuất báo cáo Excel</h3>
            </div>
            <div class="card-body">
                <p class="text-body-secondary small">Lọc theo <code>created_at</code> của bản ghi. Đơn nhập / đơn bán: gộp ô cột thông tin phiếu cho các dòng cùng một đơn. Cột tiền dùng định dạng VND.</p>
                <form method="get" action="<?= site_url('admin/export/download') ?>" class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Loại dữ liệu</label>
                        <select name="type" class="form-select" required>
                            <option value="orders">Đơn hàng (theo dòng sản phẩm)</option>
                            <option value="imports">Phiếu nhập (theo dòng sản phẩm)</option>
                            <option value="customers">Khách hàng</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="start_date" class="form-control" required value="<?= esc($defaultStart ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="end_date" class="form-control" required value="<?= esc($defaultEnd ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-download"></i> Tải file .xlsx</button>
                        <a href="<?= site_url('admin') ?>" class="btn btn-outline-secondary">Về tổng quan</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
