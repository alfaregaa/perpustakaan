<h1 class="auth-title">Masuk</h1>
<p class="auth-sub">Pilih jenis akun, lalu masukkan username dan password.</p>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= url('auth/login') ?>" class="stack">
    <?= Csrf::field() ?>

    <div class="role-switch">
        <label class="role-option">
            <input type="radio" name="role" value="user" <?= $role !== 'admin' ? 'checked' : '' ?>>
            <span>Siswa</span>
        </label>
        <label class="role-option">
            <input type="radio" name="role" value="admin" <?= $role === 'admin' ? 'checked' : '' ?>>
            <span>Admin</span>
        </label>
    </div>

    <label for="username">Username</label>
    <input id="username" name="username" value="<?= e($username) ?>" required autofocus autocomplete="username">

    <label for="password">Password</label>
    <input id="password" type="password" name="password" required autocomplete="current-password">

    <button type="submit" class="btn btn-primary btn-block">Masuk</button>
</form>

<p class="auth-switch">Siswa baru? <a href="<?= url('auth/register') ?>">Daftar sebagai anggota</a></p>
<p class="hint">Demo: admin / admin123 &middot; siswa / user123</p>
