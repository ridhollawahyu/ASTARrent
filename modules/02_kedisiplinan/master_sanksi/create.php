<?php
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses Tendik
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Super Admin.');
    header('Location: ../../00_auth/login.php');
    exit;
}

if (isset($_POST['submit'])) {
    // 1. GENERATE ID OTOMATIS (Prefix AST, tabel sanksi, PK idSanksi) - Format 5 Digit
    $id_otomatis = generate_id('SNK', 'sanksi', 'idSanksi');

    // 2. TANGKAP INPUTAN
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jamMinus = mysqli_real_escape_string($koneksi, $_POST['jamMinus']);
    $denda = mysqli_real_escape_string($koneksi, $_POST['denda']);

    // 3. INSERT KE DATABASE (Kondisi & Ketersediaan diisi Default oleh MySQL)
    $query_simpan = "INSERT INTO sanksi (idSanksi, namaSanksi, sanksi_jamMinus, sanksi_denda) 
                     VALUES ('$id_otomatis', '$nama', $jamMinus, $denda)";

    if (mysqli_query($koneksi, $query_simpan)) {
        set_notifikasi('success', "Sukses! Sanksi baru ditambahkan dengan ID: $id_otomatis");
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
                <h5 class="mb-0 fw-bold"><i class="bi bi-pc-display me-2"></i>Tambah Sanksi Manual</h5>
            </div>
            <div class="card-body p-4">

                <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <strong>Info:</strong> ID Sanksi akan di-generate otomatis (SNK-00001). Kondisi awal diset <b>Aktif</b>.
                </div>

                <form action="" method="POST">
                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Nama Sanksi <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" required placeholder="Contoh: Telat Kurang dari 1 Jam">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Jam Minus (jam/hour) <span class="text-danger">*</span></label>
                        <input type="number" name="jamMinus" class="form-control" required>
                        <small class="text-secondary mt-1 d-block" style="font-size:11px;">*Boleh kosong/0.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Denda (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="denda" class="form-control" required>
                        <small class="text-secondary mt-1 d-block" style="font-size:11px;">*Boleh kosong/0.</small>
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