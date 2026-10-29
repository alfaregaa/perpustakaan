<?php
/**
 * Manajemen sesi login: login, logout, dan pengecekan role.
 */
class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && $user['role'] === 'admin';
    }

    /** Simpan data minimal ke session (password hash TIDAK disimpan). */
    public static function login(array $user): void
    {
        session_regenerate_id(true); // cegah session fixation
        $_SESSION['auth'] = [
            'id_user'    => (int)$user['id_user'],
            'username'   => $user['username'],
            'nama'       => $user['nama'],
            'role'       => $user['role'],
            'id_anggota' => $user['id_anggota'] !== null ? (int)$user['id_anggota'] : null,
        ];
        unset($_SESSION['csrf_token']);
    }

    /** Hapus seluruh session dan cookie-nya, lalu mulai session kosong (untuk pesan flash). */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        session_start();
    }
}
