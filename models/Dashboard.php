<?php
/**
 * MODEL: query ringkasan untuk halaman dashboard.
 */
class Dashboard extends Model
{
    public function admin(): array
    {
        return [
            'totalJudul'       => $this->count('SELECT COUNT(*) FROM buku'),
            'totalEksemplar'   => $this->count('SELECT COALESCE(SUM(stok), 0) FROM buku'),
            'totalAnggota'     => $this->count('SELECT COUNT(*) FROM anggota'),
            'sedangDipinjam'   => $this->count("SELECT COUNT(*) FROM peminjaman WHERE status = 'Dipinjam'"),
            'terlambat'        => $this->count("SELECT COUNT(*) FROM peminjaman WHERE status = 'Dipinjam' AND tanggal_jatuh_tempo < CURDATE()"),
            'dikembalikan'     => $this->count("SELECT COUNT(*) FROM peminjaman WHERE status = 'Dikembalikan'"),
            'totalDenda'       => (float)$this->scalar('SELECT COALESCE(SUM(denda), 0) FROM pengembalian'),
            'transaksiTerbaru' => array_slice((new Peminjaman())->all(), 0, 5),
        ];
    }

    public function siswa(int $idAnggota): array
    {
        return [
            'sedangDipinjam' => $this->count(
                "SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ? AND status = 'Dipinjam'", [$idAnggota]
            ),
            'terlambat' => $this->count(
                "SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ? AND status = 'Dipinjam' AND tanggal_jatuh_tempo < CURDATE()",
                [$idAnggota]
            ),
            'totalPinjam' => $this->count('SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ?', [$idAnggota]),
            'totalDenda'  => (float)$this->scalar(
                'SELECT COALESCE(SUM(denda), 0) FROM pengembalian WHERE id_anggota = ?', [$idAnggota]
            ),
        ];
    }

    private function scalar(string $sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    private function count(string $sql, array $params = []): int
    {
        return (int)$this->scalar($sql, $params);
    }
}
