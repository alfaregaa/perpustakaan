<?php
/**
 * Fungsi bantu global.
 */

/** Escape output HTML agar aman dari XSS. */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Direktori aplikasi berdasarkan lokasi index.php.
 * Mendukung instalasi di root domain maupun subfolder, mis. /perpustakaan/index.php.
 */
function app_base_path(): string
{
    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $dir = str_replace('\\', '/', dirname($script));
    if ($dir === '/' || $dir === '.' || $dir === '\\') {
        return '';
    }
    return rtrim($dir, '/');
}

/** URL menuju route aplikasi. */
function url(string $route, array $params = []): string
{
    $query = http_build_query(array_merge(['r' => $route], $params));
    return app_base_path() . '/index.php?' . $query;
}

/** URL aset dengan cache-busting otomatis saat file berubah. */
function asset_url(string $path): string
{
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $url = app_base_path() . '/' . $path;
    if (defined('ROOT_PATH')) {
        $file = ROOT_PATH . '/' . $path;
        if (is_file($file)) {
            $url .= '?v=' . (string)filemtime($file);
        }
    }
    return $url;
}

function redirect(string $route, array $params = []): void
{
    header('Location: ' . url($route, $params));
    exit;
}

/** Menandai menu aktif di sidebar. */
function is_active(string $prefix): bool
{
    $current = is_string($_GET['r'] ?? null) ? $_GET['r'] : '';
    return $current === $prefix || strpos($current, $prefix . '/') === 0;
}

function nav_link(string $route, string $label): string
{
    $class = is_active($route) ? 'nav-link active' : 'nav-link';
    return '<a class="' . $class . '" href="' . e(url($route)) . '">' . e($label) . '</a>';
}

/** Nilai lama dari form (dipakai saat validasi gagal). */
function old(array $data, string $key, string $default = ''): string
{
    return e($data[$key] ?? $default);
}

function field_error(array $errors, string $field): string
{
    if (!isset($errors[$field])) {
        return '';
    }
    return '<span class="field-error">' . e($errors[$field]) . '</span>';
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function tgl(?string $tanggal): string
{
    return $tanggal ? date('d/m/Y', strtotime($tanggal)) : '-';
}

function nullIfEmpty(string $value): ?string
{
    return $value === '' ? null : $value;
}

/** Badge status peminjaman; "Terlambat" dihitung dari tanggal jatuh tempo. */
function status_badge(array $p): string
{
    if (($p['status'] ?? '') === 'Dikembalikan') {
        return '<span class="badge badge-success">Dikembalikan</span>';
    }
    if (!empty($p['tanggal_jatuh_tempo']) && $p['tanggal_jatuh_tempo'] < date('Y-m-d')) {
        return '<span class="badge badge-danger">Terlambat</span>';
    }
    return '<span class="badge badge-warning">Dipinjam</span>';
}
