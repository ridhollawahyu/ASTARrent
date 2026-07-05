<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// AMBIL DATA ASET (JOIN DENGAN KATEGORI BIAR DAPAT NAMA KATEGORINYA)
$query_data = mysqli_query($koneksi, "SELECT * FROM kategori WHERE idKategori = '$id'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    set_notifikasi('error', 'Data Kategori tidak ditemukan!');
    echo "<script>window.location='index.php';</script>";
    exit;
}

// PROSES UPDATE
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);

    // UPDATE DATABASE (Kita cuma nge-update nama dan kondisi. Kategori dan Ketersediaan gak usah dimasukin query!)
    $query_update = "UPDATE kategori SET 
                        namaKategori = '$nama'
                     WHERE idKategori = '$id'";

    if (mysqli_query($koneksi, $query_update)) {
        set_notifikasi('success', 'Data Kategori berhasil diperbarui!');
        echo "<script>window.location='index.php';</script>";
        exit;
    } else {
        set_notifikasi('error', 'Gagal memperbarui data!');
    }
}

include '../../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Kategori</h5>
            </div>
            <div class="card-body p-4">

                <form action="" method="POST">

                    <div class="mb-4">
                        <div class="md-6">
                            <label class="form-label text-astar fw-bold">ID Kategori / Barcode</label>
                            <input type="text" class="form-control bg-light fw-bold text-secondary" value="<?= $data['idKategori']; ?>" readonly>
                            <small class="text-danger mt-1" style="font-size:11px;">*ID Kategori tidak dapat diubah.</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Nama Kategori (Merek/Tipe)</label>
                        <input type="text" name="nama" class="form-control" value="<?= $data['namaKategori']; ?>" required>
                    </div>

                    <hr class="my-4">

                    <div class="row mb-4 bg-light p-3 rounded align-items-center">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Tipe</label>
                            <input type="text" class="form-control bg-light fw-bold text-secondary" value="<?= $data['tipeKategori']; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*Tipe Kategori tidak dapat diubah.</small>
                        </div>
                        <div class="col-md-6 text-center border-start">
                            <label class="form-label text-secondary fw-bold">Status</label><br>

                            <!-- BADGE STATUS KETERSEDIAAN (Read-Only) -->
                            <?php if ($data['statusKategori'] == 'Aktif'): ?>
                                <span class="text-success fw-bold px-4 py-2 fs-6">Aktif</span>
                            <?php else: ?>
                                <!-- Warna Abu-abu Gelap untuk Soft Delete (Tidak Tersedia) -->
                                <span class="text-secondary fw-bold px-4 py-2 fs-6">Nonaktif</span>
                            <?php endif; ?>

                            <small class="d-block text-muted mt-2" style="font-size:11px;">*Berubah otomatis berdasarkan Peminjaman/Reparasi.</small>
                        </div>
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

<?php include '../../../../components/footer.php'; ?>