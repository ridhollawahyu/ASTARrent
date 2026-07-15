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
    // 1. TANGKAP JABATAN UNTUK PREFIX ID
    $jabatan = $_POST['jabatan'];
    $prefix = "";

    if ($jabatan == "Tenaga Pendidik") {
        $prefix = "TDK";
    } elseif ($jabatan == "Staff GA" || $jabatan == "Kepala GA") {
        $prefix = "GA";
    } elseif ($jabatan == "Finance") {
        $prefix = "FIN";
    }

    // 2. GENERATE ID OTOMATIS (5 Digit)
    $idUser = generate_id($prefix, 'users', 'idUser');

    // 3. TANGKAP DATA LAINNYA
    $nama   = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $telp   = format_no_telp($_POST['kode_negara'], $_POST['no_telp']);
    $email  = mysqli_real_escape_string($koneksi, $_POST['email']);
    $pass   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $dept   = !empty($_POST['dept_prodi']) ? $_POST['dept_prodi'] : $_POST['dept_auto'];

    // 4. VALIDASI & INSERT
    if (cek_email_ganda($email)) {
        set_notifikasi('error', 'Gagal! Email tersebut sudah terdaftar.');
        header('Location: create.php');
        exit;
    }
    if (cek_telp_ganda($telp)) {
        set_notifikasi('error', 'Gagal! Nomor telepon tersebut sudah terdaftar.');
        header('Location: create.php');
        exit;
    } else {
        $query = "INSERT INTO users (idUser, namaUser, noTelp_user, jabatanUser, emailUser, passUser, kodeDepartemen) 
                  VALUES ('$idUser', '$nama', '$telp', '$jabatan', '$email', '$pass', '$dept')";

        if (mysqli_query($koneksi, $query)) {
            set_notifikasi('success', "Sukses! User $nama terdaftar dengan ID: $idUser");
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
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Tambah User Baru</h5>
            </div>

            <div class="card-body p-4">
                <form action="" method="POST">

                    <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> <strong>Sistem Otomatis:</strong> ID User akan di-generate otomatis berdasarkan Jabatan untuk mencegah duplikasi.
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" required placeholder="Masukkan Nama Lengkap">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Jabatan <span class="text-danger">*</span></label>
                            <?php
                            $opsi_jabatan = [
                                'Tenaga Pendidik' => 'Tenaga Pendidik',
                                'Staff GA' => 'Staff GA',
                                'Kepala GA' => 'Kepala GA',
                                'Finance' => 'Finance'
                            ];
                            // Panggil Dropdown Global ASTARrent!
                            echo buat_dropdown_astar('jabatan', $opsi_jabatan);
                            ?>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Departemen / Prodi <span class="text-danger">*</span></label>

                            <!-- 1. MODE KARYAWAN (Auto-Fill Terkunci) -->
                            <div id="dept_autofill_container">
                                <input type="text" id="dept_autofill_text" class="form-control bg-light text-secondary fw-bold" readonly placeholder="Otomatis terisi...">
                                <input type="hidden" name="dept_auto" id="dept_autofill_value">
                            </div>

                            <!-- 2. MODE TENDIK (Dropdown Prodi Custom) -->
                            <div id="dept_dropdown_container" style="display:none;">
                                <?php
                                $opsi_prodi = ['P3P' => 'P3P', 'TPM' => 'TPM', 'MOT' => 'MOT', 'MEK' => 'MEK', 'MIN' => 'MIN', 'TKB' => 'TKB', 'TAB' => 'TAB', 'TRL' => 'TRL', 'RPL' => 'RPL'];
                                // Panggil Dropdown Global ASTARrent tanpa required agar tidak error saat mode Karyawan
                                echo buat_dropdown_astar('dept_prodi', $opsi_prodi, '', false);
                                ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Nomor Telepon <span class="text-danger">*</span></label>
                            <!-- Panggil Fungsi Telepon Global! -->
                            <?= buat_input_telp(); ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="user@astratech.ac.id" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required placeholder="Minimal 6 Karakter" minlength="6" autocomplete="off">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="submit" class="btn btn-astar px-5 fw-bold">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- PANGGIL SCRIPT DINAMIS DARI FUNCTIONS.PHP -->
<?= script_dinamis_jabatan_dept(); ?>

<?php include '../../../components/footer.php'; ?>