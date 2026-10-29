<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? APP_NAME) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">Perpustakaan<span>Digital</span></div>

        <nav class="menu">
            <?= nav_link('dashboard', 'Dashboard') ?>

            <?php if (Auth::isAdmin()): ?>
                <div class="menu-label">Data Master</div>
                <?= nav_link('buku', 'Data Buku') ?>
                <?= nav_link('anggota', 'Data Anggota') ?>

                <div class="menu-label">Transaksi</div>
                <?= nav_link('peminjaman', 'Peminjaman') ?>
                <?= nav_link('pengembalian', 'Pengembalian') ?>
            <?php else: ?>
                <div class="menu-label">Layanan</div>
                <?= nav_link('buku', 'Katalog Buku') ?>
                <?= nav_link('peminjaman', 'Peminjaman Saya') ?>
                <?= nav_link('pengembalian', 'Kembalikan Buku') ?>
            <?php endif; ?>
        </nav>

        <div class="sidebar-user">
            <div class="user-name"><?= e(Auth::user()['nama']) ?></div>
            <div class="user-role"><?= Auth::isAdmin() ? 'Administrator' : 'Siswa' ?></div>
            <form method="post" action="<?= url('auth/logout') ?>">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-outline btn-block">Keluar</button>
            </form>
        </div>
    </aside>

    <main class="content">
        <?php require VIEW_PATH . '/partials/flash.php'; ?>
        <?= $content ?>
    </main>
</div>
</body>
</html>
