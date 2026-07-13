<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

// 1. VALIDASI HAK AKSES
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini tidak dapat diakses oleh Anda.');
    header('Location: ../../../00_auth/login.php');
    exit;
} elseif (isset($_SESSION['status']) && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun Anda sudah dinonaktifkan.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

$role_login = $_SESSION['role'];

// Ambil filter default
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status_filter = $_GET['status'] ?? '';

// 2. PROSES SQL DENGAN FILTER (Staff GA: Aset ATAU Fasilitas Non-Akademik)
$query_where = " WHERE 1=1 AND (tp.idAset IS NOT NULL OR f.tipeFasilitas = 'Non-Akademik') ";
if (!empty($tgl_awal)) {
    $query_where .= " AND DATE(tpm.tanggalPengembalian) >= '$tgl_awal' ";
}
if (!empty($tgl_akhir)) {
    $query_where .= " AND DATE(tpm.tanggalPengembalian) <= '$tgl_akhir' ";
}
if (!empty($status_filter)) {
    $query_where .= " AND tpm.kondisiFisik = '$status_filter' ";
}

$sql = "
    SELECT tpm.*, tp.tanggalRencana_kembali, tp.idAset, tp.idFasilitas, 
           m.namaMahasiswa, m.nimMahasiswa AS nim, m.kodeProdi_mahasiswa AS prodi,
           a.namaAset, f.namaFasilitas, u.namaUser AS namaPengurus,
           TIMESTAMPDIFF(HOUR, tp.tanggalRencana_kembali, tpm.tanggalPengembalian) AS jam_terlambat
    FROM transaksi_pengembalian tpm
    JOIN transaksi_peminjaman tp ON tpm.idPeminjaman = tp.idPeminjaman
    JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa
    LEFT JOIN aset a ON tp.idAset = a.idAset
    LEFT JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
    LEFT JOIN users u ON tpm.idPengurus = u.idUser
    $query_where
    ORDER BY tpm.tanggalPengembalian DESC
";

$data_report = [];
$queryResult = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($queryResult)) {
    $data_report[] = $row;
}

// 3. HITUNG RINGKASAN DATA UNTUK CARDS
$stat_card1_title = "Total Pengembalian";
$stat_card1_val = count($data_report);
$stat_card1_icon = "bi-box-arrow-in-down-left";
$stat_card1_bg = "bg-primary shadow-primary";

$terlambat = array_filter($data_report, fn($r) => (int)$r['jam_terlambat'] > 0);
$stat_card2_title = "Kembali Terlambat";
$stat_card2_val = count($terlambat);
$stat_card2_icon = "bi-alarm";
$stat_card2_bg = "bg-danger shadow-danger";

$normal = array_filter($data_report, fn($r) => $r['kondisiFisik'] !== 'Normal' && $r['kondisiFisik'] !== 'Berfungsi');
$stat_card3_title = "Pengembalian Rusak";
$stat_card3_val = count($normal);
$stat_card3_icon = "bi-tools";
$stat_card3_bg = "bg-warning shadow-warning";

$dashboard_link = "../../../dashboards/staffga_home.php";

include '../../../../components/header.php';
?>

