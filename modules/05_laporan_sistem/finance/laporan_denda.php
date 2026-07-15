<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../../config/database.php';
require '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Finance') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Finance.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Finance') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah dinonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}
$role_login = $_SESSION['role'];
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';
$prodi_filter = $_GET['prodi'] ?? '';

// MENGAMBIL HISTORI DENDA DARI TRANSAKSI PENGEMBALIAN YANG BERBAYAR
$query_where = " WHERE s.sanksi_denda > 0 ";
if (!empty($tgl_awal)) $query_where .= " AND DATE(tpm.tanggalPengembalian) >= '$tgl_awal' ";
if (!empty($tgl_akhir)) $query_where .= " AND DATE(tpm.tanggalPengembalian) <= '$tgl_akhir' ";
if (!empty($prodi_filter)) $query_where .= " AND m.kodeProdi_mahasiswa = '$prodi_filter' ";

$sql = "SELECT tpm.idPengembalian, m.nimMahasiswa, m.namaMahasiswa, m.kodeProdi_mahasiswa, s.namaSanksi, s.sanksi_denda, tpm.tanggalPengembalian FROM transaksi_pengembalian tpm JOIN transaksi_peminjaman tp ON tpm.idPeminjaman = tp.idPeminjaman JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa JOIN sanksi s ON tpm.idSanksi = s.idSanksi $query_where ORDER BY tpm.tanggalPengembalian DESC";
$data_report = [];
$queryResult = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($queryResult)) {
    $data_report[] = $row;
}

$total_uang_denda = array_sum(array_column($data_report, 'sanksi_denda'));

// ======================= EKSPOR DOMPDF =======================
if (isset($_GET['export_pdf']) && $_GET['export_pdf'] == '1') {
    require '../../../vendor/autoload.php';
    $path_logo = __DIR__ . '/../../../assets/images/full_logo_blue.png';
    $img_tag = file_exists($path_logo) ? '<img src="data:image/png;base64,' . base64_encode(file_get_contents($path_logo)) . '" height="50">' : '<h2>ASTARrent</h2>';
    $html = '<!DOCTYPE html><html><head><style>body { font-family: "Helvetica", Arial, sans-serif; font-size: 11px; color: #333; } .kop { text-align: center; border-bottom: 3px double #1d4197; margin-bottom: 20px; padding-bottom: 10px; } .kop h3 { margin: 10px 0 5px 0; color: #1d4197; font-size: 18px; } table { width: 100%; border-collapse: collapse; margin-top: 10px; } th, td { border: 1px solid #777; padding: 6px; text-align: center; vertical-align: middle; } th { background-color: #e8f0fe; color: #1d4197; font-weight: bold; } thead { display: table-header-group; } tr { page-break-inside: avoid; } .rp { font-weight:bold; color:#1d4197; }</style></head><body>';
    $html .= '<div class="kop">' . $img_tag . '<h3>LAPORAN HISTORI PENERIMAAN DENDA (FINANCE)</h3><p>Periode: ' . (!empty($tgl_awal) ? date('d/m/Y', strtotime($tgl_awal)) : 'Awal') . ' s/d ' . (!empty($tgl_akhir) ? date('d/m/Y', strtotime($tgl_akhir)) : 'Akhir') . '</p><p>Dicetak Oleh: ' . htmlspecialchars($_SESSION['username'] ?? 'Finance') . ' | Tanggal Cetak: ' . date('d/m/Y H:i:s') . '</p></div>';
    $html .= '<table><thead><tr><th width="5%">No</th><th width="15%">ID Pengembalian</th><th width="25%">Mahasiswa (NIM)</th><th width="10%">Prodi</th><th width="25%">Jenis Sanksi</th><th width="20%">Biaya Denda (Rp)</th></tr></thead><tbody>';
    $no = 1;
    foreach ($data_report as $row) {
        $html .= '<tr><td>' . $no++ . '</td><td>' . $row['idPengembalian'] . '</td><td style="text-align:left;"><b>' . $row['namaMahasiswa'] . '</b><br>' . $row['nimMahasiswa'] . '</td><td>' . $row['kodeProdi_mahasiswa'] . '</td><td style="text-align:left;">' . $row['namaSanksi'] . '</td><td class="rp">Rp ' . number_format($row['sanksi_denda'], 0, ',', '.') . '</td></tr>';
    }
    // Baris Grand Total Bawah
    $html .= '<tr><td colspan="5" style="text-align:right; font-weight:bold; font-size:14px; padding:10px;">TOTAL PENERIMAAN KESELURUHAN : </td><td class="rp" style="font-size:14px; background-color:#e8f0fe;">Rp ' . number_format($total_uang_denda, 0, ',', '.') . '</td></tr>';
    $html .= '</tbody></table></body></html>';
    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Laporan_PenerimaanDenda_Finance.pdf", array("Attachment" => true));
    exit;
}

