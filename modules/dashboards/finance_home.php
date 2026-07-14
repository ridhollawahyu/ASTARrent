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

$q_beku_fin = mysqli_query($koneksi, "SELECT COUNT(nimMahasiswa) AS total FROM mahasiswa WHERE dendaMahasiswa > 0");
$total_beku_fin = mysqli_fetch_assoc($q_beku_fin)['total'];

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
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #1d4197 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Antrean E-Proc</p>
                        <h4 class="fw-bold mb-0 text-dark"><?= $total_tugas ?> Request</h4>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-astar" style="font-size: 1.5rem; line-height: 1;">
                        <i class="bi bi-file-earmark-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2: Pengadaan Selesai -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #1d4197 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Pengadaan Selesai</p>
                        <h4 class="fw-bold mb-0 text-dark"><?= $total_selesai ?> Transaksi</h4>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-astar" style="font-size: 1.5rem; line-height: 1;">
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
                        <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.15rem;">Rp <?= number_format($total_dana, 0, ',', '.') ?></h4>
                    </div>
                    <div class="rounded-circle p-3 bg-primary-subtle text-astar" style="font-size: 1.5rem; line-height: 1;">
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

        <!-- Menu 2: Pelunasan Denda -->
        <div class="col-md-6 col-lg-4">
            <div class="card menu-card h-100 p-4">
                <div class="card-body d-flex flex-column">
                    <div class="icon-box"><i class="bi bi-cash-stack"></i></div>
                    <h5 class="fw-bold text-dark mb-3">Penerimaan Denda</h5>
                    <p class="text-secondary mb-4 flex-grow-1">Proses pelunasan tagihan denda mahasiswa dari seluruh Prodi agar akun kembali normal.</p>
                    <a href="../02_kedisiplinan/pelunasan_sanksi/finance/index.php" class="btn btn-astar mt-auto py-2 fw-bold position-relative">
                        Proses Pembayaran <i class="bi bi-arrow-right ms-2"></i>
                        <?php if ($total_beku_fin > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white shadow-sm">
                                <?= $total_beku_fin; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- ZONA LAPORAN FINANCE -->
        <h4 class="fw-bold mt-5 mb-3 text-astar border-bottom pb-2"><i class="bi bi-wallet2 me-2"></i>Zona Laporan Keuangan</h4>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card menu-card h-100 p-4">
                    <div class="card-body d-flex flex-column">
                        <div class="icon-box"><i class="bi bi-cash-stack"></i></div>
                        <h5 class="fw-bold text-dark mb-3">Laporan Penerimaan Denda</h5>
                        <p class="text-secondary mb-4 flex-grow-1">Rekapitulasi sanksi denda mahasiswa yang telah masuk ke kas kampus.</p>
                        <a href="../05_laporan_sistem/finance/laporan_denda.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Buka Laporan <i class="bi bi-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card menu-card h-100 p-4">
                    <div class="card-body d-flex flex-column">
                        <div class="icon-box"><i class="bi bi-cash-stack"></i></div>
                        <h5 class="fw-bold text-dark mb-3">Laporan Pengeluaran Pengadaan</h5>
                        <p class="text-secondary mb-4 flex-grow-1">Rincian belanja, Vendor pemenang, Subtotal, PPN 12%, dan Grand Total pengadaan.</p>
                        <a href="../05_laporan_sistem/finance/laporan_pengeluaran.php" class="btn btn-outline-secondary mt-auto py-2 fw-bold" style="color: #1d4197; border-color: #1d4197;">Buka Laporan <i class="bi bi-arrow-right ms-2"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../../components/footer.php'; ?>