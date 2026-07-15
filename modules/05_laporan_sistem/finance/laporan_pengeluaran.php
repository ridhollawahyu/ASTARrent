<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../../config/database.php';
require '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Finance') {
    header('Location: ../../../00_auth/login.php');
    exit;
}
$role_login = $_SESSION['role'];
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// FINANCE HANYA MELIHAT YANG SUDAH DICAIRKAN OLEH MEREKA (Status 'Disetujui Finance')
$query_where = " WHERE tp.statusPengadaan = 'Disetujui Finance' ";
if (!empty($tgl_awal)) $query_where .= " AND DATE(tp.tanggalPengadaan) >= '$tgl_awal' ";
if (!empty($tgl_akhir)) $query_where .= " AND DATE(tp.tanggalPengadaan) <= '$tgl_akhir' ";

// KITA JOIN DENGAN VENDOR TERPILIH UNTUK MELIHAT SIAPA YANG DAPAT UANGNYA
$sql = "SELECT tp.*, k.namaKategori, s.namaSupplier, u.namaUser as namaTendik, dpv.namaVendor, dpv.stok, dpv.hargaSatuan FROM transaksi_pengadaan tp JOIN kategori k ON tp.idKategori = k.idKategori LEFT JOIN users u ON tp.idTendik = u.idUser LEFT JOIN supplier s ON tp.idSupplier = s.idSupplier LEFT JOIN detail_pengadaan_vendor dpv ON tp.idPengadaan = dpv.idPengadaan AND dpv.statusPilihan = 'Terpilih' $query_where ORDER BY tp.tanggalPengadaan DESC";

$data_report = [];
$queryResult = mysqli_query($koneksi, $sql);
while ($row = mysqli_fetch_assoc($queryResult)) {
    $data_report[] = $row;
}

$total_pengeluaran = array_sum(array_column($data_report, 'totalBiaya')); // SUDAH TERMASUK PPN SAAT ACC

// ======================= EKSPOR DOMPDF (MENGGUNAKAN LANDSCAPE UNTUK FINANCE) =======================
if (isset($_GET['export_pdf']) && $_GET['export_pdf'] == '1') {
    require '../../../vendor/autoload.php';
    $path_logo = __DIR__ . '/../../../assets/images/full_logo_blue.png';
    $img_tag = file_exists($path_logo) ? '<img src="data:image/png;base64,' . base64_encode(file_get_contents($path_logo)) . '" height="50">' : '<h2>ASTARrent</h2>';
    $html = '<!DOCTYPE html><html><head><style>body { font-family: "Helvetica", Arial, sans-serif; font-size: 11px; color: #333; } .kop { text-align: center; border-bottom: 3px double #1d4197; margin-bottom: 20px; padding-bottom: 10px; } .kop h3 { margin: 10px 0 5px 0; color: #1d4197; font-size: 18px; } table { width: 100%; border-collapse: collapse; margin-top: 10px; } th, td { border: 1px solid #777; padding: 6px; text-align: center; vertical-align: middle; } th { background-color: #e8f0fe; color: #1d4197; font-weight: bold; } thead { display: table-header-group; } tr { page-break-inside: avoid; } .rp { font-weight:bold; color:#1d4197; }</style></head><body>';
    $html .= '<div class="kop">' . $img_tag . '<h3>LAPORAN PENGELUARAN PENGADAAN ASET KAMPUS</h3><p>Periode Cair: ' . (!empty($tgl_awal) ? date('d/m/Y', strtotime($tgl_awal)) : 'Awal') . ' s/d ' . (!empty($tgl_akhir) ? date('d/m/Y', strtotime($tgl_akhir)) : 'Akhir') . '</p><p>Dicetak Oleh: ' . htmlspecialchars($_SESSION['username'] ?? 'Finance') . ' | Tanggal Cetak: ' . date('d/m/Y H:i:s') . '</p></div>';

    $html .= '<table><thead><tr><th width="5%">No</th><th width="15%">ID Pengadaan</th><th width="20%">Nama Aset (Kategori)</th><th width="15%">Pemenang Vendor (Toko)</th><th width="10%">Unit Dipesan</th><th width="15%">Subtotal Belanja</th><th width="20%">Grand Total Cair (+PPN 12%)</th></tr></thead><tbody>';
    $no = 1;
    foreach ($data_report as $row) {
        $subtotal = $row['stok'] * $row['hargaSatuan'];
        $html .= '<tr><td>' . $no++ . '</td><td>' . $row['idPengadaan'] . '</td><td style="text-align:left;"><b>' . $row['namaKebutuhan'] . '</b><br>' . $row['namaKategori'] . '</td><td>' . ($row['namaVendor'] ?? 'N/A') . '</td><td>' . $row['jumlah'] . ' Unit</td><td class="rp">Rp ' . number_format($subtotal, 0, ',', '.') . '</td><td class="rp" style="color:#198754;">Rp ' . number_format($row['totalBiaya'], 0, ',', '.') . '</td></tr>';
    }
    // Baris Grand Total Bawah
    $html .= '<tr><td colspan="6" style="text-align:right; font-weight:bold; font-size:14px; padding:10px;">TOTAL ARUS KAS KELUAR : </td><td class="rp" style="font-size:14px; background-color:#e8f0fe; color:#198754;">Rp ' . number_format($total_pengeluaran, 0, ',', '.') . '</td></tr>';
    $html .= '</tbody></table></body></html>';

    // Gunakan Kertas Landscape agar tabel pengeluaran luas
    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("Laporan_Pengeluaran_Finance.pdf", array("Attachment" => true));
    exit;
}

