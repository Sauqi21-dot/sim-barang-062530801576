<?php
/* =====================================================
   SIM-Barang - index.php (Pertemuan 6, diperbarui di Pertemuan 7)
   Halaman utama. Hanya bisa dibuka setelah login.
   ===================================================== */
session_start();

// Proteksi halaman: belum login -> kembali ke login.php
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

// Cegah browser menyimpan halaman ini (tombol Back setelah logout)
header("Cache-Control: no-store");

require_once "Barang.php";
require_once "koneksi.php";   // menghasilkan variabel $pdo

function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, "UTF-8");
}

// Ambil data dari MySQL: tabel barang digabung (JOIN) dengan tabel kategori
$daftarBarang = [];
$pesanDb = "";

try {
    $sql = "SELECT b.kode, b.nama, k.nama_kategori, b.jumlah, b.kondisi
            FROM barang b
            JOIN kategori k ON k.id = b.kategori_id
            ORDER BY b.kode";

    foreach ($pdo->query($sql) as $baris) {
        // Setiap baris hasil query dijadikan objek dari class Barang
        $daftarBarang[] = new Barang(
            $baris["kode"],
            $baris["nama"],
            $baris["nama_kategori"],
            (int) $baris["jumlah"],
            $baris["kondisi"]
        );
    }
} catch (PDOException $ex) {
    $pesanDb = "Data barang tidak dapat dimuat dari database.";
}

$barangTerakhir = $daftarBarang ? end($daftarBarang) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIM-Barang - Inventaris Barang Jurusan</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="tambahan-p5.css">
  <link rel="stylesheet" href="tambahan-p6.css">
  <script src="main.js" defer></script>
</head>
<body>

  <header>
    <h1>SIM-Barang</h1>
    <p>Sistem Pengelolaan Inventaris Barang Jurusan</p>
  </header>

  <nav>
    <ul>
      <li><a href="#inventaris">Inventaris Barang</a></li>
      <li><a href="#form-barang">Tambah Barang</a></li>
      <li class="keluar"><a href="logout.php">Keluar (<?= e($_SESSION["username"]) ?>)</a></li>
    </ul>
  </nav>

  <main>

    <section id="inventaris">
      <h2>Tabel Inventaris Barang</h2>
<?php if ($pesanDb !== ""): ?>
      <p class="status gagal" role="alert"><?= e($pesanDb) ?></p>
<?php endif; ?>
      <p class="pencarian">
        <label for="cari">Cari barang</label>
        <input type="search" id="cari" placeholder="Ketik kode, nama, atau kategori...">
      </p>
      <table>
        <caption>Daftar barang laboratorium dan jurusan</caption>
        <thead>
          <tr>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Jumlah</th>
            <th>Kondisi</th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($daftarBarang as $barang): ?>
          <tr>
            <td><?= e($barang->getKode()) ?></td>
            <td><?= e($barang->getNama()) ?></td>
            <td><?= e($barang->getKategori()) ?></td>
            <td><?= $barang->getJumlah() ?></td>
            <td><?= e($barang->getKondisi()) ?></td>
          </tr>
<?php endforeach; ?>
<?php if (!$daftarBarang): ?>
          <tr class="kosong"><td colspan="5">Belum ada data barang.</td></tr>
<?php endif; ?>
        </tbody>
      </table>
<?php if ($barangTerakhir): ?>
      <p class="info-objek">Barang terakhir: <?= e($barangTerakhir->tampilkanInfo()) ?></p>
<?php endif; ?>
    </section>

    <section id="form-barang">
      <h2>Formulir Input Barang</h2>
      <form action="simpan.php" method="post" novalidate>

        <p>
          <label for="kode">Kode Barang</label>
          <input type="text" id="kode" name="kode" placeholder="Contoh: BRG-005" required>
        </p>

        <p>
          <label for="nama">Nama Barang</label>
          <input type="text" id="nama" name="nama" placeholder="Contoh: Printer Canon" required>
        </p>

        <p>
          <label for="kategori">Kategori</label>
          <select id="kategori" name="kategori" required>
            <option value="">-- Pilih Kategori --</option>
            <option value="Elektronik">Elektronik</option>
            <option value="Furnitur">Furnitur</option>
            <option value="Perangkat Jaringan">Perangkat Jaringan</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </p>

        <p>
          <label for="jumlah">Jumlah</label>
          <input type="number" id="jumlah" name="jumlah" placeholder="Contoh: 10" required>
        </p>

        <p>
          <label for="harga">Harga Satuan (Rp)</label>
          <input type="number" id="harga" name="harga" placeholder="Contoh: 3500000" required>
        </p>

        <p>
          <label for="kondisi">Kondisi</label>
          <select id="kondisi" name="kondisi" required>
            <option value="">-- Pilih Kondisi --</option>
            <option value="Baik">Baik</option>
            <option value="Rusak Ringan">Rusak Ringan</option>
            <option value="Rusak Berat">Rusak Berat</option>
          </select>
        </p>

        <p>
          <button type="submit">Simpan Barang</button>
        </p>

      </form>
    </section>

  </main>

  <footer>
    <p>&copy; 2026 SIM-Barang - Ahmad Sauqi Efriansyah (062530801576) - D3 Manajemen Informatika, Politeknik Negeri Sriwijaya</p>
  </footer>

</body>
</html>