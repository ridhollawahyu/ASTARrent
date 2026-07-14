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
$status_filter = $_GET['status'] ?? '';

$where = " WHERE 1=1 ";
if (!empty($tgl_awal)) $where .= " AND DATE(tr.tanggalLapor) >= '$tgl_awal' ";
if (!empty($tgl_akhir)) $where .= " AND DATE(tr.tanggalLapor) <= '$tgl_akhir' ";
if (!empty($status_filter)) $where .= " AND tr.statusReparasi = '$status_filter' ";

$sql = "SELECT tr.*, a.namaAset, f.namaFasilitas, u1.namaUser AS pelapor, u2.namaUser AS teknisi FROM reparasi_fasilitas_aset tr LEFT JOIN aset a ON tr.idAset = a.idAset LEFT JOIN fasilitas f ON tr.idFasilitas = f.idFasilitas LEFT JOIN users u1 ON tr.idPelapor = u1.idUser LEFT JOIN users u2 ON tr.idTeknisi = u2.idUser $where ORDER BY tr.tanggalLapor DESC";

$data_report = [];
$q = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($q)) $data_report[] = $row;

$total_tiket = count($data_report);
$selesai = count(array_filter($data_report, fn($r) => $r['statusReparasi'] === 'Selesai'));
$kanibal = count(array_filter($data_report, fn($r) => $r['statusReparasi'] === 'Dikanibal'));

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
    <h3>LAPORAN GLOBAL REPARASI KAMPUS</h3>
    <div class="print-date">Dicetak: <?= date('d-m-Y H:i') ?> | Oleh: Kepala GA</div>
</div>

<div class="card shadow-sm border-0 no-print mb-4" style="border-radius: 15px;">
    <div class="card-header bg-astar text-white" style="border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 fw-bold">Filter Laporan Reparasi</h5>
    </div>
    <div class="card-body p-4 bg-light">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4"><label class="fw-bold">Dari Tanggal</label><input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>"></div>
            <div class="col-md-4"><label class="form-label fw-bold text-astar">Sampai Tanggal</label><input type="date" name="tgl_akhir" class="form-control" value="<?= htmlspecialchars($tgl_akhir) ?>"></div>
            <div class="col-md-4">
                <label class="form-label fw-bold text-astar">Status Reparasi</label>
                <select name="status" class="form-select border-2">
                    <option value="">-- Semua Status --</option>
                    <option value="Selesai" <?= $status_filter === 'Selesai' ? 'selected' : '' ?>>Berhasil Diperbaiki (Selesai)</option>
                    <option value="Dikanibal" <?= $status_filter === 'Dikanibal' ? 'selected' : '' ?>>Gagal Diperbaiki (Dikanibal)</option>
                    <option value="Menunggu GA" <?= $status_filter === 'Menunggu GA' ? 'selected' : '' ?>>Menunggu Tim GA</option>
                </select>
            </div>
            <div class="col-12 text-end mt-4"><button type="submit" class="btn btn-astar fw-bold">Filter</button><button type="button" onclick="window.print()" class="btn btn-danger fw-bold ms-2">Cetak PDF</button><button type="button" onclick="exportToCSV('Laporan_Global_Reparasi')" class="btn btn-success fw-bold ms-2">Excel</button></div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #1d4197 !important;">
            <p class="text-muted fw-semibold mb-1">Total Tiket Laporan</p>
            <h4 class="fw-bold text-primary"><?= $total_tiket ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #198754 !important;">
            <p class="text-muted fw-semibold mb-1">Berhasil Diperbaiki Tim</p>
            <h4 class="fw-bold text-success"><?= $selesai ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #dc3545 !important;">
            <p class="text-muted fw-semibold mb-1">Dikanibal (Mati Total)</p>
            <h4 class="fw-bold text-danger"><?= $kanibal ?></h4>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4 table-responsive">
        <table class="datatable-astar table table-hover border text-center align-middle" id="tableLaporan">
            <thead style="background-color:#f4f6f9; color:#1d4197;">
                <tr>
                    <th>No.</th>
                    <th>ID Reparasi</th>
                    <th>Barang Rusak</th>
                    <th>Tgl Lapor</th>
                    <th>Teknisi Bertugas</th>
                    <th>Status Akhir</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($data_report as $row): $nm_barang = !empty($row['idAset']) ? '[Aset] ' . $row['namaAset'] : '[Fasilitas] ' . $row['namaFasilitas']; ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row['idReparasi'] ?></td>
                        <td class="text-start fw-bold text-secondary"><?= $nm_barang ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggalLapor'])) ?></td>
                        <td><span class="badge bg-secondary"><?= $row['teknisi'] ?? 'Belum Ditangani' ?></span></td>
                        <td><?= $row['statusReparasi'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../../components/footer.php'; ?>