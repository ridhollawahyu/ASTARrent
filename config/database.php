<?php
// config/database.php
$host = "localhost";
$user = "root";
$pass = ""; // Kosongkan jika pakai XAMPP default
$db   = "astarrent_db";

// Koneksi awal (Tanpa milih DB, buat jaga-jaga kalau DB belum ada)
$koneksi_awal = mysqli_connect($host, $user, $pass);

// Koneksi utama
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Fungsi cek error koneksi
if (mysqli_connect_errno()) {
    echo "Gagal koneksi ke MySQL: " . mysqli_connect_error();
}
?>