<?php
/**
 * CONTROLLER: transaksi peminjaman.
 * - Admin: melihat semua, mencatat peminjaman untuk anggota mana pun, mengubah tanggal, menghapus.
 * - Siswa: melihat dan mencatat peminjaman untuk dirinya sendiri saja.
 */
class PeminjamanController extends Controller
{
    private $peminjaman;
    private $buku;
    private $anggota;

    public function __construct()
    {
        $this->peminjaman = new Peminjaman();
        $this->buku = new Buku();
        $this->anggota = new Anggota();
    }

    public function index(): void
    {
        $this->requireLogin();
        $isAdmin = Auth::isAdmin();
        $q = $this->query('q');
        $status = $this->query('status');

        $this->view('peminjaman/index', [
            'title'   => 'Peminjaman',
            'isAdmin' => $isAdmin,
            'q'       => $q,
            'status'  => $status,
            'data'    => $this->peminjaman->all($q, $status, $isAdmin ? null : $this->idAnggotaSaya()),
        ]);
    }

    public function form(): void
    {
        $this->requireLogin();
        $id = (int)$this->query('id', '0');

        // Mengubah transaksi yang sudah ada: khusus admin.
        if ($id > 0) {
            $this->requireAdmin();
            $item = $this->peminjaman->find($id);
            if ($item === null || $item['status'] !== 'Dipinjam') {
                Flash::set('error', 'Transaksi ini tidak dapat diubah.');
                $this->redirect('peminjaman');
            }
            $this->view('peminjaman/form', [
                'title'       => 'Ubah Tanggal Peminjaman',
                'mode'        => 'edit',
                'item'        => $item,
                'errors'      => [],
                'isAdmin'     => true,
                'anggotaList' => [],
                'anggotaSaya' => null,
                'bukuList'    => [],
            ]);
            return;
        }

        // Mencatat peminjaman baru
        $isAdmin = Auth::isAdmin();
        $this->view('peminjaman/form', [
            'title'       => 'Tambah Peminjaman',
            'mode'        => 'create',
            'item'        => [
                'id_buku'             => $this->query('id_buku'),
                'id_anggota'          => '',
                'tanggal_pinjam'      => date('Y-m-d'),
                'tanggal_jatuh_tempo' => date('Y-m-d', strtotime('+' . LAMA_PINJAM_HARI . ' days')),
            ],
            'errors'      => [],
            'isAdmin'     => $isAdmin,
            'anggotaList' => $isAdmin ? $this->anggota->all() : [],
            'anggotaSaya' => $isAdmin ? null : $this->anggota->find($this->idAnggotaSaya()),
            'bukuList'    => $this->buku->tersedia(),
        ]);
    }

    public function simpan(): void
    {
        $this->requireLogin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int)$this->input('id', '0');
        $tglPinjam = $this->input('tanggal_pinjam');
        $tglJatuhTempo = $this->input('tanggal_jatuh_tempo');

        // --- Ubah tanggal transaksi (admin) ---
        if ($id > 0) {
            $this->requireAdmin();
            $this->validasiTanggal($tglPinjam, $tglJatuhTempo, '/peminjaman/form', ['id' => $id]);
            try {
                $this->peminjaman->updateTanggal($id, $tglPinjam, $tglJatuhTempo);
                Flash::set('success', 'Tanggal peminjaman berhasil diubah.');
            } catch (DomainException $e) {
                Flash::set('error', $e->getMessage());
            }
            $this->redirect('peminjaman');
        }

        // --- Catat peminjaman baru ---
        $isAdmin = Auth::isAdmin();
        $idAnggota = $isAdmin ? (int)$this->input('id_anggota', '0') : $this->idAnggotaSaya();
        $idBuku = (int)$this->input('id_buku', '0');

        if ($isAdmin && $idAnggota > 0 && $this->anggota->find($idAnggota) === null) {
            Flash::set('error', 'Anggota tidak ditemukan.');
            $this->redirect('peminjaman/form');
        }
        if ($idAnggota <= 0 || $idBuku <= 0) {
            Flash::set('error', 'Anggota dan buku wajib dipilih.');
            $this->redirect('peminjaman/form');
        }

        $this->validasiTanggal($tglPinjam, $tglJatuhTempo, 'peminjaman/form', ['id_buku' => $idBuku]);

        try {
            $this->peminjaman->create($idAnggota, $idBuku, $tglPinjam, $tglJatuhTempo);
            Flash::set('success', 'Peminjaman berhasil dicatat.');
            $this->redirect('peminjaman');
        } catch (DomainException $e) {
            Flash::set('error', $e->getMessage());
            $this->redirect('peminjaman/form', ['id_buku' => $idBuku]);
        } catch (PDOException $e) {
            error_log('Simpan peminjaman gagal: ' . $e->getMessage());
            Flash::set('error', 'Peminjaman gagal disimpan. Silakan coba lagi.');
            $this->redirect('peminjaman/form');
        }
    }

    public function hapus(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int)$this->input('id', '0');
        try {
            $this->peminjaman->delete($id);
            Flash::set('success', 'Transaksi peminjaman berhasil dihapus.');
        } catch (DomainException $e) {
            Flash::set('error', $e->getMessage());
        }

        $this->redirect('peminjaman');
    }

    /** Validasi format tanggal dan urutannya; jika salah, kembali ke form dengan pesan error. */
    private function validasiTanggal(string $tglPinjam, string $tglJatuhTempo, string $route, array $params): void
    {
        $v = (new Validator())
            ->date('tanggal_pinjam', 'Tanggal pinjam', $tglPinjam)
            ->date('tanggal_jatuh_tempo', 'Tanggal jatuh tempo', $tglJatuhTempo);

        if (!$v->fails() && $tglJatuhTempo < $tglPinjam) {
            $v->add('tanggal_jatuh_tempo', 'Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.');
        }

        if ($v->fails()) {
            Flash::set('error', $v->first());
            $this->redirect(ltrim($route, '/'), $params);
        }
    }
}
