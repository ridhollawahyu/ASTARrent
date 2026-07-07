<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

// 1. VALIDASI KEAMANAN (Hanya SA)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Super Admin.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Super Admin') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}

// 2. TANGKAP ID & ANTI-HACKER ROOT ACCOUNT
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// 3. AMBIL DATA LAMA
$query_data = mysqli_query($koneksi, "SELECT * FROM supplier WHERE idSupplier = '$id'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    set_notifikasi('error', 'Data Supplier tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// 4. PROSES JIKA TOMBOL UPDATE DITEKAN
if (isset($_POST['update'])) {

    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jumlahTugas = $data['jumlahTugas_aktif'];
    $status  = $data['statusSupplier'];

    // Validasi Nomor Telepon
    $no_telp_final = format_no_telp($_POST['kode_negara'], $_POST['no_telp']);

    // Logika Password (Kosong = Tetap pakai yang lama)
    if (empty($_POST['password'])) {
        $password_final = $data['passSupplier'];
    } else {
        $password_final = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // CATATAN: Email dan ID TIDAK DIMASUKKAN ke query UPDATE karena sifatnya identitas mutlak (Read-Only)
    $query_update = "UPDATE supplier SET 
                        namaSupplier = '$nama',
                        noTelp_supplier = '$no_telp_final',
                        passSupplier = '$password_final',
                        statusSupplier = '$status',
                        jumlahTugas_aktif = '$jumlahTugas'
                     WHERE idSupplier = '$id'";

    if (mysqli_query($koneksi, $query_update)) {
        set_notifikasi('success', 'Data Supplier berhasil diperbarui!');
        header('Location: index.php');
        exit;
    } else {
        set_notifikasi('error', 'Gagal memperbarui data ke database!');
    }
}

include '../../../components/header.php';
?>

<div class="row justify-content-center mt-4 mb-5">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Supplier</h5>
            </div>

            <div class="card-body p-4">
                <form action="" method="POST">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" required value="<?= $data['namaSupplier']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Nomor Telepon</label>
                            <?php
                            $telp_db = $data['noTelp_supplier'];
                            // Cek dan potong kode negara
                            if (strpos($telp_db, '+1') === 0) {
                                $kode_lama = '+1';
                                $nomor_lama = substr($telp_db, 2);
                            } else if (strpos($telp_db, '+60') === 0) {
                                $kode_lama = '+60';
                                $nomor_lama = substr($telp_db, 3);
                            } else {
                                $kode_lama = '+62';
                                $nomor_lama = (strlen($telp_db) >= 3) ? substr($telp_db, 3) : '';
                            }
                            ?>
                            <?= buat_input_telp($nomor_lama, $kode_lama); ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Email Supplier</label>
                            <!-- KUNCI EMAIL: Read-only untuk menjaga konsistensi SSO -->
                            <input type="email" class="form-control bg-light text-secondary fw-bold" value="<?= $data['emailSupplier']; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*Email tidak dapat diubah.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Jumlah Tugas</label>
                            <!-- KUNCI EMAIL: Read-only untuk menjaga konsistensi SSO -->
                            <input type="text" class="form-control bg-light text-secondary fw-bold" value="<?= $data['jumlahTugas_aktif']; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*Jumlah tugas Supplier tidak dapat diubah.</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- ZONA SOFT DELETE & PASSWORD -->
                    <div class="row mb-4 align-items-center bg-light p-3 rounded">
                        <div class="col-md-6 border-end">
                            <label class="form-label text-astar fw-bold">Status Akun</label>
                            <?php if ($data['statusKategori'] == 'Aktif'): ?>
                                <input type="text" class="form-control bg-light text-success fw-bold" value="<?= $data['statusSupplier']; ?>" readonly>
                            <?php else: ?>
                                <input type="text" class="form-control bg-light text-text-danger fw-bold" value="<?= $data['statusSupplier']; ?>" readonly>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah" minlength="6" autocomplete="off">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="update" class="btn btn-astar px-5 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>