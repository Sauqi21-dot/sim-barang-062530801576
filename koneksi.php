<?php
/* =====================================================
   SIM-Barang - koneksi.php (Pertemuan 7)
   Membuat koneksi ke database MySQL memakai PDO.
   Dipanggil dengan: require_once "koneksi.php";
   Hasilnya variabel $pdo yang dipakai untuk query.
   ===================================================== */

$dbHost = "localhost";
$dbNama = "sim_barang";
$dbUser = "root";   // pengguna bawaan XAMPP
$dbPass = "";       // password bawaan XAMPP kosong

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbNama;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // error dilempar sebagai exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // hasil query berupa array asosiatif
            PDO::ATTR_EMULATE_PREPARES   => false,                   // pakai prepared statement asli
        ]
    );
} catch (PDOException $ex) {
    // Jangan tampilkan detail error ke pengguna (bisa membocorkan info server)
    http_response_code(500);
    exit("Koneksi ke database gagal. Pastikan MySQL di XAMPP sudah berjalan dan database sim_barang sudah diimpor.");
}