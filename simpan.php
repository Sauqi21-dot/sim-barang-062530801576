<?php
/* =====================================================
   SIM-Barang - simpan.php (Pertemuan 5, diperbarui di Pertemuan 6)
   Menerima data formulir lewat HTTP POST, menghitung
   total estimasi nilai aset, lalu menampilkan ringkasan.
   ===================================================== */

/* ---------- Proteksi: hanya pengguna yang sudah login ---------- */
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

/* ---------- Fungsi ---------- */

// Total estimasi nilai aset = jumlah barang x harga satuan
function hitungTotalAset(int $jumlah, int $hargaSatuan): int
{
    return $jumlah * $hargaSatuan;
}

// Ubah angka menjadi format rupiah, contoh: 3500000 -> Rp 3.500.000
function formatRupiah(int $angka): string
{
    return "Rp " . number_format($angka, 0, ",", ".");
}

// Amankan teks sebelum dicetak ke HTML (mencegah penyisipan tag/script)
function bersihkan(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, "UTF-8");
}

// Cek bilangan bulat positif (maksimal 9 digit)
function bilanganPositif(string $teks): bool
{
    return ctype_digit($teks) && strlen($teks) <= 9 && (int) $teks > 0;
}

/* ---------- Hanya terima data kiriman form (POST) ---------- */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

/* ---------- Tangkap data dari $_POST ---------- */
$kode     = trim($_POST["kode"] ?? "");
$nama     = trim($_POST["nama"] ?? "");
$kategori = trim($_POST["kategori"] ?? "");
$jumlah   = trim($_POST["jumlah"] ?? "");
$harga    = trim($_POST["harga"] ?? "");
$kondisi  = trim($_POST["kondisi"] ?? "");

/* ---------- Validasi di sisi server ---------- */
// Validasi JavaScript bisa dilewati, jadi server tetap harus memeriksa ulang.
$daftarKategori = ["Elektronik", "Furnitur", "Perangkat Jaringan", "Lainnya"];
$daftarKondisi  = ["Baik", "Rusak Ringan", "Rusak Berat"];
$kesalahan = [];

if (!preg_match('/^BRG-\d+$/', $kode)) {
    $kesalahan[] = 'Kode barang harus diawali "BRG-" diikuti angka, contoh: BRG-001.';
}
if (strlen($nama) < 3) {
    $kesalahan[] = "Nama barang minimal 3 karakter.";
}
if (!in_array($kategori, $daftarKategori, true)) {
    $kesalahan[] = "Kategori tidak valid.";
}
if (!bilanganPositif($jumlah)) {
    $kesalahan[] = "Jumlah harus berupa bilangan bulat lebih dari 0.";
}
if (!bilanganPositif($harga)) {
    $kesalahan[] = "Harga satuan harus berupa bilangan bulat lebih dari 0.";
}
if (!in_array($kondisi, $daftarKondisi, true)) {
    $kesalahan[] = "Kondisi tidak valid.";
}

/* ---------- Hitung total jika semua data valid ---------- */
$berhasil = count($kesalahan) === 0;
if ($berhasil) {
    $totalAset = hitungTotalAset((int) $jumlah, (int) $harga);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ringkasan Pemrosesan - SIM-Barang</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="tambahan-p5.css">
</head>
<body>

  <header>
    <h1>SIM-Barang</h1>
    <p>Sistem Pengelolaan Inventaris Barang Jurusan</p>
  </header>

  <main>
    <section id="ringkasan">
<?php if ($berhasil): ?>
      <h2>Ringkasan Pemrosesan Data Barang</h2>
      <p class="status sukses">Data barang berhasil diterima dan diproses oleh server.</p>

      <table class="tabel-ringkasan">
        <caption>Data barang yang diinputkan</caption>
        <tbody>
          <tr><th scope="row">Kode Barang</th><td><?= bersihkan($kode) ?></td></tr>
          <tr><th scope="row">Nama Barang</th><td><?= bersihkan($nama) ?></td></tr>
          <tr><th scope="row">Kategori</th><td><?= bersihkan($kategori) ?></td></tr>
          <tr><th scope="row">Kondisi</th><td><?= bersihkan($kondisi) ?></td></tr>
          <tr><th scope="row">Jumlah</th><td><?= (int) $jumlah ?> unit</td></tr>
          <tr><th scope="row">Harga Satuan</th><td><?= formatRupiah((int) $harga) ?></td></tr>
          <tr class="total"><th scope="row">Total Estimasi Nilai Aset</th><td><?= formatRupiah($totalAset) ?></td></tr>
        </tbody>
      </table>
<?php else: ?>
      <h2>Data Belum Valid</h2>
      <p class="status gagal">Data tidak dapat diproses. Perbaiki hal berikut:</p>
      <ul class="daftar-error">
<?php foreach ($kesalahan as $pesan): ?>
        <li><?= bersihkan($pesan) ?></li>
<?php endforeach; ?>
      </ul>
<?php endif; ?>

      <p class="aksi">
        <a href="index.php#form-barang">Kembali ke Formulir</a>
        <a href="index.php#inventaris" class="sekunder">Lihat Inventaris</a>
      </p>
    </section>
  </main>

  <footer>
    <p>&copy; 2026 SIM-Barang - Ahmad Sauqi Efriansyah (062530801576) - D3 Manajemen Informatika, Politeknik Negeri Sriwijaya</p>
  </footer>

</body>
</html>