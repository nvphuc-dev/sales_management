<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="row mb-3">
    <div class="col-md-6">
        <form class="d-flex gap-2" method="get" action="<?= site_url('admin/view/import-orders') ?>">
            <input type="search" name="q" value="<?= esc($search ?? '') ?>" class="form-control" placeholder="Mã phiếu / NCC">
            <button type="submit" class="btn btn-outline-secondary">Tìm</button>
        </form>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <a href="<?= site_url('admin/view/import-orders/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tạo phiếu nhập</a>
    </div>
</div>
<div class="card">
    <div class="card-header">Danh sách (<?= esc((string) ($pager->getTotal('default') ?? 0)) ?>)</div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Mã</th><th>NCC</th><th class="text-end">Tổng</th><th>Ngày</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= esc($r['code']) ?></td>
                    <td><?= esc((string) ($r['supplier_name'] ?? '')) ?></td>
                    <td class="text-end"><?= esc($r['total_amount']) ?></td>
                    <td class="small"><?= esc((string) ($r['created_at'] ?? '')) ?></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/view/import-orders/' . (int) $r['id']) ?>">Chi tiết</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer"><?= $pager->links('default', 'default_full') ?></div>
</div>
<?= $this->endSection() ?>
