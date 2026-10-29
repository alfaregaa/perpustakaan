<?php
/**
 * CONTROLLER: data anggota (hanya admin).
 */
class AnggotaController extends Controller
{
    private $anggota;

    public function __construct()
    {
        $this->anggota = new Anggota();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $q = $this->query('q');

        $this->view('anggota/index', [
            'title' => 'Data Anggota',
            'q'     => $q,
            'data'  => $this->anggota->all($q),
        ]);
    }

    public function form(): void
    {
        $this->requireAdmin();
        $id = (int)$this->query('id', '0');

        $item = [];
        if ($id > 0) {
            $item = $this->anggota->find($id) ?? [];
            if (!$item) {
                Flash::set('error', 'Anggota tidak ditemukan.');
                $this->redirect('anggota');
            }
        }

        $this->view('anggota/form', [
            'title'  => $id > 0 ? 'Edit Anggota' : 'Tambah Anggota',
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
            'id_anggota' => $id > 0 ? $id : '',
            'nis'        => $this->input('nis'),
            'nama'       => $this->input('nama'),
            'kelas'      => $this->input('kelas'),
            'jurusan'    => $this->input('jurusan'),
            'no_hp'      => $this->input('no_hp'),
            'alamat'     => $this->input('alamat'),
        ];

        $v = (new Validator())
            ->required('nis', 'NIS', $item['nis'])
            ->max('nis', 'NIS', $item['nis'], 30)
            ->required('nama', 'Nama', $item['nama'])
            ->max('nama', 'Nama', $item['nama'], 150)
            ->max('kelas', 'Kelas', $item['kelas'], 50)
            ->max('jurusan', 'Jurusan', $item['jurusan'], 100)
            ->pattern('no_hp', 'No. HP hanya boleh berisi angka, spasi, + dan -', $item['no_hp'], '/^[0-9+\- ]{1,30}$/')
            ->max('alamat', 'Alamat', $item['alamat'], 255);

        if (!$v->fails() && $this->anggota->nisExists($item['nis'], $id ?: null)) {
            $v->add('nis', 'NIS sudah dipakai anggota lain.');
        }

        if ($v->fails()) {
            $this->view('anggota/form', [
                'title'  => $id > 0 ? 'Edit Anggota' : 'Tambah Anggota',
                'item'   => $item,
                'errors' => $v->errors(),
            ]);
            return;
        }

        $data = [
            'nis'     => $item['nis'],
            'nama'    => $item['nama'],
            'kelas'   => nullIfEmpty($item['kelas']),
            'jurusan' => nullIfEmpty($item['jurusan']),
            'no_hp'   => nullIfEmpty($item['no_hp']),
            'alamat'  => nullIfEmpty($item['alamat']),
        ];

        try {
            if ($id > 0) {
                $this->anggota->update($id, $data);
            } else {
                $this->anggota->create($data);
            }
            Flash::set('success', 'Data anggota berhasil disimpan.');
        } catch (PDOException $e) {
            error_log('Simpan anggota gagal: ' . $e->getMessage());
            Flash::set('error', 'Data anggota gagal disimpan. Periksa kembali isian Anda.');
        }

        $this->redirect('anggota');
    }

    public function hapus(): void
    {
        $this->requireAdmin();
        $this->requirePost();
        $this->verifyCsrf();

        $id = (int)$this->input('id', '0');
        try {
            $this->anggota->delete($id);
            Flash::set('success', 'Anggota berhasil dihapus.');
        } catch (DomainException $e) {
            Flash::set('error', $e->getMessage());
        }

        $this->redirect('anggota');
    }
}
