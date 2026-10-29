<h1 class="auth-title">Daftar Anggota</h1>
<p class="auth-sub">Isi data diri dan buat akun untuk mulai meminjam buku.</p>

<?php if (!empty($errors['form'])): ?>
    <div class="alert alert-error"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form method="post" action="<?= url('auth/register') ?>" class="stack">
    <?= Csrf::field() ?>

    <h3 class="form-section">Data Diri</h3>
    <div class="grid-2">
        <div>
            <label for="nis">NIS *</label>
            <input id="nis" name="nis" value="<?= old($old, 'nis') ?>" maxlength="30" required>
            <?= field_error($errors, 'nis') ?>
        </div>
        <div>
            <label for="nama">Nama lengkap *</label>
            <input id="nama" name="nama" value="<?= old($old, 'nama') ?>" maxlength="150" required>
            <?= field_error($errors, 'nama') ?>
        </div>
        <div>
            <label for="kelas">Kelas</label>
            <input id="kelas" name="kelas" value="<?= old($old, 'kelas') ?>" maxlength="50" placeholder="contoh: XI RPL 1">
            <?= field_error($errors, 'kelas') ?>
        </div>
        <div>
            <label for="jurusan">Jurusan</label>
            <input id="jurusan" name="jurusan" value="<?= old($old, 'jurusan') ?>" maxlength="100">
            <?= field_error($errors, 'jurusan') ?>
        </div>
        <div>
            <label for="no_hp">No. HP</label>
            <input id="no_hp" name="no_hp" value="<?= old($old, 'no_hp') ?>" maxlength="30">
            <?= field_error($errors, 'no_hp') ?>
        </div>
    </div>
    <label for="alamat">Alamat</label>
    <textarea id="alamat" name="alamat" maxlength="255"><?= old($old, 'alamat') ?></textarea>
    <?= field_error($errors, 'alamat') ?>

    <h3 class="form-section">Akun Login</h3>
    <div class="grid-2">
        <div>
            <label for="username">Username *</label>
            <input id="username" name="username" value="<?= old($old, 'username') ?>" maxlength="50" required autocomplete="username">
            <?= field_error($errors, 'username') ?>
        </div>
        <div></div>
        <div>
            <label for="password">Password * <small>(min. 6 karakter)</small></label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            <?= field_error($errors, 'password') ?>
        </div>
        <div>
            <label for="password_confirm">Ulangi password *</label>
            <input id="password_confirm" type="password" name="password_confirm" required autocomplete="new-password">
            <?= field_error($errors, 'password_confirm') ?>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Daftar</button>
</form>

<p class="auth-switch">Sudah punya akun? <a href="<?= url('auth/login') ?>">Masuk</a></p>
