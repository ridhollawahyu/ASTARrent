<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../../../../config/database.php';
require '../../../../../config/functions.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Akses ini hanya bisa dilakukan oleh Tenaga Pendidik.');
    header('Location: ../../../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Tenaga Pendidik') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../../../00_auth/login.php');
}

if (isset($_POST['submit_kategori_draft'])) {
    global $koneksi;
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama_kategori']));
    $id_pembuat = $_SESSION['id'];

    if (!empty($nama)) {
        $id_otomatis = generate_id('KTG', 'kategori', 'idKategori');
        $query = "INSERT INTO kategori (idKategori, namaKategori, statusKategori, tipeKategori, idPembuat) 
                  VALUES ('$id_otomatis', '$nama', 'Draft', 'Aset', '$id_pembuat')";

        if (mysqli_query($koneksi, $query)) {
            $_SESSION['draft_kategori_id'] = $id_otomatis;
            $_SESSION['draft_kategori_nama'] = $nama;
            set_notifikasi('success', 'Kategori baru berhasil dibuat. Form otomatis dikunci ke Draft baru.');
        } else {
            set_notifikasi('error', 'Gagal menyimpan ke database!');
        }
    }
    // Langsung redirect ke halaman sebelumnya!
    header('Location: ../../../transaksi_pengadaan/tendik/create.php');
    exit;
}
