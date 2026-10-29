<?php
/**
 * CONTROLLER: pengembalian buku.
 * - Admin: melihat semua buku yang sedang dipinjam & riwayat, bisa memproses dan membatalkan pengembalian.
 * - Siswa: hanya bisa mengembalikan buku miliknya sendiri.
 */
class PengembalianController extends Controller
{
    private $pengembalian;
    private $peminjaman;

    public function __construct()
    {
        $this->pengembalian = new Pengembalian();
        $this->peminjaman = new Peminjaman();
    }

    public function index(): void
    {
        $this->requireLogin();
        $isAdmin = Auth::isAdmin();
        $idAnggota = $isAdmin ? null : $this->idAnggotaSaya();
        $q = $this->query('q');

        $this->view('pengembalian/index', [
            'title'   => 'Pengembalian',
            'isAdmin' => $isAdmin,
            'q'       => $q,
            'aktif'   => $this->peminjaman->aktif($idAnggota),
            'riwayat' => $this->pengembalian->all($q, $idAnggota),
            'today'   => date('Y-m-d'),
        ]);
    }

    /** Tanggal pengembalian otomatis = hari ini. */
    public function proses(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->verifyCsrf();

        $idPeminjaman = (int)$this->input('id_peminjaman', '0');
        $idAnggota = Auth::isAdmin() ? null : $this->idAnggotaSaya();

        try {
            $denda = $this->pengembalian->proses($idPeminjaman, date('Y-m-d'), $idAnggota);
            $pesan = $denda > 0
                ? 'Buku berhasil dikembalikan. Terlambat dikenai denda ' . rupiah($denda) . '.'
                : 'Buku berhasil dikembalikan tepat waktu. Terima kasih!';
            Flash::set('success', $pesan);
        } catch (DomainException $e) {
            Flash::set('error', $e->getMessage());
        }

        $this->redirect('pengembalian');
    }

    /** Membatalkan pengembalian yang salah input (admin). */
    public function batal(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int)$this->input('id', '0');
        try {
            $this->pengembalian->batal($id);
            Flash::set('success', 'Pengembalian dibatalkan, status buku kembali "Dipinjam".');
        } catch (DomainException $e) {
            Flash::set('error', $e->getMessage());
        }

        $this->redirect('pengembalian');
    }
}
