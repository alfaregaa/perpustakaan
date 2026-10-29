<?php
/**
 * Pengaturan umum aplikasi dan aturan bisnis peminjaman.
 */
define('APP_NAME', 'Perpustakaan Digital');
define('APP_DEBUG', false);          // produksi: pesan error teknis disembunyikan
define('APP_TIMEZONE', 'Asia/Jakarta');

// Aturan bisnis peminjaman
define('LAMA_PINJAM_HARI', 7);      // jatuh tempo = tanggal pinjam + 7 hari
define('MAKS_PINJAM_AKTIF', 3);     // maksimal buku dipinjam bersamaan per anggota
define('DENDA_PER_HARI', 1000);     // denda keterlambatan per hari (Rp)
