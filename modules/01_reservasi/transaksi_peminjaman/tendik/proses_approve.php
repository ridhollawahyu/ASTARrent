<?php
// --- FILE: modules/01_reservasi/transaksi_peminjaman/tendik/proses_approve.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// 1. PANGGIL DATABASE & FUNCTIONS DI PALING ATAS!
require '../../../../config/database.php';
require '../../../../config/functions.php';

/** @var mysqli $koneksi */

// 2. VALIDASI AKSES
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Akses ini hanya bisa dilakukan oleh Tenaga Pendidik.');
    header('Location: ../../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Tenaga Pendidik') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../../00_auth/login.php');
}

// ==============================================================================
// 3. PROSES PENOLAKAN DARI MODAL POP-UP (POST)
// ==============================================================================
if (isset($_POST['submit_tolak'])) {

    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $alasan_tolak = mysqli_real_escape_string($koneksi, trim($_POST['alasan_tolak']));
    $idTendik = $_SESSION['id'];
    $dept_tendik = $_SESSION['departemen'];

    if (empty($alasan_tolak)) {
        set_notifikasi('error', 'Alasan penolakan tidak boleh kosong!');
    } elseif (!validasi_otoritas_tendik($id_peminjaman, $dept_tendik)) {
        set_notifikasi('error', 'Akses Ditolak! Mahasiswa bukan dari Prodi Anda.');
    } else {
        $query = "UPDATE transaksi_peminjaman 
                  SET statusPeminjaman = 'Ditolak', 
                      idPenyetuju = '$idTendik', 
                      alasanPenolakan_peminjaman = '$alasan_tolak' 
                  WHERE idPeminjaman = '$id_peminjaman'";

        if (mysqli_query($koneksi, $query)) {
            set_notifikasi('success', 'Peminjaman berhasil ditolak.');
        } else {
            set_notifikasi('error', 'Gagal memproses penolakan ke database.');
        }
    }

    header('Location: index.php');
    exit;
}

// ==============================================================================
// 4. PROSES PERSETUJUAN/ACC (GET)
// ==============================================================================
if (isset($_GET['id']) && isset($_GET['aksi']) && $_GET['aksi'] == 'setuju') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $idTendik = $_SESSION['id'];
    $dept_tendik = $_SESSION['departemen'];
    $tgl_sekarang = date('Y-m-d H:i:s');

    if (!validasi_otoritas_tendik($id, $dept_tendik)) {
        set_notifikasi('error', 'Akses Ditolak! Mahasiswa ini bukan dari Program Studi Anda.');
        header("Location: index.php");
        exit;
    }

    $query_cek = "SELECT idAset, idFasilitas FROM transaksi_peminjaman WHERE idPeminjaman = '$id'";
    $cek = mysqli_query($koneksi, $query_cek);
    $data = mysqli_fetch_assoc($cek);

    // Update status transaksi
    mysqli_query($koneksi, "UPDATE transaksi_peminjaman 
                            SET statusPeminjaman = 'Disetujui', 
                                tanggalPeminjaman = '$tgl_sekarang', 
                                idPenyetuju = '$idTendik' 
                            WHERE idPeminjaman = '$id'");

    // Update status ketersediaan barang
    if (!empty($data['idAset'])) {
        perbarui_status_barang('aset', $data['idAset'], 'Dipinjam');
    } else if (!empty($data['idFasilitas'])) {
        perbarui_status_barang('fasilitas', $data['idFasilitas'], 'Dipinjam');
    }

    // Tolak barang yang dipesan bersamaan di waktu yang sama
    tolak_peminjaman_bentrok($data['idAset'], $data['idFasilitas'], $id);

    set_notifikasi('success', 'Transaksi disetujui! Barang kini berstatus Dipinjam.');
    header("Location: index.php");
    exit;
}

// Jika mengakses tanpa parameter
header("Location: index.php");
exit;
