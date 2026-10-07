<?php
session_start();

$nama = trim($_POST['nama'] ?? '');
$noAnggota = trim($_POST['no_anggota'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$noHp = trim($_POST['no_hp'] ?? '');
$email = trim($_POST['email'] ?? '');

$errors = [];

// Nama: wajib, hanya huruf, spasi, titik, apostrof, tanda hubung
if ($nama === '') {
    $errors[] = "Nama wajib diisi.";
} elseif (!preg_match("/^[\p{L}\s.'-]+$/u", $nama)) {
    $errors[] = "Nama hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung.";
}

// No. Anggota: wajib, huruf/angka/tanda hubung, dan tidak boleh kembar
if ($noAnggota === '') {
    $errors[] = "No. Anggota wajib diisi.";
} elseif (!preg_match('/^[A-Za-z0-9-]+$/', $noAnggota)) {
    $errors[] = "No. Anggota hanya boleh berisi huruf, angka, dan tanda hubung (-).";
} else {
    foreach ($_SESSION['anggota'] ?? [] as $a) {
        if (strcasecmp($a['no_anggota'], $noAnggota) === 0) {
            $errors[] = "No. Anggota sudah terdaftar.";
            break;
        }
    }
}

// Alamat: opsional, tapi dibatasi panjangnya
if (mb_strlen($alamat) > 200) {
    $errors[] = "Alamat maksimal 200 karakter.";
}

// No. HP: opsional, kalau diisi harus 10-15 digit angka
if ($noHp !== '' && !preg_match('/^[0-9]{10,15}$/', $noHp)) {
    $errors[] = "No. HP harus berupa angka sebanyak 10-15 digit.";
}

// Email: opsional, kalau diisi harus format email yang valid
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Format email tidak valid.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

if (!isset($_SESSION['anggota'])) {
    $_SESSION['anggota'] = [];
}

$_SESSION['anggota'][] = [
    'nama' => $nama,
    'no_anggota' => $noAnggota,
    'alamat' => $alamat,
    'no_hp' => $noHp,
    'email' => $email,
];

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anggota berhasil ditambahkan.'];
header('Location: list.php');
exit;