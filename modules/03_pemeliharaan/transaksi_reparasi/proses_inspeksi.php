<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses: Hanya Staff GA
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (empty($_GET['id']) || empty($_GET['tipe'])) {
    header('Location: index.php');
    exit;
}

$id_barang = mysqli_real_escape_string($koneksi, $_GET['id']);
$tipe_barang = strtolower(mysqli_real_escape_string($koneksi, $_GET['tipe']));
$id_staff_ga = $_SESSION['id'];

// =========================================================================
// 1. CARI TIKET REPARASI YANG SUDAH ADA (Dibuat otomatis dari Pengembalian)
// =========================================================================
$kolom_id_pencarian = ($tipe_barang == 'aset') ? 'idAset' : 'idFasilitas';

$q_cari_tiket = mysqli_query($koneksi, "
    SELECT * FROM reparasi_fasilitas_aset 
    WHERE $kolom_id_pencarian = '$id_barang' AND statusReparasi = 'Menunggu GA' 
    LIMIT 1
");

// Jika tidak ada tiket (misal URL diubah manual oleh user)
if (mysqli_num_rows($q_cari_tiket) == 0) {
    set_notifikasi('error', 'Tiket Reparasi tidak ditemukan atau barang sedang tidak berstatus Menunggu GA.');
    header('Location: index.php');
    exit;
}

$data_tiket = mysqli_fetch_assoc($q_cari_tiket);
$id_reparasi = $data_tiket['idReparasi'];
$kondisi_barang_saat_ini = $data_tiket['klasifikasiKerusakan'];

// =========================================================================
// 2. PROSES UPDATE SAAT TOMBOL DIKLIK (Mulai Reparasi)
// =========================================================================
if (isset($_POST['mulai'])) {
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $waktu_sekarang = date('Y-m-d H:i:s');

    // Gabungkan catatan dari Tendik dengan catatan awal dari Staff GA
    $catatan_baru = $data_tiket['catatanReparasi'];
    if (!empty($catatan)) {
        $catatan_baru .= "\n[Diagnosa Awal GA]: " . $catatan;
    }

    // UPDATE TIKET LAMA MENJADI 'SEDANG DIKERJAKAN'
    $q_update = "UPDATE reparasi_fasilitas_aset SET 
                 idStaffGA = '$id_staff_ga', 
                 tanggalReparasi = '$waktu_sekarang', 
                 statusReparasi = 'Sedang Dikerjakan', 
                 catatanReparasi = '$catatan_baru' 
                 WHERE idReparasi = '$id_reparasi'";

    if (mysqli_query($koneksi, $q_update)) {
        // Update Status Barang menjadi "Sedang Diperbaiki"
        perbarui_status_barang($tipe_barang, $id_barang, 'Sedang Diperbaiki', $kondisi_barang_saat_ini);

        set_notifikasi('success', 'Barang ditarik ke bengkel! Status berubah menjadi Sedang Diperbaiki.');
    } else {
        set_notifikasi('error', 'Gagal memproses data: ' . mysqli_error($koneksi));
    }

    echo "<script>window.location='index.php';</script>";
    exit;
}

include '../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-9">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-wrench me-2"></i>Mulai Proses Reparasi</h5>
            </div>
            <div class="card-body p-4">
                <form action="" method="POST">

                    <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;">
                        <i class="bi bi-info-circle-fill me-2"></i> Memproses <strong><?= strtoupper($tipe_barang) ?> (<?= $id_barang ?>)</strong>.
                        Kondisi dilaporkan: <span class="badge bg-danger"><?= $kondisi_barang_saat_ini ?></span>
                    </div>

                    <div class="mb-4 p-3 rounded" style="background-color: #fff8e1; border: 1px solid #ffc107;">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        <span class="text-dark fw-bold" style="font-size: 0.9rem;">Tindakan Perbaikan / Kanibal akan ditentukan setelah barang selesai diinspeksi di bengkel (pada menu Selesaikan).</span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Catatan Awal / Diagnosa (Opsional)</label>
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Barang diterima dari Tendik, akan dilakukan pembongkaran untuk mengecek kerusakan dalam..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4 border-top pt-3">
                        <a href="index.php" class="btn btn-light fw-bold text-secondary px-4 border">Batal</a>
                        <button type="submit" name="mulai" class="btn btn-astar px-5 fw-bold"><i class="bi bi-tools me-2"></i> Bawa ke Bengkel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>