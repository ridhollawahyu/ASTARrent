<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';
require '../../../../vendor/autoload.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Supplier') {
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_pengadaan = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_supplier = $_SESSION['id'];

// Ambil Detail Data
$q_detail = mysqli_query($koneksi, "
    SELECT tp.*, k.namaKategori, u.namaUser as namaTendik 
    FROM transaksi_pengadaan tp 
    JOIN kategori k ON tp.idKategori = k.idKategori 
    JOIN users u ON tp.idTendik = u.idUser 
    WHERE tp.idPengadaan = '$id_pengadaan' AND tp.statusPengadaan = 'Disetujui GA'
");
$data = mysqli_fetch_assoc($q_detail);
if (!$data) {
    set_notifikasi('error', 'Tugas tidak ditemukan atau sudah selesai dikerjakan!');
    header('Location: index.php');
    exit;
}

$kebutuhan_jumlah = (int)$data['jumlah']; // Target yang harus dipenuhi

if (isset($_POST['submit_penawaran'])) {
    $nama_toko = $_POST['nama_toko'];
    $spek_toko = $_POST['spek_toko'];
    $stok_toko = $_POST['stok_toko'];
    $harga_toko = $_POST['harga_toko'];

    // ====================================================================
    // VALIDASI BACKEND: TOTAL STOK HARUS >= KEBUTUHAN
    // ====================================================================
    $total_stok_diinput = 0;
    foreach ($stok_toko as $stok) {
        $total_stok_diinput += (int)$stok;
    }

    if ($total_stok_diinput < $kebutuhan_jumlah) {
        set_notifikasi('error', "Total stok gabungan ($total_stok_diinput Unit) kurang dari target kebutuhan ($kebutuhan_jumlah Unit)! Cari vendor lain untuk menggenapinya.");
        echo "<script>window.location='input_harga.php?id=$id_pengadaan';</script>";
        exit;
    }

    $format_tgl_file = date('Ymd');
    $nama_file_pdf = "Penawaran_{$id_pengadaan}_{$format_tgl_file}.pdf";
    $folder_simpan = "../../../../uploads/dokumen_penawaran/";
    if (!is_dir($folder_simpan)) mkdir($folder_simpan, 0777, true);

    // RAKIT ARRAY JSON
    $array_vendor = [];
    for ($i = 0; $i < count($nama_toko); $i++) {
        if (!empty(trim($nama_toko[$i]))) {
            $array_vendor[] = [
                'toko' => mysqli_real_escape_string($koneksi, trim($nama_toko[$i])),
                'spek' => mysqli_real_escape_string($koneksi, trim($spek_toko[$i])),
                'stok' => (int)$stok_toko[$i],
                'harga' => (int)$harga_toko[$i]
            ];
        }
    }

    $json_vendor = json_encode($array_vendor);
    $alasan_baru = $data['alasanKebutuhan'] . "|||VENDOR|||" . $json_vendor;
    $alasan_aman = mysqli_real_escape_string($koneksi, $alasan_baru);

    mysqli_query($koneksi, "UPDATE transaksi_pengadaan 
                            SET dokumen_penawaran = '$nama_file_pdf', 
                                statusPengadaan = 'Harga Diinput Supplier',
                                alasanKebutuhan = '$alasan_aman' 
                            WHERE idPengadaan = '$id_pengadaan'");

    mysqli_query($koneksi, "UPDATE supplier SET jumlahTugas_aktif = GREATEST(0, jumlahTugas_aktif - 1) WHERE idSupplier = '$id_supplier'");

    // GENERATE PDF
    buat_pdf_penawaran($id_pengadaan);

    set_notifikasi('success', "Tugas Selesai! PDF Penawaran berhasil dibuat.");
    echo "<script>window.location='index.php';</script>";
    exit;
}

include '../../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-11">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Input Perbandingan Harga Vendor</h5>
            </div>
            <div class="card-body p-4">

                <div class="bg-light p-4 rounded border mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="fw-bold text-astar mb-2"><i class="bi bi-info-circle-fill me-2"></i>Kebutuhan Aset:</h6>
                            <h5 class="fw-bold text-dark mb-1"><?= $data['namaKategori'] ?> - <?= $data['namaKebutuhan'] ?></h5>
                            <p class="text-muted mb-0">Total yang dibutuhkan: <span class="fw-bold text-danger fs-5" id="target_kebutuhan"><?= $kebutuhan_jumlah ?></span> <span class="fw-bold text-danger">Unit</span></p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <a href="../../../../uploads/dokumen_pengajuan/<?= $data['dokumen_pengajuan'] ?>?v=<?= time(); ?>" target="_blank" class="btn btn-outline-danger fw-bold shadow-sm">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Proposal Tendik
                            </a>
                        </div>
                    </div>
                </div>

                <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;">
                    <i class="bi bi-magic me-2"></i> <strong>Sistem Auto-Generate:</strong> Masukkan minimal 2 vendor perbandingan. <b>Pastikan Total Stok memenuhi jumlah kebutuhan!</b>
                </div>

                <form action="" method="POST">
                    <h6 class="fw-bold text-astar mb-3"><i class="bi bi-shop me-2"></i>Daftar Vendor Eksternal (Form Dinamis)</h6>

                    <div id="vendor_container">
                        <!-- Baris 1 Default -->
                        <div class="row g-2 mb-3 vendor-row align-items-start">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" style="font-size: 13px;">Nama Toko/Vendor</label>
                                <input type="text" name="nama_toko[]" class="form-control" style="border: 2px solid #e0e6ed;" required placeholder="Misal: Toko ABC">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" style="font-size: 13px;">Keterangan</label>
                                <input type="text" name="spek_toko[]" class="form-control" style="border: 2px solid #e0e6ed;" required placeholder="Misal: Garansi Resmi">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label fw-bold text-danger" style="font-size: 13px;">Stok</label>
                                <input type="number" name="stok_toko[]" class="form-control fw-bold border-danger input-stok" required min="0" placeholder="0" oninput="cekTotalStok()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" style="font-size: 13px;">Harga Satuan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">Rp</span>
                                    <input type="number" name="harga_toko[]" class="form-control fw-bold" style="border: 2px solid #e0e6ed;" required min="1000" placeholder="1000000">
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end" style="height: 65px;">
                                <button type="button" class="btn btn-outline-danger w-100 fw-bold" onclick="hapusBaris(this)" title="Hapus Baris"><i class="bi bi-x-lg"></i></button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success btn-sm fw-bold px-4 py-2 shadow-sm" onclick="tambahBaris()"><i class="bi bi-plus-circle-fill me-2"></i>Tambah Toko Lain</button>

                    <!-- Peringatan JS Realtime -->
                    <div id="peringatan_stok" class="alert alert-danger fw-bold mt-4" style="display: block;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Total stok yang Anda input belum memenuhi target kebutuhan (<span id="teks_total_stok">0</span> / <?= $kebutuhan_jumlah ?> Unit).
                    </div>

                    <div class="d-flex justify-content-between mt-4 border-top pt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Kembali</a>
                        <button type="submit" id="btn_submit" name="submit_penawaran" class="btn btn-astar px-5 fw-bold shadow-sm" disabled>
                            <i class="bi bi-printer me-2"></i> Generate PDF Penawaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= script_dinamis_supplier_input($kebutuhan_jumlah); ?>

<?php include '../../../../components/footer.php'; ?>