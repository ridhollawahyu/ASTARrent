<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../config/functions.php';
include '../../components/header.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Mahasiswa') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Mahasiswa.');
    header('Location: ../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Mahasiswa') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../00_auth/login.php');
    exit;
}
?>

<?php
$nim = $_SESSION['id'];
$query_mhs = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE nimMahasiswa = '$nim'");
$data_mhs = mysqli_fetch_assoc($query_mhs);
?>

<div class="container mt-4 mb-5">
    <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #1d4197 0%, #2a5bd4 100%); color: white; border-radius: 15px; padding: 30px 40px; margin-bottom: 30px;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-2">Halo, Mahasiswa!</h2>
                <p class="mb-0 text-white-50">Silakan ajukan peminjaman fasilitas atau aset kampus untuk keperluan kegiatan akademik Anda hari ini.</p>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <i class="bi bi-backpack-fill text-white" style="font-size: 4rem; opacity: 0.8;"></i>
            </div>
        </div>
    </div>

    <!-- Kartu Informasi Kelayakan Mahasiswa -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Status Akun -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid <?= ($data_mhs['statusMahasiswa'] == 'Normal') ? '#198754' : '#dc3545' ?> !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Status Akun</p>
                        <h4 class="fw-bold mb-0 <?= ($data_mhs['statusMahasiswa'] == 'Normal') ? 'text-success' : 'text-danger' ?>">
                            <?= $data_mhs['statusMahasiswa'] ?>
                        </h4>
                    </div>
                    <div class="rounded-circle p-3 <?= ($data_mhs['statusMahasiswa'] == 'Normal') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi <?= ($data_mhs['statusMahasiswa'] == 'Normal') ? 'bi-shield-check' : 'bi-shield-slash' ?>"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2: Jam Minus -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid <?= ($data_mhs['jamMinus_mahasiswa'] > 0) ? '#ffc107' : '#1d4197' ?> !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Jam Minus Kedisiplinan</p>
                        <h4 class="fw-bold mb-0 <?= ($data_mhs['jamMinus_mahasiswa'] > 0) ? 'text-warning' : 'text-dark' ?>">
                            <?= $data_mhs['jamMinus_mahasiswa'] ?> Jam
                        </h4>
                    </div>
                    <div class="rounded-circle p-3 <?= ($data_mhs['jamMinus_mahasiswa'] > 0) ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary' ?>" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 3: Denda Kumulatif -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid <?= ($data_mhs['dendaMahasiswa'] > 0) ? '#dc3545' : '#198754' ?> !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Denda Keterlambatan</p>
                        <h4 class="fw-bold mb-0 <?= ($data_mhs['dendaMahasiswa'] > 0) ? 'text-danger' : 'text-success' ?>">
                            Rp <?= number_format($data_mhs['dendaMahasiswa'], 0, ',', '.') ?>
                        </h4>
                    </div>
                    <div class="rounded-circle p-3 <?= ($data_mhs['dendaMahasiswa'] > 0) ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?>" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Menu 1: Form Pinjam -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 p-4 border-0 shadow-sm" style="border-radius: 15px; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body d-flex flex-column">
                    <div style="width: 70px; height: 70px; background-color: #e8f0fe; color: #1d4197; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 20px;">
                        <i class="bi bi-cart-plus-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Ajukan Peminjaman</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Lihat katalog Aset/Fasilitas yang tersedia dan ajukan request ke Tenaga Pendidik.</p>
                    <a href="../01_reservasi/transaksi_peminjaman/mahasiswa/create.php" class="btn btn-astar mt-auto py-2 fw-bold">Pinjam Sekarang <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
        <!-- Menu 2: Status Pinjaman -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 p-4 border-0 shadow-sm" style="border-radius: 15px; transition: all 0.3s;" onmouseover="this.style.transform='translateY(-10px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body d-flex flex-column">
                    <div style="width: 70px; height: 70px; background-color: #e8f0fe; color: #1d4197; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 20px;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Riwayat Transaksi</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Pantau status persetujuan, jadwal kembali, dan riwayat peminjaman Anda.</p>
                    <a href="../01_reservasi/transaksi_peminjaman/mahasiswa/index.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Cek Status <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../components/footer.php'; ?>