<div class="page-head">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="muted">Isi data buku dengan lengkap. Tanda * wajib diisi.</p>
    </div>
    <a class="btn btn-outline" href="<?= url('buku') ?>">&larr; Kembali</a>
</div>

<div class="card form-card">
    <form method="post" action="<?= url('buku/simpan') ?>" class="stack">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= old($item, 'id_buku') ?>">

        <div class="grid-2">
            <div>
                <label for="kode_buku">Kode Buku *</label>
                <input id="kode_buku" name="kode_buku" value="<?= old($item, 'kode_buku') ?>" maxlength="30" required>
                <?= field_error($errors, 'kode_buku') ?>
            </div>
            <div>
                <label for="stok">Stok (eksemplar tersedia) *</label>
                <input id="stok" type="number" min="0" name="stok" value="<?= old($item, 'stok', '0') ?>" required>
                <?= field_error($errors, 'stok') ?>
            </div>
        </div>

        <label for="judul">Judul *</label>
        <input id="judul" name="judul" value="<?= old($item, 'judul') ?>" maxlength="200" required>
        <?= field_error($errors, 'judul') ?>

        <div class="grid-2">
            <div>
                <label for="penulis">Penulis *</label>
                <input id="penulis" name="penulis" value="<?= old($item, 'penulis') ?>" maxlength="150" required>
                <?= field_error($errors, 'penulis') ?>
            </div>
            <div>
                <label for="penerbit">Penerbit</label>
                <input id="penerbit" name="penerbit" value="<?= old($item, 'penerbit') ?>" maxlength="150">
                <?= field_error($errors, 'penerbit') ?>
            </div>
            <div>
                <label for="tahun_terbit">Tahun Terbit</label>
                <input id="tahun_terbit" type="number" name="tahun_terbit" value="<?= old($item, 'tahun_terbit') ?>" min="1900" max="<?= (int)date('Y') + 1 ?>">
                <?= field_error($errors, 'tahun_terbit') ?>
            </div>
            <div>
                <label for="kategori">Kategori</label>
                <input id="kategori" name="kategori" value="<?= old($item, 'kategori') ?>" maxlength="100">
                <?= field_error($errors, 'kategori') ?>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn-outline" href="<?= url('buku') ?>">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
