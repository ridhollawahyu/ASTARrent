<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses Tendik
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header('Location: ../../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Tenaga Pendidik') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../../00_auth/login.php');
}

if (isset($_POST['submit'])) {
    // 1. GENERATE ID OTOMATIS (Prefix AST, tabel fasilitas, PK idFasilitas) - Format 5 Digit
    $id_otomatis = generate_id('FSL', 'fasilitas', 'idFasilitas');
    $id_tendik = $_SESSION['id'];

    // 2. TANGKAP INPUTAN
    $id_pengelola = $_SESSION['id'];
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);

    // Insert menggunakan idPengelola dan otomatis tipeFasilitas = 'Akademik'
    $query_simpan = "INSERT INTO fasilitas (idFasilitas, idPengelola, idKategori, namaFasilitas, lokasiFasilitas, tipeFasilitas) 
                     VALUES ('$id_otomatis', '$id_pengelola', '$kategori', '$nama', '$lokasi', 'Akademik')";

    if (mysqli_query($koneksi, $query_simpan)) {
        set_notifikasi('success', "Sukses! Fasilitas baru diregistrasi dengan ID: $id_otomatis");
        header('Location: index.php');
        exit;
    } else {
        set_notifikasi('error', 'Gagal menyimpan data ke database!');
    }
}
?>

<!-- HTML VIEW -->
<?php include '../../../../components/header.php'; ?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pc-display me-2"></i>Registrasi Fasilitas Manual</h5>
            </div>
            <div class="card-body p-4">

                <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <strong>Info:</strong> ID Fasilitas akan di-generate otomatis (FSL-00001). Kondisi awal diset <b>Normal</b> dan <b>Tersedia</b>.
                </div>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-astar fw-bold">Kategori Fasilitas <span class="text-danger">*</span></label>
                        <?php
                        // AMBIL DATA KATEGORI DARI DATABASE PAKE FUNGSI GLOBAL
                        $pilihan_kategori = ambil_pilihan_kategori('Fasilitas Akademik');
                        // PANGGIL DROPDOWN TEMA ASTARRENT
                        echo buat_dropdown_astar('kategori', $pilihan_kategori);
                        ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Nama Fasilitas <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required placeholder="Contoh: Ruang Kelas TRPL 1B">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Lokasi Fasilitas (Gedung & Lantai) <span class="text-danger">*</span></label>
                        <input type="text" name="lokasi" class="form-control" required placeholder="Contoh: Gedung A, Lantai 3">
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="submit" class="btn btn-astar px-5">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../../../components/footer.php'; ?>