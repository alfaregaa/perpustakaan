<?php
/**
 * MODEL: transaksi peminjaman (tabel peminjaman).
 * Aturan: stok buku berkurang saat dipinjam, dan kembali saat dikembalikan.
 */
class Peminjaman extends Model
{
    /**
     * Daftar peminjaman dengan filter pencarian & status.
     * $status: '' (semua) | 'Dipinjam' | 'Dikembalikan' | 'Terlambat'
     * $idAnggota: jika diisi, hanya menampilkan peminjaman milik anggota tsb.
     */
    public function all(string $q = '', string $status = '', ?int $idAnggota = null): array
    {
        $sql = 'SELECT p.*, a.nis, a.nama AS nama_anggota, b.kode_buku, b.judul
                FROM peminjaman p
                JOIN anggota a ON a.id_anggota = p.id_anggota
                JOIN buku b ON b.id_buku = p.id_buku
                WHERE (a.nama LIKE :q1 OR a.nis LIKE :q2 OR b.judul LIKE :q3)';
        // Catatan: dengan ATTR_EMULATE_PREPARES=false, placeholder bernama tidak boleh dipakai dua kali,
        // karena itu setiap pencarian memakai nama yang berbeda.
        $params = [':q1' => '%' . $q . '%', ':q2' => '%' . $q . '%', ':q3' => '%' . $q . '%'];

        if ($idAnggota !== null) {
            $sql .= ' AND p.id_anggota = :anggota';
            $params[':anggota'] = $idAnggota;
        }
        if ($status === 'Terlambat') {
            $sql .= " AND p.status = 'Dipinjam' AND p.tanggal_jatuh_tempo < CURDATE()";
        } elseif (in_array($status, ['Dipinjam', 'Dikembalikan'], true)) {
            $sql .= ' AND p.status = :status';
            $params[':status'] = $status;
        }

        $sql .= ' ORDER BY p.id_peminjaman DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Peminjaman yang belum dikembalikan (untuk halaman pengembalian & dashboard). */
    public function aktif(?int $idAnggota = null): array
    {
        $sql = "SELECT p.*, a.nis, a.nama AS nama_anggota, b.kode_buku, b.judul
                FROM peminjaman p
                JOIN anggota a ON a.id_anggota = p.id_anggota
                JOIN buku b ON b.id_buku = p.id_buku
                WHERE p.status = 'Dipinjam'";
        $params = [];
        if ($idAnggota !== null) {
            $sql .= ' AND p.id_anggota = ?';
            $params[] = $idAnggota;
        }
        $sql .= ' ORDER BY p.tanggal_jatuh_tempo ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, a.nis, a.nama AS nama_anggota, b.kode_buku, b.judul
             FROM peminjaman p
             JOIN anggota a ON a.id_anggota = p.id_anggota
             JOIN buku b ON b.id_buku = p.id_buku
             WHERE p.id_peminjaman = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function countAktifByAnggota(int $idAnggota): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ? AND status = 'Dipinjam'");
        $stmt->execute([$idAnggota]);
        return (int)$stmt->fetchColumn();
    }

    public function sedangDipinjamBuku(int $idAnggota, int $idBuku): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ? AND id_buku = ? AND status = 'Dipinjam'"
        );
        $stmt->execute([$idAnggota, $idBuku]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Mencatat peminjaman baru.
     * Dilakukan dalam transaksi + kunci baris buku (FOR UPDATE) agar stok tidak
     * bisa "minus" saat dua orang meminjam buku terakhir secara bersamaan.
     */
    public function create(int $idAnggota, int $idBuku, string $tglPinjam, string $tglJatuhTempo): int
    {
        return $this->transaction(function () use ($idAnggota, $idBuku, $tglPinjam, $tglJatuhTempo) {
            $stmt = $this->db->prepare('SELECT stok FROM buku WHERE id_buku = ? FOR UPDATE');
            $stmt->execute([$idBuku]);
            $buku = $stmt->fetch();

            if (!$buku) {
                throw new DomainException('Buku tidak ditemukan.');
            }
            if ((int)$buku['stok'] < 1) {
                throw new DomainException('Stok buku ini sedang habis.');
            }
            if ($this->countAktifByAnggota($idAnggota) >= MAKS_PINJAM_AKTIF) {
                throw new DomainException('Anda sudah mencapai batas maksimal ' . MAKS_PINJAM_AKTIF . ' buku yang dipinjam. Kembalikan dulu sebagian buku.');
            }
            if ($this->sedangDipinjamBuku($idAnggota, $idBuku)) {
                throw new DomainException('Anda masih meminjam buku yang sama dan belum mengembalikannya.');
            }

            $this->db->prepare(
                "INSERT INTO peminjaman (id_anggota, id_buku, tanggal_pinjam, tanggal_jatuh_tempo, status)
                 VALUES (?, ?, ?, ?, 'Dipinjam')"
            )->execute([$idAnggota, $idBuku, $tglPinjam, $tglJatuhTempo]);
            $idPeminjaman = (int)$this->db->lastInsertId();

            $this->db->prepare('UPDATE buku SET stok = stok - 1 WHERE id_buku = ?')->execute([$idBuku]);

            return $idPeminjaman;
        });
    }

    /** Admin hanya boleh mengubah tanggal transaksi yang masih berstatus Dipinjam. */
    public function updateTanggal(int $id, string $tglPinjam, string $tglJatuhTempo): void
    {
        $p = $this->find($id);
        if ($p === null) {
            throw new DomainException('Data peminjaman tidak ditemukan.');
        }
        if ($p['status'] !== 'Dipinjam') {
            throw new DomainException('Transaksi yang sudah dikembalikan tidak dapat diubah.');
        }
        $this->db->prepare(
            'UPDATE peminjaman SET tanggal_pinjam = ?, tanggal_jatuh_tempo = ? WHERE id_peminjaman = ?'
        )->execute([$tglPinjam, $tglJatuhTempo, $id]);
    }

    /**
     * Menghapus transaksi (admin). Jika buku masih dipinjam, stoknya dikembalikan
     * agar tidak hilang dari inventaris.
     */
    public function delete(int $id): void
    {
        $this->transaction(function () use ($id) {
            $stmt = $this->db->prepare('SELECT * FROM peminjaman WHERE id_peminjaman = ? FOR UPDATE');
            $stmt->execute([$id]);
            $p = $stmt->fetch();
            if (!$p) {
                throw new DomainException('Data peminjaman tidak ditemukan.');
            }
            if ($p['status'] === 'Dipinjam') {
                $this->db->prepare('UPDATE buku SET stok = stok + 1 WHERE id_buku = ?')->execute([$p['id_buku']]);
            }
            $this->db->prepare('DELETE FROM pengembalian WHERE id_peminjaman = ?')->execute([$id]);
            $this->db->prepare('DELETE FROM peminjaman WHERE id_peminjaman = ?')->execute([$id]);
        });
    }
}
