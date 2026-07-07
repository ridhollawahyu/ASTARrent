<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Akses ini hanya bisa dilakukan oleh Tenaga Pendidik.');
    header("Location: ../../../00_auth/login.php");
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Tenaga Pendidik') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../../00_auth/login.php');
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // PERBAIKAN CERDAS: Kita TIDAK menghapus datanya, kita "Soft Delete" dengan UPDATE!
    $query_soft_delete = "UPDATE kategori SET statusKategori = 'Nonaktif' WHERE idKategori = '$id'";

    if (mysqli_query($koneksi, $query_soft_delete)) {
        set_notifikasi('success', 'Berhasil! Kategori dipindahkan ke arsip (Nonaktif).');
    } else {
        set_notifikasi('error', 'Terjadi kesalahan pada database!');
    }
}
header("Location: index.php");
exit;
