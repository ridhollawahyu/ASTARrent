<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Kepala GA') {
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_pengadaan = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_kepala_ga = $_SESSION['id'];

// Ambil Detail Data
$q_detail = mysqli_query($koneksi, "
    SELECT tp.*, k.namaKategori, k.statusKategori, u.namaUser, u.kodeDepartemen 
    FROM transaksi_pengadaan tp 
    JOIN kategori k ON tp.idKategori = k.idKategori 
    JOIN users u ON tp.idTendik = u.idUser 
    WHERE tp.idPengadaan = '$id_pengadaan' AND tp.statusPengadaan = 'Draft'
");

$data = mysqli_fetch_assoc($q_detail);
if (!$data) {
    set_notifikasi('error', 'Data tidak ditemukan atau sudah divalidasi!');
    header('Location: index.php');
    exit;
}

// PROSES SUBMIT
if (isset($_POST['proses_validasi'])) {
    $keputusan = $_POST['keputusan']; // 'setuju' atau 'tolak'

    if ($keputusan === 'tolak') {
        // TANGKAP ALASAN TOLAK
        $alasan_tolak = mysqli_real_escape_string($koneksi, trim($_POST['alasan_tolak']));

        // 1. UPDATE TRANSAKSI MENJADI DITOLAK BESERTA ALASANNYA
        mysqli_query($koneksi, "UPDATE transaksi_pengadaan 
                                SET statusPengadaan = 'Ditolak', 
                                    idKepalaGA = '$id_kepala_ga',
                                    alasanPenolakan_pengadaan = '$alasan_tolak' 
                                WHERE idPengadaan = '$id_pengadaan'");

        // 2. CEK KATEGORI DRAFT (Jika ada, langsung Nonaktifkan agar tidak jadi sampah)
        if ($data['statusKategori'] === 'Draft') {
            $id_kat_draft = $data['idKategori'];
            mysqli_query($koneksi, "UPDATE kategori SET statusKategori = 'Nonaktif' WHERE idKategori = '$id_kat_draft'");
        }

        set_notifikasi('success', 'Pengajuan berhasil ditolak.');
    } elseif ($keputusan === 'setuju') {
        $id_supplier = mysqli_real_escape_string($koneksi, $_POST['id_supplier']);
        if (empty($id_supplier)) {
            set_notifikasi('error', 'Supplier wajib dipilih!');
            echo "<script>window.location='approve.php?id=$id_pengadaan';</script>";
            exit;
        }

        // 1. UPDATE DB
        mysqli_query($koneksi, "UPDATE transaksi_pengadaan 
                                SET statusPengadaan = 'Disetujui GA', 
                                    idKepalaGA = '$id_kepala_ga', 
                                    idSupplier = '$id_supplier' 
                                WHERE idPengadaan = '$id_pengadaan'");

        // 2. TAMBAH TUGAS SUPPLIER
        mysqli_query($koneksi, "UPDATE supplier SET jumlahTugas_aktif = jumlahTugas_aktif + 1 WHERE idSupplier = '$id_supplier'");

        // 3. GENERATE ULANG PDF AGAR TTD GA MUNCUL! (Panggil Autoloader dulu)
        require '../../../../vendor/autoload.php';
        buat_pdf_pengajuan($id_pengadaan);

        set_notifikasi('success', 'Disetujui! TTD Anda telah tercetak otomatis di PDF Proposal.');
    }

    echo "<script>window.location='index.php';</script>";
    exit;
}

include '../../../../components/header.php';

// Cek ketersediaan supplier sebelum merender HTML
$pilihan_supplier = ambil_pilihan_supplier();
$has_supplier = !empty($pilihan_supplier) ? 'true' : 'false';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-9">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Validasi Pengadaan Aset</h5>
            </div>
            <div class="card-body p-4">

                <!-- INFORMASI DOKUMEN -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="bg-light p-4 rounded border h-100">
                            <h6 class="fw-bold text-astar mb-3 border-bottom pb-2"><i class="bi bi-info-circle-fill me-2"></i>Detail Kebutuhan (ID: <?= $data['idPengadaan'] ?>)</h6>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Pemohon</div>
                                <div class="col-8 fw-bold">: <?= $data['namaUser'] ?> (<?= $data['kodeDepartemen'] ?>)</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Kategori Aset</div>
                                <div class="col-8 fw-bold">: <?= $data['namaKategori'] ?> <?= ($data['statusKategori'] == 'Draft') ? '<span class="badge bg-warning text-dark ms-1">Draft Baru</span>' : '' ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Spesifikasi</div>
                                <div class="col-8 fw-bold">: <?= $data['namaKebutuhan'] ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Jumlah</div>
                                <div class="col-8 fw-bold text-astar">: <?= $data['jumlah'] ?> Unit</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-4 rounded border h-100 text-center" style="background-color: #fdfdfd;">
                            <i class="bi bi-file-earmark-pdf-fill text-danger mb-2" style="font-size: 3.5rem;"></i>
                            <h6 class="fw-bold mb-3">Proposal Pengajuan</h6>
                            <a href="../../../../uploads/dokumen_pengajuan/<?= $data['dokumen_pengajuan'] ?>?v=<?= time(); ?>" target="_blank" class="btn btn-outline-danger w-100 fw-bold shadow-sm">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka PDF
                            </a>
                        </div>
                    </div>
                </div>

                <!-- FORM KEPUTUSAN -->
                <form action="" method="POST">

                    <div class="mb-4 p-4 rounded" style="background-color: #f4f6f9; border: 2px dashed #c2d5ff;">
                        <label class="form-label fw-bold text-astar mb-3"><i class="bi bi-hammer me-2"></i>Keputusan Kepala GA <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="keputusan" id="aksi_setuju" value="setuju" checked onchange="toggleKeputusan()">
                                <label class="btn btn-outline-astar w-100 py-3 fw-bold text-start" for="aksi_setuju" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-check-lg fs-4 d-block mb-1"></i> Setujui & Lanjutkan
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="keputusan" id="aksi_tolak" value="tolak" onchange="toggleKeputusan()">
                                <label class="btn btn-outline-danger w-100 py-3 fw-bold text-start" for="aksi_tolak" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-x-lg fs-4 d-block mb-1"></i> Tolak Pengajuan
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL SUPPLIER (HANYA MUNCUL JIKA SETUJU) -->
                    <div id="panel_supplier" class="mb-4 p-4 rounded bg-light border">
                        <label class="form-label fw-bold text-astar">Pilih Staf Supplier (Pencari Vendor) <span class="text-danger">*</span></label>
                        <p class="text-muted" style="font-size: 13px;">Delegasikan tugas mencari harga 3 toko termurah kepada tim supplier di bawah ini.</p>
                        <?php
                        if (empty($pilihan_supplier)) {
                            echo '<div class="alert alert-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Tidak ada staf Supplier yang aktif. Harap tambahkan di Master Supplier!</div>';
                        } else {
                            $opsi_final = ['' => '-- Pilih Penanggung Jawab --'] + $pilihan_supplier;
                            echo buat_dropdown_astar('id_supplier', $opsi_final, '', false);
                        }
                        ?>
                    </div>

                    <!-- PANEL TOLAK (HANYA MUNCUL JIKA DITOLAK) -->
                    <div id="panel_tolak" class="mb-4 p-4 rounded border bg-light" style="display: none;">
                        <label class="form-label fw-bold text-astar"><i class="bi bi-chat-left-text-fill me-1"></i> Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_tolak" id="input_alasan_tolak" class="form-control" rows="3" placeholder="Jelaskan alasan pengadaan ini ditolak agar Tendik mengetahuinya..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4 border-top pt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Kembali</a>
                        <!-- ID btn_submit DITAMBAHKAN UNTUK DIKONTROL OLEH JS -->
                        <button type="submit" id="btn_submit" name="proses_validasi" class="btn btn-astar px-5 fw-bold shadow-sm">
                            Simpan Keputusan <i class="bi bi-send-check ms-1"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?= script_dinamis_kepalaga_approve($has_supplier); ?>

<?php include '../../../../components/footer.php'; ?>