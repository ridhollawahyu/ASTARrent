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
$ketersediaan_filter = $_GET['ketersediaan'] ?? '';

$where_fsl = "WHERE tipeFasilitas = 'Non-Akademik'";
$where_komp = "WHERE 1=1"; // KondisiKomponen

if (!empty($ketersediaan_filter)) {
    $where_fsl .= " AND ketersediaanFasilitas = '$ketersediaan_filter'";
    // Untuk komponen, statusnya 'Tersedia' atau 'Sudah Dipakai' (mapping kasar)
    if ($ketersediaan_filter == 'Dipinjam') {
        $where_komp .= " AND statusKomponen = 'Sudah Dipakai'";
    } elseif ($ketersediaan_filter == 'Tidak Tersedia') {
        $where_komp .= " AND statusKomponen = 'Nonaktif'";
    } else {
        $where_komp .= " AND statusKomponen = '$ketersediaan_filter'";
    }
}

$sql = "SELECT idFasilitas AS id_brg, namaFasilitas AS nama_brg, kondisiFasilitas AS kondisi, ketersediaanFasilitas AS status, 'Fasilitas Non-Akademik' AS tipe, k.namaKategori FROM fasilitas JOIN kategori k ON fasilitas.idKategori = k.idKategori $where_fsl
        UNION ALL
        SELECT idKomponen, namaKomponen, kondisiKomponen, statusKomponen, 'Suku Cadang (Kanibal)', 'Ex-Reparasi' FROM komponen $where_komp
        ORDER BY tipe ASC, nama_brg ASC";

$data_report = [];
$q = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($q)) $data_report[] = $row;

$tersedia = count(array_filter($data_report, fn($r) => $r['status'] === 'Tersedia'));
$dipakai = count(array_filter($data_report, fn($r) => in_array($r['status'], ['Dipinjam', 'Sudah Dipakai'])));
$arsip = count(array_filter($data_report, fn($r) => in_array($r['status'], ['Sedang Diperbaiki', 'Tidak Tersedia', 'Nonaktif'])));

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
    <h3>LAPORAN STOCK OPNAME NON-AKADEMIK & KOMPONEN</h3>
    <div class="print-date">Dicetak: <?= date('d-m-Y H:i') ?> | Oleh: Staff GA</div>
</div>

<div class="card shadow-sm border-0 no-print mb-4" style="border-radius: 15px;">
    <div class="card-header bg-astar text-white" style="border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 fw-bold">Filter Laporan Stok Non-Akademik</h5>
    </div>
    <div class="card-body p-4 bg-light">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-6"><label class="fw-bold">Status Ketersediaan</label>
                <select name="ketersediaan" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Tersedia" <?= $ketersediaan_filter === 'Tersedia' ? 'selected' : '' ?>>Tersedia (Di Gudang)</option>
                    <option value="Dipinjam" <?= $ketersediaan_filter === 'Dipinjam' ? 'selected' : '' ?>>Sedang Dipinjam / Suku Cadang Terpakai</option>
                    <option value="Tidak Tersedia" <?= $ketersediaan_filter === 'Tidak Tersedia' ? 'selected' : '' ?>>Rusak / Arsip</option>
                </select>
            </div>
            <div class="col-12 text-end mt-4"><button type="submit" class="btn btn-astar fw-bold">Filter</button><button type="button" onclick="window.print()" class="btn btn-danger fw-bold ms-2">Cetak PDF</button><button type="button" onclick="exportToCSV('Laporan_Inventaris_GA')" class="btn btn-success fw-bold ms-2">Excel</button></div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #198754 !important;">
            <p class="text-muted fw-semibold mb-1">Tersedia (Siap Pakai)</p>
            <h4 class="fw-bold text-success"><?= $tersedia ?> Unit</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #0d6efd !important;">
            <p class="text-muted fw-semibold mb-1">Dipinjam / Digunakan</p>
            <h4 class="fw-bold text-primary"><?= $dipakai ?> Unit</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-left: 5px solid #dc3545 !important;">
            <p class="text-muted fw-semibold mb-1">Rusak / Arsip / Bengkel</p>
            <h4 class="fw-bold text-danger"><?= $arsip ?> Unit</h4>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4 table-responsive">
        <table class="datatable-astar table table-hover border text-center align-middle" id="tableLaporan">
            <thead style="background-color:#f4f6f9; color:#1d4197;">
                <tr>
                    <th>No.</th>
                    <th>ID Barang</th>
                    <th>Nama & Kategori</th>
                    <th>Tipe/Sumber</th>
                    <th>Kondisi Fisik</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                foreach ($data_report as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><code class="text-dark fw-bold"><?= $row['id_brg'] ?></code></td>
                        <td class="text-start"><b><?= $row['nama_brg'] ?></b><br><small><?= $row['namaKategori'] ?></small></td>
                        <td><span class="badge bg-secondary"><?= $row['tipe'] ?></span></td>
                        <td><?= $row['kondisi'] ?></td>
                        <td><span class="badge <?= ($row['status'] == 'Tersedia') ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $row['status'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../../components/footer.php'; ?>