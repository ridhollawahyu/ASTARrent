<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */
// ==========================================
// 1. VALIDASI KEAMANAN (HARUS DI ATAS HEADER HTML!)
// ==========================================
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Super Admin') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Super Admin.');
    header('Location: ../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Super Admin') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../00_auth/login.php');
    exit;
}

// ==========================================
// 2. LOGIKA FILTER PRODI
// ==========================================
$where_sql = "WHERE mahasiswa.statusMahasiswa != 'Nonaktif' ";

$prodi_terpilih = "";
$status_terpilih = "";

if (isset($_GET['status']) && $_GET['status'] != '') {
    $status_terpilih = mysqli_real_escape_string($koneksi, $_GET['status']);

    if ($status_terpilih == 'Semua_Termasuk_Arsip') {
        // Tampilkan semua data, override default
        $where_sql = "WHERE 1=1";
    } else {
        // Tampilkan sesuai yang dipilih user
        $where_sql = "WHERE mahasiswa.statusMahasiswa = '$status_terpilih'";
    }
}

if (isset($_GET['prodi']) && $_GET['prodi'] != '') {
    $prodi_terpilih = mysqli_real_escape_string($koneksi, $_GET['prodi']);
    $where_sql .= " AND kodeProdi_mahasiswa = '$prodi_terpilih' ";
}

// ==========================================
// 3. BARU PANGGIL HEADER HTML
// ==========================================
include '../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">

    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-people-fill me-2"></i>Data Master Mahasiswa</h5>
        <div>
            <a href="../../dashboards/superadmin_home.php" class="btn btn-outline-light btn-sm fw-bold me-2"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <a href="create.php" class="btn btn-light btn-sm fw-bold text-astar">+ Tambah Mahasiswa</a>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- FITUR FILTER PRODI -->
        <form method="GET" action="index.php" class="row g-3 align-items-center mb-4 pb-3 border-bottom">
            <div class="col-auto">
                <label class="col-form-label fw-bold" style="color: #1d4197;"><i class="bi bi-filter-circle me-1"></i> Filter :</label>
            </div>

            <div class="col-md-3 col-sm-6">
                <?php
                $pilihan_filter = [
                    '' => '-- Tampilkan Semua --',
                    'P3P' => 'P3P',
                    'TPM' => 'TPM',
                    'MIN' => 'MIN',
                    'MOT' => 'MOT',
                    'MEK' => 'MEK',
                    'TKB' => 'TKB',
                    'TAB' => 'TAB',
                    'TRL' => 'TRL',
                    'RPL' => 'RPL'
                ];
                echo buat_dropdown_astar('prodi', $pilihan_filter, $prodi_terpilih, false);
                ?>
            </div>

            <div class="col-md-3 col-sm-6">
                <?php
                $opsi_status = [
                    '' => '-- Status Default (Normal) --',
                    'Normal' => 'Normal',
                    'Dibekukan' => 'Suspended',
                    'Nonaktif' => 'Arsip',
                    'Semua_Termasuk_Arsip' => 'Tampilkan Semua Data'
                ];
                // Panggil fungsi Custom Dropdown kita (Tanpa wajib diisi/false)
                echo buat_dropdown_astar('status', $opsi_status, $status_terpilih, false);
                ?>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn fw-bold text-white px-4" style="background-color: #1d4197; border-radius: 8px;">
                    Terapkan
                </button>
                <a href="index.php" class="btn btn-light fw-bold px-4" style="border: 2px solid #e0e6ed; border-radius: 8px; color: #1d4197;">Reset</a>
            </div>
        </form>

        <!-- TABEL DATA MAHASISWA -->
        <div class="table-responsive">
            <?php
            $query_sql = "SELECT * FROM mahasiswa " . $where_sql . " ORDER BY nimMahasiswa ASC";
            $query = mysqli_query($koneksi, $query_sql);
            if (mysqli_num_rows($query) > 0):
            ?>
                <table class="datatable-astar table table-hover table-striped mb-0  align-middle">
                    <thead style="background-color: #f4f6f9; color: #1d4197;">
                        <tr>
                            <th class="text-center" width="5%">No.</th>
                            <th class="text-center">NIM</th>
                            <th>Nama Lengkap</th>
                            <th>Prodi</th>
                            <th>No. Telp</th>
                            <th>Email</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;

                        while ($data = mysqli_fetch_array($query)) {
                        ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++; ?></td>
                                <td class="fw-bold"><?= $data['nimMahasiswa']; ?></td>
                                <td><?= $data['namaMahasiswa']; ?></td>
                                <td><span class="badge bg-secondary"><?= $data['kodeProdi_mahasiswa']; ?></span></td>
                                <td><?= $data['noTelp_mahasiswa']; ?></td>
                                <td><?= $data['emailMahasiswa']; ?></td>
                                <td class="text-center">
                                    <?php if ($data['statusMahasiswa'] == 'Normal'): ?>
                                        <span class="badge bg-success rounded-pill px-3">Normal</span>
                                    <?php elseif ($data['statusMahasiswa'] == 'Dibekukan'): ?>
                                        <span class="badge bg-danger rounded-pill px-3">Dibekukan</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark rounded-pill px-3">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">

                                    <a href="edit.php?nim=<?= $data['nimMahasiswa']; ?>" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-pencil-square"></i></a>
                                    <button type="button" class="btn btn-danger btn-sm fw-bold" onclick="konfirmasiHapus('delete.php?nim=<?= $data['nimMahasiswa']; ?>')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
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
                    <p class="text-muted">Tidak ada data Mahasiswa.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>