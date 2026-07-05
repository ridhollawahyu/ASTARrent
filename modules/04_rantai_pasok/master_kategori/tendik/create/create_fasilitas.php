<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../../config/database.php';
include '../../../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses Tendik
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (isset($_POST['submit'])) {
    // 1. GENERATE ID OTOMATIS (Prefix AST, tabel kategori, PK idKategori) - Format 5 Digit
    $id_otomatis = generate_id('KTG', 'kategori', 'idKategori');

    // 2. TANGKAP INPUTAN
    $id_pembuat = $_SESSION['id'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);

    $query_simpan = "INSERT INTO kategori (idKategori, namaKategori, tipeKategori, idPembuat) 
                     VALUES ('$id_otomatis', '$nama', 'Fasilitas Akademik', '$id_pembuat')";

    if (mysqli_query($koneksi, $query_simpan)) {
        set_notifikasi('success', "Sukses! Kategori baru diregistrasi dengan ID: $id_otomatis");
        header('Location: ../index.php');
        exit;
    } else {
        set_notifikasi('error', 'Gagal menyimpan data ke database!');
    }
}
?>

<!-- HTML VIEW -->
<?php include '../../../../../components/header.php'; ?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pc-display me-2"></i>Registrasi Kategori Fasilitas</h5>
            </div>
            <div class="card-body p-4">

                <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <strong>Info:</strong> ID Kategori akan di-generate otomatis (KTG-00001). Kondisi awal Tipe dan Status selalu diset <b>Fasilitas</b> dan <b>Aktif</b>.
                </div>

                <form action="" method="POST">
                    <div class="mb-6">
                        <label class="form-label text-astar fw-bold">Nama Kategori Fasilitas <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required placeholder="Contoh: Ruang Kelas">
                    </div>
                    <div class="mb-6">
                        <label class="form-label text-astar fw-bold">Tipe Kategori </label>
                        <input type="text" name="tipe" class="form-control fw-bold" required value="Fasilitas" readonly>
                        <small class="text-danger">*Untuk create kategori Aset, dibuat langsung pada Transaksi Pengadaan</small>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="../index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="submit" class="btn btn-astar px-5">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../../../../components/footer.php'; ?>