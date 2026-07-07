<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Super Admin.');
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
$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// AMBIL DATA ASET (JOIN DENGAN KATEGORI BIAR DAPAT NAMA KATEGORINYA)
$query_data = mysqli_query($koneksi, "SELECT * FROM sanksi WHERE idSanksi = '$id'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    set_notifikasi('error', 'Data Sanksi tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// PROSES UPDATE
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jamMinus = mysqli_real_escape_string($koneksi, $_POST['jamMinus']);
    $denda = mysqli_real_escape_string($koneksi, $_POST['denda']);

    // UPDATE DATABASE (Kita cuma nge-update nama dan kondisi. Kategori dan Ketersediaan gak usah dimasukin query!)
    $query_update = "UPDATE sanksi SET 
                        namaSanksi = '$nama',
                        sanksi_jamMinus = $jamMinus,
                        sanksi_denda = $denda
                     WHERE idSanksi = '$id'";

    if (mysqli_query($koneksi, $query_update)) {

        set_notifikasi('success', 'Data Sanksi berhasil diperbarui!');
        header('Location: index.php');
        exit;
    } else {
        set_notifikasi('error', 'Gagal memperbarui data!');
    }
}

include '../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Sanksi</h5>
            </div>
            <div class="card-body p-4">

                <form action="" method="POST">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">ID Sanksi</label>
                            <input type="text" class="form-control bg-light fw-bold text-secondary" value="<?= $data['idSanksi']; ?>" readonly>
                            <small class="text-danger mt-1" style="font-size:11px;">*ID Sanksi tidak dapat diubah.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Nama Sanksi (Merek/Tipe)</label>
                            <input type="text" name="nama" class="form-control" value="<?= $data['namaSanksi']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Nama Sanksi (Merek/Tipe)</label>
                        <input type="number" name="jamMinus" class="form-control" value="<?= $data['sanksi_jamMinus']; ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Nama Sanksi (Merek/Tipe)</label>
                        <input type="number" name="denda" class="form-control" value="<?= $data['sanksi_denda']; ?>" required>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="update" class="btn btn-astar px-5">Simpan Perubahan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>