<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Đăng nhập') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body class="bg-body-tertiary d-flex align-items-center min-vh-100">
<div class="container" style="max-width: 420px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h1 class="h4 mb-3 text-center">Quản Lý Bán Hàng</h1>
            <p class="text-body-secondary small text-center mb-4">Đăng nhập để tiếp tục</p>
            <?php if ($m = session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small"><?= esc($m) ?></div>
            <?php endif; ?>
            <?php if ($m = session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small"><?= esc($m) ?></div>
            <?php endif; ?>
            <?php
            $errs = session()->getFlashdata('errors');
            if (is_array($errs) && $errs !== []): ?>
                <div class="alert alert-warning py-2 small">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errs as $msg): ?>
                            <li><?= esc(is_array($msg) ? implode(' ', $msg) : (string) $msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= site_url('auth/login') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Tên đăng nhập</label>
                    <input type="text" name="username" class="form-control" value="<?= esc(old('username')) ?>" required autocomplete="username" autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
