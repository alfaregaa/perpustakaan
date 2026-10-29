<div class="page-head">
    <div>
        <h1>Data Anggota</h1>
        <p class="muted">Kelola akun dan informasi anggota perpustakaan.</p>
    </div>
    <div class="page-actions"><a class="btn btn-primary" href="<?= e(url('anggota/form')) ?>"><span aria-hidden="true">＋</span> Tambah Anggota</a></div>
</div>

<form method="get" action="<?= e(app_base_path() . '/index.php') ?>" class="toolbar">
    <input type="hidden" name="r" value="anggota">
    <input type="search" name="q" value="<?= e($q) ?>" aria-label="Cari anggota" placeholder="Cari berdasarkan nama atau NIS...">
    <button type="submit" class="btn btn-primary">Cari Anggota</button>
    <?php if ($q !== ''): ?><a class="btn btn-outline" href="<?= e(url('anggota')) ?>">Reset</a><?php endif; ?>
</form>

<section class="card data-card">
    <div class="section-head">
        <div><h2>Daftar Anggota</h2><p class="muted">Informasi anggota yang terdaftar.</p></div>
        <span class="count-chip"><?= count($data) ?> anggota</span>
    </div>
    <div class="table-wrap responsive-table">
        <table>
            <thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>Jurusan</th><th>No. HP</th><th>Alamat</th><th class="actions">Aksi</th></tr></thead>
            <tbody>
            <?php if (!$data): ?><tr><td colspan="7" class="empty"><?= $q !== '' ? 'Anggota tidak ditemukan. Coba kata kunci lain.' : 'Belum ada data anggota. Tekan “Tambah Anggota” untuk memasukkan anggota baru.' ?></td></tr><?php endif; ?>
            <?php foreach ($data as $a): ?>
                <tr>
                    <td data-label="NIS"><span class="member-id"><?= e($a['nis'] ?? '-') ?></span></td>
                    <td data-label="Nama"><strong><?= e($a['nama'] ?? '-') ?></strong></td>
                    <td data-label="Kelas"><?= e($a['kelas'] ?? '-') ?: '-' ?></td>
                    <td data-label="Jurusan"><?= e($a['jurusan'] ?? '-') ?: '-' ?></td>
                    <td data-label="No. HP"><?= e($a['no_hp'] ?? '-') ?: '-' ?></td>
                    <td data-label="Alamat"><?= e($a['alamat'] ?? '-') ?: '-' ?></td>
                    <td data-label="Aksi" class="actions action-buttons">
                        <a class="btn btn-sm btn-outline" href="<?= e(url('anggota/form', ['id' => $a['id_anggota']])) ?>">Edit</a>
                        <form method="post" action="<?= e(url('anggota/hapus')) ?>" class="inline" onsubmit="return confirm('Hapus anggota ini? Akun login terkait juga akan terhapus.');">
                            <?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int)$a['id_anggota'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
