<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../../config/database.php';
require '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Staff GA') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah dinonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}
$role_login = $_SESSION['role'];
$tipe_filter = $_GET['tipe'] ?? '';
$sedia_filter = $_GET['sedia'] ?? '';

// LOGIKA UNION UNTUK MENGGABUNGKAN KOMPONEN DAN FASILITAS NON-AKADEMIK
$query_where = " WHERE 1=1 ";
if (!empty($sedia_filter)) {
    $query_where .= " AND sedia = '$sedia_filter' ";
}
if (!empty($tipe_filter)) {
    $query_where .= " AND tipe = '$tipe_filter' ";
}

$sql = "SELECT * FROM (
            SELECT idFasilitas AS id_brg, namaFasilitas AS nama, 'Fasilitas Non-Akademik' AS tipe, kondisiFasilitas AS kondisi, ketersediaanFasilitas AS sedia 
            FROM fasilitas WHERE tipeFasilitas = 'Non-Akademik'
            UNION ALL 
            SELECT idKomponen AS id_brg, namaKomponen AS nama, 'Komponen (Pembongkaran)' AS tipe, kondisiKomponen AS kondisi, statusKomponen AS sedia
        ) AS gabungan $query_where ORDER BY id_brg ASC";

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
    $html = '<!DOCTYPE html><html><head><style>body { font-family: "Helvetica", Arial, sans-serif; font-size: 11px; color: #333; } .kop { text-align: center; border-bottom: 3px double #1d4197; margin-bottom: 20px; padding-bottom: 10px; } .kop h3 { margin: 10px 0 5px 0; color: #1d4197; font-size: 18px; } table { width: 100%; border-collapse: collapse; margin-top: 10px; } th, td { border: 1px solid #777; padding: 6px; text-align: center; vertical-align: middle; } th { background-color: #e8f0fe; color: #1d4197; font-weight: bold; } thead { display: table-header-group; } tr { page-break-inside: avoid; }</style></head><body>';
    $html .= '<div class="kop">' . $img_tag . '<h3>LAPORAN STATUS INVENTARIS NON-AKADEMIK & KOMPONEN</h3><p>Dicetak Oleh: ' . htmlspecialchars($_SESSION['username'] ?? 'Staff GA') . ' | Tanggal Cetak: ' . date('d/m/Y H:i:s') . '</p></div>';
    $html .= '<table><thead><tr><th width="5%">No</th><th width="15%">ID Barang</th><th width="35%">Nama Barang/Fasilitas</th><th width="15%">Tipe</th><th width="15%">Kondisi Fisik</th><th width="15%">Ketersediaan</th></tr></thead><tbody>';
    $no = 1;
    foreach ($data_report as $row) {
        $html .= '<tr><td>' . $no++ . '</td><td>' . $row['id_brg'] . '</td><td style="text-align:left;">' . $row['nama'] . '</td><td>' . $row['tipe'] . '</td><td>' . $row['kondisi'] . '</td><td>' . $row['sedia'] . '</td></tr>';
    }
    $html .= '</tbody></table></body></html>';
    $options = new \Dompdf\Options();
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("Laporan_Inventaris_StaffGA.pdf", array("Attachment" => true));
    exit;
}
include '../../../components/header.php';
?>

<ul class="nav nav-tabs mb-4 border-bottom-0 gap-1">
    <li class="nav-item"><a class="nav-link fw-bold text-secondary px-4 py-2 border border-bottom-0" href="laporan_sirkulasi.php" style="border-radius: 8px 8px 0 0; border-color: transparent;">Sirkulasi Non-Akademik</a></li>
    <li class="nav-item"><a class="nav-link fw-bold text-secondary px-4 py-2 border border-bottom-0" href="laporan_reparasi.php" style="border-radius: 8px 8px 0 0; border-color: transparent;">Eksekusi Reparasi</a></li>
    <li class="nav-item"><a class="nav-link active fw-bold text-astar border border-bottom-0 px-4 py-2" href="laporan_inventaris.php" style="border-radius: 8px 8px 0 0; background-color: #fff;">Status Inventaris</a></li>
</ul>

<div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
    <div class="card-header" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-box-seam-fill me-2"></i>Laporan Status Inventaris & Komponen</h5>
    </div>
    <div class="card-body p-4 bg-light" style="border-radius: 0 0 15px 15px;">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-bold text-astar">Tipe Inventaris</label>
                <?php
                $opsi_tipe = [
                    '' => '-- Semua Tipe --',
                    'Aset' => 'Aset Elektronik',
                    'Fasilitas Akademik' => 'Fasilitas Akademik'
                ];
                echo buat_dropdown_astar('tipe', $opsi_tipe, $tipe_filter, false);
                ?>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold text-astar">Status Ketersediaan</label>
                <?php
                $opsi_sedia = [
                    '' => '-- Semua Status --',
                    'Tersedia' => 'Tersedia di Gudang',
                    'Dipinjam' => 'Sedang Dipinjam',
                    'Sedang Diperbaiki' => 'Sedang Direparasi'
                ];
                echo buat_dropdown_astar('sedia', $opsi_sedia, $sedia_filter, false);
                ?>
            </div>
            <div class="col-md-2 d-flex align-items-end justify-content-end gap-2">
                <button type="submit" class="btn btn-astar px-3 fw-bold w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
            </div>
            <div class="col-12 mt-3 text-end">
                <button type="submit" name="export_pdf" value="1" class="btn btn-danger fw-bold px-4"><i class="bi bi-file-pdf-fill me-1"></i> Generate PDF</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; border-left: 5px solid #1d4197 !important;">
            <p class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem;">TOTAL INVENTARIS</p>
            <h4 class="fw-bold mb-0 text-dark"><?= count($data_report) ?> Item</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; border-left: 5px solid #198754 !important;">
            <p class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem;">TERSEDIA AMAN</p>
            <h4 class="fw-bold mb-0 text-success"><?= count(array_filter($data_report, fn($r) => $r['sedia'] === 'Tersedia')) ?> Item</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 12px; border-left: 5px solid #dc3545 !important;">
            <p class="text-muted mb-1 fw-semibold" style="font-size: 0.75rem;">KOMPONEN TERPAKAI</p>
            <h4 class="fw-bold mb-0 text-danger"><?= count(array_filter($data_report, fn($r) => $r['sedia'] === 'Sudah Dipakai')) ?> Item</h4>
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
                            <th width="15%">ID Barang</th>
                            <th width="35%">Nama Barang/Fasilitas</th>
                            <th width="15%">Tipe</th>
                            <th width="15%">Kondisi Fisik</th>
                            <th width="15%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($data_report as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="text-primary fw-bold"><?= $row['id_brg'] ?></span></td>
                                <td class="text-start fw-bold text-dark"><?= $row['nama'] ?></td>
                                <td><span class="badge bg-secondary"><?= $row['tipe'] ?></span></td>
                                <td><?= ($row['kondisi'] == 'Normal' || $row['kondisi'] == 'Sangat Baik' || $row['kondisi'] == 'Layak Pakai') ? '<span class="text-success fw-bold">' . $row['kondisi'] . '</span>' : '<span class="text-danger fw-bold">' . $row['kondisi'] . '</span>' ?></td>
                                <td><span class="badge bg-<?= ($row['sedia'] == 'Tersedia') ? 'success' : (($row['sedia'] == 'Dipinjam') ? 'primary' : 'warning text-dark') ?> rounded-pill px-3 py-2"><?= $row['sedia'] ?></span></td>
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