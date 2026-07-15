<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses Tendik
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Super Admin.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Super Admin') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}

$where_sql = "WHERE sanksi.statusSanksi != 'Nonaktif'";
$status_terpilih = "";

// 1. Cek Filter Status Ketersediaan
if (isset($_GET['status_sanksi']) && $_GET['status_sanksi'] != '') {
    $status_terpilih = mysqli_real_escape_string($koneksi, $_GET['status_sanksi']);

    if ($status_terpilih == 'Semua_Termasuk_Arsip') {
        // Tampilkan semua data, override default
        $where_sql = "WHERE 1=1";
    } else {
        // Tampilkan sesuai yang dipilih user
        $where_sql = "WHERE sanksi.statusSanksi = '$status_terpilih'";
    }
}

// Panggil header HTML setelah semua logika selesai
include '../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">

    <!-- Bagian Header Card -->
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-pc-display me-2"></i>Data Master Sanksi</h5>
        <div>
            <a href="../../dashboards/superadmin_home.php" class="btn btn-outline-light btn-sm fw-bold me-2"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <a href="create.php" class="btn btn-light btn-sm fw-bold text-astar">+ Tambah Sanksi Manual</a>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- ========================================== -->
        <!-- FITUR FILTER GANDA (TAMPILAN UI) -->
        <!-- ========================================== -->
        <form method="GET" action="index.php" class="row g-2 align-items-center mb-4 pb-3 border-bottom">
            <div class="col-auto">
                <label class="col-form-label fw-bold" style="color: #1d4197;"><i class="bi bi-funnel-fill me-1"></i> Filter:</label>
            </div>

            <!-- 1. Dropdown Filter Status Ketersediaan -->
            <div class="col-md-3 col-sm-6">
                <?php
                $opsi_status = [
                    '' => '-- Status Default (Aktif) --',
                    'Nonaktif' => 'Arsip (Soft Delete)',
                    'Semua_Termasuk_Arsip' => 'Tampilkan Semua Data'
                ];
                // Panggil fungsi Custom Dropdown kita (Tanpa wajib diisi/false)
                echo buat_dropdown_astar('status_sanksi', $opsi_status, $status_terpilih, false);
                ?>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn fw-bold text-white px-4" style="background-color: #1d4197; border-radius: 8px;">
                    Terapkan
                </button>
                <a href="index.php" class="btn btn-light fw-bold px-4" style="border: 2px solid #e0e6ed; border-radius: 8px; color: #1d4197;">Reset</a>
            </div>
        </form>

        <!-- ========================================== -->
        <!-- TABEL DATA SANKSI -->
        <!-- ========================================== -->
        <div class="table-responsive">
            <?php
            // QUERY UTAMA (JOIN Sanksi dan Kategori + Filter + Ascending)
            $query_sql = "SELECT * FROM sanksi " . $where_sql . " ORDER BY idSanksi ASC";
            $query = mysqli_query($koneksi, $query_sql);
            if (mysqli_num_rows($query) > 0):
            ?>
                <table class="datatable-astar table table-hover table-striped mb-0  align-middle">
                    <thead style="background-color: #f4f6f9; color: #1d4197;">
                        <tr>
                            <th class="text-center pe-5" width="10%">No.</th>
                            <th>Nama Sanksi</th>
                            <th>Jam Minus (Jam/Hour)</th>
                            <th>Denda (Rp)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;

                        while ($data = mysqli_fetch_array($query)) {
                        ?>
                            <tr>
                                <td class="fw-bold pe-5"><?= $no++; ?></td>
                                <td><?= $data['namaSanksi']; ?></td>
                                <td class="text-center"><?= $data['sanksi_jamMinus']; ?></td>
                                <td class="text-center"><?= $data['sanksi_denda']; ?></td>

                                <td>
                                    <?php if ($data['statusSanksi'] == 'Aktif'): ?>
                                        <!-- Tombol Edit -->
                                        <a href="edit.php?id=<?= $data['idSanksi']; ?>" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-pencil-square"></i></a>

                                        <!-- Tombol Soft Delete (Memanggil fungsi dari footer) -->
                                        <button type="button" class="btn btn-danger btn-sm fw-bold" onclick="konfirmasiHapus('delete.php?id=<?= $data['idSanksi']; ?>')">
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
                    <p class="text-muted">Tidak ada data Sanksi.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Panggil footer untuk mengaktifkan Modal Pop-Up Delete & Notifikasi
include '../../../components/footer.php';
?>