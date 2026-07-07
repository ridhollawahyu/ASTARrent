<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Akses ini hanya bisa dilakukan oleh Staff GA.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Staff GA') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah dinonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    $query_data = mysqli_query($koneksi, "SELECT statusKomponen FROM komponen WHERE idKomponen = '$id'");
    $data = mysqli_fetch_assoc($query_data);

    if (!$data) {
        set_notifikasi('error', 'Data komponen tidak ditemukan.');
    } elseif ($data['statusKomponen'] == 'Sudah Dipakai') {
        set_notifikasi('error', 'Komponen yang sudah dipakai tidak boleh diarsipkan dari tombol hapus. Ubah status lewat halaman edit jika dibutuhkan.');
    } else {
        $query_soft_delete = "UPDATE komponen SET statusKomponen = 'Nonaktif' WHERE idKomponen = '$id'";

        if (mysqli_query($koneksi, $query_soft_delete)) {
            set_notifikasi('success', 'Berhasil! Komponen dipindahkan ke arsip.');
        } else {
            set_notifikasi('error', 'Terjadi kesalahan pada database! ' . mysqli_error($koneksi));
        }
    }
}

header("Location: index.php");
exit;
