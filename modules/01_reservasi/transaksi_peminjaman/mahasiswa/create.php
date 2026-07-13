<?php
// --- FILE: modules/01_reservasi/transaksi_peminjaman/mahasiswa/create.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Mahasiswa') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Mahasiswa.');
    header('Location: ../../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Mahasiswa') && $_SESSION['status'] === 'Dibekukan') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu dibekukan.');
    header('Location: ../../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Mahasiswa') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (isset($_POST['submit'])) {
    $nim = $_SESSION['id'];
    $keperluan = mysqli_real_escape_string($koneksi, $_POST['keperluan']);
    $tgl_kembali = mysqli_real_escape_string($koneksi, $_POST['tgl_kembali']);
    $tgl_pengajuan = date('Y-m-d H:i:s');

    // TANGKAP INPUTAN XOR
    $idAset = !empty($_POST['aset']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['aset']) . "'" : "NULL";
    $idFasilitas = !empty($_POST['fasilitas']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['fasilitas']) . "'" : "NULL";

    // VALIDASI LOGIKA XOR (Wajib isi salah satu)
    if (($idAset == "NULL" && $idFasilitas == "NULL") || ($idAset != "NULL" && $idFasilitas != "NULL")) {
        set_notifikasi('error', 'Pilih SATU saja: Aset atau Fasilitas!');
        header('Location: create.php');
        exit;
    }

    $id_otomatis = generate_id('PJM', 'transaksi_peminjaman', 'idPeminjaman');

    $query = "INSERT INTO transaksi_peminjaman (idPeminjaman, tanggalPengajuan, tanggalRencana_kembali, keperluan, nimMahasiswa, idAset, idFasilitas) 
              VALUES ('$id_otomatis', '$tgl_pengajuan', '$tgl_kembali', '$keperluan', '$nim', $idAset, $idFasilitas)";

    if (mysqli_query($koneksi, $query)) {
        set_notifikasi('success', "Request terkirim! Menunggu persetujuan dari Tenaga Pendidik.");
        header('Location: index.php');
        exit;
    } else {
        set_notifikasi('error', 'Gagal memproses pengajuan! Terjadi kesalahan pada database.');
    }
}

include '../../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cart-plus-fill me-2"></i>Form Pengajuan Peminjaman</h5>
            </div>
            <div class="card-body p-4">
                <div class="alert py-2 mb-4" style="background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Aturan:</strong> Anda hanya dapat meminjam 1 Aset ATAU 1 Fasilitas dalam satu formulir ini.
                </div>

                <form action="" method="POST">

                    <div class="mb-4 p-4 rounded" style="background-color: #f4f6f9; border: 2px dashed #c2d5ff;">
                        <label class="form-label fw-bold text-astar mb-3"><i class="bi bi-box me-2"></i>Pilih Tipe Peminjaman <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="tipe_pinjam" id="btn_aset" autocomplete="off" onchange="toggleTipePinjam('aset')">
                                <label class="btn btn-outline-astar w-100 py-3 fw-bold text-start" for="btn_aset" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-pc-display fs-4 d-block mb-1"></i> Aset (Elektronik)
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="tipe_pinjam" id="btn_fasilitas" autocomplete="off" onchange="toggleTipePinjam('fasilitas')">
                                <label class="btn btn-outline-astar w-100 py-3 fw-bold text-start" for="btn_fasilitas" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-house-door-fill fs-4 d-block mb-1"></i> Fasilitas (Ruangan)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- KOTAK DROPDOWN ASET (Sembunyi by default) -->
                    <div id="box_aset" class="mb-4 bg-light p-4 rounded border" style="display: none;">
                        <label class="form-label text-astar fw-bold"><i class="bi bi-search me-1"></i> Pilih Aset yang Tersedia <span class="text-danger">*</span></label>
                        <?php
                        $pilihan_aset = ambil_barang_tersedia('aset');
                        echo buat_dropdown_astar('aset', $pilihan_aset, '', false);
                        ?>
                    </div>

                    <!-- KOTAK DROPDOWN FASILITAS (Sembunyi by default) -->
                    <div id="box_fasilitas" class="mb-4 bg-light p-4 rounded border" style="display: none;">
                        <label class="form-label text-astar fw-bold"><i class="bi bi-search me-1"></i> Pilih Fasilitas yang Tersedia <span class="text-danger">*</span></label>
                        <?php
                        $pilihan_fasilitas = ambil_barang_tersedia('fasilitas');
                        echo buat_dropdown_astar('fasilitas', $pilihan_fasilitas, '', false);
                        ?>
                    </div>
                    <!-- ========================================================= -->

                    <div class="mb-3">
                        <label class="form-label text-astar fw-bold">Rencana Tanggal & Jam Pengembalian <span class="text-danger">*</span></label>
                        <?= buat_input_datetime('tgl_kembali'); ?>
                        <small class="text-secondary mt-1 d-block" style="font-size:11px;">*Sesuaikan jam jika Anda ingin mengembalikannya nanti atau besok.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Keperluan Peminjaman <span class="text-danger">*</span></label>
                        <textarea name="keperluan" class="form-control" required rows="3" placeholder="Jelaskan untuk kegiatan apa..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4 border-top pt-4">
                        <a href="../../../dashboards/mahasiswa_home.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="submit" id="btn_submit_pinjam" class="btn btn-secondary px-5 fw-bold shadow-sm" disabled>Kirim Request <i class="bi bi-send-check ms-1"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= script_dinamis_tipe_pinjam(); ?>

<?php include '../../../../components/footer.php'; ?>