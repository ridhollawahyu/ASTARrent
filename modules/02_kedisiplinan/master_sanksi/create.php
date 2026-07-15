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

if (isset($_POST['submit'])) {
    $id_otomatis = generate_id('SNK', 'sanksi', 'idSanksi');

    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jamMinus = empty($_POST['jamMinus']) ? 0 : (int)$_POST['jamMinus'];
    $denda_str = empty($_POST['denda']) ? '0' : $_POST['denda'];
    $denda = (int)str_replace('.', '', $denda_str);

    // TANGKAP INPUTAN TRIGGER BARU
    $klasifikasi_waktu = mysqli_real_escape_string($koneksi, $_POST['klasifikasi_waktu']);
    $klasifikasi_kondisi = mysqli_real_escape_string($koneksi, $_POST['klasifikasi_kondisi']);

    $query_simpan = "INSERT INTO sanksi (idSanksi, namaSanksi, sanksi_jamMinus, sanksi_denda, klasifikasi_waktu, klasifikasi_kondisi) 
                     VALUES ('$id_otomatis', '$nama', $jamMinus, $denda, '$klasifikasi_waktu', '$klasifikasi_kondisi')";

    if (mysqli_query($koneksi, $query_simpan)) {
        set_notifikasi('success', "Sukses! Sanksi baru ditambahkan dengan ID: $id_otomatis");
        header('Location: index.php');
        exit;
    } else {
        set_notifikasi('error', 'Gagal menyimpan data ke database!');
    }
}
?>

<!-- HTML VIEW -->
<?php include '../../../components/header.php'; ?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-slash-circle me-2"></i>Tambah Sanksi & Aturan Sistem Pakar</h5>
            </div>
            <div class="card-body p-4">

                <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
                    <i class="bi bi-robot me-2"></i> <strong>Sistem Pakar:</strong> Tentukan <i>Trigger</i> (Pemicu) agar sistem otomatis menjatuhkan sanksi ini saat barang dikembalikan. Pilih <b>"Manual"</b> jika sanksi hanya dijatuhkan secara spesifik/manual.
                </div>

                <form action="" method="POST">
                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Nama Sanksi <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required placeholder="Contoh: Telat Kurang dari 1 Jam & Barang Normal">
                    </div>

                    <div class="row mb-4 bg-light p-3 rounded border">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold"><i class="bi bi-clock-history me-1"></i> Klasifikasi Waktu Sanksi <span class="text-danger">*</span></label>
                            <?php
                            $opsi_waktu = [
                                'Manual' => 'Pilih Kondisi Waktu',
                                'Tepat Waktu' => 'Tepat Waktu',
                                'Telat < 24 Jam' => 'Telat < 24 Jam',
                                'Telat 1-3 Hari' => 'Telat 1-3 Hari',
                                'Telat > 3 Hari' => 'Telat > 3 Hari',
                                'Manual' => 'Lainnya / Sanksi Khusus (Manual)'
                            ];
                            echo buat_dropdown_astar('klasifikasi_waktu', $opsi_waktu, 'Manual');
                            ?>
                        </div>
                        <div class="col-md-6 border-start">
                            <label class="form-label text-astar fw-bold"><i class="bi bi-box me-1"></i> Klasifikasi Kondisi Fisik Sanksi <span class="text-danger">*</span></label>
                            <?php
                            $opsi_kondisi = [
                                'Manual' => 'Pilih Kondisi Fisik',
                                'Normal' => 'Normal / Aman',
                                'Berfungsi' => 'Rusak (Masih Berfungsi)',
                                'Tidak Berfungsi' => 'Rusak (Tidak Berfungsi)',
                                'Manual' => 'Lainnya / Sanksi Khusus (Manual)'
                            ];
                            echo buat_dropdown_astar('klasifikasi_kondisi', $opsi_kondisi, 'Manual');
                            ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-danger fw-bold">Hukuman: Jam Minus (Jam)</label>
                            <input type="number" name="jamMinus" class="form-control border-danger" placeholder="0">
                            <small class="text-secondary mt-1 d-block" style="font-size:11px;">*Kosongkan jika tidak ada hukuman jam.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-danger fw-bold">Hukuman: Denda (Rp)</label>
                            <input type="text" name="denda" class="form-control border-danger" placeholder="0" oninput="formatRupiahASTAR(this)">
                            <small class="text-secondary mt-1 d-block" style="font-size:11px;">*Kosongkan jika tidak ada denda uang.</small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4 border-top pt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="submit" class="btn btn-astar px-5 fw-bold">Simpan Aturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>