<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">Tạo phiếu nhập</div>
    <div class="card-body">
        <?= $this->include('admin/partials/validation_errors') ?>
        <form method="post" action="<?= site_url('admin/view/import-orders') ?>" id="import-form">
            <?= csrf_field() ?>
            <input type="hidden" name="items_json" id="items_json" value="">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mã phiếu</label>
                    <input type="text" name="code" class="form-control" required value="<?= esc(old('code')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nhà cung cấp</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">— Chọn —</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= (int) $s['id'] ?>" <?= (string) old('supplier_id') === (string) $s['id'] ? 'selected' : '' ?>><?= esc($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="notes" class="form-control" rows="2"><?= esc(old('notes')) ?></textarea>
                </div>
            </div>
            <h5 class="mt-3">Dòng hàng</h5>
            <table class="table table-bordered" id="lines-table">
                <thead><tr><th style="width:40%">Sản phẩm</th><th>SL</th><th>Đơn giá nhập</th><th></th></tr></thead>
                <tbody></tbody>
            </table>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-line">+ Thêm dòng</button>
            <hr>
            <button type="submit" class="btn btn-primary">Lưu phiếu</button>
            <a href="<?= site_url('admin/view/import-orders') ?>" class="btn btn-outline-secondary">Hủy</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function(){
  const searchUrl = '<?= site_url('api/search-products') ?>';
  function rowTemplate() {
    const tr = document.createElement('tr');
    tr.innerHTML = '<td><select class="form-select product-sel" style="width:100%"></select></td>' +
      '<td><input type="number" min="1" value="1" class="form-control qty"></td>' +
      '<td><input type="text" class="form-control price" placeholder="Đơn giá"></td>' +
      '<td><button type="button" class="btn btn-sm btn-danger btn-remove">&times;</button></td>';
    return tr;
  }
  function initProductSelect($sel) {
    $sel.select2({
      theme: 'bootstrap-5',
      width: '100%',
      placeholder: 'Chọn sản phẩm',
      allowClear: true,
      minimumInputLength: 0,
      delay: 250,
      ajax: {
        url: searchUrl,
        data: function (p) { return { q: p.term || '' }; },
        processResults: function (data) {
          var r = data.results != null ? data.results : (data.data && data.data.results ? data.data.results : []);
          return { results: r };
        }
      }
    });
  }
  function addLine() {
    const $tbody = $('#lines-table tbody');
    const $tr = $(rowTemplate());
    $tbody.append($tr);
    initProductSelect($tr.find('.product-sel'));
    $tr.find('.btn-remove').on('click', function(){ $tr.remove(); });
  }
  $('#btn-add-line').on('click', addLine);
  $('#import-form').on('submit', function(){
    const lines = [];
    $('#lines-table tbody tr').each(function(){
      const $tr = $(this);
      const pid = parseInt($tr.find('.product-sel').val(), 10);
      const qty = parseInt($tr.find('.qty').val(), 10) || 0;
      const price = ($tr.find('.price').val() || '').trim();
      if (pid && qty > 0 && price) lines.push({ product_id: pid, quantity: qty, unit_price: price });
    });
    document.getElementById('items_json').value = JSON.stringify(lines);
  });
  addLine();
})();
</script>
<?= $this->endSection() ?>
