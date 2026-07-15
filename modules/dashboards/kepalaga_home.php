<?php
// --- FILE: modules/dashboards/kepalaga_home.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include '../../config/database.php';
include '../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Keamanan (Hanya Kepala GA)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Kepala GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Kepala GA.');
    header('Location: ../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Kepala GA') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah dinonaktifkan.');
    header('Location: ../00_auth/login.php');
    exit;
}

// Menghitung jumlah antrean Pengadaan yang berstatus Draft
$q_antrean = mysqli_query($koneksi, "SELECT COUNT(idPengadaan) AS total FROM transaksi_pengadaan WHERE statusPengadaan = 'Draft'");
$total_pengajuan = mysqli_fetch_assoc($q_antrean)['total'];

// Query Aset Aktif & Fasilitas Aktif
$q_aset = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM aset WHERE ketersediaanAset != 'Nonaktif'");
$total_aset = mysqli_fetch_assoc($q_aset)['total'];

$q_fas = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM fasilitas WHERE ketersediaanFasilitas != 'Nonaktif'");
$total_fas = mysqli_fetch_assoc($q_fas)['total'];

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
                <h2 class="fw-bold mb-2">Halo, Kepala GA!</h2>
                <p class="mb-0 text-white-50">Selamat datang di panel manajerial operasional dan validasi pengadaan kampus.</p>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <i class="bi bi-building-check text-white" style="font-size: 4rem; opacity: 0.8;"></i>
            </div>
        </div>
    </div>

    <!-- Metric Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Antrean Validasi E-Proc -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #1d4197 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Antrean E-Proc</p>
                        <h4 class="fw-bold mb-0 text-dark"><?= $total_pengajuan ?> Pengajuan</h4>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-astar" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-file-earmark-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2: Total Aset Aktif -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #1d4197 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Total Aset Aktif</p>
                        <h4 class="fw-bold mb-0 text-dark"><?= $total_aset ?> Items</h4>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-astar" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-pc-display"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 3: Total Fasilitas Aktif -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #1d4197 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Total Fasilitas Aktif</p>
                        <h4 class="fw-bold mb-0 text-dark"><?= $total_fas ?> Lokasi</h4>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-astar" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-house-up-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Menu: Approval Pengadaan -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-ui-checks-grid"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Persetujuan Pengadaan (E-Proc)</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Tinjau proposal dari Tendik, setujui, dan delegasikan tugas survei harga ke tim Supplier.</p>
                    <a href="../04_rantai_pasok/transaksi_pengadaan/kepala_ga/index.php" class="btn btn-astar mt-auto py-2 fw-bold position-relative">
                        Lihat Antrean Pengajuan <i class="bi bi-arrow-right ms-2"></i>
                        <?php if ($total_pengajuan > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white shadow-sm" style="font-size: 0.85rem;">
                                <?= $total_pengajuan; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- ZONA LAPORAN KEPALA GA -->
        <h4 class="fw-bold mt-5 mb-3 text-astar border-bottom pb-2"><i class="bi bi-graph-up-arrow me-2"></i>Zona Laporan Manajerial</h4>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card menu-card h-100 p-4">
                    <div class="card-body d-flex flex-column">
                        <div class="icon-box"><i class="bi bi-clipboard-pulse"></i></div>
                        <h5 class="fw-bold text-dark mb-3">Laporan Global Reparasi</h5>
                        <p class="text-secondary mb-4 flex-grow-1">Pantauan performa perbaikan seluruh aset & fasilitas kampus oleh tim Staff GA.</p>
                        <a href="../05_laporan_sistem/kepala_ga/laporan_reparasi.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Buka Laporan <i class="bi bi-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card menu-card h-100 p-4">
                    <div class="card-body d-flex flex-column">
                        <div class="icon-box"><i class="bi bi-graph-up-arrow"></i></div>
                        <h5 class="fw-bold text-dark mb-3">Laporan Pertumbuhan Aset</h5>
                        <p class="text-secondary mb-4 flex-grow-1">Daftar seluruh pengadaan aset yang sudah ACC/Tiba beserta kuantitas barang barunya.</p>
                        <a href="../05_laporan_sistem/kepala_ga/laporan_pertumbuhan.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Buka Laporan <i class="bi bi-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../components/footer.php'; ?>