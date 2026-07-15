<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses (Hanya Super Admin)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Super Admin.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Super Admin') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}

$adminTerkini = $_SESSION['id'];

// 1. LOGIKA FILTER YANG SUDAH DIPERBAIKI
$where_sql = "WHERE statusUser = 'Aktif' AND idUser != 'SA-00000' AND idUser != '$adminTerkini'"; // Default: Tampilkan yang Aktif saja
$jabatan_terpilih = "";

if (isset($_GET['jabatan']) && $_GET['jabatan'] != '') {
    $jabatan_terpilih = mysqli_real_escape_string($koneksi, $_GET['jabatan']);

    if ($jabatan_terpilih == 'Semua_Termasuk_Arsip') {
        $where_sql = "WHERE 1=1 AND idUser != 'SA-00000' AND idUser != '$adminTerkini'"; // Tampilkan semua tanpa filter status
    } elseif ($jabatan_terpilih == 'Arsip') {
        $where_sql = "WHERE statusUser = 'Nonaktif' AND idUser != 'SA-00000' AND idUser != '$adminTerkini'"; // Filter khusus yang di-soft delete
    } else {
        // Filter berdasarkan Jabatan asli DAN statusnya harus Aktif
        $where_sql = "WHERE jabatanUser = '$jabatan_terpilih' AND statusUser = 'Aktif' AND idUser != 'SA-00000' AND idUser != '$adminTerkini'";
    }
}

include '../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-people-fill me-2"></i>Data Master User</h5>
        <div>
            <a href="../../dashboards/superadmin_home.php" class="btn btn-outline-light btn-sm fw-bold me-2"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <a href="create.php" class="btn btn-light btn-sm fw-bold text-astar">+ Tambah User</a>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- FITUR FILTER -->
        <form method="GET" action="index.php" class="row g-2 align-items-center mb-4 pb-3 border-bottom">
            <div class="col-auto">
                <label class="col-form-label fw-bold" style="color: #1d4197;"><i class="bi bi-funnel-fill me-1"></i> Filter:</label>
            </div>
            <div class="col-md-4">
                <?php
                $opsi_jabatan = [
                    '' => '-- Status Default (Aktif) --',
                    'Tenaga Pendidik' => 'Tenaga Pendidik',
                    'Staff GA' => 'Staff GA',
                    'Kepala GA' => 'Kepala GA',
                    'Finance' => 'Finance',
                    'Super Admin' => 'Super Admin',
                    'Arsip' => 'Lihat Arsip (User Nonaktif)',
                    'Semua_Termasuk_Arsip' => 'Tampilkan Semua Data'
                ];
                echo buat_dropdown_astar('jabatan', $opsi_jabatan, $jabatan_terpilih, false);
                ?>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn fw-bold text-white px-4" style="background-color: #1d4197; border-radius: 8px;">Terapkan</button>
                <a href="index.php" class="btn btn-light fw-bold px-4" style="border: 2px solid #e0e6ed; border-radius: 8px;">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <?php
            $query = mysqli_query($koneksi, "SELECT * FROM users $where_sql ORDER BY idUser ASC");
            if (mysqli_num_rows($query) > 0):
            ?>
                <table class="datatable-astar table table-hover table-striped mb-0  align-middle border">
                    <thead style="background-color: #f4f6f9; color: #1d4197;">
                        <tr>
                            <th class="text-center" width="5%">No.</th>
                            <th>ID User</th>
                            <th>Nama Lengkap</th>
                            <th>Jabatan</th>
                            <th>Departemen</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($data = mysqli_fetch_assoc($query)) {
                        ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="fw-bold"><?= $data['idUser']; ?></td>
                                <td><?= $data['namaUser']; ?></td>
                                <td><span class="badge bg-primary text-white px-3"><?= $data['jabatanUser']; ?></span></td>
                                <td><span class="badge bg-secondary px-3"><?= $data['kodeDepartemen']; ?></span></td>
                                <td class="text-center">
                                    <?php if ($data['statusUser'] == 'Aktif'): ?>
                                        <span class="badge bg-success rounded-pill px-3">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill px-3">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="edit.php?id=<?= $data['idUser']; ?>" class="btn btn-warning btn-sm shadow-sm" title="Edit Data"><i class="bi bi-pencil-square"></i></a>

                                    <?php if ($data['statusUser'] == 'Aktif'): ?>
                                        <button type="button" class="btn btn-danger btn-sm shadow-sm" onclick="konfirmasiHapus('delete.php?id=<?= $data['idUser']; ?>')" title="Arsipkan">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>

                    </tbody>
                </table>
            <?php else: ?>
                <!-- PESAN KOSONG DITAMPILKAN DILUAR TABEL JIKA DATA 0 -->
                <div class="text-center py-5">
                    <i class="bi bi-check-circle-fill text-success d-block mb-3" style="font-size: 4rem;"></i>
                    <h4 class="text-success fw-bold">Aman!</h4>
                    <p class="text-muted">Tidak ada data Pengguna.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../../../components/footer.php'; ?>