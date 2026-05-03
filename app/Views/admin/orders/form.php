<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">Tạo đơn hàng</div>
    <div class="card-body">
        <?= $this->include('admin/partials/validation_errors') ?>
        <form method="post" action="<?= site_url('admin/view/orders') ?>" id="order-form">
            <?= csrf_field() ?>
            <input type="hidden" name="items_json" id="items_json" value="">
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Khách hàng</label>
                    <select name="customer_id" id="customer_id" class="form-select" style="width:100%" required></select>
                </div>
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Tài xế (tuỳ chọn)</label>
                    <select name="driver_id" id="driver_id" class="form-select" style="width:100%"></select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mã đơn</label>
                    <input type="text" name="order_code" class="form-control" required value="<?= esc(old('order_code')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <?php $st = old('status', 'pending'); ?>
                        <option value="pending" <?= $st === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="shipping" <?= $st === 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Ghi chú giao hàng</label>
                    <textarea name="delivery_notes" class="form-control" rows="2"><?= esc(old('delivery_notes')) ?></textarea>
                </div>
            </div>
            <h5 class="mt-2">Sản phẩm</h5>
            <div class="table-responsive">
                <table class="table table-bordered" id="order-lines">
                    <thead><tr><th>Sản phẩm</th><th style="width:110px">SL</th><th class="text-end">Đơn giá</th><th class="text-end">Thành tiền</th><th></th></tr></thead>
                    <tbody></tbody>
                    <tfoot><tr><th colspan="3" class="text-end">Tổng cộng</th><th class="text-end" id="grand-total">0</th><th></th></tr></tfoot>
                </table>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-line">+ Thêm dòng</button>
            <hr>
            <button type="submit" class="btn btn-primary">Lưu đơn</button>
            <a href="<?= site_url('admin/view/orders') ?>" class="btn btn-outline-secondary">Hủy</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function(){
  const urlCustomers = '<?= site_url('api/search-customers') ?>';
  const urlDrivers = '<?= site_url('api/search-drivers') ?>';
  const urlProducts = '<?= site_url('api/search-products') ?>';
  const urlLinePrice = '<?= site_url('api/line-price') ?>';

  $('#customer_id').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Chọn khách', ajax: {
    url: urlCustomers, delay: 250,
    data: function (p) { return { q: p.term || '' }; },
    processResults: function (d) { return { results: d.results || [] }; }
  }});
  $('#driver_id').select2({ theme: 'bootstrap-5', width: '100%', placeholder: '—', allowClear: true, ajax: {
    url: urlDrivers, delay: 250,
    data: function (p) { return { q: p.term || '' }; },
    processResults: function (d) { return { results: d.results || [] }; }
  }});

  function parseMoney(a) {
    a = String(a || '').replace(/\s/g, '').replace(',', '.');
    var n = parseFloat(a);
    return isNaN(n) ? 0 : n;
  }
  function fmt(n) {
    return (Math.round(n * 100) / 100).toFixed(2);
  }

  function recalcGrand() {
    var sum = 0;
    $('#order-lines tbody tr').each(function () {
      var q = parseInt($(this).find('.qty').val(), 10) || 0;
      var u = parseMoney($(this).attr('data-unit'));
      var line = q * u;
      $(this).find('.line-total').text(fmt(line));
      sum += line;
    });
    $('#grand-total').text(fmt(sum));
  }

  function fetchPrice($tr) {
    var cid = parseInt($('#customer_id').val(), 10);
    var pid = parseInt($tr.find('.product-sel').val(), 10);
    if (!cid || !pid) {
      $tr.attr('data-unit', '0');
      $tr.find('.unit-display').text('—');
      recalcGrand();
      return;
    }
    fetch(urlLinePrice + '?customer_id=' + cid + '&product_id=' + pid, { headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        var u = (d.data && d.data.unit_price) ? String(d.data.unit_price) : '0';
        $tr.attr('data-unit', u);
        $tr.find('.unit-display').text(u);
        recalcGrand();
      })
      .catch(function () { $tr.find('.unit-display').text('!'); });
  }

  function addLine() {
    var $tbody = $('#order-lines tbody');
    var $tr = $('<tr/>');
    $tr.html(
      '<td><select class="form-select product-sel" style="width:100%"></select></td>' +
        '<td><input type="number" min="1" value="1" class="form-control qty"></td>' +
        '<td class="text-end unit-display">—</td>' +
        '<td class="text-end line-total">0</td>' +
        '<td><button type="button" class="btn btn-sm btn-danger btn-remove">&times;</button></td>'
    );
    $tbody.append($tr);
    $tr.find('.product-sel').select2({
      theme: 'bootstrap-5',
      width: '100%',
      placeholder: 'SP',
      ajax: {
        url: urlProducts,
        delay: 250,
        data: function (p) { return { q: p.term || '' }; },
        processResults: function (d) { return { results: d.results || [] }; }
      }
    });
    $tr.find('.product-sel').on('change', function () { fetchPrice($tr); });
    $tr.find('.qty').on('input', function () { recalcGrand(); });
    $tr.find('.btn-remove').on('click', function () {
      $tr.remove();
      recalcGrand();
    });
  }

  $('#customer_id').on('change', function () {
    $('#order-lines tbody tr').each(function () { fetchPrice($(this)); });
  });

  $('#btn-add-line').on('click', addLine);
  $('#order-form').on('submit', function () {
    var lines = [];
    $('#order-lines tbody tr').each(function () {
      var $tr = $(this);
      var pid = parseInt($tr.find('.product-sel').val(), 10);
      var qty = parseInt($tr.find('.qty').val(), 10) || 0;
      if (pid && qty > 0) lines.push({ product_id: pid, quantity: qty });
    });
    document.getElementById('items_json').value = JSON.stringify(lines);
  });
  addLine();
})();
</script>
<?= $this->endSection() ?>
