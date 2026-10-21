<?php
session_start();
require __DIR__ . '/../includes/koneksi.php';

$id = $_POST['id'] ?? null;
$nama = trim($_POST['nama'] ?? '');
$noAnggota = trim($_POST['no_anggota'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$noHp = trim($_POST['no_hp'] ?? '');
$email = trim($_POST['email'] ?? '');

if (!$id) {
    header('Location: list.php');
    exit;
}

// Validasi identik dengan proses_tambah.php
$errors = [];

if ($nama === '') {
    $errors[] = "Nama wajib diisi.";
} elseif (!preg_match("/^[\p{L}\s.'-]+$/u", $nama)) {
    $errors[] = "Nama hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung.";
}

if ($noAnggota === '') {
    $errors[] = "No. Anggota wajib diisi.";
} elseif (!preg_match('/^[A-Za-z0-9-]+$/', $noAnggota)) {
    $errors[] = "No. Anggota hanya boleh berisi huruf, angka, dan tanda hubung (-).";
}

if (mb_strlen($alamat) > 200) {
    $errors[] = "Alamat maksimal 200 karakter.";
}

if ($noHp !== '' && !preg_match('/^[0-9]{10,15}$/', $noHp)) {
    $errors[] = "No. HP harus berupa angka sebanyak 10-15 digit.";
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Format email tidak valid.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: edit.php?id=' . urlencode($id));
    exit;
}

try {
    $stmt = $pdo->prepare(
        "UPDATE anggota SET nama = :nama, no_anggota = :no_anggota, alamat = :alamat,
         no_hp = :no_hp, email = :email WHERE id = :id"
    );
    $stmt->execute([
        'nama' => $nama,
        'no_anggota' => $noAnggota,
        'alamat' => $alamat !== '' ? $alamat : null,
        'no_hp' => $noHp !== '' ? $noHp : null,
        'email' => $email !== '' ? $email : null,
        'id' => $id,
    ]);
} catch (PDOException $e) {
    // 23505 = pelanggaran UNIQUE (no_anggota dipakai anggota lain)
    if ($e->getCode() === '23505') {
        $pesan = "No. Anggota sudah dipakai, gunakan nomor lain.";
    } else {
        $pesan = "Gagal memperbarui data anggota.";
    }
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => $pesan];
    header('Location: edit.php?id=' . urlencode($id));
    exit;
}

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anggota berhasil diperbarui.'];
header('Location: list.php');
exit;