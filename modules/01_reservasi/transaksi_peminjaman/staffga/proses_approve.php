<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// 1. PANGGIL DATABASE & FUNCTIONS DI PALING ATAS!
require '../../../../config/database.php';
require '../../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (isset($_POST['submit_tolak'])) {

    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $alasan_tolak = mysqli_real_escape_string($koneksi, trim($_POST['alasan_tolak']));
    $idStaffGA = $_SESSION['id'];

    if (empty($alasan_tolak)) {
        set_notifikasi('error', 'Alasan penolakan tidak boleh kosong!');
    } else {
        $query = "UPDATE transaksi_peminjaman 
                  SET statusPeminjaman = 'Ditolak', 
                      idPenyetuju = '$idStaffGA', 
                      alasanPenolakan_peminjaman = '$alasan_tolak' 
                  WHERE idPeminjaman = '$id_peminjaman'";

        if (mysqli_query($koneksi, $query)) {
            set_notifikasi('success', 'Peminjaman berhasil ditolak oleh Staff GA.');
        } else {
            set_notifikasi('error', 'Gagal menyimpan alasan ke database.');
        }
    }

    header('Location: index.php');
    exit;
}

// ==============================================================================
// 2. PROSES PERSETUJUAN/ACC (GET)
// ==============================================================================
if (isset($_GET['id']) && isset($_GET['aksi']) && $_GET['aksi'] == 'setuju') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $idStaffGA = $_SESSION['id'];
    $tgl_sekarang = date('Y-m-d H:i:s');

    $query_cek = "SELECT idAset, idFasilitas FROM transaksi_peminjaman WHERE idPeminjaman = '$id'";
    $cek = mysqli_query($koneksi, $query_cek);
    $data = mysqli_fetch_assoc($cek);

    // Update status transaksi
    mysqli_query($koneksi, "UPDATE transaksi_peminjaman 
                            SET statusPeminjaman = 'Disetujui', 
                                tanggalPeminjaman = '$tgl_sekarang', 
                                idPenyetuju = '$idStaffGA' 
                            WHERE idPeminjaman = '$id'");

    // Update status barang
    if (!empty($data['idAset'])) {
        perbarui_status_barang('aset', $data['idAset'], 'Dipinjam');
    } else if (!empty($data['idFasilitas'])) {
        perbarui_status_barang('fasilitas', $data['idFasilitas'], 'Dipinjam');
    }

    // Auto reject barang bentrok
    tolak_peminjaman_bentrok($data['idAset'], $data['idFasilitas'], $id);

    set_notifikasi('success', 'Transaksi disetujui! Fasilitas kini berstatus Dipinjam.');
    header("Location: index.php");
    exit;
}

// Jika mengakses URL tanpa parameter apapun
header("Location: index.php");
exit;
