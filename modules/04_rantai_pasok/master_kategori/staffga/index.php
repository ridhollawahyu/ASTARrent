<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses Tendik
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Staff GA') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah dinonaktifkan.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

$where_sql = "WHERE statusKategori != 'Nonaktif' AND tipeKategori = 'Fasilitas Non-Akademik'";

$status_terpilih = "";
$kategori_terpilih = "";

// 1. Cek Filter Status Ketersediaan
if (isset($_GET['filter']) && $_GET['filter'] != '') {
    $status_terpilih = mysqli_real_escape_string($koneksi, $_GET['filter']);

    if ($status_terpilih == 'Semua_Termasuk_Arsip') {
        // Tampilkan semua data, override default
        $where_sql = "WHERE tipeKategori = 'Fasilitas Non-Akademik'";
    } else {
        // Tampilkan sesuai yang dipilih user
        $where_sql = "WHERE statusKategori = '$status_terpilih' AND tipeKategori = 'Fasilitas Non-Akademik'";
    }
}

// 2. Cek Filter Kategori (Ditambahkan dengan AND)
if (isset($_GET['kategori']) && $_GET['kategori'] != '') {
    $kategori_terpilih = mysqli_real_escape_string($koneksi, $_GET['kategori']);
    $where_sql .= " AND kategori.idKategori = '$kategori_terpilih'";
}

// Panggil header HTML setelah semua logika selesai
include '../../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">

    <!-- Bagian Header Card -->
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-pc-display me-2"></i>Data Master Kategori</h5>
        <div>
            <a href="../../../dashboards/staffga_home.php" class="btn btn-outline-light btn-sm fw-bold me-2"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <a href="create_fasilitas.php" class="btn btn-light btn-sm fw-bold text-astar">+ Tambah Kategori Fasilitas</a>
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
                $opsi_filter = [
                    '' => '-- Status Default (Aktif) --',
                    'Nonaktif' => 'Arsip (Soft Delete)',
                    'Semua_Termasuk_Arsip' => 'Tampilkan Semua Data'
                ];
                // Panggil fungsi Custom Dropdown kita (Tanpa wajib diisi/false)
                echo buat_dropdown_astar('filter', $opsi_filter, $status_terpilih, false);
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
        <!-- TABEL DATA ASET -->
        <!-- ========================================== -->
        <div class="table-responsive">
            <table class="datatable-astar table table-hover table-striped mb-0 text-center align-middle">
                <thead style="background-color: #f4f6f9; color: #1d4197;">
                    <tr>
                        <th class="text-center pe-5" width="10%">No.</th>
                        <th class="text-start">Nama Kategori</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // QUERY UTAMA (JOIN Kategori dan Kategori + Filter + Ascending)
                    $query_sql = "SELECT * FROM kategori "
                        . $where_sql .
                        " ORDER BY idKategori ASC";
                    $query = mysqli_query($koneksi, $query_sql);

                    $no = 1;

                    while ($data = mysqli_fetch_array($query)) {
                    ?>
                        <tr>
                            <td class="fw-bold pe-5"><?= $no++; ?></td>
                            <td class="text-start"><?= $data['namaKategori']; ?></td>

                            <!-- PEWARNAAN KONDISI FISIK -->
                            <td class="text-secondary"><?= $data['tipeKategori']; ?></td>

                            <!-- PEWARNAAN KETERSEDIAAN (Sesuai Logika Terbaru Anda) -->
                            <td>
                                <?php if ($data['statusKategori'] == 'Aktif'): ?>
                                    <span class="badge bg-success rounded-pill px-3">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill px-3">Nonaktif</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($data['statusKategori'] != 'Nonaktif'): ?>
                                    <!-- Tombol Edit -->
                                    <a href="edit.php?id=<?= $data['idKategori']; ?>" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-pencil-square"></i></a>

                                    <!-- Tombol Soft Delete (Memanggil fungsi dari footer) -->
                                    <button type="button" class="btn btn-danger btn-sm fw-bold" onclick="konfirmasiHapus('delete.php?id=<?= $data['idKategori']; ?>')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Jika data kosong -->
                    <?php if (mysqli_num_rows($query) == 0): ?>
                        <tr>
                            <td colspan="5" class="py-4 text-muted fst-italic">Tidak ada data kategori yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Panggil footer untuk mengaktifkan Modal Pop-Up Delete & Notifikasi
include '../../../../components/footer.php';
?>