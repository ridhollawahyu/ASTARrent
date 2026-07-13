<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../config/database.php';
include '../../config/functions.php';

/** @var mysqli $koneksi */

// 1. VALIDASI HAK AKSES
$allowed_roles = ['Kepala GA', 'Staff GA', 'Finance', 'Tenaga Pendidik'];
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], $allowed_roles)) {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini tidak dapat diakses oleh Anda.');
    header('Location: ../00_auth/login.php');
    exit;
} elseif (isset($_SESSION['status']) && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun Anda sudah dinonaktifkan.');
    header('Location: ../00_auth/login.php');
    exit;
}

$role_login = $_SESSION['role'];
$dept_login = $_SESSION['departemen'] ?? '';
$id_login = $_SESSION['id'] ?? '';

// 2. TENTUKAN DINAMIS TIPE TRANSAKSI YANG DIPERBOLEHKAN BAGI SETIAP ROLE
$tipe_diperbolehkan = [];
if ($role_login === 'Kepala GA') {
    $tipe_diperbolehkan = [
        'peminjaman' => 'Peminjaman Barang & Fasilitas',
        'pengembalian' => 'Pengembalian Barang & Fasilitas',
        'reparasi' => 'Reparasi Aset & Fasilitas',
        'pengadaan' => 'Pengadaan Aset Baru'
    ];
} elseif ($role_login === 'Staff GA') {
    $tipe_diperbolehkan = [
        'peminjaman' => 'Peminjaman Barang & Fasilitas',
        'pengembalian' => 'Pengembalian Barang & Fasilitas',
        'reparasi' => 'Reparasi Aset & Fasilitas'
    ];
} elseif ($role_login === 'Finance') {
    $tipe_diperbolehkan = [
        'peminjaman' => 'Laporan Peminjaman & Denda',
        'pengadaan' => 'Laporan Pengadaan Aset'
    ];
} elseif ($role_login === 'Tenaga Pendidik') {
    $tipe_diperbolehkan = [
        'peminjaman' => 'Laporan Peminjaman Mahasiswa'
    ];
}

// Ambil filter default
$tipe_terpilih = isset($_GET['tipe']) && array_key_exists($_GET['tipe'], $tipe_diperbolehkan) ? $_GET['tipe'] : array_key_first($tipe_diperbolehkan);
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status_filter = $_GET['status'] ?? '';

// 3. PROSES SQL DENGAN FILTER
$query_where = "";
$data_report = [];

