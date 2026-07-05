<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../config/database.php';
include '../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Finance') {
    set_notifikasi('error', 'Akses Ditolak!');
    echo "<script>window.location='../00_auth/login.php';</script>";
    exit;
}
$q_tugas = mysqli_query($koneksi, "SELECT COUNT(idPengadaan) AS total FROM transaksi_pengadaan WHERE statusPengadaan = 'Harga Diinput Supplier'");
$total_tugas = mysqli_fetch_assoc($q_tugas)['total'];
include '../../components/header.php';
?>
<div class="container mt-4 mb-5">
    <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #1d4197 0%, #2a5bd4 100%); color: white; border-radius: 15px; padding: 30px 40px; margin-bottom: 40px;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2">Halo, Tim Finance!</h2>
                <p class="mb-0 text-white-50">Silakan tinjau penawaran harga dan cairkan anggaran pengadaan.</p>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 p-4 border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-file-earmark-check-fill"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Pencairan Dana Pengadaan</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Tinjau proposal dari Tendik dan Supplier, setujui, dan cairkan dana untuk proses Pengadaan.</p>
                    <a href="../04_rantai_pasok/transaksi_pengadaan/finance/index.php" class="btn btn-astar mt-auto py-2 fw-bold">
                        Cek Antrean
                        <?php if ($total_tugas > 0) echo "<span class='badge bg-danger ms-2'>$total_tugas</span>"; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../../components/footer.php'; ?>