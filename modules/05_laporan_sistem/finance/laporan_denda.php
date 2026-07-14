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

// Cari riwayat denda yang nilainya > 0
$query_where = " WHERE s.sanksi_denda > 0 ";
if (!empty($tgl_awal)) $query_where .= " AND DATE(tpm.tanggalPengembalian) >= '$tgl_awal' ";
if (!empty($tgl_akhir)) $query_where .= " AND DATE(tpm.tanggalPengembalian) <= '$tgl_akhir' ";

$sql = "SELECT tpm.tanggalPengembalian, m.nimMahasiswa, m.namaMahasiswa, m.kodeProdi_mahasiswa, s.namaSanksi, s.sanksi_denda, m.dendaMahasiswa AS sisa_tagihan FROM transaksi_pengembalian tpm JOIN transaksi_peminjaman tp ON tpm.idPeminjaman = tp.idPeminjaman JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa JOIN sanksi s ON tpm.idSanksi = s.idSanksi $query_where ORDER BY tpm.tanggalPengembalian DESC";

$data_report = [];
$total_historis_denda = 0;
$q = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($q)) {
    $data_report[] = $row;
    $total_historis_denda += (int)$row['sanksi_denda'];
}

$q_tg = mysqli_query($koneksi, "SELECT SUM(dendaMahasiswa) AS total_ngutang, COUNT(nimMahasiswa) AS mhs_beku FROM mahasiswa WHERE dendaMahasiswa > 0");
$dtg = mysqli_fetch_assoc($q_tg);
$total_tunggakan = (int)$dtg['total_ngutang'];
$mhs_dibekukan = (int)$dtg['mhs_beku'];

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
    <h3>LAPORAN PENERIMAAN DENDA MAHASISWA</h3>
    <div class="print-date">Dicetak: <?= date('d-m-Y H:i') ?> | Oleh: Finance</div>
</div>

<div class="card shadow-sm border-0 no-print mb-4" style="border-radius: 15px;">
    <div class="card-header bg-danger text-white" style="border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 fw-bold">Filter Laporan Denda</h5>
    </div>
    <div class="card-body p-4 bg-light">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-5"><label class="fw-bold">Dari Tanggal</label><input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>"></div>
            <div class="col-md-5"><label class="fw-bold">Sampai Tanggal</label><input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>"></div>
            <div class="col-12 text-end mt-4"><button type="submit" class="btn btn-danger fw-bold">Filter</button><button type="button" onclick="window.print()" class="btn btn-danger fw-bold ms-2">Cetak PDF</button><button type="button" onclick="exportToCSV('Laporan_Denda')" class="btn btn-success fw-bold ms-2">Excel</button></div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #198754 !important;">
            <p class="text-muted fw-semibold mb-1">Historis Denda (Filter)</p>
            <h4 class="fw-bold text-success">Rp <?= number_format($total_historis_denda, 0, ',', '.') ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #dc3545 !important;">
            <p class="text-muted fw-semibold mb-1">Tunggakan Aktif (Global)</p>
            <h4 class="fw-bold text-danger">Rp <?= number_format($total_tunggakan, 0, ',', '.') ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #ffc107 !important;">
            <p class="text-muted fw-semibold mb-1">Mhs Dibekukan</p>
            <h4 class="fw-bold text-warning text-dark"><?= $mhs_dibekukan ?> Org</h4>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4 table-responsive">
        <table class="datatable-astar table table-hover border text-center align-middle" id="tableLaporan">
            <thead class="table-danger">
                <tr>
                    <th>No.</th>
                    <th>Tgl Terkena Denda</th>
                    <th>NIM / Mahasiswa</th>
                    <th>Jenis Pelanggaran</th>
                    <th>Nominal Denda</th>
                    <th>Sisa Tagihan Mhs</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($data_report as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggalPengembalian'])) ?></td>
                        <td class="text-start"><b><?= $row['nimMahasiswa'] ?></b><br><small><?= $row['namaMahasiswa'] ?></small></td>
                        <td class="text-start text-danger"><?= $row['namaSanksi'] ?></td>
                        <td class="fw-bold">Rp <?= number_format($row['sanksi_denda'], 0, ',', '.') ?></td>
                        <td><span class="badge <?= ((int)$row['sisa_tagihan'] > 0) ? 'bg-danger' : 'bg-success' ?>">Rp <?= number_format($row['sisa_tagihan'], 0, ',', '.') ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../../components/footer.php'; ?>