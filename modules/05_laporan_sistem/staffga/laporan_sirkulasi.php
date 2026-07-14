<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../../config/database.php';
require '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    header('Location: ../../00_auth/login.php');
    exit;
}
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// HANYA MENGAMBIL FASILITAS NON-AKADEMIK
$where = "WHERE f.tipeFasilitas = 'Non-Akademik'";
if (!empty($tgl_awal)) $where .= " AND DATE(tp.tanggalPengajuan) >= '$tgl_awal'";
if (!empty($tgl_akhir)) $where .= " AND DATE(tp.tanggalPengajuan) <= '$tgl_akhir'";

$sql = "SELECT tp.*, tpm.tanggalPengembalian, m.namaMahasiswa, m.nimMahasiswa, f.namaFasilitas,
        TIMESTAMPDIFF(HOUR, tp.tanggalRencana_kembali, IFNULL(tpm.tanggalPengembalian, NOW())) AS jam_telat
        FROM transaksi_peminjaman tp JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa
        LEFT JOIN transaksi_pengembalian tpm ON tp.idPeminjaman = tpm.idPeminjaman
        JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
        $where ORDER BY tp.tanggalPengajuan DESC";

$data_report = [];
$telat_count = [];
$fasilitas_count = [];
$q = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($q)) {
    $data_report[] = $row;
    $fasilitas_count[$row['namaFasilitas']] = ($fasilitas_count[$row['namaFasilitas']] ?? 0) + 1;
    if ((int)$row['jam_telat'] > 0 && $row['statusPeminjaman'] == 'Selesai') {
        $telat_count[$row['namaMahasiswa']] = ($telat_count[$row['namaMahasiswa']] ?? 0) + 1;
    }
}
arsort($telat_count);
$top_telat = key($telat_count) ?? 'Tidak Ada';
arsort($fasilitas_count);
$top_fasilitas = key($fasilitas_count) ?? 'Tidak Ada';

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
    <h3>LAPORAN SIRKULASI NON-AKADEMIK</h3>
    <div class="print-date">Dicetak: <?= date('d-m-Y H:i') ?> | Oleh: Staff GA</div>
</div>

<div class="card shadow-sm border-0 no-print mb-4" style="border-radius: 15px;">
    <div class="card-header bg-astar text-white" style="border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 fw-bold">Filter Laporan Sirkulasi Fasilitas</h5>
    </div>
    <div class="card-body p-4 bg-light">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-5"><label class="fw-bold">Dari Tanggal</label><input type="date" name="tgl_awal" class="form-control" value="<?= $tgl_awal ?>"></div>
            <div class="col-md-5"><label class="fw-bold">Sampai Tanggal</label><input type="date" name="tgl_akhir" class="form-control" value="<?= $tgl_akhir ?>"></div>
            <div class="col-12 text-end mt-4"><button type="submit" class="btn btn-astar fw-bold">Filter</button><button type="button" onclick="window.print()" class="btn btn-danger fw-bold ms-2">Cetak PDF</button><button type="button" onclick="exportToCSV('Laporan_Sirkulasi_GA')" class="btn btn-success fw-bold ms-2">Excel</button></div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #1d4197 !important;">
            <p class="text-muted fw-semibold mb-1">Total Peminjaman</p>
            <h4 class="fw-bold text-primary"><?= count($data_report) ?> Transaksi</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #dc3545 !important;">
            <p class="text-muted fw-semibold mb-1">Mhs Sering Telat Kembali</p>
            <h5 class="fw-bold text-danger"><?= $top_telat ?></h5>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #198754 !important;">
            <p class="text-muted fw-semibold mb-1">Fasilitas Paling Laris</p>
            <h5 class="fw-bold text-success"><?= $top_fasilitas ?></h5>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4 table-responsive">
        <table class="datatable-astar table table-hover border text-center align-middle" id="tableLaporan">
            <thead style="background-color:#f4f6f9; color:#1d4197;">
                <tr>
                    <th>No.</th>
                    <th>Mahasiswa</th>
                    <th>Fasilitas Komunal</th>
                    <th>Tgl Pinjam</th>
                    <th>Tgl Kembali</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($data_report as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="text-start"><b><?= $row['namaMahasiswa'] ?></b><br><small><?= $row['nimMahasiswa'] ?></small></td>
                        <td class="text-start fw-bold text-secondary"><?= $row['namaFasilitas'] ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggalPengajuan'])) ?></td>
                        <td><?= $row['tanggalPengembalian'] ? date('d-m-Y', strtotime($row['tanggalPengembalian'])) : '-' ?></td>
                        <td><span class="badge bg-secondary"><?= $row['statusPeminjaman'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../../components/footer.php'; ?>