<!-- STYLE PRINTING & EXPORT -->
<style>
    @media print {
        body {
            background: white !important;
            color: black !important;
            font-size: 12px !important;
        }
        .no-print, .navbar, .btn, form, .dataTables_filter, .dataTables_length, .dataTables_paginate, .dataTables_info, .card-header, .nav-tabs {
            display: none !important;
        }
        .container, .card, .card-body {
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        table th, table td {
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
        .print-title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            color: #000;
        }
        .print-date {
            font-size: 11px;
            color: #555;
            margin-top: 5px;
        }
    }
    .print-header {
        display: none;
    }
</style>

<!-- KOP LAPORAN UNTUK PRINT -->
<div class="print-header">
    <h3 class="print-title">LAPORAN MONITORING TRANSAKSI ASTARRENT</h3>
    <h5>Tipe Laporan: Laporan Pengembalian Non-Akademik (Staff GA)</h5>
    <div class="print-date">
        Periode Ekspor: <?= (!empty($tgl_awal) ? date('d-m-Y', strtotime($tgl_awal)) : 'Semua') ?> s/d <?= (!empty($tgl_akhir) ? date('d-m-Y', strtotime($tgl_akhir)) : 'Semua') ?>
        | Dicetak pada: <?= date('d-m-Y H:i:s') ?> oleh <?= htmlspecialchars($role_login) ?> (<?= htmlspecialchars($_SESSION['username']) ?>)
    </div>
</div>

<!-- Tabs Navigasi Sub-Laporan -->
<ul class="nav nav-tabs mb-3 border-bottom-0 gap-1 no-print">
    <li class="nav-item">
        <a class="nav-link fw-bold text-secondary px-4 py-2 border border-bottom-0" href="laporan_peminjaman.php" style="border-radius: 8px 8px 0 0; border-color: transparent;">Peminjaman</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active fw-bold text-astar border border-bottom-0 px-4 py-2" href="laporan_pengembalian.php" style="border-radius: 8px 8px 0 0; background-color: #fff; border-color: #dee2e6 #dee2e6 #fff;">Pengembalian</a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-bold text-secondary px-4 py-2 border border-bottom-0" href="laporan_reparasi.php" style="border-radius: 8px 8px 0 0; border-color: transparent;">Reparasi</a>
    </li>
</ul>

<div class="card shadow-sm border-0 no-print" style="border-radius: 15px; margin-bottom: 25px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-box-arrow-in-down-left me-2"></i>Laporan Pengembalian Non-Akademik</h5>
        <a href="<?= $dashboard_link ?>" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <!-- FILTER AREA -->
    <div class="card-body p-4 bg-light" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4 col-6">
                <label class="form-label fw-bold text-astar">Dari Tanggal</label>
                <input type="date" name="tgl_awal" class="form-control border-2" style="border-radius:8px; border-color:#e0e6ed; color:#1d4197; font-weight:500;" value="<?= htmlspecialchars($tgl_awal) ?>">
            </div>
            
            <div class="col-md-4 col-6">
                <label class="form-label fw-bold text-astar">Sampai Tanggal</label>
                <input type="date" name="tgl_akhir" class="form-control border-2" style="border-radius:8px; border-color:#e0e6ed; color:#1d4197; font-weight:500;" value="<?= htmlspecialchars($tgl_akhir) ?>">
            </div>
            
            <div class="col-md-4">
                <label class="form-label fw-bold text-astar">Status / Kondisi Fisik</label>
                <select name="status" class="form-select border-2" style="border-radius:8px; border-color:#e0e6ed; color:#1d4197; font-weight:500;">
                    <option value="">-- Semua Kondisi --</option>
                    <option value="Normal" <?= $status_filter === 'Normal' ? 'selected' : '' ?>>Normal (Tepat / Sesuai)</option>
                    <option value="Berfungsi" <?= $status_filter === 'Berfungsi' ? 'selected' : '' ?>>Berfungsi</option>
                    <option value="Tidak Berfungsi" <?= $status_filter === 'Tidak Berfungsi' ? 'selected' : '' ?>>Tidak Berfungsi (Rusak)</option>
                </select>
            </div>
            
            <div class="col-12 d-flex justify-content-between mt-4">
                <div>
                    <button type="submit" class="btn btn-astar px-4 fw-bold"><i class="bi bi-funnel-fill"></i> Terapkan Filter</button>
                    <a href="laporan_pengembalian.php" class="btn btn-light border fw-bold text-secondary px-3 ms-2">Reset</a>
                </div>
                <div>
                    <button type="button" onclick="window.print()" class="btn btn-danger fw-bold px-3"><i class="bi bi-file-pdf-fill"></i> Cetak PDF</button>
                    <button type="button" onclick="exportToCSV()" class="btn btn-success fw-bold px-3 ms-2"><i class="bi bi-file-excel-fill"></i> Ekspor Excel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- INFO CARDS (STATISTICS) -->
<div class="row g-4 mb-4">
    <!-- Card 1 -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #1d4197 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;"><?= $stat_card1_title ?></p>
                    <h4 class="fw-bold mb-0 text-dark"><?= $stat_card1_val ?></h4>
                </div>
                <div class="rounded-circle p-3 bg-primary-subtle text-primary" style="font-size: 1.5rem; line-height: 1;">
                    <i class="bi <?= $stat_card1_icon ?>"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 2 -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #dc3545 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;"><?= $stat_card2_title ?></p>
                    <h4 class="fw-bold mb-0 text-danger"><?= $stat_card2_val ?></h4>
                </div>
                <div class="rounded-circle p-3 bg-danger-subtle text-danger" style="font-size: 1.5rem; line-height: 1;">
                    <i class="bi <?= $stat_card2_icon ?>"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 3 -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #ffc107 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;"><?= $stat_card3_title ?></p>
                    <h4 class="fw-bold mb-0 text-warning text-dark"><?= $stat_card3_val ?></h4>
                </div>
                <div class="rounded-circle p-3 bg-warning-subtle text-warning text-dark" style="font-size: 1.5rem; line-height: 1;">
                    <i class="bi <?= $stat_card3_icon ?>"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DATATABLE AREA -->
<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="datatable-astar table table-hover border text-center align-middle" id="tableLaporan">
                <thead style="background-color: #f4f6f9; color: #1d4197;">
                    <tr>
                        <th width="5%">No.</th>
                        <th width="15%">ID Kembali</th>
                        <th width="15%">Mahasiswa (NIM)</th>
                        <th width="20%">Barang / Fasilitas</th>
                        <th width="15%">Tgl Peminjaman</th>
                        <th width="15%">Tgl Pengembalian</th>
                        <th width="15%">Status Waktu / Kondisi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($data_report as $row): 
                        $nm_barang = !empty($row['idAset']) ? '[Aset] ' . $row['namaAset'] : '[Fasilitas] ' . $row['namaFasilitas'];
                        $is_telat = (int)$row['jam_terlambat'] > 0;
                        $kondisi = $row['kondisiFisik'];
                    ?>
                        <tr>
                            <td class="fw-bold"><?= $no++ ?></td>
                            <td><?= $row['idPengembalian'] ?></td>
                            <td class="text-start">
                                <div class="fw-bold"><?= $row['namaMahasiswa'] ?></div>
                                <small class="text-muted"><?= $row['nim'] ?> | <?= $row['prodi'] ?></small>
                            </td>
                            <td class="text-start fw-bold text-secondary"><?= $nm_barang ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($row['tanggalRencana_kembali'])) ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($row['tanggalPengembalian'])) ?></td>
                            <td>
                                <?php if ($is_telat): ?>
                                    <span class="badge bg-danger rounded-pill px-2 py-1 mb-1 d-block"><i class="bi bi-alarm"></i> Telat <?= format_waktu_terlambat($row['jam_terlambat']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success rounded-pill px-2 py-1 mb-1 d-block"><i class="bi bi-check-circle"></i> Tepat Waktu</span>
                                <?php endif; ?>
                                
                                <?php if ($kondisi === 'Normal' || $kondisi === 'Berfungsi'): ?>
                                    <span class="badge bg-secondary rounded-pill px-2 py-1 d-block"><?= $kondisi ?></span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1 d-block"><?= $kondisi ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- SCRIPT EKSPOR CSV/EXCEL -->
<script>
    function exportToCSV() {
        let table = document.getElementById("tableLaporan");
        let rows = table.querySelectorAll("tr");
        let csvContent = "";
        
        rows.forEach(function(row) {
            let cols = row.querySelectorAll("td, th");
            let rowData = [];
            cols.forEach(function(col) {
                let text = col.innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/"/g, '""');
                rowData.push('"' + text + '"');
            });
            csvContent += rowData.join(",") + "\r\n";
        });
        
        let blob = new Blob([new Uint8Array([0xEF, 0xBB, 0xBF]), csvContent], { type: "text/csv;charset=utf-8;" });
        let link = document.createElement("a");
        let url = URL.createObjectURL(blob);
        let fileName = "Laporan_Astarrent_pengembalian_" + new Date().toISOString().slice(0,10) + ".csv";
        
        link.setAttribute("href", url);
        link.setAttribute("download", fileName);
        link.style.visibility = "hidden";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<?php include '../../../../components/footer.php'; ?>
