<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
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

// 1. TANGKAP ID
if (!isset($_GET['nim'])) {
    header('Location: index.php');
    exit;
}
$nim = mysqli_real_escape_string($koneksi, $_GET['nim']);

// 2. AMBIL DATA LAMA
$query_data = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE nimMahasiswa = '$nim'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    set_notifikasi('error', 'Data Mahasiswa tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// 3. PROSES JIKA TOMBOL UPDATE DITEKAN
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $prodi = mysqli_real_escape_string($koneksi, $_POST['prodi']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);

    $jamMinus = (int)$_POST['jamMinus'];
    $denda = (int)$_POST['denda'];

    $no_telp_final = format_no_telp($_POST['kode_negara'], $_POST['no_telp']);

    if (empty($_POST['password'])) {
        $password_final = $data['passMahasiswa'];
    } else {
        $password_final = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $query_update = "UPDATE mahasiswa SET 
                        namaMahasiswa = '$nama',
                        kodeProdi_mahasiswa = '$prodi',
                        noTelp_mahasiswa = '$no_telp_final',
                        emailMahasiswa = '$email',
                        passMahasiswa = '$password_final',
                        jamMinus_mahasiswa = $jamMinus,
                        dendaMahasiswa = $denda
                     WHERE nimMahasiswa = '$nim'";

    if (mysqli_query($koneksi, $query_update)) {
        perbarui_status_mahasiswa($nim);
        set_notifikasi('success', 'Data mahasiswa berhasil diperbarui!');
        header('Location: index.php');
        exit;
    } else {
        // PERBAIKAN: Jika error (seperti kepanjangan huruf), munculkan pop-up error dan refresh halaman!
        set_notifikasi('error', 'Gagal memperbarui data! Pastikan input tidak melebihi batas.');
        echo "<script>window.location='edit.php?nim=$nim';</script>";
        exit;
    }
}

// ==========================================
// SETELAH PROSES SELESAI, BARU KITA PANGGIL HTML!
// ==========================================
include '../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Mahasiswa</h5>
            </div>
            <div class="card-body p-4">

                <form action="" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-astar fw-bold">NIM Mahasiswa</label>
                            <input type="text" name="nim" class="form-control bg-light" value="<?= $data['nimMahasiswa']; ?>" readonly>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-astar fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?= $data['namaMahasiswa']; ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Program Studi</label>
                            <input type="text" class="form-control bg-light text-secondary fw-bold" value="<?= $data['kodeProdi_mahasiswa']; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*Prodi tidak dapat diubah karena terikat dengan struktur NIM.</small>
                            <input type="hidden" name="prodi" value="<?= $data['kodeProdi_mahasiswa']; ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Nomor Telepon</label>
                            <?php
                            $telp_database = $data['noTelp_mahasiswa'];

                            // LOGIKA CERDAS MENDETEKSI KODE NEGARA
                            if (strpos($telp_database, '+1') === 0) {
                                $kode_lama = '+1';
                                $nomor_lama = substr($telp_database, 2); // Potong 2 karakter
                            } else if (strpos($telp_database, '+60') === 0) {
                                $kode_lama = '+60';
                                $nomor_lama = substr($telp_database, 3); // Potong 3 karakter
                            } else {
                                // Default +62
                                $kode_lama = '+62';
                                $nomor_lama = (strlen($telp_database) >= 3) ? substr($telp_database, 3) : '';
                            }
                            ?>
                            <?= buat_input_telp($nomor_lama, $kode_lama); ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Email (Akun SSO)</label>
                            <!-- PERBAIKAN: Kunci (Read-Only) agar email tidak bisa diubah! -->
                            <input type="email" name="email" class="form-control bg-light text-secondary fw-bold" value="<?= $data['emailMahasiswa']; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*Email tidak dapat diubah karena merupakan identitas SSO.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Password Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah" minlength="6">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-danger fw-bold mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Zona Sanksi & Status (Hak Super Admin)</h6>

                    <div class="row mb-4 align-items-center bg-light p-3 rounded">
                        <div class="col-md-4">
                            <label class="form-label text-danger fw-bold">Jam Minus</label>
                            <input type="text" name="jamMinus" class="form-control border-danger" value="<?= $data['jamMinus_mahasiswa']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-danger fw-bold">Denda (Rp)</label>
                            <input type="text" name="denda" class="form-control border-danger" value="<?= $data['dendaMahasiswa']; ?>" required>
                        </div>
                        <div class="col-md-4 text-center border-start border-danger">
                            <label class="form-label text-secondary fw-bold">Status Saat Ini</label><br>
                            <?php if ($data['statusMahasiswa'] == 'Normal'): ?>
                                <span class="badge bg-success px-4 py-2 fs-6 shadow-sm">Normal</span>
                            <?php else: ?>
                                <span class="badge bg-danger px-4 py-2 fs-6 shadow-sm">Dibekukan</span>
                            <?php endif; ?>
                            <small class="d-block text-muted mt-2" style="font-size:11px;">*Berubah otomatis jika denda/jam > 0</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="update" class="btn btn-astar px-5">Simpan Perubahan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>