if ($tipe_terpilih === 'peminjaman') {
    $query_where = " WHERE 1=1 ";
    
    // Prodi filter untuk Tendik
    if ($role_login === 'Tenaga Pendidik') {
        $query_where .= " AND m.kodeProdi_mahasiswa = '$dept_login' ";
        // Tendik juga hanya mengelola Aset atau Fasilitas Akademik
        $query_where .= " AND (tp.idAset IS NOT NULL OR f.tipeFasilitas = 'Akademik') ";
    } elseif ($role_login === 'Staff GA') {
        // Staff GA hanya memverifikasi Fasilitas Non-Akademik
        $query_where .= " AND f.tipeFasilitas = 'Non-Akademik' ";
    }
    
    if (!empty($tgl_awal)) {
        $query_where .= " AND DATE(tp.tanggalPengajuan) >= '$tgl_awal' ";
    }
    if (!empty($tgl_akhir)) {
        $query_where .= " AND DATE(tp.tanggalPengajuan) <= '$tgl_akhir' ";
    }
    if (!empty($status_filter)) {
        $query_where .= " AND tp.statusPeminjaman = '$status_filter' ";
    }
    
    $sql = "
        SELECT tp.*, m.namaMahasiswa, m.nimMahasiswa AS nim, m.kodeProdi_mahasiswa AS prodi,
               a.namaAset, f.namaFasilitas
        FROM transaksi_peminjaman tp
        JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa
        LEFT JOIN aset a ON tp.idAset = a.idAset
        LEFT JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
        $query_where
        ORDER BY tp.tanggalPengajuan DESC
    ";
    
} elseif ($tipe_terpilih === 'pengembalian') {
    $query_where = " WHERE 1=1 ";
    
    // Prodi filter untuk Tendik
    if ($role_login === 'Tenaga Pendidik') {
        $query_where .= " AND m.kodeProdi_mahasiswa = '$dept_login' ";
        $query_where .= " AND (tp.idAset IS NOT NULL OR f.tipeFasilitas = 'Akademik') ";
    } elseif ($role_login === 'Staff GA') {
        $query_where .= " AND f.tipeFasilitas = 'Non-Akademik' ";
    }
    
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
    
} elseif ($tipe_terpilih === 'reparasi') {
    $query_where = " WHERE 1=1 ";
    
    if (!empty($tgl_awal)) {
        $query_where .= " AND DATE(tr.tanggalLapor) >= '$tgl_awal' ";
    }
    if (!empty($tgl_akhir)) {
        $query_where .= " AND DATE(tr.tanggalLapor) <= '$tgl_akhir' ";
    }
    if (!empty($status_filter)) {
        $query_where .= " AND tr.statusReparasi = '$status_filter' ";
    }
    
    $sql = "
        SELECT tr.*, a.namaAset, f.namaFasilitas, u1.namaUser AS namaPelapor, u2.namaUser AS namaPekerja
        FROM reparasi_fasilitas_aset tr
        LEFT JOIN aset a ON tr.idAset = a.idAset
        LEFT JOIN fasilitas f ON tr.idFasilitas = f.idFasilitas
        LEFT JOIN users u1 ON tr.idPelapor = u1.idUser
        LEFT JOIN users u2 ON tr.idTeknisi = u2.idUser
        $query_where
        ORDER BY tr.tanggalLapor DESC
    ";
    
} elseif ($tipe_terpilih === 'pengadaan') {
    $query_where = " WHERE 1=1 ";
    
    if ($role_login === 'Tenaga Pendidik') {
        // Tendik hanya melihat pengadaan yang diajukan oleh prodinya
        $query_where .= " AND u.departemen = '$dept_login' ";
    }
    
    if (!empty($tgl_awal)) {
        $query_where .= " AND DATE(tpd.tanggalPengadaan) >= '$tgl_awal' ";
    }
    if (!empty($tgl_akhir)) {
        $query_where .= " AND DATE(tpd.tanggalPengadaan) <= '$tgl_akhir' ";
    }
    if (!empty($status_filter)) {
        $query_where .= " AND tpd.statusPengadaan = '$status_filter' ";
    }
    
    $sql = "
        SELECT tpd.*, u.namaUser AS namaTendik, s.namaSupplier
        FROM transaksi_pengadaan tpd
        LEFT JOIN users u ON tpd.idTendik = u.idUser
        LEFT JOIN supplier s ON tpd.idSupplier = s.idSupplier
        $query_where
        ORDER BY tpd.tanggalPengadaan DESC
    ";
}

$queryResult = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($queryResult)) {
    $data_report[] = $row;
}

// 4. HITUNG RINGKASAN DATA UNTUK CARDS
$stat_card1_title = "";
$stat_card1_val = 0;
$stat_card1_icon = "bi-cash";
$stat_card1_bg = "bg-primary";

$stat_card2_title = "";
$stat_card2_val = 0;
$stat_card2_icon = "bi-check-all";
$stat_card2_bg = "bg-success";

$stat_card3_title = "";
$stat_card3_val = 0;
$stat_card3_icon = "bi-exclamation-triangle";
$stat_card3_bg = "bg-danger";

