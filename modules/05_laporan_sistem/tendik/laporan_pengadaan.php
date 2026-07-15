<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../../config/database.php';
require '../../../config/functions.php';

/** @var mysqli $koneksi */

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
$id_tendik = $_SESSION['id'];
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$status_filter = $_GET['status'] ?? '';

$query_where = " WHERE tp.idTendik = '$id_tendik' ";
if (!empty($tgl_awal)) $query_where .= " AND DATE(tp.tanggalPengadaan) >= '$tgl_awal' ";
if (!empty($tgl_akhir)) $query_where .= " AND DATE(tp.tanggalPengadaan) <= '$tgl_akhir' ";
if (!empty($status_filter)) $query_where .= " AND tp.statusPengadaan = '$status_filter' ";

$sql = "SELECT tp.*, k.namaKategori FROM transaksi_pengadaan tp JOIN kategori k ON tp.idKategori = k.idKategori $query_where ORDER BY tp.tanggalPengadaan DESC";
$data_report = [];
$queryResult = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($queryResult)) {
    $data_report[] = $row;
}

// ======================= EKSPOR DOMPDF =======================
if (isset($_GET['export_pdf']) && $_GET['export_pdf'] == '1') {
    require '../../../vendor/autoload.php';
    $path_logo = __DIR__ . '/../../../assets/images/full_logo_blue.png';
    $img_tag = file_exists($path_logo) ? '<img src="data:image/png;base64,' . base64_encode(file_get_contents($path_logo)) . '" height="50">' : '<h2>ASTARrent</h2>';

    $html = '<!DOCTYPE html><html><head><style>
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 11px; color: #333; }
        .kop { text-align: center; border-bottom: 3px double #1d4197; margin-bottom: 20px; padding-bottom: 10px; }
        .kop h3 { margin: 10px 0 5px 0; color: #1d4197; font-size: 18px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #777; padding: 6px; text-align: center; vertical-align: middle; }
        th { background-color: #e8f0fe; color: #1d4197; font-weight: bold; }
        thead { display: table-header-group; } tr { page-break-inside: avoid; }
    </style></head><body>';

    $html .= '<div class="kop">' . $img_tag . '<h3>LAPORAN PENGAJUAN PENGADAAN (TENDIK)</h3><p>Periode: ' . (!empty($tgl_awal) ? date('d/m/Y', strtotime($tgl_awal)) : 'Awal') . ' s/d ' . (!empty($tgl_akhir) ? date('d/m/Y', strtotime($tgl_akhir)) : 'Akhir') . '</p><p>Dicetak Oleh: ' . htmlspecialchars($_SESSION['username'] ?? 'Tendik') . ' | Tanggal Cetak: ' . date('d/m/Y H:i:s') . '</p></div>';

    $html .= '<table><thead><tr><th width="5%">No</th><th width="15%">ID Pengadaan</th><th width="25%">Nama Kebutuhan</th><th width="10%">Jumlah</th><th width="20%">Tgl Pengajuan</th><th width="25%">Status</th></tr></thead><tbody>';
    $no = 1;
    foreach ($data_report as $row) {
        $html .= '<tr><td>' . $no++ . '</td><td>' . $row['idPengadaan'] . '</td><td style="text-align:left;"><b>' . $row['namaKategori'] . '</b><br>' . $row['namaKebutuhan'] . '</td><td>' . $row['jumlah'] . ' Unit</td><td>' . date('d-m-Y H:i', strtotime($row['tanggalPengadaan'])) . '</td><td>' . $row['statusPengadaan'] . '</td></tr>';
    }
    $html .= '</tbody></table></body></html>';

    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Laporan_Pengadaan_Tendik.pdf", array("Attachment" => true));
    exit;
}

include '../../../components/header.php';
?>

<ul class="nav nav-tabs mb-4 border-bottom-0 gap-1">
    <li class="nav-item"><a class="nav-link fw-bold text-secondary px-4 py-2 border border-bottom-0" href="laporan_sirkulasi.php" style="border-radius: 8px 8px 0 0; border-color: transparent;">Sirkulasi Akademik</a></li>
    <li class="nav-item"><a class="nav-link active fw-bold text-astar border border-bottom-0 px-4 py-2" href="laporan_pengadaan.php" style="border-radius: 8px 8px 0 0; background-color: #fff;">Pengajuan Pengadaan</a></li>
    <li class="nav-item"><a class="nav-link fw-bold text-secondary px-4 py-2 border border-bottom-0" href="laporan_inventaris.php" style="border-radius: 8px 8px 0 0; border-color: transparent;">Status Inventaris</a></li>
</ul>

<div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-cart-plus-fill me-2"></i>Laporan Pengajuan Pengadaan</h5>
        <a href="../../dashboards/tendik_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
    <div class="card-body p-4 bg-light" style="border-radius: 0 0 15px 15px;">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4"><label class="form-label fw-bold text-astar">Dari Tanggal</label><input type="date" name="tgl_awal" class="form-control border-2" value="<?= htmlspecialchars($tgl_awal) ?>"></div>
            <div class="col-md-4"><label class="form-label fw-bold text-astar">Sampai Tanggal</label><input type="date" name="tgl_akhir" class="form-control border-2" value="<?= htmlspecialchars($tgl_akhir) ?>"></div>
            <div class="col-md-4">
                <label class="form-label fw-bold text-astar">Status Transaksi</label>
                <?php
                $opsi_pengadaan = [
                    '' => '-- Semua Status --',
                    'Draft' => 'Usulan Baru (Menunggu GA)',
                    'Disetujui GA' => 'Disetujui GA (Proses Supplier)',
                    'Harga Diinput Supplier' => 'Menunggu Acc Finance',
                    'Disetujui Finance' => 'Selesai (Dicairkan)',
                    'Ditolak' => 'Ditolak'
                ];
                echo buat_dropdown_astar('status', $opsi_pengadaan, $status_filter, false);
                ?>
            </div>
            <div class="col-12 d-flex justify-content-between mt-4">
                <div><button type="submit" class="btn btn-astar px-4 fw-bold"><i class="bi bi-funnel-fill"></i> Filter</button><a href="laporan_pengadaan.php" class="btn btn-light border fw-bold text-secondary px-3 ms-2">Reset</a></div>
                <div><button type="submit" name="export_pdf" value="1" class="btn btn-danger fw-bold px-4"><i class="bi bi-file-pdf-fill me-1"></i> Generate PDF</button></div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; border-left: 5px solid #1d4197 !important;">
            <p class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem;">TOTAL PENGAJUAN</p>
            <h4 class="fw-bold mb-0 text-dark"><?= count($data_report) ?> Request</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; border-left: 5px solid #198754 !important;">
            <p class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem;">SELESAI (SUKSES)</p>
            <h4 class="fw-bold mb-0 text-success"><?= count(array_filter($data_report, fn($r) => $r['statusPengadaan'] === 'Disetujui Finance')) ?> Transaksi</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; border-left: 5px solid #ffc107 !important;">
            <p class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem;">MENUNGGU PROSES</p>
            <h4 class="fw-bold mb-0 text-warning text-dark"><?= count(array_filter($data_report, fn($r) => in_array($r['statusPengadaan'], ['Draft', 'Disetujui GA', 'Harga Diinput Supplier']))) ?> Request</h4>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4">
        <div class="table-responsive mt-2">
            <?php if (count($data_report) > 0): ?>
                <table class="datatable-astar table table-hover border text-center align-middle">
                    <thead style="background-color: #f4f6f9; color: #1d4197;">
                        <tr>
                            <th width="5%">No.</th>
                            <th width="15%">ID Pengadaan</th>
                            <th width="25%">Nama Kebutuhan</th>
                            <th width="10%">Jumlah</th>
                            <th width="20%">Tgl Pengajuan</th>
                            <th width="25%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($data_report as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="text-primary fw-bold"><?= $row['idPengadaan'] ?></span></td>
                                <td class="text-start">
                                    <div class="fw-bold"><?= $row['namaKebutuhan'] ?></div><small class="text-muted"><?= $row['namaKategori'] ?></small>
                                </td>
                                <td><?= $row['jumlah'] ?> Unit</td>
                                <td><?= date('d M Y', strtotime($row['tanggalPengadaan'])) ?></td>
                                <td><span class="badge bg-<?= ($row['statusPengadaan'] == 'Disetujui Finance') ? 'success' : (($row['statusPengadaan'] == 'Ditolak') ? 'danger' : 'warning text-dark') ?> rounded-pill px-3 py-2"><?= $row['statusPengadaan'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-5"><i class="bi bi-file-earmark-x text-muted d-block mb-3" style="font-size: 4rem;"></i>
                    <h5 class="text-muted fw-bold">Data Tidak Ditemukan</h5>
                    <p class="text-muted">Tidak ada transaksi pengadaan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../../../components/footer.php'; ?>