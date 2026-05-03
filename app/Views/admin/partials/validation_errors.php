<?php
$errs = $errors ?? session()->getFlashdata('errors');
if (is_array($errs) && $errs !== []): ?>
    <div class="alert alert-warning">
        <ul class="mb-0">
            <?php foreach ($errs as $msg): ?>
                <li><?= esc(is_array($msg) ? implode(' ', $msg) : (string) $msg) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
