<?php
/**
 * MODEL: data anggota / siswa (tabel anggota).
 */
class Anggota extends Model
{
    public function all(string $q = ''): array
    {
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare(
            'SELECT * FROM anggota WHERE nama LIKE ? OR nis LIKE ? ORDER BY nama ASC'
        );
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM anggota WHERE id_anggota = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function nisExists(string $nis, ?int $exceptId = null): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM anggota WHERE nis = ? AND id_anggota <> ?');
        $stmt->execute([$nis, $exceptId ?? 0]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $d): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO anggota (nis, nama, kelas, jurusan, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute($this->values($d));
    }

    /** Nama juga disinkronkan ke tabel users bila anggota ini punya akun login. */
    public function update(int $id, array $d): void
    {
        $this->transaction(function () use ($id, $d) {
            $this->db->prepare(
                'UPDATE anggota SET nis = ?, nama = ?, kelas = ?, jurusan = ?, no_hp = ?, alamat = ?
                 WHERE id_anggota = ?'
            )->execute(array_merge($this->values($d), [$id]));

            $this->db->prepare('UPDATE users SET nama = ? WHERE id_anggota = ?')
                ->execute([$d['nama'], $id]);
        });
    }

    /** Anggota yang punya riwayat peminjaman tidak boleh dihapus. */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM peminjaman WHERE id_anggota = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new DomainException('Anggota tidak dapat dihapus karena memiliki riwayat peminjaman.');
        }
        // Akun login terkait ikut terhapus otomatis (ON DELETE CASCADE).
        $this->db->prepare('DELETE FROM anggota WHERE id_anggota = ?')->execute([$id]);
    }

    private function values(array $d): array
    {
        return [
            $d['nis'], $d['nama'], $d['kelas'], $d['jurusan'], $d['no_hp'], $d['alamat'],
        ];
    }
}
