<?php
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

validasi_kadaluwarsa_peminjaman();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Tenaga Pendidik.');
    header('Location: ../../../00_auth/login.php');
    exit;
}
include '../../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-list-check me-2"></i>Daftar Pengajuan Peminjaman</h5>
        <a href="../../../dashboards/tendik_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead style="background-color: #f4f6f9; color: #1d4197;">
                    <tr>
                        <th class="text-center" width="5%">No.</th>
                        <th>Mahasiswa</th>
                        <th>Barang yang Dipinjam</th>
                        <th>Rencana Kembali</th>
                        <th>Alasan Meminjam</th>
                        <th>Pengurus (Tendik)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Ambil departemen tendik yang lagi login
                    $dept_tendik = $_SESSION['departemen'];

                    // QUERY CERDAS: HANYA TAMPILKAN MAHASISWA YANG PRODINYA SAMA DENGAN TENDIK!
                    $queryTransaksi = mysqli_query($koneksi, "
                        SELECT tp.*, m.namaMahasiswa, m.kodeProdi_mahasiswa, a.namaAset, f.namaFasilitas, u.namaUser
                        FROM transaksi_peminjaman tp
                        JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa
                        LEFT JOIN aset a ON tp.idAset = a.idAset
                        LEFT JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
                        LEFT JOIN users u ON tp.idTendik = u.idUser
                        WHERE m.kodeProdi_mahasiswa = '$dept_tendik' 
                        ORDER BY tp.tanggalPengajuan DESC
                    ");

                    $no = 1;

                    while ($data = mysqli_fetch_array($queryTransaksi)) {
                        $nama_barang = ($data['idAset'] != NULL) ? "[ASET] " . $data['namaAset'] : "[FASILITAS] " . $data['namaFasilitas'];
                        $nama_tendik = ($data['idTendik'] != NULL) ? $data['namaUser'] : "Belum Dikelola";
                        if ($data['statusPeminjaman'] == "Ditolak") {
                            $nama_tendik = "Ditolak";
                        }
                    ?>
                        <tr>
                            <td class="fw-bold"><?= $no++; ?></td>
                            <td><?= $data['namaMahasiswa']; ?></td>
                            <td class="text-start fw-bold text-secondary"><?= $nama_barang; ?></td>
                            <td><?= date('d M Y, H:i', strtotime($data['tanggalRencana_kembali'])); ?></td>
                            <td><?= $data['keperluan']; ?></td>
                            <td class="text-center fw-bold text-secondary"><?= $nama_tendik; ?></td>
                            <td>
                                <?php if ($data['statusPeminjaman'] == 'Menunggu'): ?>
                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                <?php elseif ($data['statusPeminjaman'] == 'Disetujui'): ?>
                                    <span class="badge bg-success">Disetujui</span>
                                <?php elseif ($data['statusPeminjaman'] == 'Selesai'): ?>
                                    <span class="badge bg-primary">Selesai</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Ditolak</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($data['statusPeminjaman'] == 'Menunggu'): ?>
                                    <!-- Tombol Setuju & Tolak yang diarahkan ke proses_approve.php -->
                                    <a href="proses_approve.php?id=<?= $data['idPeminjaman']; ?>&aksi=setuju" class="btn btn-success btn-sm fw-bold"><i class="bi bi-check-lg"></i> Setuju</a>
                                    <a href="proses_approve.php?id=<?= $data['idPeminjaman']; ?>&aksi=tolak" class="btn btn-danger btn-sm fw-bold"><i class="bi bi-x-lg"></i> Tolak</a>
                                <?php else: ?>
                                    <span class="text-muted"><i class="bi bi-lock-fill"></i> Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <!-- Jika data kosong -->
                    <?php if (mysqli_num_rows($queryTransaksi) == 0): ?>
                        <tr>
                            <td colspan="8" class="py-4 text-muted fst-italic">Tidak ada Antrean Peminjaman yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../../../../components/footer.php'; ?>