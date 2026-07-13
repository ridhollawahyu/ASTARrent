<?php
// --- FILE: modules/01_reservasi/transaksi_peminjaman/mahasiswa/index.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Mahasiswa') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Mahasiswa.');
    header('Location: ../../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Mahasiswa') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

validasi_kadaluwarsa_peminjaman();

$nim_login = $_SESSION['id'];

include '../../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-clock-history me-2"></i>Riwayat Peminjaman Anda</h5>
        <div>
            <a href="../../../dashboards/mahasiswa_home.php" class="btn btn-outline-light btn-sm fw-bold me-2"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <a href="create.php" class="btn btn-light btn-sm fw-bold text-astar"><i class="bi bi-plus-circle me-1"></i> Ajukan Baru</a>
        </div>
    </div>

    <div class="card-body p-4">

        <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> <strong>Info:</strong> Harap kembalikan barang tepat waktu sebelum "Rencana Kembali" untuk menghindari sanksi denda atau pembekuan akun.
        </div>

        <div class="table-responsive">
            <table class="datatable-astar table table-hover align-middle text-center">
                <thead style="background-color: #f4f6f9; color: #1d4197;">
                    <tr>
                        <th class="text-center" width="5%">No.</th>
                        <th>Tanggal Pengajuan</th>
                        <th class="text-start">Barang yang Dipinjam</th>
                        <th>Rencana Kembali</th>
                        <th>Status & Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_sql = "
                        SELECT tp.*, a.namaAset, f.namaFasilitas 
                        FROM transaksi_peminjaman tp
                        LEFT JOIN aset a ON tp.idAset = a.idAset
                        LEFT JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
                        WHERE tp.nimMahasiswa = '$nim_login'
                        ORDER BY tp.tanggalPengajuan DESC
                    ";
                    $query = mysqli_query($koneksi, $query_sql);

                    $no = 1;

                    while ($data = mysqli_fetch_array($query)) {
                        if ($data['idAset'] != NULL) {
                            $nama_barang = "<span class='badge bg-secondary me-1'>Aset</span> " . $data['namaAset'];
                        } else {
                            $nama_barang = "<span class='badge bg-dark me-1'>Fasilitas</span> " . $data['namaFasilitas'];
                        }
                    ?>
                        <tr>
                            <td class="fw-bold"><?= $no++; ?></td>
                            <td><?= date('d M Y, H:i', strtotime($data['tanggalPengajuan'])); ?></td>
                            <td class="text-start fw-bold text-secondary"><?= $nama_barang; ?></td>
                            <td class="text-danger fw-bold"><?= date('d M Y, H:i', strtotime($data['tanggalRencana_kembali'])); ?></td>

                            <!-- STATUS & ALASAN PENOLAKAN -->
                            <td>
                                <?php if ($data['statusPeminjaman'] == 'Menunggu'): ?>
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Menunggu</span>

                                <?php elseif ($data['statusPeminjaman'] == 'Disetujui'): ?>
                                    <span class="badge bg-primary px-3 py-2 rounded-pill">Disetujui (Sedang Dipinjam)</span>

                                <?php elseif ($data['statusPeminjaman'] == 'Ditolak'): ?>
                                    <span class="badge bg-danger px-3 py-2 rounded-pill mb-1">Ditolak</span><br>

                                    <!-- Tombol Info Alasan Penolakan -->
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-1" style="font-size: 11px; padding: 2px 8px; border-radius: 5px;"
                                        onclick="lihatDetailTeks('<?= htmlspecialchars(addslashes($data['alasanPenolakan_peminjaman'] ?? 'Tidak ada alasan.')) ?>')">
                                        <i class="bi bi-info-circle me-1"></i> Cek Alasan
                                    </button>

                                <?php else: ?>
                                    <span class="badge bg-success px-3 py-2 rounded-pill">Selesai Dikembalikan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if (mysqli_num_rows($query) == 0): ?>
                        <tr>
                            <td colspan="5" class="py-4 text-muted fst-italic">Anda belum pernah melakukan transaksi peminjaman.</td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../../../components/footer.php'; ?>