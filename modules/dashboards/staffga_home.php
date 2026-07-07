<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../config/database.php';
include '../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Staff GA') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah dinonaktifkan.');
    header('Location: ../00_auth/login.php');
    exit;
}

$total_komponen_tersedia = 0;
$total_reparasi_menunggu = 0;

$q_komponen = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM komponen WHERE statusKomponen = 'Tersedia'");
if ($q_komponen) {
    $total_komponen_tersedia = (int) mysqli_fetch_assoc($q_komponen)['total'];
}

$q_reparasi = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM reparasi_fasilitas_aset WHERE statusReparasi = 'Menunggu GA' OR statusReparasi = 'Sedang Dikerjakan'");
if ($q_reparasi) {
    $total_reparasi_menunggu = (int) mysqli_fetch_assoc($q_reparasi)['total'];
}

$dept_tendik = $_SESSION['departemen'];
$hitung_request = mysqli_query($koneksi, "
    SELECT COUNT(tp.idPeminjaman) AS total_antrean 
    FROM transaksi_peminjaman tp 
    JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
    WHERE tp.statusPeminjaman = 'Menunggu' AND f.tipeFasilitas = 'Non-Akademik'
");
$data_antrean = mysqli_fetch_assoc($hitung_request);
$total_antrean = $data_antrean['total_antrean'];

// 3. QUERY PENGHITUNG ANTREAN PENGEMBALIAN
$hitung_request = mysqli_query($koneksi, "
    SELECT COUNT(tp.idPeminjaman) AS total_antrean 
    FROM transaksi_peminjaman tp 
    JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
    WHERE tp.statusPeminjaman = 'Disetujui' AND f.tipeFasilitas = 'Non-Akademik'
");
$data_antreanP = mysqli_fetch_assoc($hitung_request);
$total_antreanP = $data_antreanP['total_antrean'];

include '../../components/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    .welcome-banner {
        background: linear-gradient(135deg, #1d4197 0%, #2a5bd4 100%);
        color: white;
        border-radius: 15px;
        padding: 30px 40px;
        margin-bottom: 40px;
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
                <h2 class="fw-bold mb-2">Halo, <?= $_SESSION['role']; ?>!</h2>
                <p class="mb-0 text-white-50">Silakan pilih menu sesuai hak akses kamu di ASTARrent.</p>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <i class="bi bi-briefcase-fill text-white" style="font-size: 4rem; opacity: 0.8;"></i>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- Menu 1: Master Komponen -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-cpu-fill"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Master Komponen</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Kelola komponen hasil bongkar dari aset atau fasilitas rusak total.</p>
                    <a href="../03_pemeliharaan/master_komponen/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold position-relative" style="color: #1d4197; border-color: #1d4197;">
                        Kelola Komponen <i class="bi bi-arrow-right ms-2"></i>
                        <?php if ($total_komponen_tersedia > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success border border-2 border-white shadow-sm" style="font-size: 0.85rem;">
                                <?= $total_komponen_tersedia; ?>
                                <span class="visually-hidden">komponen tersedia</span>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Menu 2: Master Kategori (Fasilitas) -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-pc-display"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Pengelolaan Kategori Fasilitas</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Kelola data inventaris barang elektronik, cek ketersediaan, dan update kondisi aset.</p>
                    <a href="../04_rantai_pasok/master_kategori/staffga/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Kelola Aset <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <!-- Menu 3: Master Fasilitas -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-house-up-fill"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Pengelolaan Fasilitas</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Kelola data Fasilitas ruangan, lapangan, lalu cek ketersediaan, dan update kondisi fasilitas.</p>
                    <a href="../02_kedisiplinan/master_fasilitas/staffga/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Kelola Aset <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <!-- Menu 4: Approval Peminjaman -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-check-circle-fill"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Persetujuan Peminjaman</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Tinjau dan berikan persetujuan (Approve) untuk permintaan peminjaman mahasiswa.</p>

                    <!-- PERBAIKAN: Tombol Dengan Badge Notifikasi Angka Merah -->
                    <a href="../01_reservasi/transaksi_peminjaman/staffga/index.php" class="btn btn-astar mt-auto py-2 fw-bold position-relative">
                        Lihat Request <i class="bi bi-arrow-right ms-2"></i>

                        <?php if ($total_antrean > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white shadow-sm" style="font-size: 0.85rem;">
                                <?= $total_antrean; ?>
                                <span class="visually-hidden">request baru</span>
                            </span>
                        <?php endif; ?>

                    </a>
                </div>
            </div>
        </div>

        <!-- Menu 5: Approval Pengembalian -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-check-circle-fill"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Persetujuan Pengembalian</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Tinjau dan berikan persetujuan (Approve) untuk Pengembalian mahasiswa.</p>

                    <!-- PERBAIKAN: Tombol Dengan Badge Notifikasi Angka Merah -->
                    <a href="../02_kedisiplinan/transaksi_pengembalian/staffga/index.php" class="btn btn-astar mt-auto py-2 fw-bold position-relative">
                        Lihat Data <i class="bi bi-arrow-right ms-2"></i>

                        <?php if ($total_antreanP > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white shadow-sm" style="font-size: 0.85rem;">
                                <?= $total_antreanP; ?>
                                <span class="visually-hidden">request baru</span>
                            </span>
                        <?php endif; ?>

                    </a>
                </div>
            </div>
        </div>

        <!-- Menu 6: Transaksi Reparasi -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-tools"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Reparasi Fasilitas dan Aset</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Menu lanjutan untuk apply dan proses reparasi Staff GA.</p>
                    <a href="../03_pemeliharaan/transaksi_reparasi/index.php" class="btn btn-astar mt-auto py-2 fw-bold position-relative">
                        Kelola Reparasi
                        <?php if ($total_reparasi_menunggu > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white shadow-sm" style="font-size: 0.85rem;">
                                <?= $total_reparasi_menunggu; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../components/footer.php'; ?>