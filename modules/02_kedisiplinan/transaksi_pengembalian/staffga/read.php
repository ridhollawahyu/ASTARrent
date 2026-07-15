<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Staff GA') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah dinonaktifkan.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

$dept_tendik = $_SESSION['departemen'];

// Ambil data yang masih 'Disetujui' (Belum kembali)
$query_sql = "
    SELECT tpm.*, tp.*, m.namaMahasiswa, m.nimMahasiswa AS nim, 
           a.namaAset, f.namaFasilitas, u.namaUser,
           udf_hitung_jam_telat(tp.tanggalRencana_kembali, tpm.tanggalPengembalian) AS jam_terlambat
    FROM transaksi_pengembalian tpm
    JOIN transaksi_peminjaman tp ON tpm.idPeminjaman = tp.idPeminjaman
    JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa
    LEFT JOIN aset a ON tp.idAset = a.idAset
    LEFT JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
    LEFT JOIN users u ON tpm.idPengurus = u.idUser
    WHERE f.tipeFasilitas = 'Non-Akademik'
    ORDER BY tpm.tanggalPengembalian ASC
";
$queryTransaksi = mysqli_query($koneksi, $query_sql);

include '../../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-box-arrow-in-down-left me-2"></i>Data Pengembalian Barang/Fasilitas</h5>
        <div>
            <a href="../../../dashboards/staffga_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
            <a href="index.php" class="btn btn-light btn-sm fw-bold"><i class="bi bi-clipboard-check"></i> Lihat Antrean</a>
        </div>
    </div>

    <div class="card-body p-4">

        <div class="table-responsive">
            <?php if (mysqli_num_rows($queryTransaksi) > 0): ?>
                <table class="datatable-astar table table-hover align-middle ">
                    <thead style="background-color: #f4f6f9; color: #1d4197;">
                        <tr>
                            <th class="text-center" width="5%">No.</th>
                            <th>Mahasiswa</th>
                            <th>Barang yang Dipinjam</th>
                            <th>Batas Kembali</th>
                            <th class="text-center">Status Waktu</th>
                            <th class="text-center">Pengurus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($data = mysqli_fetch_assoc($queryTransaksi)):
                            $is_terlambat = (int)$data['jam_terlambat'] > 0;
                            $teks_waktu = format_waktu_terlambat((int)$data['jam_terlambat']);
                            $nama_barang = !empty($data['idAset']) ? $data['namaAset'] : $data['namaFasilitas'];
                            $namaStaffGA = ($data['idPengurus'] != NULL) ? $data['namaUser'] : "Belum Dikelola";
                        ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= $data['namaMahasiswa'] ?></div>
                                    <div class="text-muted" style="font-size:0.8rem;"><?= $data['nim'] ?></div>
                                </td>
                                <td class="text-start fw-semibold text-secondary"><?= $nama_barang ?></td>
                                <td class="fw-bold <?= $is_terlambat ? 'text-danger' : 'text-success' ?>">
                                    <?= date('d M Y, H:i', strtotime($data['tanggalRencana_kembali'])) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_terlambat): ?>
                                        <span class="badge bg-danger rounded-pill px-3 py-2" style="white-space: normal; line-height: 1.5;"><i class="bi bi-alarm-fill me-1"></i> Telat <?= $teks_waktu ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check2-circle me-1"></i> Tepat Waktu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center fw-bold text-secondary"><?= $namaStaffGA; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <!-- PESAN KOSONG DITAMPILKAN DILUAR TABEL JIKA DATA 0 -->
                <div class="text-center py-5">
                    <i class="bi bi-check-circle-fill text-success d-block mb-3" style="font-size: 4rem;"></i>
                    <h4 class="text-success fw-bold">Aman!</h4>
                    <p class="text-muted">Tidak ada data Pengembalian.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../../../components/footer.php'; ?>