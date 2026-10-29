<?php
/**
 * ROUTING: nama route => [Controller, method].
 * Route yang tidak ada di daftar ini otomatis dianggap 404 (aman dari pemanggilan sembarang).
 */
return [
    // Autentikasi
    'auth/login'    => ['AuthController', 'login'],
    'auth/register' => ['AuthController', 'register'],
    'auth/logout'   => ['AuthController', 'logout'],

    // Dashboard
    'dashboard'     => ['DashboardController', 'index'],

    // Data buku (lihat: semua user, kelola: admin)
    'buku'          => ['BukuController', 'index'],
    'buku/form'     => ['BukuController', 'form'],
    'buku/simpan'   => ['BukuController', 'simpan'],
    'buku/hapus'    => ['BukuController', 'hapus'],

    // Data anggota (admin)
    'anggota'        => ['AnggotaController', 'index'],
    'anggota/form'   => ['AnggotaController', 'form'],
    'anggota/simpan' => ['AnggotaController', 'simpan'],
    'anggota/hapus'  => ['AnggotaController', 'hapus'],

    // Transaksi peminjaman
    'peminjaman'        => ['PeminjamanController', 'index'],
    'peminjaman/form'   => ['PeminjamanController', 'form'],
    'peminjaman/simpan' => ['PeminjamanController', 'simpan'],
    'peminjaman/hapus'  => ['PeminjamanController', 'hapus'],

    // Transaksi pengembalian
    'pengembalian'        => ['PengembalianController', 'index'],
    'pengembalian/proses' => ['PengembalianController', 'proses'],
    'pengembalian/batal'  => ['PengembalianController', 'batal'],
];
