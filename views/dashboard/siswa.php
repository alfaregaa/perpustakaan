<div class="page-head">
    <div>
        <h1>Halo, <?= e(Auth::user()['nama']) ?></h1>
        <p class="muted">Selamat datang di perpustakaan sekolah.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-primary" href="<?= url('peminjaman/form') ?>">Pinjam Buku</a>
        <a class="btn btn-outline" href="<?= url('pengembalian') ?>">Kembalikan Buku</a>
    </div>
</div>

<div class="stats">
    <div class="stat stat-accent">
        <span class="stat-label">Sedang Dipinjam</span>
        <strong class="stat-value"><?= (int)$sedangDipinjam ?></strong>
        <small class="stat-note">maks. <?= (int)MAKS_PINJAM_AKTIF ?> buku sekaligus</small>
    </div>
    <div class="stat">
        <span class="stat-label">Terlambat</span>
        <strong class="stat-value"><?= (int)$terlambat ?></strong>
        <small class="stat-note">segera kembalikan</small>
    </div>
    <div class="stat">
        <span class="stat-label">Total Peminjaman</span>
        <strong class="stat-value"><?= (int)$totalPinjam ?></strong>
        <small class="stat-note">sepanjang waktu</small>
    </div>
    <div class="stat">
        <span class="stat-label">Total Denda</span>
        <strong class="stat-value stat-money"><?= e(rupiah($totalDenda)) ?></strong>
        <small class="stat-note">Rp <?= number_format(DENDA_PER_HARI, 0, ',', '.') ?> per hari</small>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Buku yang Sedang Saya Pinjam</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Buku</th>
                <th>Tgl Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$aktif): ?>
                <tr><td colspan="4" class="empty">Anda tidak sedang meminjam buku.</td></tr>
            <?php endif; ?>
            <?php foreach ($aktif as $p): ?>
                <tr>
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
