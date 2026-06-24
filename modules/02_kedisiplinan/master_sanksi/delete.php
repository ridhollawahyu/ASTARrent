<?php
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Super Admin.');
    header("Location: ../../00_auth/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // PERBAIKAN CERDAS: Kita TIDAK menghapus datanya, kita "Soft Delete" dengan UPDATE!
    $query_soft_delete = "UPDATE sanksi SET statusSanksi = 'Nonaktif' WHERE idSanksi = '$id'";

    if (mysqli_query($koneksi, $query_soft_delete)) {
        set_notifikasi('success', 'Berhasil! Sanksi dipindahkan ke arsip (Inaktif).');
    } else {
        set_notifikasi('error', 'Terjadi kesalahan pada database!');
    }
}
header("Location: index.php");
exit;
