<?php
/**
 * Proteksi CSRF: setiap form POST harus membawa token rahasia dari session.
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(self::token()) . '">';
    }

    public static function valid(): bool
    {
        $sent = $_POST['csrf_token'] ?? '';
        return is_string($sent)
            && $sent !== ''
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $sent);
    }
}
