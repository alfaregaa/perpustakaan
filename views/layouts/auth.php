<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? APP_NAME) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
</head>
<body class="auth-body">
<div class="auth-wrap">
    <div class="auth-brand"><?= e(APP_NAME) ?></div>
    <div class="auth-card">
        <?php require VIEW_PATH . '/partials/flash.php'; ?>
        <?= $content ?>
    </div>
    <p class="auth-foot">Aplikasi Peminjaman Buku Sekolah &middot; UKK RPL</p>
</div>
</body>
</html>
