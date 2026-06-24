<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Keamanan
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (isset($_GET['id']) && isset($_GET['aksi'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $aksi = $_GET['aksi'];
    $idTendik = $_SESSION['id'];
    $dept_tendik = $_SESSION['departemen'];
    $tgl_sekarang = date('Y-m-d H:i:s');

    if (!validasi_otoritas_tendik($id, $dept_tendik)) {
        set_notifikasi('error', 'Akses Ditolak! Mahasiswa ini bukan dari Program Studi Anda.');
        header("Location: index.php");
        exit;
    }

    // Ambil info barang yang dipinjam
    $query_cek = "SELECT idAset, idFasilitas FROM transaksi_peminjaman WHERE idPeminjaman = '$id'";
    $cek = mysqli_query($koneksi, $query_cek);

    // Pastikan query tidak error
    if (!$cek) {
        die("Error Query Database: " . mysqli_error($koneksi));
    }

    $data = mysqli_fetch_assoc($cek);

    if ($aksi == 'setuju') {
        // 1. Update Transaksi
        $update_peminjaman = "UPDATE transaksi_peminjaman SET statusPeminjaman = 'Disetujui', tanggalPeminjaman = '$tgl_sekarang', idTendik = '$idTendik' WHERE idPeminjaman = '$id'";
        mysqli_query($koneksi, $update_peminjaman);

        // 2. Ubah Status Barang pakai Fungsi Global
        // Perbaikan Logika: Pakai !empty untuk memastikan data benar-benar ada
        if (!empty($data['idAset'])) {
            perbarui_status_barang('aset', $data['idAset'], 'Dipinjam');
        } else if (!empty($data['idFasilitas'])) {
            perbarui_status_barang('fasilitas', $data['idFasilitas'], 'Dipinjam');
        }

        tolak_peminjaman_bentrok($data['idAset'], $data['idFasilitas'], $id);

        set_notifikasi('success', 'Transaksi disetujui! Barang kini berstatus Dipinjam.');
    } else if ($aksi == 'tolak') {
        mysqli_query($koneksi, "UPDATE transaksi_peminjaman SET statusPeminjaman = 'Ditolak', idTendik = '$idTendik' WHERE idPeminjaman = '$id'");
        set_notifikasi('error', 'Transaksi telah ditolak.');
    }
}

// Lakukan pindah halaman jika tidak ada error
header("Location: index.php");
exit;
