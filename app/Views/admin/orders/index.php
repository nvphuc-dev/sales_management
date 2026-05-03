<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row mb-3">
    <div class="col-md-6">
        <form class="d-flex gap-2" method="get" action="<?= site_url('admin/view/orders') ?>">
            <input type="search" name="q" value="<?= esc($search ?? '') ?>" class="form-control" placeholder="Mã đơn / khách">
            <button type="submit" class="btn btn-outline-secondary">Tìm</button>
        </form>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <a href="<?= site_url('admin/view/orders/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tạo đơn</a>
    </div>
</div>
<div class="card">
    <div class="card-header">Danh sách (<?= esc((string) ($pager->getTotal('default') ?? 0)) ?>)</div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped mb-0">
            <thead>
            <tr>
                <th>Mã</th><th>Khách</th><th>TT đơn</th><th>TT thanh toán</th><th class="text-end">Tổng</th><th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= esc($r['order_code']) ?></td>
                    <td><?= esc((string) ($r['customer_name'] ?? '')) ?></td>
                    <td><?= esc($r['status']) ?></td>
                    <td><?= esc($r['payment_status']) ?></td>
                    <td class="text-end"><?= esc($r['total_amount']) ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/view/orders/' . (int) $r['id']) ?>">Chi tiết</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer"><?= $pager->links('default', 'default_full') ?></div>
</div>
<?= $this->endSection() ?>
