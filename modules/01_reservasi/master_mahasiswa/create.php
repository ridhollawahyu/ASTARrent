<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // Wajib ada untuk set notifikasi
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Super Admin.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Super Admin') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}

// PROSES SIMPAN DATA JIKA TOMBOL SUBMIT DITEKAN
if (isset($_POST['submit'])) {

    $nama = $_POST['nama'];
    $prodi = $_POST['prodi'];
    $no_telp_final = format_no_telp($_POST['kode_negara'], $_POST['no_telp']);
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (empty($prodi)) {
        set_notifikasi('error', 'Gagal! Harus memilih Prodi.');
        header('Location: create.php');
        exit;
    }
    if (cek_email_ganda($email)) {
        set_notifikasi('error', 'Gagal! Email tersebut sudah terdaftar.');
        header('Location: create.php');
        exit;
    }
    if (cek_telp_ganda($no_telp_final)) {
        set_notifikasi('error', 'Gagal! Nomor telepon tersebut sudah terdaftar.');
        header('Location: create.php');
        exit;
    }


    // Panggil fungsi generate NIM & Telepon
    $nim_otomatis = generate_nim_mahasiswa($prodi);

    $query_simpan = "INSERT INTO mahasiswa (nimMahasiswa, namaMahasiswa, kodeProdi_mahasiswa, noTelp_mahasiswa, emailMahasiswa, passMahasiswa) 
                     VALUES ('$nim_otomatis', '$nama', '$prodi', '$no_telp_final', '$email', '$password')";

    if (mysqli_query($koneksi, $query_simpan)) {
        set_notifikasi('success', "Sukses! Mahasiswa berhasil didaftarkan dengan NIM: $nim_otomatis");
        header('Location: index.php');
        exit;
    } else {
        set_notifikasi('error', 'Gagal menyimpan data! Cek kembali form Anda.');
    }
}

include '../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Form Tambah Mahasiswa</h5>
            </div>
            <div class="card-body p-4">

                <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <strong>Info:</strong> NIM akan dibuat otomatis oleh sistem berdasarkan Prodi dan Tahun saat ini.
                </div>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-astar fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required placeholder="Masukkan nama lengkap">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Program Studi <span class="text-danger">*</span></label>

                            <!-- IMPLEMENTASI DROPDOWN GLOBAL INTERAKTIF -->
                            <?php
                            $pilihan_prodi = [
                                'P3P' => 'P3P - Prodi 1',
                                'TPM' => 'TPM - Prodi 2',
                                'MIN' => 'MIN - Prodi 3',
                                'MOT' => 'MOT - Prodi 4',
                                'MEK' => 'MEK - Prodi 5',
                                'TKB' => 'TKB - Prodi 6',
                                'TAB' => 'TAB - Prodi 7',
                                'TRL' => 'TRL - Prodi 8',
                                'RPL' => 'RPL - Prodi 9'
                            ];

                            // Panggil fungsi Dropdown Mewah (Kosongkan nilai lamanya karena ini form Create)
                            echo buat_dropdown_astar('prodi', $pilihan_prodi);
                            ?>

                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <!-- PANGGIL FUNGSI GLOBAL INPUT TELEPON -->
                            <?= buat_input_telp(); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-astar fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="email@student.astratech.ac.id">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter" minlength="6">
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