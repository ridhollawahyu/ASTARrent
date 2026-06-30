<?php
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

$role_diizinkan = ['Staff GA'];
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], $role_diizinkan, true)) {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../00_auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query_data = mysqli_query($koneksi, "SELECT komponen.*, reparasi_fasilitas_aset.klasifikasiKerusakan, reparasi_fasilitas_aset.statusReparasi,
                                             aset.namaAset, fasilitas.namaFasilitas
                                      FROM komponen
                                      JOIN reparasi_fasilitas_aset ON komponen.idReparasi = reparasi_fasilitas_aset.idReparasi
                                      LEFT JOIN aset ON reparasi_fasilitas_aset.idAset = aset.idAset
                                      LEFT JOIN fasilitas ON reparasi_fasilitas_aset.idFasilitas = fasilitas.idFasilitas
                                      WHERE komponen.idKomponen = '$id'");
$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    set_notifikasi('error', 'Data komponen tidak ditemukan!');
    header('Location: index.php');
    exit;
}

if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['namaKomponen']));
    $spesifikasi = mysqli_real_escape_string($koneksi, trim($_POST['spesifikasiKomponen']));
    $kondisi = mysqli_real_escape_string($koneksi, trim($_POST['kondisiKomponen']));
    $status = mysqli_real_escape_string($koneksi, trim($_POST['statusKomponen']));

    if (strlen($nama) < 3) {
        set_notifikasi('error', 'Nama komponen minimal 3 karakter.');
    } elseif (!kondisi_komponen_valid($kondisi)) {
        set_notifikasi('error', 'Kondisi komponen tidak valid.');
    } elseif (!status_komponen_valid($status)) {
        set_notifikasi('error', 'Status komponen tidak valid.');
    } elseif (komponen_duplikat($data['idReparasi'], $nama, $id)) {
        set_notifikasi('error', 'Komponen dengan nama yang sama sudah ada pada sumber reparasi ini.');
    } else {
        $query_update = "UPDATE komponen SET
                            namaKomponen = '$nama',
                            spesifikasiKomponen = '$spesifikasi',
                            kondisiKomponen = '$kondisi',
                            statusKomponen = '$status'
                         WHERE idKomponen = '$id'";

        if (mysqli_query($koneksi, $query_update)) {
            set_notifikasi('success', 'Data komponen berhasil diperbarui!');
            header('Location: index.php');
            exit;
        } else {
            set_notifikasi('error', 'Gagal memperbarui data komponen! ' . mysqli_error($koneksi));
        }
    }
}

include '../../../components/header.php';
$nama_barang = $data['namaAset'] ?: $data['namaFasilitas'];
if (!$nama_barang) {
    $nama_barang = 'Barang tidak ditemukan';
}
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Komponen</h5>
            </div>
            <div class="card-body p-4">
                <form action="" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">ID Komponen</label>
                            <input type="text" class="form-control bg-light fw-bold text-secondary" value="<?= $data['idKomponen']; ?>" readonly>
                            <small class="text-danger mt-1" style="font-size:11px;">*ID Komponen tidak dapat diubah.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Sumber Reparasi</label>
                            <input type="text" class="form-control bg-light fw-bold text-secondary" value="<?= $data['idReparasi']; ?> - <?= $nama_barang; ?>" readonly>
                            <small class="text-danger mt-1 d-block" style="font-size:11px;">*Sumber reparasi tidak dapat diubah.</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Nama Komponen</label>
                        <input type="text" name="namaKomponen" class="form-control" value="<?= $data['namaKomponen']; ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Spesifikasi Komponen</label>
                        <textarea name="spesifikasiKomponen" class="form-control" rows="3"><?= $data['spesifikasiKomponen']; ?></textarea>
                    </div>

                    <div class="row mb-4 bg-light p-3 rounded align-items-center">
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Kondisi Komponen</label>
                            <?php
                            $opsi_kondisi = [
                                'Sangat Baik' => 'Sangat Baik',
                                'Layak Pakai' => 'Layak Pakai'
                            ];
                            echo buat_dropdown_astar('kondisiKomponen', $opsi_kondisi, $data['kondisiKomponen']);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-astar fw-bold">Status Komponen</label>
                            <?php
                            $opsi_status = [
                                'Tersedia' => 'Tersedia',
                                'Sudah Dipakai' => 'Sudah Dipakai',
                                'Nonaktif' => 'Nonaktif'
                            ];
                            echo buat_dropdown_astar('statusKomponen', $opsi_status, $data['statusKomponen']);
                            ?>
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