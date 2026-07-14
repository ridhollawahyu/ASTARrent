<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../../config/database.php';
require '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Finance') {
    header('Location: ../../00_auth/login.php');
    exit;
}
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

$where = " WHERE tp.statusPengadaan = 'Disetujui Finance' AND dv.statusPilihan = 'Terpilih' ";
if (!empty($tgl_awal)) $where .= " AND DATE(tp.tanggalPengadaan) >= '$tgl_awal' ";
if (!empty($tgl_akhir)) $where .= " AND DATE(tp.tanggalPengadaan) <= '$tgl_akhir' ";

$sql = "SELECT tp.idPengadaan, tp.tanggalPengadaan, tp.namaKebutuhan, dv.namaVendor, dv.stok, dv.hargaSatuan
        FROM transaksi_pengadaan tp JOIN detail_pengadaan_vendor dv ON tp.idPengadaan = dv.idPengadaan
        $where ORDER BY tp.tanggalPengadaan DESC";

$data_report = [];
$grand_total_all = 0;
$total_ppn_all = 0;
$q = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($q)) {
    $subtotal = $row['stok'] * $row['hargaSatuan'];
    $ppn = $subtotal * 0.12;
    $grand = $subtotal + $ppn;

    $row['subtotal'] = $subtotal;
    $row['ppn'] = $ppn;
    $row['grand'] = $grand;
    $data_report[] = $row;

    $total_ppn_all += $ppn;
    $grand_total_all += $grand;
}

include '../../../components/header.php';
?>
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
    <h3>LAPORAN PENGELUARAN KEUANGAN (PENGADAAN)</h3>
    <div class="print-date">Dicetak: <?= date('d-m-Y H:i') ?> | Oleh: Finance</div>
</div>

<div class="card shadow-sm border-0 no-print mb-4" style="border-radius: 15px;">
    <div class="card-header bg-danger text-white" style="border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 fw-bold">Filter Laporan Pengeluaran</h5>
    </div>
    <div class="card-body p-4 bg-light">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-5"><label class="fw-bold">Dari Tanggal</label><input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>"></div>
            <div class="col-md-5"><label class="fw-bold">Sampai Tanggal</label><input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>"></div>
            <div class="col-12 text-end mt-4"><button type="submit" class="btn btn-danger fw-bold">Filter</button><button type="button" onclick="window.print()" class="btn btn-danger fw-bold ms-2">Cetak PDF</button><button type="button" onclick="exportToCSV('Laporan_Pengeluaran')" class="btn btn-success fw-bold ms-2">Excel</button></div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #198754 !important;">
            <p class="text-muted fw-semibold mb-1">Total PPN 12% Disetorkan</p>
            <h4 class="fw-bold text-success">Rp <?= number_format($total_ppn_all, 0, ',', '.') ?></h4>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #dc3545 !important;">
            <p class="text-muted fw-semibold mb-1">Grand Total Belanja (Kas Keluar)</p>
            <h4 class="fw-bold text-danger">Rp <?= number_format($grand_total_all, 0, ',', '.') ?></h4>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4 table-responsive">
        <table class="datatable-astar table table-hover border text-center align-middle" id="tableLaporan">
            <thead class="table-danger">
                <tr>
                    <th>No.</th>
                    <th>Tgl Cair</th>
                    <th>Nama Barang</th>
                    <th>Vendor Penerima Dana</th>
                    <th>Subtotal</th>
                    <th>PPN 12%</th>
                    <th>Grand Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($data_report as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggalPengadaan'])) ?></td>
                        <td class="text-start"><b><?= $row['namaKebutuhan'] ?></b><br><small><?= $row['stok'] ?> Unit</small></td>
                        <td><?= $row['namaVendor'] ?></td>
                        <td>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                        <td class="text-danger">Rp <?= number_format($row['ppn'], 0, ',', '.') ?></td>
                        <td class="fw-bold text-success">Rp <?= number_format($row['grand'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../../components/footer.php'; ?>