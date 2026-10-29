<?php
/**
 * FRONT CONTROLLER
 * Semua request masuk lewat file ini, contoh: index.php?r=buku/form&id=3
 * Parameter "r" = nama route (lihat config/routes.php).
 */
require __DIR__ . '/core/bootstrap.php';

$routes = require ROOT_PATH . '/config/routes.php';

$route = is_string($_GET['r'] ?? null) ? $_GET['r'] : '';
if ($route === '') {
    $route = Auth::check() ? 'dashboard' : 'auth/login';
}

if (!array_key_exists($route, $routes)) {
    http_response_code(404);
    View::render('errors/404', ['title' => 'Halaman tidak ditemukan']);
    exit;
}

[$controllerName, $action] = $routes[$route];
$controller = new $controllerName();
$controller->$action();
