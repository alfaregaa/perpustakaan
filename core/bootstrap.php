<?php
/**
 * BOOTSTRAP: konstanta, autoloader, session. Dipanggil sekali dari index.php.
 */
define('ROOT_PATH', dirname(__DIR__));
define('VIEW_PATH', ROOT_PATH . '/views');

require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/core/helpers.php';

// Autoloader sederhana: nama class harus sama dengan nama file-nya.
spl_autoload_register(function ($class) {
    foreach (['core', 'models', 'controllers'] as $folder) {
        $file = ROOT_PATH . '/' . $folder . '/' . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

date_default_timezone_set(APP_TIMEZONE);

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Penangkap error fatal: mencegah halaman terpotong tanpa pesan.
// Error dicatat ke error log; jika APP_DEBUG = true, pesannya juga ditampilkan.
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err === null || !in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    error_log('FATAL: ' . $err['message'] . ' di ' . $err['file'] . ':' . $err['line']);
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>Error</title><body style="font-family:Arial,sans-serif;max-width:720px;margin:30px auto;padding:0 16px">';
    echo '<h2>Terjadi error pada aplikasi</h2>';
    if (APP_DEBUG) {
        echo '<pre style="background:#fef2f2;padding:12px;white-space:pre-wrap">' . htmlspecialchars($err['message'] . "\n" . $err['file'] . ' : baris ' . $err['line']) . '</pre>';
    } else {
        echo '<p>Silakan cek Error Logs di panel hosting, atau hubungi admin.</p>';
    }
    echo '</body></html>';
});

