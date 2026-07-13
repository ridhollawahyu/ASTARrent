<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../config/database.php';
include '../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Keamanan (Hanya Finance)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Finance') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Finance.');
    header('Location: ../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Finance') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah dinonaktifkan.');
    header('Location: ../00_auth/login.php');
    exit;
}
$q_tugas = mysqli_query($koneksi, "SELECT COUNT(idPengadaan) AS total FROM transaksi_pengadaan WHERE statusPengadaan = 'Harga Diinput Supplier'");
$total_tugas = mysqli_fetch_assoc($q_tugas)['total'];

// Query tambahan untuk Finance
$q_selesai = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM transaksi_pengadaan WHERE statusPengadaan = 'Disetujui Finance'");
$total_selesai = mysqli_fetch_assoc($q_selesai)['total'];

$q_dana = mysqli_query($koneksi, "SELECT SUM(totalBiaya) AS total FROM transaksi_pengadaan WHERE statusPengadaan = 'Disetujui Finance'");
$data_dana = mysqli_fetch_assoc($q_dana);
$total_dana = $data_dana['total'] ?: 0;

include '../../components/header.php';
?>

<style>
    .welcome-banner {
        background: linear-gradient(135deg, #1d4197 0%, #2a5bd4 100%);
        color: white;
        border-radius: 15px;
        padding: 30px 40px;
        margin-bottom: 25px;
        box-shadow: 0 10px 20px rgba(29, 65, 151, 0.2);
    }

    .menu-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .menu-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(29, 65, 151, 0.15);
    }

    .icon-box {
        width: 70px;
        height: 70px;
        background-color: #e8f0fe;
        color: #1d4197;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin-bottom: 20px;
    }
</style>

<div class="container mt-4 mb-5">
    <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #1d4197 0%, #2a5bd4 100%); color: white; border-radius: 15px; padding: 30px 40px; margin-bottom: 25px;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2">Halo, Tim Finance!</h2>
                <p class="mb-0 text-white-50">Silakan tinjau penawaran harga dan cairkan anggaran pengadaan.</p>
            </div>
        </div>
    </div>

    <!-- Metric Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Antrean Pencairan -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #ffc107 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Antrean E-Proc</p>
                        <h4 class="fw-bold mb-0 text-warning"><?= $total_tugas ?> Request</h4>
                    </div>
                    <div class="rounded-circle p-3 bg-warning-subtle text-warning" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-file-earmark-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2: Pengadaan Selesai -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #198754 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Pengadaan Selesai</p>
                        <h4 class="fw-bold mb-0 text-success"><?= $total_selesai ?> Transaksi</h4>
                    </div>
                    <div class="rounded-circle p-3 bg-success-subtle text-success" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 3: Anggaran Tersalurkan -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #1d4197 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Anggaran Terpakai</p>
                        <h4 class="fw-bold mb-0 text-primary" style="font-size: 1.15rem;">Rp <?= number_format($total_dana, 0, ',', '.') ?></h4>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-file-earmark-check-fill"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Pencairan Dana Pengadaan</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Tinjau proposal dari Tendik dan Supplier, setujui, dan cairkan dana untuk proses Pengadaan.</p>
                    <a href="../04_rantai_pasok/transaksi_pengadaan/finance/index.php" class="btn btn-astar mt-auto py-2 fw-bold position-relative">
                        Lihat Antrean Pengajuan <i class="bi bi-arrow-right ms-2"></i>
                        <?php if ($total_tugas > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white shadow-sm" style="font-size: 0.85rem;">
                                <?= $total_tugas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Menu: Laporan Transaksi -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-bar-chart-line-fill"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Laporan Transaksi</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Lihat dan ekspor laporan monitoring peminjaman dan pengadaan aset baru.</p>
                    <a href="../05_laporan_sistem/index.php" class="btn btn-astar mt-auto py-2 fw-bold">Buka Laporan <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../../components/footer.php'; ?>