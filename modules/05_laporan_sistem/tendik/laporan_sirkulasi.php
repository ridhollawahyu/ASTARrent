<?php
// --- FILE: modules/05_laporan_sistem/tendik/laporan_sirkulasi.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../../config/database.php';
require '../../../config/functions.php';

/** @var mysqli $koneksi */

// 1. VALIDASI HAK AKSES (TENDIK)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Tenaga Pendidik') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}

$role_login = $_SESSION['role'];
$dept_login = $_SESSION['departemen'] ?? '';

// 2. TANGKAP FILTER TANGGAL
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status_filter = $_GET['status'] ?? '';

// 3. LOGIKA QUERY SIRKULASI (Aset & Fasilitas Akademik di Prodi Tendik)
$query_where = " WHERE 1=1 AND m.kodeProdi_mahasiswa = '$dept_login' AND (tp.idAset IS NOT NULL OR f.tipeFasilitas = 'Akademik') ";

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
    SELECT tp.*, m.namaMahasiswa, m.nimMahasiswa AS nim, 
           a.namaAset, f.namaFasilitas,
           TIMESTAMPDIFF(HOUR, tp.tanggalRencana_kembali, NOW()) AS jam_telat
    FROM transaksi_peminjaman tp
    JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa
    LEFT JOIN aset a ON tp.idAset = a.idAset
    LEFT JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
    $query_where
    ORDER BY tp.tanggalPengajuan DESC
";

$data_report = [];
$queryResult = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($queryResult)) {
    $data_report[] = $row;
}

