<?php
/**
 * CONTROLLER: login, pendaftaran siswa, dan logout.
 * Alur sesuai flowchart: Login (admin/siswa) -> Anggota? -> Daftar anggota -> Validasi login -> Dashboard.
 */
class AuthController extends Controller
{
    private $users;
    private $anggota;

    public function __construct()
    {
        $this->users = new User();
        $this->anggota = new Anggota();
    }

    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }

        $error = null;
        $username = '';
        $role = 'user';

        if ($this->isPost()) {
            $this->verifyCsrf();

            $username = $this->input('username');
            $password = (string)($_POST['password'] ?? '');
            $role = $this->input('role') === 'admin' ? 'admin' : 'user';

            $user = $this->users->verify($username, $password);

            if ($user === null) {
                $error = 'Username atau password salah.';
            } elseif ($user['role'] !== $role) {
                $error = $role === 'admin'
                    ? 'Akun ini bukan akun admin. Pilih "Siswa" untuk masuk.'
                    : 'Akun ini adalah akun admin. Pilih "Admin" untuk masuk.';
            } else {
                Auth::login($user);
                Flash::set('success', 'Selamat datang, ' . $user['nama'] . '!');
                $this->redirect('dashboard');
            }
        }

        $this->view('auth/login', [
            'title'    => 'Masuk',
            'error'    => $error,
            'username' => $username,
            'role'     => $role,
        ], 'auth');
    }

    public function register(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }

        $errors = [];
        $old = [];

        if ($this->isPost()) {
            $this->verifyCsrf();

            $old = [
                'nis'      => $this->input('nis'),
                'nama'     => $this->input('nama'),
                'kelas'    => $this->input('kelas'),
                'jurusan'  => $this->input('jurusan'),
                'no_hp'    => $this->input('no_hp'),
                'alamat'   => $this->input('alamat'),
                'username' => $this->input('username'),
            ];
            $password = (string)($_POST['password'] ?? '');
            $konfirmasi = (string)($_POST['password_confirm'] ?? '');

            $v = (new Validator())
                ->required('nis', 'NIS', $old['nis'])
                ->max('nis', 'NIS', $old['nis'], 30)
                ->required('nama', 'Nama lengkap', $old['nama'])
                ->max('nama', 'Nama lengkap', $old['nama'], 150)
                ->max('kelas', 'Kelas', $old['kelas'], 50)
                ->max('jurusan', 'Jurusan', $old['jurusan'], 100)
                ->pattern('no_hp', 'No. HP hanya boleh berisi angka, spasi, + dan -', $old['no_hp'], '/^[0-9+\- ]{1,30}$/')
                ->max('alamat', 'Alamat', $old['alamat'], 255)
                ->required('username', 'Username', $old['username'])
                ->pattern('username', 'Username hanya boleh huruf, angka, titik, dan garis bawah (3-50 karakter)', $old['username'], '/^[A-Za-z0-9_.]{3,50}$/')
                ->required('password', 'Password', $password)
                ->min('password', 'Password', $password, 6);

            if ($password !== $konfirmasi) {
                $v->add('password_confirm', 'Konfirmasi password tidak sama.');
            }
            if (!$v->fails() && $this->anggota->nisExists($old['nis'])) {
                $v->add('nis', 'NIS ini sudah terdaftar.');
            }
            if (!$v->fails() && $this->users->usernameExists($old['username'])) {
                $v->add('username', 'Username sudah digunakan, silakan pilih yang lain.');
            }

            if (!$v->fails()) {
                try {
                    $data = $old;
                    foreach (['kelas', 'jurusan', 'no_hp', 'alamat'] as $field) {
                        $data[$field] = nullIfEmpty($data[$field]);
                    }
                    $this->users->registerSiswa($data, $old['username'], $password);

                    Flash::set('success', 'Pendaftaran berhasil. Silakan login dengan akun yang baru dibuat.');
                    $this->redirect('auth/login');
                } catch (PDOException $e) {
                    error_log('Pendaftaran gagal: ' . $e->getMessage());
                    $v->add('form', 'Pendaftaran gagal disimpan. Silakan coba lagi.');
                }
            }

            $errors = $v->errors();
        }

        $this->view('auth/register', [
            'title'  => 'Daftar Siswa',
            'errors' => $errors,
            'old'    => $old,
        ], 'auth');
    }

    public function logout(): void
    {
        if (!$this->isPost()) {
            $this->redirect('dashboard');
        }
        if (!Csrf::valid()) {
            Flash::set('error', 'Permintaan keluar tidak valid. Silakan coba lagi.');
            $this->redirect('dashboard');
        }

        Auth::logout();
        Flash::set('info', 'Anda telah keluar dari sistem.');
        $this->redirect('auth/login');
    }
}