if ($tipe_terpilih === 'peminjaman') {
    $stat_card1_title = "Total Request Peminjaman";
    $stat_card1_val = count($data_report);
    $stat_card1_icon = "bi-journal-list";
    $stat_card1_bg = "bg-primary shadow-primary";
    
    $setuju = array_filter($data_report, fn($r) => $r['statusPeminjaman'] === 'Disetujui');
    $stat_card2_title = "Sedang Dipinjam";
    $stat_card2_val = count($setuju);
    $stat_card2_icon = "bi-box-arrow-up-right";
    $stat_card2_bg = "bg-success shadow-success";
    
    $menunggu = array_filter($data_report, fn($r) => $r['statusPeminjaman'] === 'Menunggu');
    $stat_card3_title = "Menunggu Persetujuan";
    $stat_card3_val = count($menunggu);
    $stat_card3_icon = "bi-hourglass-split";
    $stat_card3_bg = "bg-warning shadow-warning";

} elseif ($tipe_terpilih === 'pengembalian') {
    $stat_card1_title = "Total Pengembalian";
    $stat_card1_val = count($data_report);
    $stat_card1_icon = "bi-box-arrow-in-down-left";
    
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

} elseif ($tipe_terpilih === 'reparasi') {
    $stat_card1_title = "Total Tiket Reparasi";
    $stat_card1_val = count($data_report);
    $stat_card1_icon = "bi-wrench";
    
    $selesai = array_filter($data_report, fn($r) => $r['statusReparasi'] === 'Selesai');
    $stat_card2_title = "Reparasi Selesai";
    $stat_card2_val = count($selesai);
    $stat_card2_icon = "bi-check-circle";
    $stat_card2_bg = "bg-success shadow-success";
    
    $aktif = array_filter($data_report, fn($r) => $r['statusReparasi'] !== 'Selesai');
    $stat_card3_title = "Tiket Aktif";
    $stat_card3_val = count($aktif);
    $stat_card3_icon = "bi-exclamation-octagon";
    $stat_card3_bg = "bg-warning shadow-warning";

} elseif ($tipe_terpilih === 'pengadaan') {
    $stat_card1_title = "Total Usulan Pengadaan";
    $stat_card1_val = count($data_report);
    $stat_card1_icon = "bi-cart-plus";
    
    $selesai = array_filter($data_report, fn($r) => $r['statusPengadaan'] === 'Disetujui Finance');
    $stat_card2_title = "Pengadaan Berhasil";
    $stat_card2_val = count($selesai);
    $stat_card2_icon = "bi-cart-check";
    $stat_card2_bg = "bg-success shadow-success";
    
    // Total Anggaran
    $total_anggaran = 0;
    foreach ($data_report as $item) {
        if (isset($item['totalBiaya']) && $item['totalBiaya'] !== null) {
            $total_anggaran += (int)$item['totalBiaya'];
        }
    }
    $stat_card3_title = "Total Anggaran Terpakai (PPN 12%)";
    $stat_card3_val = "Rp " . number_format($total_anggaran, 0, ',', '.');
    $stat_card3_icon = "bi-wallet2";
    $stat_card3_bg = "bg-primary shadow-primary";
}

// Link Dashboard dinamis
$dashboard_link = "../dashboards/superadmin_home.php";
if ($role_login === 'Tenaga Pendidik') {
    $dashboard_link = "../dashboards/tendik_home.php";
} elseif ($role_login === 'Staff GA') {
    $dashboard_link = "../dashboards/staffga_home.php";
} elseif ($role_login === 'Kepala GA') {
    $dashboard_link = "../dashboards/kepalaga_home.php";
} elseif ($role_login === 'Finance') {
    $dashboard_link = "../dashboards/finance_home.php";
}

include '../../components/header.php';
?>

