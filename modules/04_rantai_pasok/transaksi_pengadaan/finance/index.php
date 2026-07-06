<?php
// --- FILE: modules/04_rantai_pasok/transaksi_pengadaan/finance/index.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Finance') {
    header('Location: ../../../00_auth/login.php');
    exit;
}

$where_sql = "WHERE tp.statusPengadaan IN ('Harga Diinput Supplier', 'Disetujui Finance')";
if (isset($_GET['filter_status']) && $_GET['filter_status'] != '') {
    $where_sql = "WHERE tp.statusPengadaan = '" . mysqli_real_escape_string($koneksi, $_GET['filter_status']) . "'";
}

include '../../../../components/header.php';
?>
<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold">Antrean Persetujuan Finance</h5>
        <a href="../../../dashboards/finance_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle text-center">
                <thead style="background-color: #f4f6f9; color: #1d4197;">
                    <tr>
                        <th>No.</th>
                        <th class="text-start">Kebutuhan Aset</th>
                        <th>Jumlah Req</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($koneksi, "SELECT tp.*, k.namaKategori FROM transaksi_pengadaan tp JOIN kategori k ON tp.idKategori = k.idKategori $where_sql ORDER BY tp.tanggalPengadaan DESC");
                    $no = 1;
                    while ($data = mysqli_fetch_array($query)) { ?>
                        <tr>
                            <td class="fw-bold"><?= $no++; ?></td>
                            <td class="text-start"><span class="badge bg-secondary mb-1"><?= $data['namaKategori']; ?></span><br><span class="fw-bold text-dark"><?= $data['namaKebutuhan']; ?></span></td>
                            <td class="fw-bold fs-5 text-primary"><?= $data['jumlah']; ?></td>
                            <td>
                                <?= ($data['statusPengadaan'] == 'Harga Diinput Supplier') ? '<span class="badge bg-warning text-dark px-3 py-2">Butuh ACC</span>' : '<span class="badge bg-success px-3 py-2">Selesai</span>' ?>
                            </td>
                            <td>
                                <?php if ($data['statusPengadaan'] == 'Harga Diinput Supplier'): ?>
                                    <a href="approve.php?id=<?= $data['idPengadaan']; ?>" class="btn btn-astar btn-sm fw-bold px-3">Cairkan Dana</a>
                                <?php else: ?>
                                    <a href="../../../../uploads/dokumen_penawaran/<?= $data['dokumen_penawaran']; ?>" target="_blank" class="btn btn-outline-danger btn-sm">PDF Akhir</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../../../../components/footer.php'; ?>