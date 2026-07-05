<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

if (isset($_POST['submit_tolak'])) {
    global $koneksi;
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $alasan_tolak = mysqli_real_escape_string($koneksi, trim($_POST['alasan_tolak']));
    $idStaffGA = $_SESSION['id'];

    mysqli_query($koneksi, "UPDATE transaksi_peminjaman SET statusPeminjaman = 'Ditolak', idPenyetuju = '$idStaffGA', alasanPenolakan_peminjaman = '$alasan_tolak' WHERE idPeminjaman = '$id_peminjaman'");
    set_notifikasi('success', 'Peminjaman berhasil ditolak oleh Staff GA.');
    header('Location: index.php');
    exit;
}

include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (isset($_GET['id']) && isset($_GET['aksi']) && $_GET['aksi'] == 'setuju') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $idStaffGA = $_SESSION['id'];
    $dept_tendik = $_SESSION['departemen'];
    $tgl_sekarang = date('Y-m-d H:i:s');

    $query_cek = "SELECT idAset, idFasilitas FROM transaksi_peminjaman WHERE idPeminjaman = '$id'";
    $cek = mysqli_query($koneksi, $query_cek);
    $data = mysqli_fetch_assoc($cek);

    mysqli_query($koneksi, "UPDATE transaksi_peminjaman SET statusPeminjaman = 'Disetujui', tanggalPeminjaman = '$tgl_sekarang', idPenyetuju = '$idStaffGA' WHERE idPeminjaman = '$id'");

    if (!empty($data['idAset'])) {
        perbarui_status_barang('aset', $data['idAset'], 'Dipinjam');
    } else if (!empty($data['idFasilitas'])) {
        perbarui_status_barang('fasilitas', $data['idFasilitas'], 'Dipinjam');
    }

    tolak_peminjaman_bentrok($data['idAset'], $data['idFasilitas'], $id);
    set_notifikasi('success', 'Transaksi disetujui! Barang kini berstatus Dipinjam.');

    header("Location: index.php");
    exit;
}

header("Location: index.php");
exit;
