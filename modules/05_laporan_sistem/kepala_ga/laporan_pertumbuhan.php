<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../../config/database.php';
require '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Kepala GA') {
    header('Location: ../../00_auth/login.php');
    exit;
}
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// HANYA MENGAMBIL YANG SUDAH DICAIRKAN FINANCE DAN TERPILIH
$where = " WHERE tp.statusPengadaan = 'Disetujui Finance' AND dv.statusPilihan = 'Terpilih' ";
if (!empty($tgl_awal)) $where .= " AND DATE(tp.tanggalPengadaan) >= '$tgl_awal' ";
if (!empty($tgl_akhir)) $where .= " AND DATE(tp.tanggalPengadaan) <= '$tgl_akhir' ";

$sql = "SELECT tp.idPengadaan, k.namaKategori, tp.namaKebutuhan, dv.namaVendor, dv.stok AS unit_baru, dv.statusKedatangan, dv.tanggalJatuhTempo 
        FROM transaksi_pengadaan tp JOIN kategori k ON tp.idKategori = k.idKategori
        JOIN detail_pengadaan_vendor dv ON tp.idPengadaan = dv.idPengadaan
        $where ORDER BY tp.tanggalPengadaan DESC";

$data_report = [];
$total_aset_baru = 0;
$q = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($q)) {
    $data_report[] = $row;
    $total_aset_baru += (int)$row['unit_baru'];
}

include '../../../components/header.php';
?>
<!-- COPY STYLE PRINTING SEPERTI BIASA -->
<style>
    @media print {
        body {
            background: white !important;
            color: black !important;
            font-size: 12px !important;
        }

        .no-print,
        .navbar,
        .btn,
        form {
            display: none !important;
        }

        .container,
        .card,
        .card-body {
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        table th,
        table td {
            border: 1px solid #111 !important;
            padding: 6px !important;
        }

        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #111;
            padding-bottom: 10px;
        }
    }

    .print-header {
        display: none;
    }
</style>

<div class="print-header">
    <h3>LAPORAN PERTUMBUHAN ASET KAMPUS</h3>
    <div class="print-date">Dicetak: <?= date('d-m-Y H:i') ?> | Oleh: Kepala GA</div>
</div>

<div class="card shadow-sm border-0 no-print mb-4" style="border-radius: 15px;">
    <div class="card-header bg-astar text-white" style="border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 fw-bold">Filter Laporan Aset Baru</h5>
    </div>
    <div class="card-body p-4 bg-light">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-5"><label class="fw-bold">Dari Tanggal</label><input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>"></div>
            <div class="col-md-5"><label class="fw-bold">Sampai Tanggal</label><input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>"></div>
            <div class="col-12 text-end mt-4"><button type="submit" class="btn btn-astar fw-bold">Filter</button><button type="button" onclick="window.print()" class="btn btn-danger fw-bold ms-2">Cetak PDF</button><button type="button" onclick="exportToCSV('Laporan_AsetBaru')" class="btn btn-success fw-bold ms-2">Excel</button></div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #198754 !important;">
            <p class="text-muted fw-semibold mb-1">Total Unit Aset Dipesan</p>
            <h4 class="fw-bold text-success"><?= $total_aset_baru ?> Unit</h4>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #1d4197 !important;">
            <p class="text-muted fw-semibold mb-1">Total Transaksi Selesai</p>
            <h4 class="fw-bold text-primary"><?= count($data_report) ?> Transaksi</h4>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4 table-responsive">
        <table class="datatable-astar table table-hover border text-center align-middle" id="tableLaporan">
            <thead style="background-color:#f4f6f9; color:#1d4197;">
                <tr>
                    <th>No.</th>
                    <th>Pengadaan</th>
                    <th>Kebutuhan Aset</th>
                    <th>Vendor</th>
                    <th>Jml Unit Baru</th>
                    <th>Status Kedatangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($data_report as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row['idPengadaan'] ?></td>
                        <td class="text-start"><b><?= $row['namaKebutuhan'] ?></b><br><small><?= $row['namaKategori'] ?></small></td>
                        <td><?= $row['namaVendor'] ?></td>
                        <td class="fw-bold text-primary fs-5"><?= $row['unit_baru'] ?></td>
                        <td><span class="badge <?= ($row['statusKedatangan'] == 'Sudah Tiba') ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $row['statusKedatangan'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../../components/footer.php'; ?>