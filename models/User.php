<?php
/**
 * MODEL: akun login (tabel users).
 */
class User extends Model
{
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Mengembalikan data user jika username & password cocok, selain itu null. */
    public function verify(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        if ($user !== null && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Pendaftaran siswa: membuat data anggota + akun login sekaligus.
     * Keduanya harus berhasil, atau tidak sama sekali (transaksi).
     */
    public function registerSiswa(array $anggota, string $username, string $password): void
    {
        $this->transaction(function () use ($anggota, $username, $password) {
            $stmt = $this->db->prepare(
                'INSERT INTO anggota (nis, nama, kelas, jurusan, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $anggota['nis'], $anggota['nama'], $anggota['kelas'],
                $anggota['jurusan'], $anggota['no_hp'], $anggota['alamat'],
            ]);
            $idAnggota = (int)$this->db->lastInsertId();

            $stmt = $this->db->prepare(
                "INSERT INTO users (username, password, nama, role, id_anggota) VALUES (?, ?, ?, 'user', ?)"
            );
            $stmt->execute([
                $username,
                password_hash($password, PASSWORD_DEFAULT),
                $anggota['nama'],
                $idAnggota,
            ]);
        });
    }
}
