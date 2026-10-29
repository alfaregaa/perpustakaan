<div class="page-head">
    <div>
        <h1>Dashboard Admin</h1>
        <p class="muted">Ringkasan perpustakaan per <?= e(date('d/m/Y')) ?></p>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <span class="stat-label">Judul Buku</span>
        <strong class="stat-value"><?= (int)$totalJudul ?></strong>
        <small class="stat-note"><?= (int)$totalEksemplar ?> eksemplar tersedia</small>
    </div>
    <div class="stat">
        <span class="stat-label">Anggota</span>
        <strong class="stat-value"><?= (int)$totalAnggota ?></strong>
        <small class="stat-note">siswa terdaftar</small>
    </div>
    <div class="stat stat-accent">
        <span class="stat-label">Sedang Dipinjam</span>
        <strong class="stat-value"><?= (int)$sedangDipinjam ?></strong>
        <small class="stat-note"><?= (int)$terlambat ?> terlambat</small>
    </div>
    <div class="stat">
        <span class="stat-label">Total Denda</span>
        <strong class="stat-value stat-money"><?= e(rupiah($totalDenda)) ?></strong>
        <small class="stat-note"><?= (int)$dikembalikan ?> transaksi selesai</small>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Transaksi Terbaru</h2>
        <a class="btn btn-sm btn-outline" href="<?= url('peminjaman') ?>">Lihat semua</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Anggota</th>
                <th>Buku</th>
                <th>Tgl Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$transaksiTerbaru): ?>
                <tr><td colspan="5" class="empty">Belum ada transaksi.</td></tr>
            <?php endif; ?>
            <?php foreach ($transaksiTerbaru as $p): ?>
                <tr>
                    <td><?= e($p['nama_anggota']) ?> <span class="muted">(<?= e($p['nis']) ?>)</span></td>
                    <td><?= e($p['judul']) ?></td>
                    <td><?= e(tgl($p['tanggal_pinjam'])) ?></td>
                    <td><?= e(tgl($p['tanggal_jatuh_tempo'])) ?></td>
                    <td><?= status_badge($p) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
