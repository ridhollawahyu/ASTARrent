<?php
// --- FILE: modules/02_kedisiplinan/pelunasan_sanksi/finance/proses_lunas.php ---
session_start();
require '../../../../config/database.php';
require '../../../../config/functions.php';

if (isset($_POST['submit_lunas']) && $_SESSION['role'] === 'Finance') {
    global $koneksi;
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);

    // HANYA MENGUBAH DENDA UANG
    if (mysqli_query($koneksi, "UPDATE mahasiswa SET dendaMahasiswa = 0 WHERE nimMahasiswa = '$nim'")) {
        set_notifikasi('success', 'Pembayaran denda berhasil! Status akun Mahasiswa diperbarui.');
    } else {
        set_notifikasi('error', 'Gagal memproses pelunasan ke database.');
    }
    header('Location: index.php');
    exit;
}
header('Location: index.php');
exit;
