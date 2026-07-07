<?php
// --- FILE: modules/04_rantai_pasok/transaksi_pengadaan/supplier/index.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Supplier') {
    set_notifikasi('error', 'Akses Ditolak!');
    header('Location: ../../../00_auth/login.php');
    exit;
}

$id_supplier = $_SESSION['id'];

// 1. FILTER GLOBAL
$where_sql = "WHERE tp.idSupplier = '$id_supplier'";
$kategori_terpilih = "";
$status_terpilih = "Disetujui GA"; // Default tugas aktif
$tgl_dari = "";
$tgl_sampai = "";

if (isset($_GET['filter_kategori']) && $_GET['filter_kategori'] != '') {
    $kategori_terpilih = mysqli_real_escape_string($koneksi, $_GET['filter_kategori']);
    $where_sql .= " AND tp.idKategori = '$kategori_terpilih'";
}

if (isset($_GET['filter_status']) && $_GET['filter_status'] != '') {
    $status_terpilih = mysqli_real_escape_string($koneksi, $_GET['filter_status']);
}
if ($status_terpilih !== 'Semua') {
    $where_sql .= " AND tp.statusPengadaan = '$status_terpilih'";
}

if (isset($_GET['tgl_dari']) && isset($_GET['tgl_sampai']) && $_GET['tgl_dari'] != '' && $_GET['tgl_sampai'] != '') {
    $tgl_dari = mysqli_real_escape_string($koneksi, $_GET['tgl_dari']);
    $tgl_sampai = mysqli_real_escape_string($koneksi, $_GET['tgl_sampai']);
    $where_sql .= " AND tp.tanggalPengadaan BETWEEN '$tgl_dari 00:00:00' AND '$tgl_sampai 23:59:59'";
}

include '../../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-shop me-2"></i>Daftar Tugas Pencarian Vendor</h5>
        <a href="../../../dashboards/supplier_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>

    <div class="card-body p-4">
        <!-- FILTER -->
        <form method="GET" action="index.php" class="row g-2 align-items-end mb-4 pb-3 border-bottom">
            <div class="col-md-2">
                <label class="form-label fw-bold text-astar" style="font-size: 13px;">Kategori Aset</label>
                <?php
                $pilihan_kategori = ['' => 'Semua Kategori'] + ambil_pilihan_kategori('Aset');
                echo buat_dropdown_astar('filter_kategori', $pilihan_kategori, $kategori_terpilih, false);
                ?>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-astar" style="font-size: 13px;">Status Tugas</label>
                <?php
                $pilihan_status = [
                    'Disetujui GA' => 'Tugas Baru (Harus Diinput)',
                    'Semua' => '-- Semua Tugas --',
                    'Harga Diinput Supplier' => 'Selesai (Menunggu Finance)',
                    'Disetujui Finance' => 'Aset Berhasil Dibeli'
                ];
                echo buat_dropdown_astar('filter_status', $pilihan_status, $status_terpilih, false);
                ?>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold text-astar" style="font-size: 13px;">Dari Tanggal</label>
                <input type="date" name="tgl_dari" class="form-control text-secondary fw-bold" value="<?= $tgl_dari; ?>" style="border: 2px solid #e0e6ed;">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold text-astar" style="font-size: 13px;">Sampai Tanggal</label>
                <input type="date" name="tgl_sampai" class="form-control text-secondary fw-bold" value="<?= $tgl_sampai; ?>" style="border: 2px solid #e0e6ed;">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn fw-bold text-white px-3" style="background-color: #1d4197; border-radius: 8px;"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="index.php" class="btn btn-light fw-bold px-3 border" style="border-radius: 8px; color: #1d4197;">Reset</a>
            </div>
        </form>

        <!-- TABEL -->
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle text-center">
                <thead style="background-color: #f4f6f9; color: #1d4197;">
                    <tr>
                        <th width="5%">No.</th>
                        <th class="text-start">Tgl Pengajuan</th>
                        <th class="text-start">Kebutuhan Aset</th>
                        <th>Jumlah</th>
                        <th>Proposal Tendik</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($koneksi, "
                        SELECT tp.*, k.namaKategori FROM transaksi_pengadaan tp
                        JOIN kategori k ON tp.idKategori = k.idKategori
                        $where_sql ORDER BY tp.tanggalPengadaan DESC
                    ");
                    $no = 1;
                    while ($data = mysqli_fetch_array($query)) {
                    ?>
                        <tr>
                            <td class="fw-bold"><?= $no++; ?></td>
                            <td class="text-start"><?= date('d M Y', strtotime($data['tanggalPengadaan'])); ?></td>
                            <td class="text-start">
                                <span class="badge bg-secondary mb-1"><?= $data['namaKategori']; ?></span><br>
                                <span class="fw-bold text-dark"><?= $data['namaKebutuhan']; ?></span>
                            </td>
                            <td class="fw-bold fs-5 text-primary"><?= $data['jumlah']; ?></td>
                            <td>
                                <a href="../../../../uploads/dokumen_pengajuan/<?= $data['dokumen_pengajuan']; ?>?v=<?= time(); ?>" target="_blank" class="btn btn-outline-danger btn-sm fw-bold">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Baca PDF
                                </a>
                            </td>
                            <td>
                                <?php if ($data['statusPengadaan'] == 'Disetujui GA'): ?>
                                    <a href="input_harga.php?id=<?= $data['idPengadaan']; ?>" class="btn btn-astar btn-sm fw-bold px-3 shadow-sm">
                                        <i class="bi bi-pencil-square me-1"></i> Input Harga Vendor
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle-fill"></i> Tugas Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if (mysqli_num_rows($query) == 0): ?>
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted fst-italic">Hore! Tidak ada tugas pencarian vendor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../../../../components/footer.php'; ?>