<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Mahasiswa') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Mahasiswa.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

$cek_mhs = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE nimMahasiswa = '{$_SESSION["id"]}'");

if (mysqli_num_rows($cek_mhs) === 1) {
    $row = mysqli_fetch_assoc($cek_mhs);

    if ($row["statusMahasiswa"] == "Dibekukan") {
        set_notifikasi('error', 'Akun kamu dibekukan!');
        header("Location: ../../../dashboards/mahasiswa_home.php");
        exit;
    }
}

if (isset($_POST['submit'])) {
    $nim = $_SESSION['id'];
    $keperluan = mysqli_real_escape_string($koneksi, $_POST['keperluan']);
    $tgl_kembali = mysqli_real_escape_string($koneksi, $_POST['tgl_kembali']);
    $tgl_pengajuan = date('Y-m-d H:i:s');

    // TANGKAP INPUTAN XOR
    $idAset = !empty($_POST['aset']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['aset']) . "'" : "NULL";
    $idFasilitas = !empty($_POST['fasilitas']) ? "'" . mysqli_real_escape_string($koneksi, $_POST['fasilitas']) . "'" : "NULL";

    // VALIDASI LOGIKA XOR (Wajib isi salah satu)
    if (($idAset == "NULL" && $idFasilitas == "NULL") || ($idAset != "NULL" && $idFasilitas != "NULL")) {
        set_notifikasi('error', 'Pilih SATU saja: Aset atau Fasilitas!');
        echo "<script>window.location='create.php';</script>";
        exit;
    }

    $id_otomatis = generate_id('PJM', 'transaksi_peminjaman', 'idPeminjaman');

    $query = "INSERT INTO transaksi_peminjaman (idPeminjaman, tanggalPengajuan, tanggalRencana_kembali, keperluan, nimMahasiswa, idAset, idFasilitas) 
              VALUES ('$id_otomatis', '$tgl_pengajuan', '$tgl_kembali', '$keperluan', '$nim', $idAset, $idFasilitas)";

    if (mysqli_query($koneksi, $query)) {
        set_notifikasi('success', "Request terkirim! Menunggu persetujuan.");
        echo "<script>window.location='index.php';</script>";
        exit;
    } else {
        set_notifikasi('error', 'Gagal memproses pengajuan!');
    }
}
include '../../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cart-plus-fill me-2"></i>Form Pengajuan Peminjaman</h5>
            </div>
            <div class="card-body p-4">
                <div class="alert py-2 mb-4" style="background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Aturan:</strong> Anda hanya dapat meminjam 1 Aset ATAU 1 Fasilitas dalam satu formulir ini.
                </div>

                <form action="" method="POST">
                    <div class="row mb-3 bg-light p-3 rounded border">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Pilih Aset (Elektronik) <span class="text-danger">*</span></label>
                            <?php
                            $pilihan_aset = ambil_barang_tersedia('aset');
                            echo buat_dropdown_astar('aset', $pilihan_aset, '', false);
                            ?>
                        </div>
                        <div class="col-md-6 border-start">
                            <label class="form-label text-astar fw-bold">ATAU Pilih Fasilitas (Ruangan) <span class="text-danger">*</span></label>
                            <?php
                            $pilihan_fasilitas = ambil_barang_tersedia('fasilitas');
                            echo buat_dropdown_astar('fasilitas', $pilihan_fasilitas, '', false);
                            ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-astar fw-bold">Rencana Tanggal & Jam Pengembalian <span class="text-danger">*</span></label>
                        <?= buat_input_datetime('tgl_kembali'); ?>
                        <small class="text-secondary mt-1 d-block" style="font-size:11px;">*Sesuaikan jam jika Anda ingin mengembalikannya nanti atau besok.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Keperluan Peminjaman <span class="text-danger">*</span></label>
                        <textarea name="keperluan" class="form-control" required rows="3" placeholder="Jelaskan untuk kegiatan apa..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="../../../dashboards/mahasiswa_home.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="submit" class="btn btn-astar px-5">Kirim Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../../../../components/footer.php'; ?>