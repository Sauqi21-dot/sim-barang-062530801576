<?php
/* =====================================================
   SIM-Barang - login.php (Pertemuan 6)
   Autentikasi pengguna dan pencatatan sesi (session).
   ===================================================== */
session_start();

// Data pengguna disimpan dalam array asosiatif: username => password
$daftarPengguna = [
    "admin"   => "password123",
    "petugas" => "inventaris2026",
];

// Jika sudah login, langsung arahkan ke halaman utama
if (isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

$pesanError = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $pesanError = "Username dan password wajib diisi.";
    } elseif (isset($daftarPengguna[$username]) && hash_equals($daftarPengguna[$username], $password)) {
        session_regenerate_id(true);          // ganti ID sesi supaya lebih aman
        $_SESSION["username"] = $username;    // catat bahwa pengguna sudah login
        header("Location: index.php");
        exit;
    } else {
        $pesanError = "Username atau password salah.";
    }
}

// Pesan setelah logout (dikirim lewat alamat: login.php?pesan=keluar)
$pesanInfo = (($_GET["pesan"] ?? "") === "keluar") ? "Anda berhasil keluar." : "";

function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk - SIM-Barang</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="tambahan-p5.css">
  <link rel="stylesheet" href="tambahan-p6.css">
</head>
<body>

  <header>
    <h1>SIM-Barang</h1>
    <p>Sistem Pengelolaan Inventaris Barang Jurusan</p>
  </header>

  <main class="halaman-login">
    <section class="kartu-login">
      <h2>Masuk ke Sistem</h2>

<?php if ($pesanError !== ""): ?>
      <p class="status gagal" role="alert"><?= e($pesanError) ?></p>
<?php elseif ($pesanInfo !== ""): ?>
      <p class="status info" role="status"><?= e($pesanInfo) ?></p>
<?php endif; ?>

      <form class="form-login" action="login.php" method="post">
        <p>
          <label for="username">Username</label>
          <input type="text" id="username" name="username" value="<?= e($username) ?>" placeholder="Masukkan username" autocomplete="username" required autofocus>
        </p>

        <p>
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
        </p>

        <p>
          <button type="submit">Masuk</button>
        </p>
      </form>

      <p class="catatan">Akun uji: admin / password123</p>
    </section>
  </main>

  <footer>
    <p>&copy; 2026 SIM-Barang - Ahmad Sauqi Efriansyah (062530801576) - D3 Manajemen Informatika, Politeknik Negeri Sriwijaya</p>
  </footer>

</body>
</html>