include '../../../components/header.php';
?>

<ul class="nav nav-tabs mb-4 border-bottom-0 gap-1">
    <li class="nav-item"><a class="nav-link active fw-bold text-astar border border-bottom-0 px-4 py-2" href="laporan_denda.php" style="border-radius: 8px 8px 0 0; background-color: #fff;">Penerimaan Denda Disiplin</a></li>
    <li class="nav-item"><a class="nav-link fw-bold text-secondary px-4 py-2 border border-bottom-0" href="laporan_pengeluaran.php" style="border-radius: 8px 8px 0 0; border-color: transparent;">Pengeluaran Pengadaan (E-Proc)</a></li>
</ul>

<div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-cash-stack me-2"></i>Laporan Histori Penerimaan Denda</h5>
        <a href="../../dashboards/finance_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
    <div class="card-body p-4 bg-light" style="border-radius: 0 0 15px 15px;">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4"><label class="form-label fw-bold text-astar">Dari Tanggal (Pengembalian)</label><input type="date" name="tgl_awal" class="form-control border-2" value="<?= htmlspecialchars($tgl_awal) ?>"></div>
            <div class="col-md-4"><label class="form-label fw-bold text-astar">Sampai Tanggal</label><input type="date" name="tgl_akhir" class="form-control border-2" value="<?= htmlspecialchars($tgl_akhir) ?>"></div>
            <div class="col-md-4">
                <label class="form-label fw-bold text-astar">Filter Prodi</label>
                <?php
                $opsi_prodi = [
                    '' => '-- Semua Prodi --',
                    'P3P' => 'P3P',
                    'TPM' => 'TPM',
                    'MIN' => 'MIN',
                    'MOT' => 'MOT',
                    'MEK' => 'MEK',
                    'TKB' => 'TKB',
                    'TAB' => 'TAB',
                    'TRL' => 'TRL',
                    'RPL' => 'RPL'
                ];
                echo buat_dropdown_astar('prodi', $opsi_prodi, $prodi_filter, false);
                ?>
            </div>
            <div class="col-12 d-flex justify-content-between mt-4">
                <div><button type="submit" class="btn btn-astar px-4 fw-bold"><i class="bi bi-funnel-fill"></i> Filter</button><a href="laporan_denda.php" class="btn btn-light border fw-bold text-secondary px-3 ms-2">Reset</a></div>
                <div><button type="submit" name="export_pdf" value="1" class="btn btn-danger fw-bold px-4"><i class="bi bi-file-pdf-fill me-1"></i> Generate PDF</button></div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; border-left: 5px solid #1d4197 !important;">
            <p class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem;">TOTAL TRANSAKSI DENDA MASUK</p>
            <h4 class="fw-bold mb-0 text-dark"><?= count($data_report) ?> Transaksi Sanksi</h4>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; border-left: 5px solid #198754 !important;">
            <p class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem;">TOTAL UANG DITERIMA</p>
            <h4 class="fw-bold mb-0 text-success">Rp <?= number_format($total_uang_denda, 0, ',', '.') ?></h4>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body p-4">
        <div class="table-responsive mt-2">
            <?php if (count($data_report) > 0): ?>
                <table class="datatable-astar table table-hover border  align-middle">
                    <thead style="background-color: #f4f6f9; color: #1d4197;">
                        <tr>
                            <th class="text-center" width="5%">No.</th>
                            <th width="15%">ID Pengembalian</th>
                            <th width="25%">Mahasiswa (NIM)</th>
                            <th class="text-center" width="10%">Prodi</th>
                            <th width="25%">Jenis Sanksi</th>
                            <th class="text-center" width="20%">Biaya Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($data_report as $row): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><span class="text-primary fw-bold"><?= $row['idPengembalian'] ?></span></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= $row['namaMahasiswa'] ?></div><small class="text-muted"><?= $row['nimMahasiswa'] ?></small>
                                </td>
                                <td class="text-center"><span class="badge bg-secondary"><?= $row['kodeProdi_mahasiswa'] ?></span></td>
                                <td><small><?= $row['namaSanksi'] ?></small></td>
                                <td class="text-center fw-bold text-danger fs-6">Rp <?= number_format($row['sanksi_denda'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-5"><i class="bi bi-file-earmark-x text-muted d-block mb-3" style="font-size: 4rem;"></i>
                    <h5 class="text-muted fw-bold">Data Tidak Ditemukan</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../../../components/footer.php'; ?>