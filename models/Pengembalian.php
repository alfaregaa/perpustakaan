<?php
/**
 * MODEL: transaksi pengembalian (tabel pengembalian).
 * Denda dihitung otomatis dari keterlambatan, bukan diisi manual.
 */
class Pengembalian extends Model
{
    /** Riwayat pengembalian dengan filter pencarian & anggota. */
    public function all(string $q = '', ?int $idAnggota = null): array
    {
        $sql = 'SELECT pg.*, p.tanggal_pinjam, p.tanggal_jatuh_tempo, a.nis, a.nama AS nama_anggota, b.judul
                FROM pengembalian pg
                JOIN peminjaman p ON p.id_peminjaman = pg.id_peminjaman
                JOIN anggota a ON a.id_anggota = pg.id_anggota
                JOIN buku b ON b.id_buku = pg.id_buku
                WHERE (a.nama LIKE :q1 OR a.nis LIKE :q2 OR b.judul LIKE :q3)';
        $params = [':q1' => '%' . $q . '%', ':q2' => '%' . $q . '%', ':q3' => '%' . $q . '%'];

        if ($idAnggota !== null) {
            $sql .= ' AND pg.id_anggota = :anggota';
            $params[':anggota'] = $idAnggota;
        }
        $sql .= ' ORDER BY pg.id_pengembalian DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Memproses pengembalian: mencatat denda, menandai peminjaman selesai, dan menambah stok.
     * $idAnggota diisi saat siswa yang memproses, agar siswa hanya bisa mengembalikan miliknya sendiri.
     * Mengembalikan jumlah denda yang dikenakan.
     */
    public function proses(int $idPeminjaman, string $tglKembali, ?int $idAnggota = null): float
    {
        return $this->transaction(function () use ($idPeminjaman, $tglKembali, $idAnggota) {
            $stmt = $this->db->prepare('SELECT * FROM peminjaman WHERE id_peminjaman = ? FOR UPDATE');
            $stmt->execute([$idPeminjaman]);
            $p = $stmt->fetch();

            if (!$p) {
                throw new DomainException('Data peminjaman tidak ditemukan.');
            }
            if ($idAnggota !== null && (int)$p['id_anggota'] !== $idAnggota) {
                throw new DomainException('Anda tidak berhak memproses pengembalian ini.');
            }
            if ($p['status'] !== 'Dipinjam') {
                throw new DomainException('Buku ini sudah dikembalikan sebelumnya.');
            }
            if ($tglKembali < $p['tanggal_pinjam']) {
                throw new DomainException('Tanggal kembali tidak boleh sebelum tanggal pinjam.');
            }

            $denda = self::hitungDenda($p['tanggal_jatuh_tempo'], $tglKembali);

            $this->db->prepare(
                'INSERT INTO pengembalian (id_peminjaman, id_anggota, id_buku, tanggal_kembali, denda)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([$idPeminjaman, $p['id_anggota'], $p['id_buku'], $tglKembali, $denda]);

            $this->db->prepare("UPDATE peminjaman SET status = 'Dikembalikan' WHERE id_peminjaman = ?")
                ->execute([$idPeminjaman]);

            $this->db->prepare('UPDATE buku SET stok = stok + 1 WHERE id_buku = ?')
                ->execute([$p['id_buku']]);

            return (float)$denda;
        });
    }

    /** Admin membatalkan pengembalian: status kembali Dipinjam dan stok dikurangi lagi. */
    public function batal(int $idPengembalian): void
    {
        $this->transaction(function () use ($idPengembalian) {
            $stmt = $this->db->prepare('SELECT * FROM pengembalian WHERE id_pengembalian = ? FOR UPDATE');
            $stmt->execute([$idPengembalian]);
            $r = $stmt->fetch();
            if (!$r) {
                throw new DomainException('Data pengembalian tidak ditemukan.');
            }

            $stmt = $this->db->prepare('SELECT stok FROM buku WHERE id_buku = ? FOR UPDATE');
            $stmt->execute([$r['id_buku']]);
            $buku = $stmt->fetch();
            if (!$buku || (int)$buku['stok'] < 1) {
                throw new DomainException('Pengembalian tidak bisa dibatalkan karena stok buku sudah habis dipinjam orang lain.');
            }

            $this->db->prepare("UPDATE peminjaman SET status = 'Dipinjam' WHERE id_peminjaman = ?")
                ->execute([$r['id_peminjaman']]);
            $this->db->prepare('UPDATE buku SET stok = stok - 1 WHERE id_buku = ?')
                ->execute([$r['id_buku']]);
            $this->db->prepare('DELETE FROM pengembalian WHERE id_pengembalian = ?')
                ->execute([$idPengembalian]);
        });
    }

    /** Jumlah hari melewati jatuh tempo (0 jika tepat waktu / lebih awal). */
    public static function hariTerlambat(string $jatuhTempo, string $tglKembali): int
    {
        $jt = new DateTime($jatuhTempo);
        $kb = new DateTime($tglKembali);
        if ($kb <= $jt) {
            return 0;
        }
        return (int)$jt->diff($kb)->days;
    }

    public static function hitungDenda(string $jatuhTempo, string $tglKembali): float
    {
        return (float)(self::hariTerlambat($jatuhTempo, $tglKembali) * DENDA_PER_HARI);
    }
}
