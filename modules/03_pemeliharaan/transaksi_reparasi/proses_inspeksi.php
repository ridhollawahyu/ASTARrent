<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses: Hanya Staff GA
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_reparasi = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_staff_ga = $_SESSION['id'];

// =============================================================
// 1. QUERY PERBAIKAN (Sesuai ERD, Tanpa Join ke Pengembalian!)
// =============================================================
$query_detail = mysqli_query($koneksi, "
    SELECT r.*,
           a.namaAset, a.kondisiAset, a.ketersediaanAset,
           f.namaFasilitas, f.kondisiFasilitas, f.ketersediaanFasilitas,
           u.namaUser AS namaTendik
    FROM reparasi_fasilitas_aset r
    LEFT JOIN aset a ON r.idAset = a.idAset
    LEFT JOIN fasilitas f ON r.idFasilitas = f.idFasilitas
    LEFT JOIN users u ON r.idTendik = u.idUser
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
// 2. PROSES SUBMIT INSPEKSI GA
// =============================================================
if (isset($_POST['submit_inspeksi'])) {
    $klasifikasi   = mysqli_real_escape_string($koneksi, $_POST['klasifikasi']);
    $aksi          = mysqli_real_escape_string($koneksi, $_POST['aksi']); // 'perbaiki' atau 'kanibal'
    // Catatan: Pastikan kolom 'catatanReparasi' ini beneran ada di tabel database kalian ya!
    $catatan_ga    = mysqli_real_escape_string($koneksi, $_POST['catatan_ga']);
    $waktu_sekarang = date('Y-m-d H:i:s');

    if ($aksi === 'kanibal') {
        // Aksi Kanibal
        $q_update = "UPDATE reparasi_fasilitas_aset SET
                        idStaffGA           = '$id_staff_ga',
                        klasifikasiKerusakan = '$klasifikasi',
                        statusReparasi      = 'Dikanibal',
                        tanggalReparasi     = '$waktu_sekarang',
                        tanggalSelesai      = '$waktu_sekarang'
                     WHERE idReparasi = '$id_reparasi'";

        if (mysqli_query($koneksi, $q_update)) {
            // Barang dimatikan (Soft Delete)
            $id_barang = ($tipe_barang === 'Aset') ? $detail['idAset'] : $detail['idFasilitas'];
            perbarui_status_barang(strtolower($tipe_barang), $id_barang, 'Tidak Tersedia', 'Tidak Berfungsi');

            set_notifikasi('success', 'Barang ditetapkan untuk dikanibal. Silakan catat komponen di modul Komponen.');
            echo "<script>window.location='index.php';</script>";
            exit;
        }
    } else {
        // Aksi Perbaiki
        $q_update = "UPDATE reparasi_fasilitas_aset SET
                        idStaffGA            = '$id_staff_ga',
                        klasifikasiKerusakan = '$klasifikasi',
                        statusReparasi       = 'Sedang Dikerjakan',
                        tanggalReparasi      = '$waktu_sekarang'
                     WHERE idReparasi = '$id_reparasi'";

        if (mysqli_query($koneksi, $q_update)) {
            $id_barang = ($tipe_barang === 'Aset') ? $detail['idAset'] : $detail['idFasilitas'];
            perbarui_status_barang(strtolower($tipe_barang), $id_barang, 'Sedang Diperbaiki');

            set_notifikasi('success', 'Reparasi dimulai! Barang ditandai "Sedang Diperbaiki".');
            echo "<script>window.location='index.php';</script>";
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

                <!-- INFO LAPORAN TENDIK (Diperbaiki tanpa error JOIN) -->
                <div class="alert" style="background-color: #fff8e1; border-left: 4px solid #ffc107; border-radius: 8px;">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person-exclamation me-2 text-warning"></i>Informasi Tiket Laporan</h6>
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
                            <p class="mb-1"><strong>Kondisi Laporan Awal:</strong><br>
                                <span class="badge bg-danger fs-6 mt-1"><?= htmlspecialchars($detail['klasifikasiKerusakan']) ?></span>
                            </p>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="fw-bold text-astar mb-3"><i class="bi bi-clipboard2-pulse me-2"></i>Hasil Inspeksi Staff GA</h6>

                <form action="" method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-danger">Klasifikasi Kerusakan Aktual <span class="text-danger">*</span></label>
                        <?php
                        $opsi_klasifikasi = [
                            'Rusak Ringan' => '🟢 Rusak Ringan (Bisa diperbaiki cepat)',
                            'Rusak Sedang' => '🟡 Rusak Sedang (Butuh waktu & sparepart)',
                            'Rusak Berat'  => '🔴 Rusak Berat (Perlu penanganan serius)',
                            'Rusak Total'  => '☠️ Rusak Total (Tidak bisa diperbaiki)',
                        ];
                        // Dropdown Global kita bekerja di sini!
                        echo buat_dropdown_astar('klasifikasi', $opsi_klasifikasi, '');
                        ?>
                        <small class="text-muted">Pilih berdasarkan kondisi fisik aktual setelah Staff GA memeriksa langsung.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-astar">Catatan Inspeksi GA (Opsional)</label>
                        <textarea name="catatan_ga" class="form-control" rows="3" placeholder="Jelaskan hasil inspeksi, komponen yang rusak, dll..."></textarea>
                    </div>

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
                                    <small class="d-block text-muted fw-normal mt-1">Barang dibongkar, data dilempar ke modul Komponen</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="submit_inspeksi" class="btn btn-astar px-5 fw-bold" onclick="return confirm('Yakin ingin menyimpan hasil inspeksi ini?');">
                            Simpan Hasil Inspeksi <i class="bi bi-send-check ms-1"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>