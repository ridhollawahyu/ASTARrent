<?php
// --- FILE: modules/05_laporan_sistem/tendik/laporan_pengadaan.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../../config/database.php';
require '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    header('Location: ../../00_auth/login.php');
    exit;
}
$id_tendik = $_SESSION['id'];
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = " WHERE tp.idTendik = '$id_tendik' ";
if (!empty($tgl_awal)) $where .= " AND DATE(tp.tanggalPengadaan) >= '$tgl_awal' ";
if (!empty($tgl_akhir)) $where .= " AND DATE(tp.tanggalPengadaan) <= '$tgl_akhir' ";
if (!empty($status_filter)) $where .= " AND tp.statusPengadaan = '$status_filter' ";

$sql = "SELECT tp.*, k.namaKategori FROM transaksi_pengadaan tp JOIN kategori k ON tp.idKategori = k.idKategori $where ORDER BY tp.tanggalPengadaan DESC";

$data_report = [];
$q = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($q)) $data_report[] = $row;

$total_pengajuan = count($data_report);
$selesai = count(array_filter($data_report, fn($r) => $r['statusPengadaan'] === 'Disetujui Finance'));
$ditolak = count(array_filter($data_report, fn($r) => $r['statusPengadaan'] === 'Ditolak'));

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
    <h3>LAPORAN HISTORI PENGAJUAN PENGADAAN</h3>
    <div class="print-date">Dicetak: <?= date('d-m-Y H:i') ?> | Oleh: Tenaga Pendidik</div>
</div>

<div class="card shadow-sm border-0 no-print mb-4" style="border-radius: 15px;">
    <div class="card-header bg-astar text-white" style="border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 fw-bold">Filter Laporan Pengajuan Pengadaan</h5>
    </div>
    <div class="card-body p-4 bg-light">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4"><label class="fw-bold">Dari Tanggal</label><input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>"></div>
            <div class="col-md-4"><label class="fw-bold">Sampai Tanggal</label><input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>"></div>
            <div class="col-md-4">
                <label class="fw-bold">Status Transaksi</label>
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Disetujui Finance" <?= $status_filter === 'Disetujui Finance' ? 'selected' : '' ?>>Disetujui / Selesai Dibeli</option>
                    <option value="Ditolak" <?= $status_filter === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-12 text-end mt-4"><button type="submit" class="btn btn-astar fw-bold">Filter</button><button type="button" onclick="window.print()" class="btn btn-danger fw-bold ms-2">Cetak PDF</button><button type="button" onclick="exportToCSV('Laporan_Pengadaan_Tendik')" class="btn btn-success fw-bold ms-2">Excel</button></div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #1d4197 !important;">
            <p class="text-muted fw-semibold mb-1">Total Pengajuan Anda</p>
            <h4 class="fw-bold text-primary"><?= $total_pengajuan ?> Berkas</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #198754 !important;">
            <p class="text-muted fw-semibold mb-1">Berhasil Disetujui (Dibeli)</p>
            <h4 class="fw-bold text-success"><?= $selesai ?> Berkas</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #dc3545 !important;">
            <p class="text-muted fw-semibold mb-1">Pengajuan Ditolak</p>
            <h4 class="fw-bold text-danger"><?= $ditolak ?> Berkas</h4>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4 table-responsive">
        <table class="datatable-astar table table-hover border text-center align-middle" id="tableLaporan">
            <thead style="background-color:#f4f6f9; color:#1d4197;">
                <tr>
                    <th>No.</th>
                    <th>Tgl Pengajuan</th>
                    <th>Kategori</th>
                    <th>Nama Kebutuhan</th>
                    <th>Kuantitas</th>
                    <th>Status Terakhir</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($data_report as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggalPengadaan'])) ?></td>
                        <td><?= $row['namaKategori'] ?></td>
                        <td class="text-start fw-bold"><?= $row['namaKebutuhan'] ?></td>
                        <td><?= $row['jumlah'] ?> Unit</td>
                        <td><span class="badge bg-secondary"><?= $row['statusPengadaan'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../../components/footer.php'; ?>