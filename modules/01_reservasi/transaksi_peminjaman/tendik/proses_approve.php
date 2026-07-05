<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tolak') {
    ob_start();
    require '../../../../config/database.php';
    require '../../../../config/functions.php';

    if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
        $response = ['status' => 'error', 'pesan' => 'Akses Ditolak!'];
    } else {
        // Panggil fungsi murni dari functions.php
        $response = proses_tolak_peminjaman_ajax($_POST['id_peminjaman'], $_POST['alasan_tolak'], $_SESSION['id'], $_SESSION['departemen']);
    }

    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}

include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

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

    mysqli_query($koneksi, "UPDATE transaksi_peminjaman SET statusPeminjaman = 'Disetujui', tanggalPeminjaman = '$tgl_sekarang', idPenyetuju = '$idTendik' WHERE idPeminjaman = '$id'");

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
