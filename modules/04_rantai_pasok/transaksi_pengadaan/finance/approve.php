<?php
// --- FILE: modules/04_rantai_pasok/transaksi_pengadaan/finance/approve.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require '../../../../config/database.php';
require '../../../../config/functions.php';
require '../../../../vendor/autoload.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Finance') {
    header('Location: ../../../00_auth/login.php');
    exit;
}
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_pengadaan = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_finance = $_SESSION['id'];

// Ambil Detail Data
$q_detail = mysqli_query($koneksi, "
    SELECT tp.*, k.idKategori, k.namaKategori, k.statusKategori, u.namaUser as namaTendik 
    FROM transaksi_pengadaan tp 
    JOIN kategori k ON tp.idKategori = k.idKategori 
    JOIN users u ON tp.idTendik = u.idUser 
    WHERE tp.idPengadaan = '$id_pengadaan' AND tp.statusPengadaan = 'Harga Diinput Supplier'
");
$data = mysqli_fetch_assoc($q_detail);
if (!$data) {
    set_notifikasi('error', 'Data tidak ditemukan atau sudah dicairkan!');
    header('Location: index.php');
    exit;
}

$kebutuhan_jumlah = (int)$data['jumlah'];
$id_kategori_aset = $data['idKategori'];

// Ambil Data Vendor dari Database
$q_vendors = mysqli_query($koneksi, "SELECT * FROM detail_pengadaan_vendor WHERE idPengadaan = '$id_pengadaan' ORDER BY hargaSatuan ASC");
$vendors = [];
while ($row = mysqli_fetch_assoc($q_vendors)) {
    $vendors[] = $row;
}

// ==============================================================================
// PROSES PENGESAHAN OLEH FINANCE
// ==============================================================================
if (isset($_POST['submit_cairkan'])) {
    $keputusan = $_POST['keputusan'];

    if ($keputusan === 'tolak') {
        $alasan_tolak = mysqli_real_escape_string($koneksi, trim($_POST['alasan_tolak']));

        mysqli_query($koneksi, "UPDATE transaksi_pengadaan 
                                SET statusPengadaan = 'Ditolak', 
                                    idFinance = '$id_finance',
                                    alasanPenolakan_pengadaan = '$alasan_tolak' 
                                WHERE idPengadaan = '$id_pengadaan'");

        // Tolak semua vendor
        mysqli_query($koneksi, "UPDATE detail_pengadaan_vendor SET statusPilihan = 'Ditolak' WHERE idPengadaan = '$id_pengadaan'");

        if ($data['statusKategori'] === 'Draft') {
            mysqli_query($koneksi, "UPDATE kategori SET statusKategori = 'Nonaktif' WHERE idKategori = '$id_kategori_aset'");
        }

        buat_pdf_pengajuan($id_pengadaan);
        buat_pdf_penawaran($id_pengadaan);

        set_notifikasi('success', 'Pengadaan berhasil ditolak oleh Finance.');
    } elseif ($keputusan === 'setuju') {

        $vendor_terpilih = isset($_POST['vendor_check']) ? $_POST['vendor_check'] : [];
        $tgl_sekarang = date('Y-m-d H:i:s');

        $total_dibeli_server = 0;
        $subtotal_uang = 0;

        foreach ($vendor_terpilih as $id_detail_vendor) {
            $q_cek_vd = mysqli_query($koneksi, "SELECT stok, hargaSatuan, estimasiTiba FROM detail_pengadaan_vendor WHERE idDetail = '$id_detail_vendor'");
            $vd = mysqli_fetch_assoc($q_cek_vd);

            $total_dibeli_server += (int)$vd['stok'];
            $subtotal_uang += ((int)$vd['stok'] * (int)$vd['hargaSatuan']);

            $estimasi = (int)$vd['estimasiTiba'];
            $tgl_jatuh_tempo = date('Y-m-d H:i:s', strtotime($tgl_sekarang . " + $estimasi days"));

            mysqli_query($koneksi, "UPDATE detail_pengadaan_vendor 
                                    SET statusPilihan = 'Terpilih', tanggalJatuhTempo = '$tgl_jatuh_tempo' 
                                    WHERE idDetail = '$id_detail_vendor'");
        }

        if ($total_dibeli_server < $kebutuhan_jumlah) {
            set_notifikasi('error', 'Total stok toko yang dipilih tidak memenuhi syarat request minimal!');
            header("Location: approve.php?id=$id_pengadaan");
            exit;
        }

        // =============================================================
        // PERHITUNGAN PPN 12% DI SERVER (BACKEND) SEBELUM DISIMPAN
        // =============================================================
        $ppn = $subtotal_uang * 0.12;
        $grand_total_uang = $subtotal_uang + $ppn;

        // Tolak vendor yang tidak dicentang
        mysqli_query($koneksi, "UPDATE detail_pengadaan_vendor SET statusPilihan = 'Ditolak' WHERE idPengadaan = '$id_pengadaan' AND statusPilihan = 'Menunggu'");

        // UBAH STATUS TRANSAKSI & SIMPAN GRAND TOTAL BIAYA (+PPN)
        mysqli_query($koneksi, "UPDATE transaksi_pengadaan 
                                SET statusPengadaan = 'Disetujui Finance', idFinance = '$id_finance', totalBiaya = $grand_total_uang
                                WHERE idPengadaan = '$id_pengadaan'");

        if ($data['statusKategori'] === 'Draft') {
            mysqli_query($koneksi, "UPDATE kategori SET statusKategori = 'Aktif' WHERE idKategori = '$id_kategori_aset'");
        }

        buat_pdf_pengajuan($id_pengadaan);
        buat_pdf_penawaran($id_pengadaan);

        $total_rp_cair = "Rp " . number_format($grand_total_uang, 0, ',', '.');
        set_notifikasi('success', "Sukses! Dana sebesar $total_rp_cair (Termasuk PPN 12%) dicairkan. Aset akan tiba sesuai jadwal estimasi.");
    }

    header('Location: index.php');
    exit;
}

include '../../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-11">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2"></i>Persetujuan Finance (Pencairan Dana)</h5>
            </div>
            <div class="card-body p-4">

                <div class="bg-light p-4 rounded border mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h6 class="fw-bold text-astar mb-2"><i class="bi bi-info-circle-fill me-2"></i>Kebutuhan Aset:</h6>
                            <h5 class="fw-bold text-dark mb-1"><?= $data['namaKategori'] ?> - <?= $data['namaKebutuhan'] ?></h5>
                            <p class="text-muted mb-0">Total yang dibutuhkan minimal: <span class="fw-bold text-danger fs-5"><?= $kebutuhan_jumlah ?></span> <span class="fw-bold text-danger">Unit</span></p>
                        </div>
                        <div class="col-md-5 text-md-end mt-3 mt-md-0">
                            <a href="../../../../uploads/dokumen_pengajuan/<?= $data['dokumen_pengajuan'] ?>?v=<?= time(); ?>" target="_blank" class="btn btn-outline-danger fw-bold shadow-sm mb-2 w-100">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Baca Proposal Pengajuan
                            </a>
                            <a href="../../../../uploads/dokumen_penawaran/<?= $data['dokumen_penawaran'] ?>?v=<?= time(); ?>" target="_blank" class="btn btn-outline-danger fw-bold shadow-sm w-100">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Baca Perbandingan Vendor
                            </a>
                        </div>
                    </div>
                </div>

                <form action="" method="POST" id="form_finance">

                    <div class="mb-4 p-4 rounded" style="background-color: #f4f6f9; border: 2px dashed #c2d5ff;">
                        <label class="form-label fw-bold text-astar mb-3"><i class="bi bi-hammer me-2"></i>Keputusan Finance <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="keputusan" id="aksi_setuju" value="setuju" checked onchange="toggleKeputusan()">
                                <label class="btn btn-outline-astar w-100 py-3 fw-bold text-start" for="aksi_setuju" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-check-circle-fill fs-4 d-block mb-1"></i> Cairkan Dana (Pilih Vendor)
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="keputusan" id="aksi_tolak" value="tolak" onchange="toggleKeputusan()">
                                <label class="btn btn-outline-danger w-100 py-3 fw-bold text-start" for="aksi_tolak" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-x-circle-fill fs-4 d-block mb-1"></i> Tolak Pengadaan
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="panel_vendor" class="mb-4">
                        <h6 class="fw-bold text-astar mb-3"><i class="bi bi-shop me-2"></i>Pilih Penawaran Vendor (Memborong Seluruh Stok)</h6>
                        <div class="table-responsive border rounded">
                            <table class="table table-hover align-middle text-center mb-0">
                                <thead style="background-color: #e8f0fe; color: #1d4197;">
                                    <tr>
                                        <th width="5%"><i class="bi bi-check2-square"></i></th>
                                        <th class="text-start">Nama Toko</th>
                                        <th>Harga Satuan</th>
                                        <th width="15%">Stok Tersedia</th>
                                        <th>Estimasi Tiba</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vendors as $v):
                                        $total_harga = $v['hargaSatuan'] * $v['stok'];
                                    ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="vendor_check[]" value="<?= $v['idDetail'] ?>" class="form-check-input chk-vendor" style="transform: scale(1.5);" data-stok="<?= $v['stok'] ?>" data-harga="<?= $total_harga ?>" onchange="updateTotal()">
                                            </td>
                                            <td class="text-start fw-bold text-secondary"><?= $v['namaVendor'] ?></td>
                                            <td class="text-muted">Rp <?= number_format($v['hargaSatuan'], 0, ',', '.') ?></td>
                                            <td><span class="badge bg-secondary" style="font-size: 14px;"><?= $v['stok'] ?> Unit</span></td>
                                            <td class="text-muted fw-bold"><?= $v['estimasiTiba'] ?> Hari</td>
                                            <td class="fw-bold text-secondary">Rp <?= number_format($total_harga, 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3 mb-2">
                            <div class="bg-white p-4 rounded border shadow-sm" style="width: 100%; max-width: 566px;">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary fw-bold">Subtotal (<span id="display_total_unit">0</span> Unit)</span>
                                    <span class="text-secondary fw-bold" id="display_subtotal_rp">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-secondary fw-bold">PPN (12%)</span>
                                    <span class="text-secondary fw-bold" id="display_ppn_rp">Rp 0</span>
                                </div>
                                <hr class="border-secondary opacity-25 my-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-danger fw-bold fs-5 mb-0">TOTAL</span>
                                    <span class="text-danger fw-bold fs-5 mb-0" id="display_grand_total_rp">Rp 0</span>
                                </div>
                            </div>
                        </div>

                        <small id="peringatan_qty" class="text-danger fw-bold mt-2" style="display: block;">
                            <i class="bi bi-exclamation-triangle-fill"></i> Total unit toko yang dipilih belum memenuhi target kebutuhan minimal (<?= $kebutuhan_jumlah ?> Unit).
                        </small>
                    </div>

                    <div id="panel_tolak" class="mb-4 p-4 rounded border" style="display: none; background-color: #fff3f3; border-color: #f5c6cb !important;">
                        <label class="form-label fw-bold text-danger"><i class="bi bi-chat-left-text-fill me-1"></i> Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_tolak" id="input_alasan_tolak" class="form-control border-danger" rows="3" placeholder="Jelaskan alasan pengadaan ini ditolak agar Tendik mengetahuinya..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4 border-top pt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" id="btn_submit" name="submit_cairkan" class="btn btn-secondary px-5 fw-bold shadow-sm" disabled>
                            Cairkan Dana & Pesan <i class="bi bi-wallet2 ms-1"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?= script_dinamis_finance_approve($kebutuhan_jumlah); ?>

<?php include '../../../../components/footer.php'; ?>