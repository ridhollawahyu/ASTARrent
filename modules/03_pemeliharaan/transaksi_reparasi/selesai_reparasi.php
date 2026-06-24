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

// Ambil detail reparasi yang sedang dikerjakan
$query_detail = mysqli_query($koneksi, "
    SELECT r.*,
           a.namaAset, f.namaFasilitas,
           u.namaUser AS namaTendik
    FROM reparasi_fasilitas_aset r
    LEFT JOIN aset a ON r.idAset = a.idAset
    LEFT JOIN fasilitas f ON r.idFasilitas = f.idFasilitas
    LEFT JOIN users u ON r.idTendik = u.idUser
    WHERE r.idReparasi = '$id_reparasi' AND r.statusReparasi = 'Sedang Dikerjakan'
");

if (mysqli_num_rows($query_detail) == 0) {
    set_notifikasi('error', 'Tiket reparasi tidak ditemukan atau statusnya bukan "Sedang Dikerjakan".');
    header('Location: index.php');
    exit;
}

$detail = mysqli_fetch_assoc($query_detail);
$tipe_barang = !empty($detail['idAset']) ? 'Aset' : 'Fasilitas';
$nama_barang = ($tipe_barang === 'Aset') ? $detail['namaAset'] : $detail['namaFasilitas'];

// =============================================================
// PROSES SELESAIKAN REPARASI
// =============================================================
if (isset($_POST['submit_selesai'])) {
    $kondisi_akhir  = mysqli_real_escape_string($koneksi, $_POST['kondisi_akhir']); // Normal atau Berfungsi
    $catatan_selesai = mysqli_real_escape_string($koneksi, $_POST['catatan_selesai']);
    $waktu_sekarang  = date('Y-m-d H:i:s');

    $opsi_valid = ['Normal', 'Berfungsi'];
    if (!in_array($kondisi_akhir, $opsi_valid)) {
        set_notifikasi('error', 'Kondisi akhir tidak valid!');
        header("Location: selesai_reparasi.php?id=$id_reparasi");
        exit;
    }

    // Update status reparasi ke Selesai
    $q_update = "UPDATE reparasi_fasilitas_aset SET
                    statusReparasi  = 'Selesai',
                    tanggalSelesai  = '$waktu_sekarang',
                    catatanReparasi = CONCAT(IFNULL(catatanReparasi, ''), '\n[Selesai] $catatan_selesai')
                 WHERE idReparasi = '$id_reparasi'";

    if (mysqli_query($koneksi, $q_update)) {
        // Update kondisi barang dan kembalikan ketersediaan ke Tersedia
        if ($tipe_barang === 'Aset') {
            perbarui_status_barang('aset', $detail['idAset'], 'Tersedia', $kondisi_akhir);
        } else {
            perbarui_status_barang('fasilitas', $detail['idFasilitas'], 'Tersedia', $kondisi_akhir);
        }
        set_notifikasi('success', 'Reparasi selesai! Barang sudah kembali tersedia dengan kondisi ' . $kondisi_akhir . '.');
        header('Location: index.php');
        exit;
    }

    set_notifikasi('error', 'Gagal menyelesaikan reparasi!');
}

include '../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center justify-content-between" style="background-color: #198754; border-radius: 15px 15px 0 0;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-check2-circle me-2"></i>Selesaikan Reparasi</h5>
                <a href="index.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
            <div class="card-body p-4">

                <!-- INFO REPARASI -->
                <div class="bg-light p-3 rounded border mb-4">
                    <p class="mb-1"><strong>ID Reparasi:</strong> <code class="text-primary"><?= htmlspecialchars($detail['idReparasi']) ?></code></p>
                    <p class="mb-1"><strong>Barang:</strong>
                        <span class="badge bg-secondary"><?= $tipe_barang ?></span>
                        <?= htmlspecialchars($nama_barang) ?>
                    </p>
                    <p class="mb-1"><strong>Klasifikasi:</strong>
                        <span class="badge bg-dark"><?= htmlspecialchars($detail['klasifikasiKerusakan']) ?></span>
                    </p>
                    <p class="mb-0"><strong>Mulai Reparasi:</strong> <?= date('d M Y, H:i', strtotime($detail['tanggalReparasi'])) ?></p>
                </div>

                <form action="" method="POST">

                    <!-- Kondisi Akhir Barang -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-astar">Kondisi Akhir Barang Setelah Diperbaiki <span class="text-danger">*</span></label>
                        <?php
                        $opsi_kondisi_akhir = [
                            'Normal'    => '✅ Normal (Sudah kembali normal sepenuhnya)',
                            'Berfungsi' => '🟡 Berfungsi (Sudah bisa dipakai meski ada keterbatasan)',
                        ];
                        echo buat_dropdown_astar('kondisi_akhir', $opsi_kondisi_akhir, 'Normal');
                        ?>
                        <small class="text-muted mt-1 d-block">Kondisi ini akan tersimpan di data master barang.</small>
                    </div>

                    <!-- Catatan Penyelesaian -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-astar">Catatan Penyelesaian</label>
                        <textarea name="catatan_selesai" class="form-control" rows="3"
                            placeholder="Tuliskan apa yang sudah diperbaiki, komponen yang diganti, dll..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="button" class="btn btn-success px-5 fw-bold" data-bs-toggle="modal" data-bs-target="#modalKonfirmasiSelesai">
                            Tandai Selesai <i class="bi bi-check-circle ms-1"></i>
                        </button>
                        <button type="submit" name="submit_selesai" id="btnSubmitSelesai" class="d-none"></button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI -->
<div class="modal fade" id="modalKonfirmasiSelesai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header text-white" style="background-color: #198754; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bi bi-question-diamond-fill me-2"></i>Konfirmasi Penyelesaian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <i class="bi bi-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-dark fw-bold mb-2">Tandai Reparasi Selesai?</h5>
                <p class="text-secondary mb-0">Barang akan otomatis dikembalikan ke status <strong>Tersedia</strong> dan bisa dipinjam kembali oleh mahasiswa.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4 px-4">
                <button type="button" class="btn btn-light fw-bold px-4 text-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Periksa Lagi</button>
                <button type="button" class="btn btn-success text-white fw-bold px-4" style="border-radius: 8px;"
                    onclick="document.getElementById('btnSubmitSelesai').click();">
                    Ya, Selesaikan
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>
