<div class="page-head">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="muted">
            <?= $mode === 'edit'
                ? 'Anggota dan buku tidak dapat diganti; hanya tanggal yang bisa diubah.'
                : 'Jatuh tempo default ' . LAMA_PINJAM_HARI . ' hari setelah tanggal pinjam.' ?>
        </p>
    </div>
    <a class="btn btn-outline" href="<?= url('peminjaman') ?>">&larr; Kembali</a>
</div>

<div class="card form-card">
    <?php if ($mode === 'create' && !$isAdmin && !$bukuList): ?>
        <p class="empty">Maaf, saat ini tidak ada buku yang tersedia untuk dipinjam.</p>
    <?php else: ?>
    <form method="post" action="<?= url('peminjaman/simpan') ?>" class="stack">
        <?= Csrf::field() ?>
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?= (int)$item['id_peminjaman'] ?>">
        <?php endif; ?>

        <?php if ($mode === 'edit'): ?>
            <label>Anggota</label>
            <input value="<?= e($item['nama_anggota']) ?> (<?= e($item['nis']) ?>)" disabled>
            <label>Buku</label>
            <input value="<?= e($item['judul']) ?>" disabled>
        <?php elseif ($isAdmin): ?>
            <label for="id_anggota">Anggota *</label>
            <select id="id_anggota" name="id_anggota" required>
                <option value="">-- pilih anggota --</option>
                <?php foreach ($anggotaList as $a): ?>
                    <option value="<?= (int)$a['id_anggota'] ?>" <?= (string)$item['id_anggota'] === (string)$a['id_anggota'] ? 'selected' : '' ?>>
                        <?= e($a['nis']) ?> - <?= e($a['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <label>Peminjam</label>
            <input value="<?= e($anggotaSaya['nama']) ?> (<?= e($anggotaSaya['nis']) ?>)" disabled>
        <?php endif; ?>

        <?php if ($mode === 'create'): ?>
            <label for="id_buku">Buku *</label>
            <select id="id_buku" name="id_buku" required>
                <option value="">-- pilih buku --</option>
                <?php foreach ($bukuList as $b): ?>
                    <option value="<?= (int)$b['id_buku'] ?>" <?= (string)$item['id_buku'] === (string)$b['id_buku'] ? 'selected' : '' ?>>
                        <?= e($b['judul']) ?> (<?= e($b['kode_buku']) ?>) - stok <?= (int)$b['stok'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <div class="grid-2">
            <div>
                <label for="tanggal_pinjam">Tanggal Pinjam *</label>
                <input id="tanggal_pinjam" type="date" name="tanggal_pinjam" value="<?= e($item['tanggal_pinjam']) ?>" required>
                <?= field_error($errors, 'tanggal_pinjam') ?>
            </div>
            <div>
                <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo *</label>
                <input id="tanggal_jatuh_tempo" type="date" name="tanggal_jatuh_tempo" value="<?= e($item['tanggal_jatuh_tempo']) ?>" required>
                <?= field_error($errors, 'tanggal_jatuh_tempo') ?>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn btn-outline" href="<?= url('peminjaman') ?>">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
    <?php endif; ?>
</div>