include '../../../components/header.php';
?>

<ul class="nav nav-tabs mb-4 border-bottom-0 gap-1">
    <li class="nav-item"><a class="nav-link fw-bold text-secondary px-4 py-2 border border-bottom-0" href="laporan_denda.php" style="border-radius: 8px 8px 0 0; border-color: transparent;">Penerimaan Denda Disiplin</a></li>
    <li class="nav-item"><a class="nav-link active fw-bold text-astar border border-bottom-0 px-4 py-2" href="laporan_pengeluaran.php" style="border-radius: 8px 8px 0 0; background-color: #fff;">Pengeluaran Pengadaan (E-Proc)</a></li>
</ul>

<div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-wallet2 me-2"></i>Laporan Pengeluaran Anggaran Pengadaan</h5>
        <a href="../../dashboards/finance_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
    <div class="card-body p-4 bg-light" style="border-radius: 0 0 15px 15px;">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-5"><label class="form-label fw-bold text-astar">Dari Tanggal (Pengajuan)</label><input type="date" name="tgl_awal" class="form-control border-2" value="<?= htmlspecialchars($tgl_awal) ?>"></div>
            <div class="col-md-5"><label class="form-label fw-bold text-astar">Sampai Tanggal</label><input type="date" name="tgl_akhir" class="form-control border-2" value="<?= htmlspecialchars($tgl_akhir) ?>"></div>
            <div class="col-md-2 d-flex justify-content-between align-items-end mt-4">
                <button type="submit" class="btn btn-astar px-4 fw-bold w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
            </div>
            <div class="col-12 mt-3 text-end">
                <a href="laporan_pengeluaran.php" class="btn btn-light border fw-bold text-secondary px-3 ms-2">Reset</a>
                <button type="submit" name="export_pdf" value="1" class="btn btn-danger fw-bold px-4 ms-2"><i class="bi bi-file-pdf-fill me-1"></i> Cetak Laporan (Landscape)</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; border-left: 5px solid #1d4197 !important;">
            <p class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem;">TRANSAKSI DICAIRKAN</p>
            <h4 class="fw-bold mb-0 text-dark"><?= count($data_report) ?> Transaksi</h4>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; border-left: 5px solid #dc3545 !important;">
            <p class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem;">TOTAL KAS KELUAR (INCL PPN 12%)</p>
            <h4 class="fw-bold mb-0 text-danger">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h4>
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
                            <th width="5%">No.</th>
                            <th width="15%">ID Pengadaan</th>
                            <th width="25%">Nama Kebutuhan</th>
                            <th width="15%">Vendor Menang</th>
                            <th width="15%">Tgl Selesai</th>
                            <th width="25%">Grand Total Cair</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($data_report as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="text-primary fw-bold"><?= $row['idPengadaan'] ?></span></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= $row['namaKebutuhan'] ?></div><small class="text-muted"><?= $row['jumlah'] ?> Unit Diproses</small>
                                </td>
                                <td><span class="badge bg-secondary"><?= $row['namaVendor'] ?? 'Toko N/A' ?></span></td>
                                <td><?= date('d M Y', strtotime($row['tanggalPengadaan'])) ?></td>
                                <td class="text-success fw-bold fs-5">Rp <?= number_format($row['totalBiaya'], 0, ',', '.') ?></td>
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