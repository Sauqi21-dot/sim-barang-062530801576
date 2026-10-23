<?php
/* =====================================================
   SIM-Barang - logout.php (Pertemuan 6)
   Menghapus seluruh data sesi, lalu kembali ke halaman login.
   ===================================================== */
session_start();

$_SESSION = [];   // kosongkan semua data sesi

// Hapus cookie sesi di browser
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}

session_destroy(); // hancurkan sesi di server

header("Location: login.php?pesan=keluar");
exit;