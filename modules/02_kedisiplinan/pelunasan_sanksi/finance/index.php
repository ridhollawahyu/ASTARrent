<?php
// --- FILE: modules/02_kedisiplinan/pelunasan_sanksi/finance/index.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Finance') {
    header('Location: ../../../../00_auth/login.php');
    exit;
}

// FILTER: Finance hanya melihat mahasiswa yang punya tunggakan Denda!
$where_sql = "WHERE dendaMahasiswa > 0";
$prodi_terpilih = "";

if (isset($_GET['prodi']) && $_GET['prodi'] != '') {
    $prodi_terpilih = mysqli_real_escape_string($koneksi, $_GET['prodi']);
    $where_sql .= " AND kodeProdi_mahasiswa = '$prodi_terpilih'";
}

$query = mysqli_query($koneksi, "SELECT * FROM mahasiswa $where_sql ORDER BY dendaMahasiswa DESC");

include '../../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center text-white" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2"></i>Penerimaan Pembayaran Denda</h5>
        <a href="../../../dashboards/finance_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>

    <div class="card-body p-4">

        <form method="GET" action="index.php" class="row g-2 align-items-center mb-4 pb-3 border-bottom">
            <div class="col-auto"><label class="fw-bold text-astar">Filter Prodi:</label></div>
            <div class="col-md-3">
                <?php
                $opsi_prodi = ['' => '-- Semua Prodi --', 'P3P' => 'P3P', 'TPM' => 'TPM', 'MIN' => 'MIN', 'MOT' => 'MOT', 'MEK' => 'MEK', 'TKB' => 'TKB', 'TAB' => 'TAB', 'TRL' => 'TRL', 'RPL' => 'RPL'];
                echo buat_dropdown_astar('prodi', $opsi_prodi, $prodi_terpilih, false);
                ?>
            </div>
            <div class="col-auto"><button type="submit" class="btn btn-astar fw-bold px-3">Filter</button></div>
        </form>

        <div class="table-responsive">
            <?php if (mysqli_num_rows($query) > 0): ?>
                <table class="datatable-astar table table-hover table-striped mb-0  align-middle">
                    <thead style="background-color: #f4f6f9; color: #1d4197;">
                        <tr>
                            <th>No.</th>
                            <th>NIM - Nama Mahasiswa</th>
                            <th>Prodi</th>
                            <th>Tagihan Denda (Rp)</th>
                            <th>Jam Minus</th>
                            <th>Aksi Finance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($data = mysqli_fetch_array($query)): ?>
                            <tr>
                                <td class="fw-bold"><?= $no++ ?></td>
                                <td class="text-start fw-bold text-dark"><?= $data['nimMahasiswa'] ?> <br><small class="text-muted"><?= $data['namaMahasiswa'] ?></small></td>
                                <td><span class="badge bg-secondary"><?= $data['kodeProdi_mahasiswa'] ?></span></td>
                                <td class="text-danger fw-bold fs-5">Rp <?= number_format($data['dendaMahasiswa'], 0, ',', '.') ?></td>
                                <td class="text-muted"><?= $data['jamMinus_mahasiswa'] ?> Jam</td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" onclick="bukaModalLunas('<?= $data['nimMahasiswa'] ?>', '<?= $data['namaMahasiswa'] ?>', 'proses_lunas.php')">
                                        <i class="bi bi-check-circle-fill me-1"></i> Lunasi Denda
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <!-- PESAN KOSONG DITAMPILKAN DILUAR TABEL JIKA DATA 0 -->
                <div class="text-center py-5">
                    <i class="bi bi-check-circle-fill text-success d-block mb-3" style="font-size: 4rem;"></i>
                    <h4 class="text-success fw-bold">Aman!</h4>
                    <p class="text-muted">Tidak ada tunggakan Denda dari Mahasiswa.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../../../components/footer.php'; ?>