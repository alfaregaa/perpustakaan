<?php
/**
 * Pesan sekali tampil (flash message), misalnya "Data berhasil disimpan".
 */
class Flash
{
    /** @param string $type success | error | info */
    public static function set(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function pull(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
}
