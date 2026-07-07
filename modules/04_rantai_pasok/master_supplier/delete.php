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

    if (mysqli_query($koneksi, "UPDATE supplier SET statusSupplier = 'Nonaktif' WHERE idSupplier = '$id'")) {
        set_notifikasi('success', 'Supplier berhasil dinonaktifkan (Arsip).');
    }
}

header("Location: index.php");
exit;
