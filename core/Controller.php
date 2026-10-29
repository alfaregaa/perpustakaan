<?php
/**
 * Base class untuk semua Controller: helper untuk view, auth, CSRF, dan input.
 */
abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = null): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $route, array $params = []): void
    {
        redirect($route, $params);
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function requirePost(): void
    {
        if (!$this->isPost()) {
            http_response_code(405);
            $this->view('errors/405', ['title' => 'Metode tidak diizinkan']);
            exit;
        }
    }

    /** Semua form POST wajib membawa token CSRF yang valid. */
    protected function verifyCsrf(): void
    {
        if (!Csrf::valid()) {
            Flash::set('error', 'Sesi formulir sudah berakhir. Silakan ulangi.');
            $this->redirect('dashboard');
        }
    }

    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            Flash::set('error', 'Silakan login terlebih dahulu.');
            $this->redirect('auth/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireLogin();
        if (!Auth::isAdmin()) {
            $this->forbidden();
        }
    }

    /** Id anggota milik siswa yang sedang login. */
    protected function idAnggotaSaya(): int
    {
        $id = Auth::user()['id_anggota'] ?? null;
        if ($id === null) {
            $this->forbidden();
        }
        return (int)$id;
    }

    protected function forbidden(): void
    {
        http_response_code(403);
        $this->view('errors/403', ['title' => 'Akses ditolak']);
        exit;
    }

    protected function input(string $key, string $default = ''): string
    {
        $value = $_POST[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    protected function query(string $key, string $default = ''): string
    {
        $value = $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }
}
