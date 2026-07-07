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

$sesi_id = $_SESSION['id'];

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    if ($id === 'SA-00000') {
        set_notifikasi('error', 'Akses Ditolak! Akun Root (SA-001) tidak boleh diubah oleh siapapun.');
        header("Location: index.php");
        exit;
    }

    if ($id === $sesi_id) {
        set_notifikasi('error', 'Akses Ditolak! Kamu tidak boleh soft delete akunmu sendiri.');
        header("Location: index.php");
        exit;
    }

    if (mysqli_query($koneksi, "UPDATE users SET statusUser = 'Nonaktif' WHERE idUser = '$id'")) {
        set_notifikasi('success', 'User berhasil dinonaktifkan (Arsip).');
    }
}

header("Location: index.php");
exit;
