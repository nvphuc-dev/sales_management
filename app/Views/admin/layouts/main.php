<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Quản lý bán hàng') ?></title>
    <meta name="color-scheme" content="light dark">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/css/adminlte.min.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" crossorigin="anonymous">
    <?= $this->renderSection('page_styles') ?>
</head>
<body class="layout-fixed fixed-header fixed-footer sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list"></i></a>
                </li>
                <li class="nav-item d-none d-md-block">
                    <a href="<?= site_url('admin') ?>" class="nav-link">Trang chủ quản trị</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link text-body-secondary small">PHP <?= PHP_VERSION ?></span>
                </li>
            </ul>
        </div>
    </nav>

    <?php $na = $navActive ?? ''; ?>
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="<?= site_url('admin') ?>" class="brand-link">
                <img src="<?= base_url('assets/adminlte/assets/img/AdminLTELogo.png') ?>" alt="Logo" class="brand-image opacity-75 shadow">
                <span class="brand-text fw-light">Bán hàng</span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
                    <li class="nav-item">
                        <a href="<?= site_url('admin') ?>" class="nav-link <?= $na === 'dashboard' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Tổng quan</p>
                        </a>
                    </li>
                    <li class="nav-header">Nghiệp vụ</li>
                    <li class="nav-item">
                        <a href="<?= site_url('admin/view/products') ?>" class="nav-link <?= $na === 'products' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-box-seam"></i><p>Sản phẩm</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('admin/view/customers') ?>" class="nav-link <?= $na === 'customers' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-people"></i><p>Khách hàng</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('admin/view/suppliers') ?>" class="nav-link <?= $na === 'suppliers' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-building"></i><p>Nhà cung cấp</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('admin/view/import-orders') ?>" class="nav-link <?= $na === 'import-orders' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-box-arrow-in-down"></i><p>Nhập hàng</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('admin/view/orders') ?>" class="nav-link <?= $na === 'orders' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-cart3"></i><p>Đơn hàng</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('admin/view/drivers') ?>" class="nav-link <?= $na === 'drivers' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-truck"></i><p>Tài xế</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link text-secondary"><i class="nav-icon bi bi-cash-coin"></i><p>Thu tiền <small>(trên chi tiết đơn)</small></p></span>
                    </li>
                    <li class="nav-header">Báo cáo</li>
                    <li class="nav-item">
                        <span class="nav-link text-secondary"><i class="nav-icon bi bi-file-earmark-spreadsheet"></i><p>Xuất Excel <small>(giai đoạn 5)</small></p></span>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0"><?= esc($title ?? '') ?></h3></div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <?php if ($m = session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= esc($m) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if ($m = session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= esc($m) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">CodeIgniter 4 · AdminLTE 4</div>
        <strong><span class="text-body-secondary">Quản lý bán hàng</span></strong>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="<?= base_url('assets/adminlte/js/adminlte.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.querySelector('.sidebar-wrapper');
    if (el && window.innerWidth > 992 && typeof OverlayScrollbarsGlobal !== 'undefined') {
        OverlayScrollbarsGlobal.OverlayScrollbars(el, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true },
        });
    }
});
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