// =========================================================================
// 4. LOGIKA DOMPDF (HANYA BERJALAN JIKA TOMBOL CETAK PDF DIKLIK)
// =========================================================================
if (isset($_GET['export_pdf']) && $_GET['export_pdf'] == '1') {
    // Panggil Autoloader Dompdf
    require '../../../vendor/autoload.php';

    // Trik Base64 Logo agar Dompdf bisa merender gambar lokal tanpa error URL
    $path_logo = __DIR__ . '/../../../assets/images/full_logo_blue.png';
    $img_tag = file_exists($path_logo) ? '<img src="data:image/png;base64,' . base64_encode(file_get_contents($path_logo)) . '" height="50">' : '<h2>ASTARrent</h2>';

    // RAKIT HTML KHUSUS UNTUK PDF
    $html = '<!DOCTYPE html><html><head><style>
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 11px; color: #333; }
        .kop { text-align: center; border-bottom: 3px double #1d4197; margin-bottom: 20px; padding-bottom: 10px; }
        .kop h3 { margin: 10px 0 5px 0; color: #1d4197; font-size: 18px; text-transform: uppercase; }
        .kop p { margin: 3px 0; font-size: 11px; color: #555; }
        
        /* CSS DOMPDF: Mengulang Thead & Mencegah TR Terpotong */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #777; padding: 6px; text-align: center; vertical-align: middle; }
        th { background-color: #e8f0fe; color: #1d4197; font-weight: bold; }
        thead { display: table-header-group; } 
        tr { page-break-inside: avoid; } 
    </style></head><body>';

    $html .= '<div class="kop">';
    $html .= $img_tag;
    $html .= '<h3>LAPORAN SIRKULASI BARANG & FASILITAS AKADEMIK</h3>';
    $html .= '<p><strong>Departemen / Prodi: ' . strtoupper($dept_login) . '</strong></p>';
    $html .= '<p>Periode: ' . (!empty($tgl_awal) ? date('d/m/Y', strtotime($tgl_awal)) : 'Awal') . ' s/d ' . (!empty($tgl_akhir) ? date('d/m/Y', strtotime($tgl_akhir)) : 'Akhir') . '</p>';
    $html .= '<p>Dicetak Oleh: ' . htmlspecialchars($role_login) . ' | Tanggal Cetak: ' . date('d/m/Y H:i:s') . '</p>';
    $html .= '</div>';

    $html .= '<table>';
    $html .= '<thead><tr>
                <th width="5%">No</th>
                <th width="15%">ID Transaksi</th>
                <th width="20%">Peminjam (NIM)</th>
                <th width="25%">Barang/Fasilitas</th>
                <th width="15%">Waktu Pengajuan</th>
                <th width="20%">Status Akhir</th>
              </tr></thead><tbody>';

    $no = 1;
    foreach ($data_report as $row) {
        $nm_barang = !empty($row['idAset']) ? $row['namaAset'] : $row['namaFasilitas'];
        $html .= '<tr>
                    <td>' . $no++ . '</td>
                    <td>' . $row['idPeminjaman'] . '</td>
                    <td style="text-align:left;"><b>' . $row['namaMahasiswa'] . '</b><br>' . $row['nim'] . '</td>
                    <td style="text-align:left;">' . $nm_barang . '</td>
                    <td>' . date('d-m-Y H:i', strtotime($row['tanggalPengajuan'])) . '</td>
                    <td>' . $row['statusPeminjaman'] . '</td>
                  </tr>';
    }

    $html .= '</tbody></table></body></html>';

    // JALANKAN DOMPDF
    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output langsung di browser (Membuka PDF di tab baru)
    $dompdf->stream("Laporan_Sirkulasi_Tendik.pdf", array("Attachment" => true));
    exit; // Wajib EXIT agar HTML web di bawah tidak ikut ter-render ke dalam file PDF
}

// 5. HITUNG RINGKASAN DATA UNTUK KOTAK SUMMARY (Sesuai Blueprint Anda)
$stat_card1_title = "Total Sirkulasi";
$stat_card1_val = count($data_report);
$stat_card1_icon = "bi-arrow-repeat";

$setuju = array_filter($data_report, fn($r) => $r['statusPeminjaman'] === 'Disetujui');
$stat_card2_title = "Barang Sedang Keluar";
$stat_card2_val = count($setuju);
$stat_card2_icon = "bi-box-arrow-right";

$menunggu = array_filter($data_report, fn($r) => $r['statusPeminjaman'] === 'Menunggu');
$stat_card3_title = "Menunggu Persetujuan";
$stat_card3_val = count($menunggu);
$stat_card3_icon = "bi-hourglass-split";

include '../../../components/header.php';
?>

<!-- TAB NAVIGASI KHUSUS TENDIK -->
<ul class="nav nav-tabs mb-4 border-bottom-0 gap-1">
    <li class="nav-item">
        <a class="nav-link active fw-bold text-astar border border-bottom-0 px-4 py-2" href="laporan_sirkulasi.php" style="border-radius: 8px 8px 0 0; background-color: #fff;">Sirkulasi Akademik</a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-bold text-secondary px-4 py-2 border border-bottom-0" href="laporan_pengadaan.php" style="border-radius: 8px 8px 0 0; border-color: transparent;">Pengajuan Pengadaan</a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-bold text-secondary px-4 py-2 border border-bottom-0" href="laporan_inventaris.php" style="border-radius: 8px 8px 0 0; border-color: transparent;">Status Inventaris</a>
    </li>
</ul>

<div class="card shadow-sm border-0" style="border-radius: 15px; margin-bottom: 25px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-bar-chart-line-fill me-2"></i>Laporan Sirkulasi Akademik</h5>
        <a href="../../dashboards/tendik_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <!-- FILTER AREA -->
    <div class="card-body p-4 bg-light" style="border-radius: 0 0 15px 15px;">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold text-astar">Dari Tanggal</label>
                <input type="date" name="tgl_awal" class="form-control border-2" value="<?= htmlspecialchars($tgl_awal) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold text-astar">Sampai Tanggal</label>
                <input type="date" name="tgl_akhir" class="form-control border-2" value="<?= htmlspecialchars($tgl_akhir) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold text-astar">Status Transaksi</label>
                <?php
                $opsi_status = [
                    '' => '-- Semua Status --',
                    'Menunggu' => 'Menunggu Persetujuan',
                    'Disetujui' => 'Sedang Dipinjam',
                    'Selesai' => 'Selesai Dikembalikan',
                    'Ditolak' => 'Ditolak'
                ];
                echo buat_dropdown_astar('status', $opsi_status, $status_filter, false);
                ?>
            </div>
            <div class="col-12 d-flex justify-content-between mt-4">
                <div>
                    <button type="submit" class="btn btn-astar px-4 fw-bold"><i class="bi bi-funnel-fill"></i> Terapkan Filter</button>
                    <a href="laporan_sirkulasi.php" class="btn btn-light border fw-bold text-secondary px-3 ms-2">Reset</a>
                </div>
                <div>
                    <!-- KUNCI DOMPDF: PENGGUNAAN FORMTARGET BLANK AGAR MUNCUL DI TAB BARU -->
                    <button type="submit" name="export_pdf" value="1" class="btn btn-danger fw-bold px-4">
                        <i class="bi bi-file-pdf-fill me-1"></i> Generate PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- KOTAK SUMMARY CARDS (Sesuai Blueprint Anda) -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #1d4197 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;"><?= $stat_card1_title ?></p>
                    <h4 class="fw-bold mb-0 text-dark"><?= $stat_card1_val ?> Transaksi</h4>
                </div>
                <div class="rounded-circle p-3 bg-primary-subtle text-primary" style="font-size: 1.5rem; line-height: 1;"><i class="bi <?= $stat_card1_icon ?>"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #198754 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;"><?= $stat_card2_title ?></p>
                    <h4 class="fw-bold mb-0 text-success"><?= $stat_card2_val ?> Aset</h4>
                </div>
                <div class="rounded-circle p-3 bg-success-subtle text-success" style="font-size: 1.5rem; line-height: 1;"><i class="bi <?= $stat_card2_icon ?>"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; background-color: #ffffff; border-left: 5px solid #ffc107 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;"><?= $stat_card3_title ?></p>
                    <h4 class="fw-bold mb-0 text-warning text-dark"><?= $stat_card3_val ?> Request</h4>
                </div>
                <div class="rounded-circle p-3 bg-warning-subtle text-warning text-dark" style="font-size: 1.5rem; line-height: 1;"><i class="bi <?= $stat_card3_icon ?>"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- DATATABLE AREA -->
<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4">
        <div class="table-responsive mt-2">
            <?php if (count($data_report) > 0): ?>
                <table class="datatable-astar table table-hover border  align-middle">
                    <thead style="background-color: #f4f6f9; color: #1d4197;">
                        <tr>
                            <th width="5%">No.</th>
                            <th width="15%">ID Transaksi</th>
                            <th width="20%">Peminjam (NIM)</th>
                            <th width="25%">Barang / Fasilitas</th>
                            <th width="15%">Tgl Pengajuan</th>
                            <th width="15%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($data_report as $row):
                            $nm_barang = !empty($row['idAset']) ? $row['namaAset'] : $row['namaFasilitas'];
                            $badge_class = 'bg-warning text-dark';
                            if ($row['statusPeminjaman'] === 'Disetujui') $badge_class = 'bg-primary';
                            elseif ($row['statusPeminjaman'] === 'Selesai') $badge_class = 'bg-success';
                            elseif ($row['statusPeminjaman'] === 'Ditolak') $badge_class = 'bg-danger';
                        ?>
                            <tr>
                                <td class="fw-bold"><?= $no++ ?></td>
                                <td><span class="text-primary fw-bold"><?= $row['idPeminjaman'] ?></span></td>
                                <td>
                                    <div class="fw-bold"><?= $row['namaMahasiswa'] ?></div>
                                    <small class="text-muted"><?= $row['nim'] ?></small>
                                </td>
                                <td class="text-start fw-bold text-secondary"><?= $nm_barang ?></td>
                                <td><?= date('d M Y, H:i', strtotime($row['tanggalPengajuan'])) ?></td>
                                <td><span class="badge <?= $badge_class ?> rounded-pill px-3 py-2"><?= $row['statusPeminjaman'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <!-- PERBAIKAN: Tidak menggunakan colspan yang bisa merusak DataTables -->
                <div class="text-center py-5">
                    <i class="bi bi-file-earmark-x text-muted d-block mb-3" style="font-size: 4rem;"></i>
                    <h5 class="text-muted fw-bold">Data Tidak Ditemukan</h5>
                    <p class="text-muted">Tidak ada transaksi sirkulasi pada rentang waktu/filter tersebut.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>