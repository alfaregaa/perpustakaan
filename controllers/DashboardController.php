<?php
/**
 * CONTROLLER: halaman dashboard (berbeda untuk admin dan siswa).
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $dashboard = new Dashboard();

        if (Auth::isAdmin()) {
            $data = $dashboard->admin();
            $this->view('dashboard/admin', array_merge(['title' => 'Dashboard'], $data));
            return;
        }

        $idAnggota = $this->idAnggotaSaya();
        $data = $dashboard->siswa($idAnggota);
        $data['aktif'] = (new Peminjaman())->aktif($idAnggota);
        $this->view('dashboard/siswa', array_merge(['title' => 'Dashboard'], $data));
    }
}
