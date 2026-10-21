<?php
// Dipakai oleh header.php dan semua proses_*.php:
// 1) memulai session, 2) mengisi data awal dari data/*.json (sekali saja).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function muat_data_awal($kunci, $namaFile)
{
    if (isset($_SESSION[$kunci])) {
        return;
    }
    $path = dirname(__DIR__) . '/data/' . $namaFile;
    $isi = is_file($path) ? json_decode(file_get_contents($path), true) : null;
    $_SESSION[$kunci] = is_array($isi) ? $isi : [];
}

muat_data_awal('buku', 'buku.json');
muat_data_awal('anggota', 'anggota.json');