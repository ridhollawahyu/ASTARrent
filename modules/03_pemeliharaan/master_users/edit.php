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
$sesi_id = $_SESSION['id'];

if ($id === 'SA-00000') {
    set_notifikasi('error', 'Akses Ditolak! Akun Root (SA-00000) tidak boleh diubah oleh siapapun.');
    header("Location: index.php");
    exit;
}

if ($id === $sesi_id) {
    set_notifikasi('error', 'Akses Ditolak! Kamu tidak boleh mengubah akunmu sendiri.');
    header("Location: index.php");
    exit;
}

// 3. AMBIL DATA LAMA
$query_data = mysqli_query($koneksi, "SELECT * FROM users WHERE idUser = '$id'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    set_notifikasi('error', 'Data User tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// 4. PROSES JIKA TOMBOL UPDATE DITEKAN
if (isset($_POST['update'])) {

    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jabatan = $data['jabatanUser'];
    $status  = $data['statusUser'];

    // Tangkap Departemen dinamis
    $dept    = !empty($_POST['dept_prodi']) ? $_POST['dept_prodi'] : $_POST['dept_auto'];

    // Validasi Nomor Telepon
    $no_telp_final = format_no_telp($_POST['kode_negara'], $_POST['no_telp']);

    if (cek_telp_ganda($no_telp_final, $id)) {
        set_notifikasi('error', 'Gagal! Nomor telepon tersebut sudah digunakan oleh akun lain.');
        header("Location: edit.php?id=$id");
        exit;
    }

    // Logika Password (Kosong = Tetap pakai yang lama)
    if (empty($_POST['password'])) {
        $password_final = $data['passUser'];
    } else {
        $password_final = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // CATATAN: Email dan ID TIDAK DIMASUKKAN ke query UPDATE karena sifatnya identitas mutlak (Read-Only)
    $query_update = "UPDATE users SET 
                        namaUser = '$nama',
                        noTelp_user = '$no_telp_final',
                        jabatanUser = '$jabatan',
                        passUser = '$password_final',
                        kodeDepartemen = '$dept',
                        statusUser = '$status'
                     WHERE idUser = '$id'";

    if (mysqli_query($koneksi, $query_update)) {
        set_notifikasi('success', 'Data User berhasil diperbarui!');
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
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data User</h5>
            </div>

            <div class="card-body p-4">
                <form action="" method="POST">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-astar fw-bold">ID User</label>
                            <input type="text" class="form-control bg-light text-secondary fw-bold" value="<?= $data['idUser']; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*ID tidak dapat diubah.</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-astar fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="<?= $data['namaUser']; ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Jabatan</label>
                            <input type="text" class="form-control bg-light text-secondary fw-bold" value="<?= $data['jabatanUser']; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*Jabatan tidak dapat diubah.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Departemen / Prodi <span class="text-danger">*</span></label>

                            <?php
                            // LOGIKA TAMPILAN AWAL SEBELUM JAVASCRIPT BEKERJA
                            $is_tendik = ($data['jabatanUser'] == 'Tenaga Pendidik');
                            $display_auto = $is_tendik ? 'none' : 'block';
                            $display_drop = $is_tendik ? 'block' : 'none';

                            // Siapkan teks auto-fill berdasarkan jabatan lama
                            $text_auto = "";
                            if ($data['jabatanUser'] == 'Super Admin') $text_auto = 'SA - System Admin';
                            elseif ($data['jabatanUser'] == 'Finance') $text_auto = 'FIN - Finance';
                            elseif ($data['jabatanUser'] == 'Staff GA' || $data['jabatanUser'] == 'Kepala GA') $text_auto = 'GA - General Affair';
                            ?>

                            <!-- 1. MODE KARYAWAN (Auto-Fill) -->
                            <div id="dept_autofill_container" style="display: <?= $display_auto; ?>;">
                                <input type="text" id="dept_autofill_text" class="form-control bg-light text-secondary fw-bold" value="<?= $text_auto; ?>" readonly>
                                <input type="hidden" name="dept_auto" id="dept_autofill_value" value="<?= $data['kodeDepartemen']; ?>">
                            </div>

                            <!-- 2. MODE TENDIK (Dropdown Prodi) -->
                            <div id="dept_dropdown_container" style="display: <?= $display_drop; ?>;">
                                <?php
                                $opsi_prodi = ['P4' => 'P4', 'TPM' => 'TPM', 'MO' => 'MO', 'MK' => 'MK', 'MI' => 'MI', 'TKBG' => 'TKBG', 'TRPAB' => 'TRPAB', 'TRL' => 'TRL', 'TRPL' => 'TRPL'];
                                echo buat_dropdown_astar('dept_prodi', $opsi_prodi, $data['kodeDepartemen'], false);
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Email User (Akun SSO)</label>
                            <!-- KUNCI EMAIL: Read-only untuk menjaga konsistensi SSO -->
                            <input type="email" class="form-control bg-light text-secondary fw-bold" value="<?= $data['emailUser']; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*Email tidak dapat diubah (Identitas SSO).</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Nomor Telepon <span class="text-danger">*</span></label>
                            <?php
                            $telp_db = $data['noTelp_user'];
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
                    </div>

                    <hr class="my-4">

                    <!-- ZONA SOFT DELETE & PASSWORD -->
                    <div class="row mb-4 align-items-center bg-light p-3 rounded">
                        <div class="col-md-6 border-end">
                            <label class="form-label text-astar fw-bold">Status Akun</label>
                            <input type="text" class="form-control bg-light text-secondary fw-bold" value="<?= $data['statusUser']; ?>" readonly>
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

<!-- PANGGIL SCRIPT DINAMIS DARI FUNCTIONS.PHP UNTUK EFEK JABATAN -->
<?= script_dinamis_jabatan_dept(); ?>

<?php include '../../../components/footer.php'; ?>