<!-- STYLE PRINTING & EXPORT -->
<style>
    /* Styling khusus format print dokumen formal */
    @media print {
        body {
            background: white !important;
            color: black !important;
            font-size: 12px !important;
        }
        .no-print, .navbar, .btn, form, .dataTables_filter, .dataTables_length, .dataTables_paginate, .dataTables_info, .card-header {
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
    <h5>Tipe Laporan: <?= $tipe_diperbolehkan[$tipe_terpilih] ?></h5>
    <div class="print-date">
        Periode Ekspor: <?= (!empty($tgl_awal) ? date('d-m-Y', strtotime($tgl_awal)) : 'Semua') ?> s/d <?= (!empty($tgl_akhir) ? date('d-m-Y', strtotime($tgl_akhir)) : 'Semua') ?>
        | Dicetak pada: <?= date('d-m-Y H:i:s') ?> oleh <?= htmlspecialchars($role_login) ?> (<?= htmlspecialchars($_SESSION['username']) ?>)
    </div>
</div>

<div class="card shadow-sm border-0 no-print" style="border-radius: 15px; margin-bottom: 25px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-bar-chart-line-fill me-2"></i>Laporan Transaksi & Monitoring</h5>
        <a href="<?= $dashboard_link ?>" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <!-- FILTER AREA -->
    <div class="card-body p-4 bg-light" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold text-astar">Komponen / Tipe Laporan</label>
                <select name="tipe" class="form-select border-2" style="border-radius:8px; border-color:#e0e6ed; color:#1d4197; font-weight:500;" onchange="this.form.submit()">
                    <?php foreach ($tipe_diperbolehkan as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $tipe_terpilih === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3 col-6">
                <label class="form-label fw-bold text-astar">Dari Tanggal</label>
                <input type="date" name="tgl_awal" class="form-control border-2" style="border-radius:8px; border-color:#e0e6ed; color:#1d4197; font-weight:500;" value="<?= htmlspecialchars($tgl_awal) ?>">
            </div>
            
            <div class="col-md-3 col-6">
                <label class="form-label fw-bold text-astar">Sampai Tanggal</label>
                <input type="date" name="tgl_akhir" class="form-control border-2" style="border-radius:8px; border-color:#e0e6ed; color:#1d4197; font-weight:500;" value="<?= htmlspecialchars($tgl_akhir) ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold text-astar">Status Transaksi</label>
                <select name="status" class="form-select border-2" style="border-radius:8px; border-color:#e0e6ed; color:#1d4197; font-weight:500;">
                    <option value="">-- Semua Status --</option>
                    <?php if ($tipe_terpilih === 'peminjaman'): ?>
                        <option value="Menunggu" <?= $status_filter === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="Disetujui" <?= $status_filter === 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="Ditolak" <?= $status_filter === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                        <option value="Selesai" <?= $status_filter === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    <?php elseif ($tipe_terpilih === 'pengembalian'): ?>
                        <option value="Normal" <?= $status_filter === 'Normal' ? 'selected' : '' ?>>Normal (Tepat / Sesuai)</option>
                        <option value="Berfungsi" <?= $status_filter === 'Berfungsi' ? 'selected' : '' ?>>Berfungsi</option>
                        <option value="Tidak Berfungsi" <?= $status_filter === 'Tidak Berfungsi' ? 'selected' : '' ?>>Tidak Berfungsi (Rusak)</option>
                    <?php elseif ($tipe_terpilih === 'reparasi'): ?>
                        <option value="Menunggu GA" <?= $status_filter === 'Menunggu GA' ? 'selected' : '' ?>>Menunggu GA</option>
                        <option value="Proses Perbaikan" <?= $status_filter === 'Proses Perbaikan' ? 'selected' : '' ?>>Proses Perbaikan</option>
                        <option value="Selesai" <?= $status_filter === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="Rusak Total" <?= $status_filter === 'Rusak Total' ? 'selected' : '' ?>>Rusak Total</option>
                    <?php elseif ($tipe_terpilih === 'pengadaan'): ?>
                        <option value="Usulan" <?= $status_filter === 'Usulan' ? 'selected' : '' ?>>Usulan Baru</option>
                        <option value="Validasi GA" <?= $status_filter === 'Validasi GA' ? 'selected' : '' ?>>Validasi GA (Diteruskan)</option>
                        <option value="Negosiasi Supplier" <?= $status_filter === 'Negosiasi Supplier' ? 'selected' : '' ?>>Negosiasi Supplier</option>
                        <option value="Persetujuan Finance" <?= $status_filter === 'Persetujuan Finance' ? 'selected' : '' ?>>Persetujuan Finance</option>
                        <option value="Pencairan" <?= $status_filter === 'Pencairan' ? 'selected' : '' ?>>Pencairan Dana</option>
                        <option value="Selesai" <?= $status_filter === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="Ditolak" <?= $status_filter === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-12 d-flex justify-content-between mt-4">
                <div>
                    <button type="submit" class="btn btn-astar px-4 fw-bold"><i class="bi bi-funnel-fill"></i> Terapkan Filter</button>
                    <a href="index.php?tipe=<?= $tipe_terpilih ?>" class="btn btn-light border fw-bold text-secondary px-3 ms-2">Reset</a>
                </div>
                <div>
                    <!-- EXPORT BUTTONS -->
                    <button type="button" onclick="window.print()" class="btn btn-danger fw-bold px-3"><i class="bi bi-file-pdf-fill"></i> Cetak PDF / Transaksi</button>
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
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #198754 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;"><?= $stat_card2_title ?></p>
                    <h4 class="fw-bold mb-0 text-success"><?= $stat_card2_val ?></h4>
                </div>
                <div class="rounded-circle p-3 bg-success-subtle text-success" style="font-size: 1.5rem; line-height: 1;">
                    <i class="bi <?= $stat_card2_icon ?>"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Card 3 -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #dc3545 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;"><?= $stat_card3_title ?></p>
                    <h4 class="fw-bold mb-0 text-danger"><?= $stat_card3_val ?></h4>
                </div>
                <div class="rounded-circle p-3 bg-danger-subtle text-danger" style="font-size: 1.5rem; line-height: 1;">
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
                    <?php if ($tipe_terpilih === 'peminjaman'): ?>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="15%">ID Pinjam</th>
                            <th width="15%">Mahasiswa (NIM)</th>
                            <th width="20%">Barang / Fasilitas</th>
                            <th width="18%">Tgl Pengajuan</th>
                            <th width="15%">Status</th>
                        </tr>
                    <?php elseif ($tipe_terpilih === 'pengembalian'): ?>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="15%">ID Kembali</th>
                            <th width="15%">Mahasiswa (NIM)</th>
                            <th width="20%">Barang / Fasilitas</th>
                            <th width="15%">Tgl Peminjaman</th>
                            <th width="15%">Tgl Pengembalian</th>
                            <th width="15%">Status Waktu / Kondisi</th>
                        </tr>
                    <?php elseif ($tipe_terpilih === 'reparasi'): ?>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="15%">ID Reparasi</th>
                            <th width="20%">Aset / Fasilitas</th>
                            <th width="15%">Tgl Lapor</th>
                            <th width="15%">Tingkat Rusak / Pelapor</th>
                            <th width="15%">Status Reparasi</th>
                        </tr>
                    <?php elseif ($tipe_terpilih === 'pengadaan'): ?>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="15%">ID Pengadaan</th>
                            <th width="15%">Pengaju (Tendik)</th>
                            <th width="20%">Usulan Aset</th>
                            <th width="15%">Harga (Incl PPN 12%)</th>
                            <th width="15%">Supplier / Status</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($data_report as $row): 
                    ?>
                        <?php if ($tipe_terpilih === 'peminjaman'): 
                            $nm_barang = !empty($row['idAset']) ? '[Aset] ' . $row['namaAset'] : '[Fasilitas] ' . $row['namaFasilitas'];
                            $badge_class = 'bg-warning text-dark';
                            if ($row['statusPeminjaman'] === 'Disetujui') $badge_class = 'bg-primary';
                            elseif ($row['statusPeminjaman'] === 'Selesai') $badge_class = 'bg-success';
                            elseif ($row['statusPeminjaman'] === 'Ditolak') $badge_class = 'bg-danger';
                        ?>
                            <tr>
                                <td class="fw-bold"><?= $no++ ?></td>
                                <td><?= $row['idPeminjaman'] ?></td>
                                <td class="text-start">
                                    <div class="fw-bold"><?= $row['namaMahasiswa'] ?></div>
                                    <small class="text-muted"><?= $row['nim'] ?> | <?= $row['prodi'] ?></small>
                                </td>
                                <td class="text-start fw-bold text-secondary"><?= $nm_barang ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($row['tanggalPengajuan'])) ?></td>
                                <td><span class="badge <?= $badge_class ?> rounded-pill px-3 py-2"><?= $row['statusPeminjaman'] ?></span></td>
                            </tr>
                        <?php elseif ($tipe_terpilih === 'pengembalian'): 
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
                        <?php elseif ($tipe_terpilih === 'reparasi'): 
                            $nm_barang = !empty($row['idAset']) ? '[Aset] ' . $row['namaAset'] : '[Fasilitas] ' . $row['namaFasilitas'];
                            $status_rep = $row['statusReparasi'];
                            $st_badge = 'bg-warning text-dark';
                            if ($status_rep === 'Proses Perbaikan') $st_badge = 'bg-primary';
                            elseif ($status_rep === 'Selesai') $st_badge = 'bg-success';
                            elseif ($status_rep === 'Rusak Total') $st_badge = 'bg-danger';
                        ?>
                            <tr>
                                <td class="fw-bold"><?= $no++ ?></td>
                                <td><?= $row['idReparasi'] ?></td>
                                <td class="text-start fw-bold text-secondary"><?= $nm_barang ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($row['tanggalLapor'])) ?></td>
                                <td>
                                    <div><span class="badge bg-light text-dark border"><?= $row['klasifikasiKerusakan'] ?></span></div>
                                    <small class="text-muted">Pelapor: <?= $row['namaPelapor'] ?></small>
                                </td>
                                <td><span class="badge <?= $st_badge ?> rounded-pill px-3 py-2"><?= $row['statusReparasi'] ?></span></td>
                            </tr>
                        <?php elseif ($tipe_terpilih === 'pengadaan'): 
                            $total_ppn = isset($row['totalBiaya']) ? (int)$row['totalBiaya'] : 0;
                            $badge_p = 'bg-warning text-dark';
                            if ($row['statusPengadaan'] === 'Disetujui Finance') $badge_p = 'bg-success';
                            elseif ($row['statusPengadaan'] === 'Ditolak') $badge_p = 'bg-danger';
                            elseif ($row['statusPengadaan'] === 'Harga Diinput Supplier') $badge_p = 'bg-primary';
                        ?>
                            <tr>
                                <td class="fw-bold"><?= $no++ ?></td>
                                <td><?= $row['idPengadaan'] ?></td>
                                <td class="text-start">
                                    <div class="fw-bold"><?= $row['namaKebutuhan'] ?></div>
                                    <small class="text-muted">Oleh: <?= $row['namaTendik'] ?></small>
                                </td>
                                <td>
                                    <div class="text-start"><small>Jml: <?= $row['jumlah'] ?> unit</small></div>
                                    <?php 
                                    $tgl_butuh_str = '';
                                    if (preg_match('/\(Tanggal Dibutuhkan:\s*([^\)]+)\)/', $row['alasanKebutuhan'], $matches)) {
                                        $tgl_butuh_str = date('d-m-Y', strtotime($matches[1]));
                                    } else {
                                        $tgl_butuh_str = date('d-m-Y', strtotime($row['tanggalPengadaan'] . ' + 7 days'));
                                    }
                                    ?>
                                    <div class="text-start"><small>Tgl Butuh: <?= $tgl_butuh_str ?></small></div>
                                </td>
                                <td class="fw-bold text-primary">Rp <?= number_format($total_ppn, 0, ',', '.') ?></td>
                                <td>
                                    <div class="mb-1"><small class="fw-semibold text-secondary"><?= $row['namaSupplier'] ?? 'Belum Ada' ?></small></div>
                                    <span class="badge <?= $badge_p ?> rounded-pill px-2 py-1"><?= $row['statusPengadaan'] ?></span>
                                </td>
                            </tr>
                        <?php endif; ?>
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
                // Bersihkan jeda baris/tabel dsb
                let text = col.innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/"/g, '""');
                rowData.push('"' + text + '"');
            });
            csvContent += rowData.join(",") + "\r\n";
        });
        
        // Buat file dan trigger unduh
        let blob = new Blob([new Uint8Array([0xEF, 0xBB, 0xBF]), csvContent], { type: "text/csv;charset=utf-8;" });
        let link = document.createElement("a");
        let url = URL.createObjectURL(blob);
        let fileName = "Laporan_Astarrent_<?= $tipe_terpilih ?>_" + new Date().toISOString().slice(0,10) + ".csv";
        
        link.setAttribute("href", url);
        link.setAttribute("download", fileName);
        link.style.visibility = "hidden";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<?php include '../../components/footer.php'; ?>
