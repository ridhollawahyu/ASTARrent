<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Akses ini hanya bisa dilakukan oleh Super Admin.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Super Admin') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}

if (isset($_GET['nim'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['nim']);

    // PERBAIKAN CERDAS: Kita TIDAK menghapus datanya, kita "Soft Delete" dengan UPDATE!
    $query_soft_delete = "UPDATE mahasiswa SET statusMahasiswa = 'Nonaktif' WHERE nimMahasiswa = '$id'";

    if (mysqli_query($koneksi, $query_soft_delete)) {
        set_notifikasi('success', 'Berhasil! data Mahasiswa dipindahkan ke arsip (Nonaktif).');
    } else {
        set_notifikasi('error', 'Terjadi kesalahan pada database!');
    }
}
header("Location: index.php");
exit;
