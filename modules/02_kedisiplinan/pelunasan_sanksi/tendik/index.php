<?php
// --- FILE: modules/02_kedisiplinan/pelunasan_sanksi/tendik/index.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    header('Location: ../../../../00_auth/login.php');
    exit;
}
$dept_tendik = $_SESSION['departemen'];

// FILTER: Tendik hanya melihat mahasiswa yang punya Jam Minus di Prodinya!
$query = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE jamMinus_mahasiswa > 0 AND kodeProdi_mahasiswa = '$dept_tendik' ORDER BY namaMahasiswa ASC");

include '../../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center text-white" style="background-color: #1d4197;; border-radius: 15px 15px 0 0;">
        <h5 class="mb-0 fw-bold"><i class="bi bi-shield-slash-fill me-2"></i>Penyelesaian Jam Minus Mahasiswa (<?= $dept_tendik ?>)</h5>
        <a href="../../../dashboards/tendik_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>

    <div class="card-body p-4">
        <div class="alert alert-warning fw-bold"><i class="bi bi-info-circle-fill me-2"></i>Tekan tombol Lunas hanya jika mahasiswa telah menyelesaikan kewajiban kompensasi Jam Minus. Akun akan otomatis kembali Normal jika tidak ada denda uang yang tertunggak.</div>

        <div class="table-responsive mt-3">
            <?php if (mysqli_num_rows($query) > 0): ?>
                <table class="datatable-astar table table-hover table-striped mb-0  align-middle">
                    <thead style="background-color: #f4f6f9; color: #1d4197;">
                        <tr>
                            <th class="text-center">No.</th>
                            <th>NIM - Nama Mahasiswa</th>
                            <th class="text-center">Jam Minus</th>
                            <th class="text-center">Denda (Uang)</th>
                            <th class="text-center">Status Saat Ini</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($data = mysqli_fetch_array($query)): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td class="text-start fw-bold text-dark"><?= $data['nimMahasiswa'] ?> <br><small class="text-muted"><?= $data['namaMahasiswa'] ?></small></td>
                                <td class="text-center text-danger fw-bold fs-5"><?= $data['jamMinus_mahasiswa'] ?> Jam</td>
                                <td class="text-center text-muted">Rp <?= number_format($data['dendaMahasiswa'], 0, ',', '.') ?></td>
                                <td class="text-center"><span class="badge bg-danger px-3 py-2">Dibekukan</span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" onclick="bukaModalLunas('<?= $data['nimMahasiswa'] ?>', '<?= $data['namaMahasiswa'] ?>', 'proses_lunas.php')">
                                        <i class="bi bi-check-circle-fill me-1"></i> Lunasi Jam Minus
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
                    <p class="text-muted">Tidak ada tunggakan Jam Minus di Prodi Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../../../components/footer.php'; ?>