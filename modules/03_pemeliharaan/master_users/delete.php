<?php
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    if ($id === 'SA-001') {
        set_notifikasi('error', 'Akses Ditolak! Akun Root (SA-001) tidak boleh diubah oleh siapapun.');
        header("Location: index.php");
        exit;
    }

    if (mysqli_query($koneksi, "UPDATE users SET statusUser = 'Nonaktif' WHERE idUser = '$id'")) {
        set_notifikasi('success', 'User berhasil dinonaktifkan (Arsip).');
    }
}

header("Location: index.php");
exit;
