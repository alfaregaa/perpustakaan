<?php
/**
 * Merender view: isi halaman dimasukkan ke dalam layout (app = dengan sidebar, auth = halaman login/daftar).
 */
class View
{
    /**
     * Catatan: parameter diberi nama $vars (bukan $data) agar extract() bisa
     * membuat variabel $data yang berisi daftar baris dari controller.
     */
    public static function render(string $view, array $vars = [], ?string $layout = null): void
    {
        $file = VIEW_PATH . '/' . $view . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('View tidak ditemukan: ' . $view);
        }

        $layout = $layout ?? (Auth::check() ? 'app' : 'auth');

        extract($vars, EXTR_SKIP);
        ob_start();
        require $file;
        $content = ob_get_clean();

        require VIEW_PATH . '/layouts/' . $layout . '.php';
    }
}
