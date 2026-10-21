<?php
session_start();
require __DIR__ . '/../includes/koneksi.php';

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

// No. Anggota: wajib, huruf/angka/tanda hubung (keunikan dicek oleh database)
if ($noAnggota === '') {
    $errors[] = "No. Anggota wajib diisi.";
} elseif (!preg_match('/^[A-Za-z0-9-]+$/', $noAnggota)) {
    $errors[] = "No. Anggota hanya boleh berisi huruf, angka, dan tanda hubung (-).";
}

// Alamat: opsional, maksimal 200 karakter
if (mb_strlen($alamat) > 200) {
    $errors[] = "Alamat maksimal 200 karakter.";
}

// No. HP: opsional, kalau diisi harus 10-15 digit angka
if ($noHp !== '' && !preg_match('/^[0-9]{10,15}$/', $noHp)) {
    $errors[] = "No. HP harus berupa angka sebanyak 10-15 digit.";
}

// Email: opsional, kalau diisi harus valid
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Format email tidak valid.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

// Simpan ke database lewat prepared statement
try {
    $stmt = $pdo->prepare(
        "INSERT INTO anggota (nama, no_anggota, alamat, no_hp, email)
         VALUES (:nama, :no_anggota, :alamat, :no_hp, :email)
         RETURNING id"
    );
    $stmt->execute([
        'nama' => $nama,
        'no_anggota' => $noAnggota,
        'alamat' => $alamat !== '' ? $alamat : null,
        'no_hp' => $noHp !== '' ? $noHp : null,
        'email' => $email !== '' ? $email : null,
    ]);
} catch (PDOException $e) {
    // 23505 = kode PostgreSQL untuk pelanggaran UNIQUE
    if ($e->getCode() === '23505') {
        $pesan = "No. Anggota sudah dipakai, gunakan nomor lain.";
    } else {
        $pesan = "Gagal menyimpan data anggota.";
    }
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => $pesan];
    header('Location: tambah.php');
    exit;
}

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anggota berhasil ditambahkan.'];
header('Location: list.php');
exit;