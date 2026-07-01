<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

// ==============================================================================
// 1. QUERY SAKTI: Membaca Tabel Tiket Reparasi + Data Barang
// ==============================================================================
$filter_status = "";
$status_terpilih = "";

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status_terpilih = mysqli_real_escape_string($koneksi, $_GET['status']);
    $filter_status = "AND r.statusReparasi = '$status_terpilih'";
} else {
    // Default: Sembunyikan yang sudah selesai atau mati (Dikanibal)
    $filter_status = "AND r.statusReparasi IN ('Menunggu GA', 'Sedang Dikerjakan')";
}

// PERBAIKAN QUERY: Tarik ketersediaanAset dan ketersediaanFasilitas agar bisa dicek!
$query_sql = "
    SELECT r.*, 
           a.namaAset, a.ketersediaanAset, 
           f.namaFasilitas, f.ketersediaanFasilitas, 
           u.namaUser AS namaTendik
    FROM reparasi_fasilitas_aset r
    LEFT JOIN aset a ON r.idAset = a.idAset
    LEFT JOIN fasilitas f ON r.idFasilitas = f.idFasilitas
    LEFT JOIN users u ON r.idTendik = u.idUser
    WHERE 1=1 $filter_status
    ORDER BY r.tanggalLapor ASC
";
$queryReparasi = mysqli_query($koneksi, $query_sql);

include '../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0; padding: 16px 24px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-tools me-2"></i>Daftar Aset & Fasilitas Bermasalah</h5>
        <a href="../../dashboards/staffga_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <div class="card-body p-4">
        <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff; border-left: 4px solid #1d4197;">
            <i class="bi bi-robot me-2"></i> <strong>Sistem Proaktif:</strong> Daftar ini otomatis mendeteksi tiket laporan dari Tendik. Anda tidak bisa memperbaiki barang yang sedang "Dipinjam" oleh mahasiswa.
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead style="background-color: #f4f6f9; color: #1d4197; border-bottom: 2px solid #e0e6ed;">
                    <tr>
                        <th width="5%" class="py-3">No.</th>
                        <th>ID Tiket</th>
                        <th class="text-start">Barang yang Rusak</th>
                        <th>Klasifikasi</th>
                        <th>Ketersediaan</th>
                        <th>Aksi GA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($data = mysqli_fetch_assoc($queryReparasi)):

                        // =========================================================
                        // PERBAIKAN LOGIKA VARIABEL (Sihir XOR)
                        // Karena data Aset dan Fasilitas terpisah kolomnya, 
                        // kita harus menyatukannya pakai PHP If-Else (Ternary)
                        // =========================================================
                        $tipe_barang = !empty($data['idAset']) ? 'Aset' : 'Fasilitas';
                        $id_barang = ($tipe_barang == 'Aset') ? $data['idAset'] : $data['idFasilitas'];
                        $nama_barang = ($tipe_barang == 'Aset') ? $data['namaAset'] : $data['namaFasilitas'];
                        $ketersediaan = ($tipe_barang == 'Aset') ? $data['ketersediaanAset'] : $data['ketersediaanFasilitas'];

                        $is_dipinjam = ($ketersediaan == 'Dipinjam');
                        $is_diperbaiki = ($data['statusReparasi'] == 'Sedang Dikerjakan'); // Patokan status tiket!
                    ?>
                        <tr>
                            <td class="fw-bold"><?= $no++ ?></td>
                            <td><code class="text-primary fw-bold"><?= $data['idReparasi'] ?></code></td>
                            <td class="text-start fw-bold text-secondary">
                                <span class="badge bg-<?= ($tipe_barang == 'Aset') ? 'secondary' : 'dark' ?> me-1"><?= $tipe_barang ?></span>
                                <?= $nama_barang ?>
                                <br><small class="text-muted fw-normal">ID: <?= $id_barang ?></small>
                            </td>
                            <td>
                                <!-- Ambil Klasifikasi dari Tabel Tiket, bukan Master -->
                                <?php if ($data['klasifikasiKerusakan'] == 'Berfungsi'): ?>
                                    <span class="text-warning fw-bold text-dark">Berfungsi (Minus)</span>
                                <?php else: ?>
                                    <span class="text-danger fw-bold">Tidak Berfungsi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Badge Ketersediaan -->
                                <?php if ($is_dipinjam): ?>
                                    <span class="badge bg-primary px-3 py-2">Sedang Dipinjam</span>
                                <?php elseif ($is_diperbaiki): ?>
                                    <span class="badge bg-warning text-dark px-3 py-2">Sedang Diperbaiki</span>
                                <?php else: ?>
                                    <span class="badge bg-success px-3 py-2">Tersedia (Di Gudang)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Tombol Aksi Menyesuaikan Status Tiket -->
                                <?php if ($is_dipinjam): ?>
                                    <button class="btn btn-secondary btn-sm fw-bold px-3 shadow-sm" disabled><i class="bi bi-lock-fill"></i> Di Tangan Mahasiswa</button>
                                <?php elseif ($is_diperbaiki): ?>
                                    <!-- Jika sedang dikerjakan, arahkan ke selesai_reparasi.php -->
                                    <a href="selesai_reparasi.php?id_rep=<?= $data['idReparasi'] ?>&tipe=<?= $tipe_barang ?>&id_brg=<?= $id_barang ?>" class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                                        <i class="bi bi-check2-circle me-1"></i> Selesaikan
                                    </a>
                                <?php else: ?>
                                    <!-- PERBAIKAN: Jika masih Menunggu GA, arahkan ke mulai_reparasi.php dengan membawa parameter GET -->
                                    <a href="proses_inspeksi.php?id=<?= $id_barang ?>&tipe=<?= $tipe_barang ?>" class="btn text-white btn-sm fw-bold px-3 shadow-sm" style="background-color: #1d4197;">
                                        <i class="bi bi-wrench me-1"></i> Mulai Perbaiki
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($queryReparasi) == 0): ?>
                        <tr>
                            <td colspan="6" class="py-5 text-center text-success fw-bold"><i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>Semua tiket reparasi telah diselesaikan!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>