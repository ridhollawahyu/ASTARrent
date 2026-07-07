<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Panggil header global
include '../../config/functions.php';
include '../../components/header.php';

// Validasi Keamanan (Hanya SA yang boleh masuk)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Super Admin.');
    header('Location: ../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Super Admin') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../00_auth/login.php');
    exit;
}
?>

<!-- Tambahkan CDN Bootstrap Icons khusus untuk halaman ini -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    /* Styling Elegan Khusus Dashboard */
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

    <!-- Banner Selamat Datang -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2">Selamat Datang, Super Admin! 👋</h2>
                <p class="mb-0 text-white-50">Sistem terpusat siap digunakan. Silakan kelola data master pengguna dan mahasiswa hari ini.</p>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <i class="bi bi-shield-lock text-white" style="font-size: 4rem; opacity: 0.8;"></i>
            </div>
        </div>
    </div>

    <!-- Pilihan Menu Utama -->
    <div class="row g-4">

        <!-- Menu 1: Master Sanksi -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-slash-circle"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Pengelolaan Sanksi</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Kelola data Sanksi jam minus dan denda, cek data pelanggaran, dan update detail pelanggaran.</p>
                    <a href="../02_kedisiplinan/master_sanksi/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Kelola Sanksi <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <!-- Menu 2: Master Mahasiswa -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Kelola Mahasiswa</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Kelola data mahasiswa, program studi, dan pembekuan jika diperlukan.</p>
                    <a href="../01_reservasi/master_mahasiswa/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Kelola Mahasiswa <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <!-- Menu 3: Master Users (Tugas Super Admin) -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Kelola Pengguna (Staff)</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Kelola akun Tendik, Kepala GA, Staff GA, dan Finance (Hak Akses Pengelolaan Pegawai).</p>
                    <!-- Link ini bisa diarahkan ke folder master_users nanti -->
                    <a href="../03_pemeliharaan/master_users/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Kelola Pengguna <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <!-- Menu 4: Master Supplier (Tugas Super Admin) -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Kelola Supplier (Pencari Vendor)</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Kelola Data Supplier dan Supplier (Hak Akses Pengelolaan Supplier).</p>
                    <!-- Link ini bisa diarahkan ke folder master_users nanti -->
                    <a href="../04_rantai_pasok//master_supplier/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Kelola Pengguna <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
include '../../components/footer.php';
?>

<!-- btn btn-astar mt-auto py-2 fw-bold -->