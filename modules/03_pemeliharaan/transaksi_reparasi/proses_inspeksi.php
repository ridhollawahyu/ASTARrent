<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses: Hanya Staff GA
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_reparasi = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_staff_ga = $_SESSION['id'];

// Ambil detail reparasi
$query_detail = mysqli_query($koneksi, "
    SELECT r.*,
           a.namaAset, a.kondisiAset, a.ketersediaanAset,
           f.namaFasilitas, f.kondisiFasilitas, f.ketersediaanFasilitas,
           u.namaUser AS namaTendik,
           p.kondisiFisik AS kondisi_laporan_tendik,
           p.catatanPengembalian
    FROM reparasi_fasilitas_aset r
    LEFT JOIN aset a ON r.idAset = a.idAset
    LEFT JOIN fasilitas f ON r.idFasilitas = f.idFasilitas
    LEFT JOIN users u ON r.idTendik = u.idUser
    LEFT JOIN transaksi_pengembalian p ON r.idPengembalian = p.idPengembalian
    WHERE r.idReparasi = '$id_reparasi' AND r.statusReparasi = 'Menunggu GA'
");

if (mysqli_num_rows($query_detail) == 0) {
    set_notifikasi('error', 'Tiket reparasi tidak ditemukan atau sudah diproses.');
    header('Location: index.php');
    exit;
}

$detail = mysqli_fetch_assoc($query_detail);
$tipe_barang = !empty($detail['idAset']) ? 'Aset' : 'Fasilitas';
$nama_barang = ($tipe_barang === 'Aset') ? $detail['namaAset'] : $detail['namaFasilitas'];

// =============================================================
// PROSES SUBMIT INSPEKSI
// =============================================================
if (isset($_POST['submit_inspeksi'])) {
    $klasifikasi   = mysqli_real_escape_string($koneksi, $_POST['klasifikasi']);
    $aksi          = mysqli_real_escape_string($koneksi, $_POST['aksi']); // 'perbaiki' atau 'kanibal'
    $catatan_ga    = mysqli_real_escape_string($koneksi, $_POST['catatan_ga']);
    $waktu_sekarang = date('Y-m-d H:i:s');

    // Validasi
    $opsi_klasifikasi_valid = ['Rusak Ringan', 'Rusak Sedang', 'Rusak Berat', 'Rusak Total'];
    if (!in_array($klasifikasi, $opsi_klasifikasi_valid)) {
        set_notifikasi('error', 'Klasifikasi kerusakan tidak valid.');
        header("Location: proses_inspeksi.php?id=$id_reparasi");
        exit;
    }

    if ($aksi === 'kanibal') {
        // Aksi Kanibal: Bongkar total → status Dikanibal, barang Tidak Tersedia
        $status_baru = 'Dikanibal';
        $q_update = "UPDATE reparasi_fasilitas_aset SET
                        idStaffGA           = '$id_staff_ga',
                        klasifikasiKerusakan = '$klasifikasi',
                        catatanReparasi     = '$catatan_ga',
                        statusReparasi      = '$status_baru',
                        tanggalReparasi     = '$waktu_sekarang',
                        tanggalSelesai      = '$waktu_sekarang'
                     WHERE idReparasi = '$id_reparasi'";

        if (mysqli_query($koneksi, $q_update)) {
            // Update barang: kondisi tetap "Tidak Berfungsi", ketersediaan "Tidak Tersedia"
            if ($tipe_barang === 'Aset') {
                perbarui_status_barang('aset', $detail['idAset'], 'Tidak Tersedia', 'Tidak Berfungsi');
            } else {
                perbarui_status_barang('fasilitas', $detail['idFasilitas'], 'Tidak Tersedia', 'Tidak Berfungsi');
            }
            set_notifikasi('success', 'Barang ditetapkan untuk dikanibal. Silakan catat komponen di modul Komponen.');
            header('Location: index.php');
            exit;
        }
    } else {
        // Aksi Perbaiki → status Sedang Dikerjakan, barang Sedang Diperbaiki
        $status_baru = 'Sedang Dikerjakan';
        $q_update = "UPDATE reparasi_fasilitas_aset SET
                        idStaffGA            = '$id_staff_ga',
                        klasifikasiKerusakan = '$klasifikasi',
                        catatanReparasi      = '$catatan_ga',
                        statusReparasi       = '$status_baru',
                        tanggalReparasi      = '$waktu_sekarang'
                     WHERE idReparasi = '$id_reparasi'";

        if (mysqli_query($koneksi, $q_update)) {
            // Update ketersediaan barang ke Sedang Diperbaiki
            if ($tipe_barang === 'Aset') {
                mysqli_query($koneksi, "UPDATE aset SET ketersediaanAset = 'Sedang Diperbaiki' WHERE idAset = '{$detail['idAset']}'");
            } else {
                mysqli_query($koneksi, "UPDATE fasilitas SET ketersediaanFasilitas = 'Sedang Diperbaiki' WHERE idFasilitas = '{$detail['idFasilitas']}'");
            }
            set_notifikasi('success', 'Reparasi dimulai! Barang ditandai "Sedang Diperbaiki". Klik Selesaikan saat reparasi tuntas.');
            header('Location: index.php');
            exit;
        }
    }

    set_notifikasi('error', 'Gagal memproses inspeksi!');
}

include '../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center justify-content-between" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-search me-2"></i>Form Inspeksi Reparasi</h5>
                <a href="index.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
            <div class="card-body p-4">

                <!-- INFO LAPORAN TENDIK -->
                <div class="alert" style="background-color: #fff8e1; border-left: 4px solid #ffc107; border-radius: 8px;">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person-exclamation me-2 text-warning"></i>Laporan dari Tendik</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Pelapor:</strong> <?= htmlspecialchars($detail['namaTendik']) ?></p>
                            <p class="mb-1"><strong>Barang:</strong>
                                <span class="badge bg-secondary"><?= $tipe_barang ?></span>
                                <?= htmlspecialchars($nama_barang) ?>
                            </p>
                            <p class="mb-0"><strong>Tanggal Lapor:</strong> <?= date('d M Y, H:i', strtotime($detail['tanggalLapor'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Kondisi Dilaporkan:</strong>
                                <span class="badge bg-danger">Tidak Berfungsi</span>
                            </p>
                            <?php if (!empty($detail['catatanPengembalian'])): ?>
                                <p class="mb-0"><strong>Catatan Tendik:</strong><br>
                                    <span class="text-muted"><?= nl2br(htmlspecialchars($detail['catatanPengembalian'])) ?></span>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- FORM INSPEKSI GA -->
                <hr class="my-4">
                <h6 class="fw-bold text-astar mb-3"><i class="bi bi-clipboard2-pulse me-2"></i>Hasil Inspeksi Staff GA</h6>

                <form action="" method="POST">

                    <!-- Klasifikasi Kerusakan -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-danger">Klasifikasi Kerusakan <span class="text-danger">*</span></label>
                        <?php
                        $opsi_klasifikasi = [
                            'Rusak Ringan' => '🟢 Rusak Ringan (Bisa diperbaiki cepat)',
                            'Rusak Sedang' => '🟡 Rusak Sedang (Butuh waktu & sparepart)',
                            'Rusak Berat'  => '🔴 Rusak Berat (Perlu penanganan serius)',
                            'Rusak Total'  => '☠️ Rusak Total (Tidak bisa diperbaiki)',
                        ];
                        echo buat_dropdown_astar('klasifikasi', $opsi_klasifikasi, '');
                        ?>
                        <small class="text-muted">Pilih berdasarkan kondisi fisik aktual setelah diperiksa langsung.</small>
                    </div>

                    <!-- Catatan Inspeksi -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-astar">Catatan Inspeksi GA</label>
                        <textarea name="catatan_ga" class="form-control" rows="3"
                            placeholder="Jelaskan hasil inspeksi, komponen yang rusak, dll..."></textarea>
                    </div>

                    <!-- Keputusan Aksi -->
                    <div class="mb-4 p-4 rounded" style="background-color: #f4f6f9; border: 2px dashed #c2d5ff;">
                        <label class="form-label fw-bold text-astar mb-3"><i class="bi bi-hammer me-2"></i>Keputusan Tindakan <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="aksi" id="aksi_perbaiki" value="perbaiki" required>
                                <label class="btn btn-outline-primary w-100 py-3 fw-bold text-start" for="aksi_perbaiki" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-tools fs-4 d-block mb-2"></i>
                                    🔧 Perbaiki
                                    <small class="d-block text-muted fw-normal mt-1">Barang akan masuk status "Sedang Diperbaiki"</small>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="aksi" id="aksi_kanibal" value="kanibal">
                                <label class="btn btn-outline-danger w-100 py-3 fw-bold text-start" for="aksi_kanibal" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-recycle fs-4 d-block mb-2"></i>
                                    ♻️ Kanibal / Bongkar
                                    <small class="d-block text-muted fw-normal mt-1">Barang dibongkar, komponen dicatat di modul Komponen</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="button" class="btn btn-astar px-5 fw-bold" data-bs-toggle="modal" data-bs-target="#modalKonfirmasiInspeksi">
                            Simpan Hasil Inspeksi <i class="bi bi-send-check ms-1"></i>
                        </button>
                        <button type="submit" name="submit_inspeksi" id="btnSubmitInspeksi" class="d-none"></button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI -->
<div class="modal fade" id="modalKonfirmasiInspeksi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header text-white" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bi bi-question-diamond-fill me-2"></i>Konfirmasi Inspeksi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="bi bi-exclamation-circle text-warning mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-dark fw-bold mb-2">Simpan Hasil Inspeksi?</h5>
                <p class="text-secondary mb-0">Pastikan klasifikasi dan keputusan tindakan sudah sesuai. Status barang akan berubah otomatis dan <b>tidak dapat dibatalkan</b>.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4 px-4">
                <button type="button" class="btn btn-light fw-bold px-4 text-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Periksa Lagi</button>
                <button type="button" class="btn text-white fw-bold px-4" style="background-color: #1d4197; border-radius: 8px;"
                    onclick="document.getElementById('btnSubmitInspeksi').click();">
                    Ya, Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>
