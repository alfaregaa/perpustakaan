<div class="page-head">
    <div>
        <h1><?= $isAdmin ? 'Data Buku' : 'Katalog Buku' ?></h1>
        <p class="muted"><?= count($data) ?> judul ditemukan<?= !$isAdmin ? ' · pilih buku yang ingin dipinjam' : '' ?></p>
    </div>
    <?php if ($isAdmin): ?>
        <div class="page-actions"><a class="btn btn-primary" href="<?= e(url('buku/form')) ?>"><span aria-hidden="true">＋</span> Tambah Buku</a></div>
    <?php endif; ?>
</div>

<form method="get" action="<?= e(app_base_path() . '/index.php') ?>" class="toolbar">
    <input type="hidden" name="r" value="buku">
    <input type="search" name="q" value="<?= e($q) ?>" aria-label="Cari buku" placeholder="Cari judul, penulis, penerbit, kode, atau kategori...">
    <button type="submit" class="btn btn-primary">Cari Buku</button>
    <?php if ($q !== ''): ?><a class="btn btn-outline" href="<?= e(url('buku')) ?>">Reset</a><?php endif; ?>
</form>

<?php if (!$isAdmin): ?>
    <div class="catalog-summary">
        <div><span class="summary-dot available"></span><span>Stok tersedia</span></div>
        <span class="muted">Pilih tombol <strong>Pinjam Buku</strong> untuk mengajukan peminjaman.</span>
    </div>
    <div class="book-grid">
        <?php if (!$data): ?>
            <div class="catalog-empty">
                <div class="empty-icon" aria-hidden="true">⌕</div>
                <strong><?= $q !== '' ? 'Buku tidak ditemukan' : 'Katalog masih kosong' ?></strong>
                <p><?= $q !== '' ? 'Coba kata kunci lain atau tekan Reset.' : 'Belum ada data buku yang tersedia di katalog.' ?></p>
                <?php if ($q !== ''): ?><a class="btn btn-outline" href="<?= e(url('buku')) ?>">Tampilkan Semua Buku</a><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php foreach ($data as $b): ?>
            <article class="book-card">
                <div class="book-card-top">
                    <span class="book-code">Kode: <?= e($b['kode_buku'] ?? '-') ?></span>
                    <span class="badge <?= (int)($b['stok'] ?? 0) > 0 ? 'badge-success' : 'badge-danger' ?>">
                        <?= (int)($b['stok'] ?? 0) > 0 ? 'Tersedia' : 'Stok Habis' ?>
                    </span>
                </div>
                <h2 class="book-title"><?= e($b['judul'] ?? 'Judul tidak tersedia') ?></h2>
                <div class="book-meta">
                    <div><span class="meta-label">Penulis</span><span class="meta-value"><?= e($b['penulis'] ?? '-') ?: '-' ?></span></div>
                    <div><span class="meta-label">Penerbit</span><span class="meta-value"><?= e($b['penerbit'] ?? '-') ?: '-' ?></span></div>
                    <div><span class="meta-label">Tahun</span><span class="meta-value"><?= e($b['tahun_terbit'] ?? '-') ?: '-' ?></span></div>
                    <div><span class="meta-label">Kategori</span><span class="meta-value"><?= e($b['kategori'] ?? '-') ?: '-' ?></span></div>
                </div>
                <div class="book-card-bottom">
                    <div class="stock-count"><strong><?= (int)($b['stok'] ?? 0) ?></strong><span>eksemplar tersedia</span></div>
                    <?php if ((int)($b['stok'] ?? 0) > 0): ?>
                        <a class="btn btn-primary book-action" href="<?= e(url('peminjaman/form', ['id_buku' => $b['id_buku']])) ?>">Pinjam Buku <span aria-hidden="true">→</span></a>
                    <?php else: ?>
                        <button type="button" class="btn btn-disabled" disabled>Stok Habis</button>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <section class="card data-card">
        <div class="section-head"><div><h2>Daftar Inventaris</h2><p class="muted">Kelola detail buku dan stok yang tersedia.</p></div><span class="count-chip"><?= count($data) ?> buku</span></div>
        <div class="table-wrap responsive-table">
            <table>
                <thead><tr><th>Kode</th><th>Judul Buku</th><th>Penulis</th><th>Penerbit</th><th>Tahun</th><th>Kategori</th><th class="num">Stok</th><th class="actions">Aksi</th></tr></thead>
                <tbody>
                <?php if (!$data): ?><tr><td colspan="8" class="empty">Belum ada data buku. Tekan “Tambah Buku” untuk mulai mengisi inventaris.</td></tr><?php endif; ?>
                <?php foreach ($data as $b): ?>
                    <tr>
                        <td data-label="Kode"><?= e($b['kode_buku'] ?? '-') ?></td>
                        <td data-label="Judul Buku"><strong><?= e($b['judul'] ?? '-') ?></strong></td>
                        <td data-label="Penulis"><?= e($b['penulis'] ?? '-') ?></td>
                        <td data-label="Penerbit"><?= e($b['penerbit'] ?? '-') ?></td>
                        <td data-label="Tahun"><?= e($b['tahun_terbit'] ?? '-') ?></td>
                        <td data-label="Kategori"><?= e($b['kategori'] ?? '-') ?></td>
                        <td data-label="Stok" class="num"><span class="badge <?= (int)($b['stok'] ?? 0) > 0 ? 'badge-success' : 'badge-danger' ?>"><?= (int)($b['stok'] ?? 0) ?></span></td>
                        <td data-label="Aksi" class="actions action-buttons">
                            <a class="btn btn-sm btn-outline" href="<?= e(url('buku/form', ['id' => $b['id_buku']])) ?>">Edit</a>
                            <form method="post" action="<?= e(url('buku/hapus')) ?>" class="inline" onsubmit="return confirm('Hapus buku ini?');">
                                <?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int)$b['id_buku'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
