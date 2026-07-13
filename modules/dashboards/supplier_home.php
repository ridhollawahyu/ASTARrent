<?php
// --- FILE: modules/dashboards/supplier_home.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../config/database.php';
include '../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Supplier') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Supplier.');
    header('Location: ../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Supplier') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah dinonaktifkan.');
    header('Location: ../00_auth/login.php');
    exit;
}

$id_supplier = $_SESSION['id'];
$q_tugas = mysqli_query($koneksi, "SELECT COUNT(idPengadaan) AS total FROM transaksi_pengadaan WHERE idSupplier = '$id_supplier' AND statusPengadaan = 'Disetujui GA'");
$total_tugas = mysqli_fetch_assoc($q_tugas)['total'];

// Query tambahan supplier info
$q_supp_info = mysqli_query($koneksi, "SELECT * FROM supplier WHERE idSupplier = '$id_supplier'");
$data_sup = mysqli_fetch_assoc($q_supp_info);

$q_selesai = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM transaksi_pengadaan WHERE idSupplier = '$id_supplier' AND statusPengadaan IN ('Harga Diinput Supplier', 'Disetujui Finance')");
$total_selesai = mysqli_fetch_assoc($q_selesai)['total'];

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
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2">Halo, Tim Supplier!</h2>
                <p class="mb-0 text-white-50">Silakan selesaikan tugas pencarian vendor Anda hari ini.</p>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <i class="bi bi-cart-check-fill text-white" style="font-size: 4rem; opacity: 0.8;"></i>
            </div>
        </div>
    </div>

    <!-- Metric Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Status Akun -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid <?= ($data_sup['statusSupplier'] == 'Aktif') ? '#198754' : '#dc3545' ?> !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Status Kemitraan</p>
                        <h4 class="fw-bold mb-0 <?= ($data_sup['statusSupplier'] == 'Aktif') ? 'text-success' : 'text-danger' ?>">
                            <?= $data_sup['statusSupplier'] ?>
                        </h4>
                    </div>
                    <div class="rounded-circle p-3 <?= ($data_sup['statusSupplier'] == 'Aktif') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi <?= ($data_sup['statusSupplier'] == 'Aktif') ? 'bi-shield-check' : 'bi-shield-slash' ?>"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2: Tugas Banding Harga Aktif -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #ffc107 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Tugas Aktif</p>
                        <h4 class="fw-bold mb-0 text-warning"><?= $total_tugas ?> Tugas</h4>
                    </div>
                    <div class="rounded-circle p-3 bg-warning-subtle text-warning" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-shop"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 3: Tugas Terselesaikan -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #1d4197 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Tugas Diselesaikan</p>
                        <h4 class="fw-bold mb-0 text-primary"><?= $total_selesai ?> Transaksi</h4>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-primary" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-check2-all"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-shop"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Tugas Pencarian Harga</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Bandingkan 3 harga vendor eksternal untuk pengadaan yang disetujui Kepala GA.</p>
                    <a href="../04_rantai_pasok/transaksi_pengadaan/supplier/index.php" class="btn btn-astar mt-auto py-2 fw-bold position-relative">
                        Lihat Tugas Saya <i class="bi bi-arrow-right ms-2"></i>
                        <?php if ($total_tugas > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white shadow-sm" style="font-size: 0.85rem;">
                                <?= $total_tugas; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../../components/footer.php'; ?>