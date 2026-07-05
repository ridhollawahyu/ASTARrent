<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header('Location: ../../../../00_auth/login.php');
    exit;
}

$dept_tendik = $_SESSION['departemen'];

// Ambil data yang masih 'Disetujui' (Belum kembali)
$query_sql = "
    SELECT tp.*, m.namaMahasiswa, m.nimMahasiswa AS nim, 
           a.namaAset, f.namaFasilitas,
           TIMESTAMPDIFF(HOUR, tp.tanggalRencana_kembali, NOW()) AS jam_terlambat
    FROM transaksi_peminjaman tp
    JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa
    LEFT JOIN aset a ON tp.idAset = a.idAset
    LEFT JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
    WHERE tp.statusPeminjaman = 'Disetujui' AND m.kodeProdi_mahasiswa = '$dept_tendik'
    AND (tp.idAset IS NOT NULL OR f.tipeFasilitas = 'Akademik')
    ORDER BY tp.tanggalRencana_kembali ASC
";
$queryTransaksi = mysqli_query($koneksi, $query_sql);

include '../../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-box-arrow-in-down-left me-2"></i>Antrean Pengembalian Barang</h5>
        <div>
            <a href="../../../dashboards/tendik_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
            <a href="read.php" class="btn btn-light btn-sm fw-bold"><i class="bi bi-archive"></i> Lihat Data Transaksi</a>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff; border-left: 4px solid #1d4197;" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> <strong>Sistem Pakar Sanksi Aktif:</strong> Klik 'Proses' untuk menginput kondisi fisik. Sanksi akan dihitung otomatis.
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead style="background-color: #f4f6f9; color: #1d4197;">
                    <tr>
                        <th width="5%">No.</th>
                        <th class="text-start">Mahasiswa</th>
                        <th class="text-start">Barang yang Dipinjam</th>
                        <th>Batas Kembali</th>
                        <th>Status Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($data = mysqli_fetch_assoc($queryTransaksi)):
                        $is_terlambat = (int)$data['jam_terlambat'] > 0;
                        $nama_barang = !empty($data['idAset']) ? '<span class="text-secondary fw-bold me-1">[Aset]</span>' . $data['namaAset'] : '<span class="text-secondary me-1 fw-bold">[Fasilitas]</span>' . $data['namaFasilitas'];
                    ?>
                        <tr>
                            <td class="fw-bold"><?= $no++ ?></td>
                            <td class="text-start">
                                <div class="fw-bold text-dark"><?= $data['namaMahasiswa'] ?></div>
                                <div class="text-muted" style="font-size:0.8rem;"><?= $data['nim'] ?></div>
                            </td>
                            <td class="text-start fw-semibold text-secondary"><?= $nama_barang ?></td>
                            <td class="fw-bold <?= $is_terlambat ? 'text-danger' : 'text-success' ?>">
                                <?= date('d M Y, H:i', strtotime($data['tanggalRencana_kembali'])) ?>
                            </td>
                            <td>
                                <?php if ($is_terlambat): ?>
                                    <span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-alarm-fill me-1"></i> Telat <?= $data['jam_terlambat'] ?> Jam</span>
                                <?php else: ?>
                                    <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check2-circle me-1"></i> Tepat Waktu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- MENGARAH KE HALAMAN PROSES APPROVE -->
                                <a href="proses_approve.php?id=<?= $data['idPeminjaman'] ?>" class="btn text-white btn-sm fw-bold px-3" style="background-color: #1d4197; border-radius: 8px;">
                                    <i class="bi bi-clipboard-check me-1"></i> Proses
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($queryTransaksi) == 0): ?>
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted fst-italic">Belum ada antrean pengembalian.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../../../components/footer.php'; ?>