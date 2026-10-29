<div class="page-head">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="muted">Tanda * wajib diisi.</p>
    </div>
    <a class="btn btn-outline" href="<?= url('anggota') ?>">&larr; Kembali</a>
</div>

<div class="card form-card">
    <form method="post" action="<?= url('anggota/simpan') ?>" class="stack">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= old($item, 'id_anggota') ?>">

        <div class="grid-2">
            <div>
                <label for="nis">NIS *</label>
                <input id="nis" name="nis" value="<?= old($item, 'nis') ?>" maxlength="30" required>
                <?= field_error($errors, 'nis') ?>
            </div>
            <div>
                <label for="nama">Nama *</label>
                <input id="nama" name="nama" value="<?= old($item, 'nama') ?>" maxlength="150" required>
                <?= field_error($errors, 'nama') ?>
            </div>
            <div>
                <label for="kelas">Kelas</label>
                <input id="kelas" name="kelas" value="<?= old($item, 'kelas') ?>" maxlength="50">
                <?= field_error($errors, 'kelas') ?>
            </div>
            <div>
                <label for="jurusan">Jurusan</label>
                <input id="jurusan" name="jurusan" value="<?= old($item, 'jurusan') ?>" maxlength="100">
                <?= field_error($errors, 'jurusan') ?>
            </div>
            <div>
                <label for="no_hp">No. HP</label>
                <input id="no_hp" name="no_hp" value="<?= old($item, 'no_hp') ?>" maxlength="30">
                <?= field_error($errors, 'no_hp') ?>
            </div>
        </div>

        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat" maxlength="255"><?= old($item, 'alamat') ?></textarea>
        <?= field_error($errors, 'alamat') ?>

        <div class="form-actions">
            <a class="btn btn-outline" href="<?= url('anggota') ?>">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
