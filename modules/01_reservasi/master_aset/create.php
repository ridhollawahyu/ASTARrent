<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses Tendik
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header('Location: ../../00_auth/login.php');
    exit;
}

if (isset($_POST['submit'])) {
    // 1. GENERATE ID OTOMATIS (Prefix AST, tabel aset, PK idAset) - Format 5 Digit
    $id_otomatis = generate_id('AST', 'aset', 'idAset');

    // 2. TANGKAP INPUTAN
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);

    // 3. INSERT KE DATABASE (Kondisi & Ketersediaan diisi Default oleh MySQL)
    $query_simpan = "INSERT INTO aset (idAset, idKategori, namaAset) 
                     VALUES ('$id_otomatis', '$kategori', '$nama')";

    if (mysqli_query($koneksi, $query_simpan)) {
        set_notifikasi('success', "Sukses! Aset baru diregistrasi dengan ID: $id_otomatis");
        echo "<script>window.location='index.php';</script>";
        exit;
    } else {
        set_notifikasi('error', 'Gagal menyimpan data ke database!');
    }
}
?>

<!-- HTML VIEW -->
<?php include '../../../components/header.php'; ?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pc-display me-2"></i>Registrasi Aset Manual</h5>
            </div>
            <div class="card-body p-4">

                <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <strong>Info:</strong> ID Aset akan di-generate otomatis (AST-00001). Kondisi awal diset <b>Normal</b> dan <b>Tersedia</b>.
                </div>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-astar fw-bold">Kategori Aset <span class="text-danger">*</span></label>
                        <?php
                        // AMBIL DATA KATEGORI DARI DATABASE PAKE FUNGSI GLOBAL
                        $pilihan_kategori = ambil_pilihan_kategori('Aset');
                        // PANGGIL DROPDOWN TEMA ASTARRENT
                        echo buat_dropdown_astar('kategori', $pilihan_kategori);
                        ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Nama Aset (Merek/Tipe) <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required placeholder="Contoh: Proyektor Epson EB-X400">
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

<?php include '../../../components/footer.php'; ?>