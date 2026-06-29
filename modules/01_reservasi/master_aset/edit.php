<?php
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header('Location: ../../00_auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// AMBIL DATA ASET (JOIN DENGAN KATEGORI BIAR DAPAT NAMA KATEGORINYA)
$query_data = mysqli_query($koneksi, "SELECT aset.*, kategori.namaKategori FROM aset JOIN kategori ON aset.idKategori = kategori.idKategori WHERE idAset = '$id'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    set_notifikasi('error', 'Data Aset tidak ditemukan!');
    echo "<script>window.location='index.php';</script>";
    exit;
}

// PROSES UPDATE
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kondisi = mysqli_real_escape_string($koneksi, $_POST['kondisi']);

    // UPDATE DATABASE (Kita cuma nge-update nama dan kondisi. Kategori dan Ketersediaan gak usah dimasukin query!)
    $query_update = "UPDATE aset SET 
                        namaAset = '$nama',
                        kondisiAset = '$kondisi'
                     WHERE idAset = '$id'";

    if (mysqli_query($koneksi, $query_update)) {

        // 🔥 LOGIKA STATE MACHINE (TRIGGER OTOMATIS)
        // Jika Tendik lapor aset ini Rusak Total, maka status ketersediaan otomatis jadi Rusak Total!
        if ($kondisi == 'Rusak Total') {
            perbarui_status_barang('aset', $id, 'Tidak Tersedia');
        }

        set_notifikasi('success', 'Data Aset berhasil diperbarui!');
        echo "<script>window.location='index.php';</script>";
        exit;
    } else {
        set_notifikasi('error', 'Gagal memperbarui data!');
    }
}

include '../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Aset</h5>
            </div>
            <div class="card-body p-4">

                <form action="" method="POST">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">ID Aset / Barcode</label>
                            <input type="text" class="form-control bg-light fw-bold text-secondary" value="<?= $data['idAset']; ?>" readonly>
                            <small class="text-danger mt-1" style="font-size:11px;">*ID Aset tidak dapat diubah.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Kategori Aset</label>
                            <!-- KUNCI KATEGORI: Pakai input text biasa yang readonly -->
                            <input type="text" class="form-control bg-light fw-bold text-secondary" value="<?= $data['namaKategori']; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*Kategori aset tidak dapat diubah.</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Nama Aset (Merek/Tipe)</label>
                        <input type="text" name="nama" class="form-control" value="<?= $data['namaAset']; ?>" required>
                    </div>

                    <hr class="my-4">

                    <div class="row mb-4 bg-light p-3 rounded align-items-center">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Update Kondisi Fisik</label>
                            <input type="text" class="form-control bg-light fw-bold text-secondary" value="<?= $data['kondisiAset']; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*Kondisi fisik Aset tidak dapat diubah.</small>
                        </div>
                        <div class="col-md-6 text-center border-start">
                            <label class="form-label text-secondary fw-bold">Status Ketersediaan</label><br>

                            <!-- BADGE STATUS KETERSEDIAAN (Read-Only) -->
                            <?php if ($data['ketersediaanAset'] == 'Tersedia'): ?>
                                <span class="text-success fw-bold px-4 py-2 fs-6">Tersedia</span>

                            <?php elseif ($data['ketersediaanAset'] == 'Dipinjam'): ?>
                                <!-- Warna Biru Primary untuk Dipinjam -->
                                <span class="text-primary fw-bold px-4 py-2 fs-6">Dipinjam</span>

                            <?php elseif ($data['ketersediaanAset'] == 'Sedang Diperbaiki'): ?>
                                <span class="text-warning fw-bold text-dark px-4 py-2 fs-6">Sedang Diperbaiki</span>

                            <?php else: ?>
                                <!-- Warna Abu-abu Gelap untuk Soft Delete (Tidak Tersedia) -->
                                <span class="text-secondary fw-bold px-4 py-2 fs-6">Tidak Tersedia</span>
                            <?php endif; ?>

                            <small class="d-block text-muted mt-2" style="font-size:11px;">*Berubah otomatis berdasarkan Peminjaman/Reparasi.</small>
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