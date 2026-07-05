<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses Tendik
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header("Location: ../../../00_auth/login.php");
    exit;
}

$where_sql = "WHERE fasilitas.ketersediaanFasilitas != 'Tidak Tersedia' AND fasilitas.ketersediaanFasilitas != 'Nonaktif' AND fasilitas.tipeFasilitas = 'Akademik'";

$status_terpilih = "";
$kategori_terpilih = "";

// 1. Cek Filter Status Ketersediaan
if (isset($_GET['ketersediaan']) && $_GET['ketersediaan'] != '') {
    $status_terpilih = mysqli_real_escape_string($koneksi, $_GET['ketersediaan']);

    if ($status_terpilih == 'Semua_Termasuk_Arsip') {
        // Tampilkan semua data, override default
        $where_sql = "WHERE fasilitas.tipeFasilitas = 'Akademik'";
    } else {
        // Tampilkan sesuai yang dipilih user
        $where_sql = "WHERE fasilitas.ketersediaanFasilitas = '$status_terpilih' AND fasilitas.tipeFasilitas = 'Akademik'";
    }
}

// 2. Cek Filter Kategori (Ditambahkan dengan AND)
if (isset($_GET['kategori']) && $_GET['kategori'] != '') {
    $kategori_terpilih = mysqli_real_escape_string($koneksi, $_GET['kategori']);
    $where_sql .= " AND fasilitas.idKategori = '$kategori_terpilih'";
}

// Panggil header HTML setelah semua logika selesai
include '../../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">

    <!-- Bagian Header Card -->
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-pc-display me-2"></i>Data Master Fasilitas</h5>
        <div>
            <a href="../../dashboards/tendik_home.php" class="btn btn-outline-light btn-sm fw-bold me-2"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <a href="create.php" class="btn btn-light btn-sm fw-bold text-astar">+ Tambah Fasilitas Manual</a>
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
                    'Tersedia' => 'Hanya Tersedia',
                    'Dipinjam' => 'Sedang Dipinjam',
                    'Sedang Diperbaiki' => 'Sedang Diperbaiki (Reparasi)',
                    'Tidak Tersedia' => 'Tidak Tersedia (Rusak)',
                    'Nonaktif' => 'Arsip (Soft Delete)',
                    'Semua_Termasuk_Arsip' => 'Tampilkan Semua Data'
                ];
                // Panggil fungsi Custom Dropdown kita (Tanpa wajib diisi/false)
                echo buat_dropdown_astar('ketersediaan', $opsi_status, $status_terpilih, false);
                ?>
            </div>

            <!-- 2. Dropdown Filter Kategori -->
            <div class="col-md-3 col-sm-6">
                <?php
                // Ambil list Kategori Tipe 'Fasilitas' dari Database
                $pilihan_kategori = ambil_pilihan_kategori('Fasilitas Akademik');
                $pilihan_filter_kategori = ['' => '-- Semua Kategori --'] + $pilihan_kategori;

                echo buat_dropdown_astar('kategori', $pilihan_filter_kategori, $kategori_terpilih, false);
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
        <!-- TABEL DATA Fasilitas -->
        <!-- ========================================== -->
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 text-center align-middle">
                <thead style="background-color: #f4f6f9; color: #1d4197;">
                    <tr>
                        <th class="text-center" width="5%">No.</th>
                        <th>Kategori</th>
                        <th>Nama Fasilitas</th>
                        <th>Lokasi Fasilitas</th>
                        <th>Kondisi Fisik</th>
                        <th>Ketersediaan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // QUERY UTAMA (JOIN Fasilitas dan Kategori + Filter + Ascending)
                    $query_sql = "SELECT fasilitas.*, kategori.namaKategori 
                                  FROM fasilitas 
                                  JOIN kategori ON fasilitas.idKategori = kategori.idKategori "
                        . $where_sql .
                        " ORDER BY fasilitas.idFasilitas ASC";
                    $query = mysqli_query($koneksi, $query_sql);

                    $no = 1;

                    while ($data = mysqli_fetch_array($query)) {
                    ?>
                        <tr>
                            <td class="fw-bold"><?= $no++; ?></td>
                            <td><span class="badge bg-secondary"><?= $data['namaKategori']; ?></span></td>
                            <td class="text-center"><?= $data['namaFasilitas']; ?></td>
                            <td class="text-center"><?= $data['lokasiFasilitas']; ?></td>

                            <!-- PEWARNAAN KONDISI FISIK -->
                            <td>
                                <?php if ($data['kondisiFasilitas'] == 'Normal') echo '<span class="text-success fw-bold">Normal</span>';
                                else if ($data['kondisiFasilitas'] == 'Tidak Berfungsi') echo '<span class="text-danger fw-bold">Tidak Berfungsi</span>';
                                else echo '<span class="text-warning text-dark fw-bold">' . $data['kondisiFasilitas'] . '</span>';
                                ?>
                            </td>

                            <!-- PEWARNAAN KETERSEDIAAN (Sesuai Logika Terbaru Anda) -->
                            <td>
                                <?php if ($data['ketersediaanFasilitas'] == 'Tersedia'): ?>
                                    <span class="badge bg-success rounded-pill px-3">Tersedia</span>
                                <?php elseif ($data['ketersediaanFasilitas'] == 'Dipinjam'): ?>
                                    <span class="badge bg-primary rounded-pill px-3">Dipinjam</span>
                                <?php elseif ($data['ketersediaanFasilitas'] == 'Sedang Diperbaiki'): ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3">Sedang Diperbaiki</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill px-3">Tidak Tersedia</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($data['ketersediaanFasilitas'] != 'Tidak Tersedia'): ?>
                                    <!-- Tombol Edit -->
                                    <a href="edit.php?id=<?= $data['idFasilitas']; ?>" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-pencil-square"></i></a>

                                    <!-- Tombol Soft Delete (Memanggil fungsi dari footer) -->
                                    <button type="button" class="btn btn-danger btn-sm fw-bold" onclick="konfirmasiHapus('delete.php?id=<?= $data['idFasilitas']; ?>')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                <?php endif; ?>
                        </tr>
                    <?php } ?>

                    <!-- Jika data kosong -->
                    <?php if (mysqli_num_rows($query) == 0): ?>
                        <tr>
                            <td colspan="7" class="py-4 text-muted fst-italic">Tidak ada data Fasilitas yang ditemukan.</td>
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