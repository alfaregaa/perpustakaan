<?php
/**
 * CONTROLLER: data buku. Semua user bisa melihat (katalog); hanya admin yang bisa kelola (CRUD).
 */
class BukuController extends Controller
{
    private $buku;

    public function __construct()
    {
        $this->buku = new Buku();
    }

    public function index(): void
    {
        $this->requireLogin();
        $q = $this->query('q');

        $this->view('buku/index', [
            'title'   => 'Data Buku',
            'q'       => $q,
            'data'    => $this->buku->all($q),
            'isAdmin' => Auth::isAdmin(),
        ]);
    }

    public function form(): void
    {
        $this->requireAdmin();
        $id = (int)$this->query('id', '0');

        $item = [];
        if ($id > 0) {
            $item = $this->buku->find($id) ?? [];
            if (!$item) {
                Flash::set('error', 'Buku tidak ditemukan.');
                $this->redirect('buku');
            }
        }

        $this->view('buku/form', [
            'title'  => $id > 0 ? 'Edit Buku' : 'Tambah Buku',
            'item'   => $item,
            'errors' => [],
        ]);
    }

    public function simpan(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int)$this->input('id', '0');
        $item = [
            'id_buku'      => $id > 0 ? $id : '',
            'kode_buku'    => $this->input('kode_buku'),
            'judul'        => $this->input('judul'),
            'penulis'      => $this->input('penulis'),
            'penerbit'     => $this->input('penerbit'),
            'tahun_terbit' => $this->input('tahun_terbit'),
            'kategori'     => $this->input('kategori'),
            'stok'         => $this->input('stok', '0'),
        ];

        $v = (new Validator())
            ->required('kode_buku', 'Kode buku', $item['kode_buku'])
            ->max('kode_buku', 'Kode buku', $item['kode_buku'], 30)
            ->required('judul', 'Judul', $item['judul'])
            ->max('judul', 'Judul', $item['judul'], 200)
            ->required('penulis', 'Penulis', $item['penulis'])
            ->max('penulis', 'Penulis', $item['penulis'], 150)
            ->max('penerbit', 'Penerbit', $item['penerbit'], 150)
            ->max('kategori', 'Kategori', $item['kategori'], 100)
            ->year('tahun_terbit', 'Tahun terbit', $item['tahun_terbit'])
            ->required('stok', 'Stok', $item['stok'])
            ->integer('stok', 'Stok', $item['stok'], 0, 100000);

        if (!$v->fails() && $this->buku->kodeExists($item['kode_buku'], $id ?: null)) {
            $v->add('kode_buku', 'Kode buku sudah dipakai oleh buku lain.');
        }

        if ($v->fails()) {
            $this->view('buku/form', [
                'title'  => $id > 0 ? 'Edit Buku' : 'Tambah Buku',
                'item'   => $item,
                'errors' => $v->errors(),
            ]);
            return;
        }

        $data = [
            'kode_buku'    => $item['kode_buku'],
            'judul'        => $item['judul'],
            'penulis'      => $item['penulis'],
            'penerbit'     => nullIfEmpty($item['penerbit']),
            'tahun_terbit' => $item['tahun_terbit'] !== '' ? (int)$item['tahun_terbit'] : null,
            'kategori'     => nullIfEmpty($item['kategori']),
            'stok'         => (int)$item['stok'],
        ];

        try {
            if ($id > 0) {
                $this->buku->update($id, $data);
            } else {
                $this->buku->create($data);
            }
            Flash::set('success', 'Data buku berhasil disimpan.');
        } catch (PDOException $e) {
            error_log('Simpan buku gagal: ' . $e->getMessage());
            Flash::set('error', 'Data buku gagal disimpan. Periksa kembali isian Anda.');
        }

        $this->redirect('buku');
    }

    public function hapus(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int)$this->input('id', '0');
        try {
            $this->buku->delete($id);
            Flash::set('success', 'Buku berhasil dihapus.');
        } catch (DomainException $e) {
            Flash::set('error', $e->getMessage());
        }

        $this->redirect('buku');
    }
}
