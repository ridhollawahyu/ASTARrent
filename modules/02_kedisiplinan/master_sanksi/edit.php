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
    $jamMinus = empty($_POST['jamMinus']) ? 0 : (int)$_POST['jamMinus'];
    $denda_str = empty($_POST['denda']) ? '0' : $_POST['denda'];
    $denda = (int)str_replace('.', '', $denda_str);

    // TANGKAP TRIGGER
    $klasifikasi_waktu = mysqli_real_escape_string($koneksi, $_POST['klasifikasi_waktu']);
    $klasifikasi_kondisi = mysqli_real_escape_string($koneksi, $_POST['klasifikasi_kondisi']);

    $query_update = "UPDATE sanksi SET 
                        namaSanksi = '$nama',
                        sanksi_jamMinus = $jamMinus,
                        sanksi_denda = $denda,
                        klasifikasi_waktu = '$klasifikasi_waktu',
                        klasifikasi_kondisi = '$klasifikasi_kondisi'
                     WHERE idSanksi = '$id'";

    if (mysqli_query($koneksi, $query_update)) {
        set_notifikasi('success', 'Aturan Sanksi berhasil diperbarui!');
        header('Location: index.php');
        exit;
    } else {
        set_notifikasi('error', 'Gagal memperbarui data!');
    }
}

include '../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Sanksi</h5>
            </div>
            <div class="card-body p-4">

                <form action="" method="POST">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-astar fw-bold">ID Sanksi</label>
                            <input type="text" class="form-control bg-light fw-bold text-secondary" value="<?= $data['idSanksi']; ?>" readonly>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-astar fw-bold">Nama Sanksi</label>
                            <input type="text" name="nama" class="form-control" value="<?= $data['namaSanksi']; ?>" required>
                        </div>
                    </div>

                    <div class="row mb-4 bg-light p-3 rounded border">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold"><i class="bi bi-clock-history me-1"></i> Trigger Waktu</label>
                            <?php
                            $opsi_waktu = [
                                'Tepat Waktu' => 'Tepat Waktu',
                                'Telat < 24 Jam' => 'Telat < 24 Jam',
                                'Telat 1-3 Hari' => 'Telat 1-3 Hari',
                                'Telat > 3 Hari' => 'Telat > 3 Hari',
                                'Manual' => 'Lainnya / Sanksi Khusus (Manual)'
                            ];
                            echo buat_dropdown_astar('klasifikasi_waktu', $opsi_waktu, $data['klasifikasi_waktu']);
                            ?>
                        </div>
                        <div class="col-md-6 border-start">
                            <label class="form-label text-astar fw-bold"><i class="bi bi-box me-1"></i> Trigger Kondisi Fisik</label>
                            <?php
                            $opsi_kondisi = [
                                'Normal' => 'Normal / Aman',
                                'Berfungsi' => 'Rusak (Masih Berfungsi)',
                                'Tidak Berfungsi' => 'Rusak (Tidak Berfungsi)',
                                'Manual' => 'Lainnya / Sanksi Khusus (Manual)'
                            ];
                            echo buat_dropdown_astar('klasifikasi_kondisi', $opsi_kondisi, $data['klasifikasi_kondisi']);
                            ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-danger fw-bold">Hukuman: Jam Minus</label>
                            <input type="number" name="jamMinus" class="form-control border-danger" value="<?= $data['sanksi_jamMinus']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-danger fw-bold">Hukuman: Denda (Rp)</label>
                            <input type="text" name="denda" class="form-control border-danger" value="<?= number_format($data['sanksi_denda'], 0, '', '.'); ?>" oninput="formatRupiahASTAR(this)">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4 border-top pt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="update" class="btn btn-astar px-5 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>