<?php
require __DIR__ . '/includes/koneksi.php';

header('Content-Type: text/plain; charset=utf-8');

$file = __DIR__ . '/data/buku.json';
if (!file_exists($file)) {
    die("File data/buku.json tidak ditemukan.");
}

$data = json_decode(file_get_contents($file), true);
if (!is_array($data)) {
    die("Isi buku.json tidak valid.");
}

// Cek duplikat supaya skrip aman dijalankan lebih dari sekali
$cek = $pdo->prepare("SELECT 1 FROM buku WHERE judul = :judul AND pengarang = :pengarang");

$insert = $pdo->prepare(
    "INSERT INTO buku (judul, pengarang, tahun, isbn, stok, kategori)
     VALUES (:judul, :pengarang, :tahun, :isbn, :stok, :kategori)"
);

$masuk = 0;
$lewat = 0;

$pdo->beginTransaction();
try {
    foreach ($data as $b) {
        $cek->execute([
            'judul' => $b['judul'],
            'pengarang' => $b['pengarang'],
        ]);

        if ($cek->fetchColumn()) {
            $lewat++;
            continue;
        }

        $insert->execute([
            'judul' => $b['judul'],
            'pengarang' => $b['pengarang'],
            'tahun' => (int) $b['tahun'],
            'isbn' => $b['isbn'] ?? null,
            'stok' => (int) $b['stok'],
            'kategori' => $b['kategori'] ?? null,
        ]);
        $masuk++;
    }
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    die("Migrasi gagal, semua perubahan dibatalkan: " . $e->getMessage());
}

echo "Migrasi selesai.\n";
echo "Data baru dimasukkan : $masuk\n";
echo "Dilewati (sudah ada) : $lewat\n";