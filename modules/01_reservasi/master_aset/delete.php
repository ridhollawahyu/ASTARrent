<?php
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header("Location: ../../00_auth/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // PERBAIKAN CERDAS: Kita TIDAK menghapus datanya, kita "Soft Delete" dengan UPDATE!
    $query_soft_delete = "UPDATE aset SET ketersediaanAset = 'Tidak Tersedia', kondisiAset = 'Rusak Total' WHERE idAset = '$id'";

    if (mysqli_query($koneksi, $query_soft_delete)) {
        set_notifikasi('success', 'Berhasil! Aset dipindahkan ke arsip (Tidak Tersedia).');
    } else {
        set_notifikasi('error', 'Terjadi kesalahan pada database!');
    }
}
header("Location: index.php");
exit;
