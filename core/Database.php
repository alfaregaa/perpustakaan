<?php
/**
 * Koneksi database (singleton PDO) - satu koneksi dipakai untuk seluruh request.
 */
class Database
{
    private static $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $cfg = require ROOT_PATH . '/config/database.php';
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
            );
            try {
                self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('Koneksi database gagal: ' . $e->getMessage());
                http_response_code(500);
                echo '<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Koneksi Database Gagal</title><body style="font-family:Arial,sans-serif;max-width:680px;margin:40px auto;padding:0 18px;line-height:1.6"><h2>Koneksi database gagal</h2><p>Periksa konfigurasi di <code>config/database.php</code>: hostname, nama database, username, dan password harus sama dengan panel hosting. Pastikan file SQL juga sudah diimpor ke database yang benar.</p><p>Jika masih gagal, buka Error Logs di panel hosting untuk melihat penyebab teknis.</p></body></html>';
                exit;
            }
        }
        return self::$pdo;
    }
}
