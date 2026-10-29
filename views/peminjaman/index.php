<div class="page-head">
    <div>
        <h1><?= $isAdmin ? 'Peminjaman' : 'Peminjaman Saya' ?></h1>
        <p class="muted"><?= $isAdmin ? 'Pantau seluruh transaksi peminjaman anggota.' : 'Pantau buku yang sedang dipinjam dan riwayat pengembalianmu.' ?></p>
    </div>
    <div class="page-actions"><a class="btn btn-primary" href="<?= e(url('peminjaman/form')) ?>"><span aria-hidden="true">＋</span> Pinjam Buku</a></div>
</div>

<form method="get" action="<?= e(app_base_path() . '/index.php') ?>" class="toolbar filter-toolbar">
    <input type="hidden" name="r" value="peminjaman">
    <input type="search" name="q" value="<?= e($q) ?>" aria-label="Cari peminjaman" placeholder="<?= $isAdmin ? 'Cari nama anggota, NIS, atau judul buku...' : 'Cari judul buku di riwayatmu...' ?>">
    <select name="status" aria-label="Filter status">
        <option value="">Semua status</option>
        <option value="Dipinjam" <?= $status === 'Dipinjam' ? 'selected' : '' ?>>Sedang Dipinjam</option>
        <option value="Terlambat" <?= $status === 'Terlambat' ? 'selected' : '' ?>>Terlambat</option>
        <option value="Dikembalikan" <?= $status === 'Dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
    </select>
    <button type="submit" class="btn btn-primary">Terapkan</button>
    <?php if ($q !== '' || $status !== ''): ?><a class="btn btn-outline" href="<?= e(url('peminjaman')) ?>">Reset</a><?php endif; ?>
</form>

<section class="card data-card">
    <div class="section-head">
        <div><h2><?= $isAdmin ? 'Daftar Transaksi' : 'Riwayat Peminjaman' ?></h2><p class="muted"><?= count($data) ?> transaksi ditampilkan</p></div>
        <span class="count-chip"><?= count($data) ?> transaksi</span>
    </div>
    <div class="table-wrap responsive-table">
        <table>
            <thead><tr><th>No.</th><?php if ($isAdmin): ?><th>Anggota</th><?php endif; ?><th>Buku</th><th>Tgl Pinjam</th><th>Jatuh Tempo</th><th>Status</th><?php if ($isAdmin): ?><th class="actions">Aksi</th><?php endif; ?></tr></thead>
            <tbody>
            <?php if (!$data): ?>
                <tr><td colspan="<?= $isAdmin ? 7 : 6 ?>" class="empty">
                    <strong><?= $q !== '' || $status !== '' ? 'Tidak ada transaksi yang cocok' : 'Belum ada transaksi peminjaman' ?></strong>
                    <div class="empty-subtext"><?= $q !== '' || $status !== '' ? 'Coba ubah kata kunci atau filter status.' : ($isAdmin ? 'Transaksi akan muncul setelah anggota meminjam buku atau admin mencatat peminjaman.' : 'Kamu bisa mulai dari katalog buku dan menekan tombol Pinjam Buku.') ?></div>
                    <?php if (!$isAdmin && $q === '' && $status === ''): ?><a class="btn btn-primary" href="<?= e(url('buku')) ?>">Buka Katalog Buku</a><?php endif; ?>
                </td></tr>
            <?php endif; ?>
            <?php foreach ($data as $i => $p): ?>
                <tr>
                    <td data-label="No."><?= (int)$i + 1 ?></td>
                    <?php if ($isAdmin): ?><td data-label="Anggota"><strong><?= e($p['nama_anggota'] ?? '-') ?></strong><span class="cell-subtext"><?= e($p['nis'] ?? '-') ?></span></td><?php endif; ?>
                    <td data-label="Buku"><strong><?= e($p['judul'] ?? '-') ?></strong><span class="cell-subtext"><?= e($p['kode_buku'] ?? '') ?></span></td>
                    <td data-label="Tgl Pinjam"><?= e(tgl($p['tanggal_pinjam'] ?? null)) ?></td>
                    <td data-label="Jatuh Tempo"><?= e(tgl($p['tanggal_jatuh_tempo'] ?? null)) ?></td>
                    <td data-label="Status"><?= status_badge($p) ?></td>
                    <?php if ($isAdmin): ?>
                        <td data-label="Aksi" class="actions action-buttons">
                            <?php if (($p['status'] ?? '') === 'Dipinjam'): ?><a class="btn btn-sm btn-outline" href="<?= e(url('peminjaman/form', ['id' => $p['id_peminjaman']])) ?>">Ubah Tanggal</a><?php endif; ?>
                            <form method="post" action="<?= e(url('peminjaman/hapus')) ?>" class="inline" onsubmit="return confirm('Hapus transaksi ini? Stok akan dikembalikan jika buku masih dipinjam.');">
                                <?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int)$p['id_peminjaman'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
