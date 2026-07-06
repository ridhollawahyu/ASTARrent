<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// 1. WAJIB PANGGIL KONEKSI & FUNGSI SEBELUM QUERY (Perbaikan Fatal Error!)
include '../../config/database.php';
include '../../config/functions.php';

/** @var mysqli $koneksi */

// 2. Validasi Keamanan (Hanya Tendik yang boleh masuk)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    echo "<script>window.location='../00_auth/login.php';</script>";
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Tenaga Pendidik') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    echo "<script>window.location='../00_auth/login.php';</script>";
}

// 3. QUERY PENGHITUNG ANTREAN
$dept_tendik = $_SESSION['departemen'];
$hitung_request = mysqli_query($koneksi, "
    SELECT COUNT(tp.idPeminjaman) AS total_antrean 
    FROM transaksi_peminjaman tp 
    JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa
    LEFT JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
    LEFT JOIN aset a ON tp.idAset = a.idAset
    WHERE tp.statusPeminjaman = 'Menunggu' AND m.kodeProdi_mahasiswa = '$dept_tendik'
    AND (tp.idAset IS NOT NULL OR f.tipeFasilitas = 'Akademik')
");
$data_antrean = mysqli_fetch_assoc($hitung_request);
$total_antrean = $data_antrean['total_antrean'];

// 3. QUERY PENGHITUNG ANTREAN PENGEMBALIAN
$hitung_request = mysqli_query($koneksi, "
    SELECT COUNT(tp.idPeminjaman) AS total_antrean 
    FROM transaksi_peminjaman tp 
    JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa 
    LEFT JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
    LEFT JOIN aset a ON tp.idAset = a.idAset
    WHERE tp.statusPeminjaman = 'Disetujui' AND m.kodeProdi_mahasiswa = '$dept_tendik'
    AND (tp.idAset IS NOT NULL OR f.tipeFasilitas = 'Akademik')
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
    <!-- Banner Selamat Datang -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2">Halo, Tenaga Pendidik! 📝</h2>
                <p class="mb-0 text-white-50">Selamat bertugas mengelola inventaris dan memvalidasi transaksi mahasiswa hari ini.</p>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <i class="bi bi-ui-checks text-white" style="font-size: 4rem; opacity: 0.8;"></i>
            </div>
        </div>
    </div>

    <!-- Pilihan Menu Utama -->
    <div class="row g-4">
        <!-- Menu 1: Master Kategori (Fasilitas) -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-pc-display"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Pengelolaan Kategori Fasilitas</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Kelola data inventaris barang elektronik, cek ketersediaan, dan update kondisi aset.</p>
                    <a href="../04_rantai_pasok/master_kategori/tendik/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Kelola Aset <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <!-- Menu 2: Master Aset -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-pc-display"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Pengelolaan Aset</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Kelola data inventaris barang elektronik, cek ketersediaan, dan update kondisi aset.</p>
                    <a href="../01_reservasi/master_aset/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Kelola Aset <i class="bi bi-arrow-right ms-2"></i></a>
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
                    <a href="../02_kedisiplinan/master_fasilitas/tendik/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Kelola Aset <i class="bi bi-arrow-right ms-2"></i></a>
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
                    <a href="../01_reservasi/transaksi_peminjaman/tendik/index.php" class="btn btn-astar mt-auto py-2 fw-bold position-relative">
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
                    <a href="../02_kedisiplinan/transaksi_pengembalian/tendik/index.php" class="btn btn-astar mt-auto py-2 fw-bold position-relative">
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

        <!-- Menu 6: Transaksi Pengadaan -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-check-circle-fill"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Request Pengadaan</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Tinjau dan berikan persetujuan (Approve) untuk Pengembalian mahasiswa.</p>

                    <a href="../04_rantai_pasok/transaksi_pengadaan/tendik/index.php" class="btn btn-astar mt-auto py-2 fw-bold position-relative">
                        Request <i class="bi bi-arrow-right ms-2"></i>
                        <span class="visually-hidden">request baru</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../components/footer.php'; ?>