<div class="page-head">
    <div>
        <h1>Pengembalian Buku</h1>
        <p class="muted">Denda keterlambatan <?= rupiah(DENDA_PER_HARI) ?> per hari, dihitung otomatis saat buku dikembalikan.</p>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2><?= $isAdmin ? 'Buku yang Sedang Dipinjam' : 'Buku Saya yang Belum Dikembalikan' ?></h2>
        <span class="badge badge-warning"><?= count($aktif) ?> buku</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <?php if ($isAdmin): ?><th>Anggota</th><?php endif; ?>
                <th>Buku</th>
                <th>Tgl Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Estimasi Denda Hari Ini</th>
                <th class="actions">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$aktif): ?>
                <tr><td colspan="6" class="empty">Tidak ada buku yang perlu dikembalikan.</td></tr>
            <?php endif; ?>
            <?php foreach ($aktif as $p):
                $telat = Pengembalian::hariTerlambat($p['tanggal_jatuh_tempo'], $today);
                $denda = Pengembalian::hitungDenda($p['tanggal_jatuh_tempo'], $today);
            ?>
                <tr>
                    <?php if ($isAdmin): ?>
                        <td><strong><?= e($p['nama_anggota']) ?></strong><br><span class="muted"><?= e($p['nis']) ?></span></td>
                    <?php endif; ?>
                    <td><?= e($p['judul']) ?></td>
                    <td><?= e(tgl($p['tanggal_pinjam'])) ?></td>
                    <td>
                        <?= e(tgl($p['tanggal_jatuh_tempo'])) ?>
                        <?php if ($telat > 0): ?><br><span class="badge badge-danger">Terlambat <?= $telat ?> hari</span><?php endif; ?>
                    </td>
                    <td><?= $denda > 0 ? e(rupiah($denda)) : '<span class="muted">Tidak ada</span>' ?></td>
                    <td class="actions">
                        <form method="post" action="<?= url('pengembalian/proses') ?>" class="inline"
                              onsubmit="return confirm('Proses pengembalian buku ini?<?= $denda > 0 ? ' Denda: ' . e(rupiah($denda)) : '' ?>');">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id_peminjaman" value="<?= (int)$p['id_peminjaman'] ?>">
                            <button type="submit" class="btn btn-sm btn-primary">Kembalikan</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h2>Riwayat Pengembalian</h2>
    </div>
    <form method="get" class="toolbar">
        <input type="hidden" name="r" value="pengembalian">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="<?= $isAdmin ? 'Cari anggota, NIS, atau judul...' : 'Cari judul buku...' ?>">
        <button type="submit" class="btn btn-primary">Cari</button>
        <?php if ($q !== ''): ?>
            <a class="btn btn-outline" href="<?= url('pengembalian') ?>">Reset</a>
        <?php endif; ?>
    </form>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <?php if ($isAdmin): ?><th>Anggota</th><?php endif; ?>
                <th>Buku</th>
                <th>Jatuh Tempo</th>
                <th>Tgl Kembali</th>
                <th>Denda</th>
                <?php if ($isAdmin): ?><th class="actions">Aksi</th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if (!$riwayat): ?>
                <tr><td colspan="6" class="empty">Belum ada riwayat pengembalian.</td></tr>
            <?php endif; ?>
            <?php foreach ($riwayat as $r): ?>
                <tr>
                    <?php if ($isAdmin): ?>
                        <td><strong><?= e($r['nama_anggota']) ?></strong><br><span class="muted"><?= e($r['nis']) ?></span></td>
                    <?php endif; ?>
                    <td><?= e($r['judul']) ?></td>
                    <td><?= e(tgl($r['tanggal_jatuh_tempo'])) ?></td>
                    <td><?= e(tgl($r['tanggal_kembali'])) ?></td>
                    <td>
                        <?php if ((float)$r['denda'] > 0): ?>
                            <span class="badge badge-danger"><?= e(rupiah($r['denda'])) ?></span>
                        <?php else: ?>
                            <span class="badge badge-success">Rp 0</span>
                        <?php endif; ?>
                    </td>
                    <?php if ($isAdmin): ?>
                        <td class="actions">
                            <form method="post" action="<?= url('pengembalian/batal') ?>" class="inline"
                                  onsubmit="return confirm('Batalkan pengembalian ini? Status buku kembali menjadi Dipinjam.');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int)$r['id_pengembalian'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline">Batalkan</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
