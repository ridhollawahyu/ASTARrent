<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Keamanan (Hanya SA)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Super Admin.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Super Admin') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}

if (isset($_POST['submit'])) {
    // 2. GENERATE ID OTOMATIS (5 Digit)
    $idSupplier = generate_id('SPL', 'supplier', 'idSupplier');

    // 3. TANGKAP DATA LAINNYA
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $telp   = format_no_telp($_POST['kode_negara'], $_POST['no_telp']);
    $email  = mysqli_real_escape_string($koneksi, $_POST['email']);
    $pass   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 4. VALIDASI & INSERT
    if (cek_email_ganda($email)) {
        set_notifikasi('error', 'Gagal! Email tersebut sudah terdaftar.');
    } else {
        $query = "INSERT INTO supplier (idSupplier, namaSupplier, noTelp_supplier, emailSupplier, passSupplier) 
                  VALUES ('$idSupplier', '$nama', '$telp', '$email', '$pass')";

        if (mysqli_query($koneksi, $query)) {
            set_notifikasi('success', "Sukses! Supplier $nama terdaftar dengan ID: $idSupplier");
            header('Location: index.php');
            exit;
        } else {
            set_notifikasi('error', 'Gagal menyimpan ke database!');
        }
    }
}

include '../../../components/header.php';
?>

<div class="row justify-content-center mt-4 mb-5">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Tambah Supplier Baru</h5>
            </div>

            <div class="card-body p-4">
                <form action="" method="POST">

                    <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> <strong>Sistem Otomatis:</strong> ID Supplier akan di-generate otomatis berdasarkan ID yang sudah ada, untuk mencegah duplikasi.
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" required placeholder="Masukkan Nama Lengkap">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Nomor Telepon <span class="text-danger">*</span></label>
                            <!-- Panggil Fungsi Telepon Global! -->
                            <?= buat_input_telp(); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="supplier@astratech.ac.id" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required placeholder="Minimal 6 Karakter" minlength="6" autocomplete="off">
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                            <button type="submit" name="submit" class="btn btn-astar px-5 fw-bold">Simpan Supplier</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>