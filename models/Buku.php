<?php
/**
 * MODEL: data buku (tabel buku). Kolom "stok" = jumlah eksemplar yang tersedia untuk dipinjam.
 */
class Buku extends Model
{
    public function all(string $q = ''): array
    {
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            'SELECT * FROM buku
             WHERE judul LIKE ? OR penulis LIKE ? OR penerbit LIKE ? OR kode_buku LIKE ? OR kategori LIKE ?
             ORDER BY judul ASC'
        );
        $stmt->execute([$like, $like, $like, $like, $like]);
        return $stmt->fetchAll();
    }

    /** Buku yang masih punya stok (untuk pilihan saat meminjam). */
    public function tersedia(): array
    {
        $stmt = $this->db->query('SELECT * FROM buku WHERE stok > 0 ORDER BY judul ASC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM buku WHERE id_buku = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function kodeExists(string $kode, ?int $exceptId = null): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM buku WHERE kode_buku = ? AND id_buku <> ?');
        $stmt->execute([$kode, $exceptId ?? 0]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $d): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO buku (kode_buku, judul, penulis, penerbit, tahun_terbit, kategori, stok)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute($this->values($d));
    }

    public function update(int $id, array $d): void
    {
        $stmt = $this->db->prepare(
            'UPDATE buku SET kode_buku = ?, judul = ?, penulis = ?, penerbit = ?,
                             tahun_terbit = ?, kategori = ?, stok = ?
             WHERE id_buku = ?'
        );
        $stmt->execute(array_merge($this->values($d), [$id]));
    }

    /** Buku yang pernah dipinjam tidak boleh dihapus, agar riwayat transaksi tetap utuh. */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM peminjaman WHERE id_buku = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new DomainException('Buku tidak dapat dihapus karena pernah dipinjam (riwayat transaksi harus tetap tersimpan).');
        }
        $this->db->prepare('DELETE FROM buku WHERE id_buku = ?')->execute([$id]);
    }

    private function values(array $d): array
    {
        return [
            $d['kode_buku'], $d['judul'], $d['penulis'], $d['penerbit'],
            $d['tahun_terbit'], $d['kategori'], (int)$d['stok'],
        ];
    }
}
