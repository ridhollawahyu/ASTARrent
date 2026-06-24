<?php
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

$role_diizinkan = ['Staff GA'];
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], $role_diizinkan, true)) {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header("Location: ../../00_auth/login.php");
    exit;
}

$where_sql = "WHERE komponen.statusKomponen != 'Nonaktif'";
$status_terpilih = "";
$kondisi_terpilih = "";

if (isset($_GET['status']) && $_GET['status'] != '') {
    $status_terpilih = mysqli_real_escape_string($koneksi, $_GET['status']);

    if ($status_terpilih == 'Semua_Termasuk_Arsip') {
        $where_sql = "WHERE 1=1";
    } else {
        $where_sql = "WHERE komponen.statusKomponen = '$status_terpilih'";
    }
}

if (isset($_GET['kondisi']) && $_GET['kondisi'] != '') {
    $kondisi_terpilih = mysqli_real_escape_string($koneksi, $_GET['kondisi']);
    $where_sql .= " AND komponen.kondisiKomponen = '$kondisi_terpilih'";
}

include '../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-cpu-fill me-2"></i>Data Master Komponen</h5>
        <div>
            <a href="../../dashboards/staffga_home.php" class="btn btn-outline-light btn-sm fw-bold me-2"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <a href="create.php" class="btn btn-light btn-sm fw-bold text-astar">+ Tambah Komponen</a>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Info:</strong> Komponen dicatat dari proses reparasi <b>Rusak Total</b>. Data lama tidak dihapus permanen, statusnya dipindahkan ke arsip.
        </div>

        <form method="GET" action="index.php" class="row g-2 align-items-center mb-4 pb-3 border-bottom">
            <div class="col-auto">
                <label class="col-form-label fw-bold" style="color: #1d4197;"><i class="bi bi-funnel-fill me-1"></i> Filter:</label>
            </div>

            <div class="col-md-3 col-sm-6">
                <?php
                $opsi_status = [
                    '' => '-- Status Default (Aktif) --',
                    'Tersedia' => 'Tersedia',
                    'Sudah Dipakai' => 'Sudah Dipakai',
                    'Nonaktif' => 'Arsip (Soft Delete)',
                    'Semua_Termasuk_Arsip' => 'Tampilkan Semua Data'
                ];
                echo buat_dropdown_astar('status', $opsi_status, $status_terpilih, false);
                ?>
            </div>

            <div class="col-md-3 col-sm-6">
                <?php
                $opsi_kondisi = [
                    '' => '-- Semua Kondisi --',
                    'Sangat Baik' => 'Sangat Baik',
                    'Layak Pakai' => 'Layak Pakai'
                ];
                echo buat_dropdown_astar('kondisi', $opsi_kondisi, $kondisi_terpilih, false);
                ?>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn fw-bold text-white px-4" style="background-color: #1d4197; border-radius: 8px;">
                    Terapkan
                </button>
                <a href="index.php" class="btn btn-light fw-bold px-4" style="border: 2px solid #e0e6ed; border-radius: 8px; color: #1d4197;">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 text-center align-middle">
                <thead style="background-color: #f4f6f9; color: #1d4197;">
                    <tr>
                        <th width="5%">No.</th>
                        <th>ID Komponen</th>
                        <th>Sumber Reparasi</th>
                        <th>Nama Komponen</th>
                        <th>Spesifikasi</th>
                        <th>Kondisi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_sql = "SELECT komponen.*, reparasi_fasilitas_aset.klasifikasiKerusakan, reparasi_fasilitas_aset.statusReparasi,
                                         aset.namaAset, fasilitas.namaFasilitas
                                  FROM komponen
                                  JOIN reparasi_fasilitas_aset ON komponen.idReparasi = reparasi_fasilitas_aset.idReparasi
                                  LEFT JOIN aset ON reparasi_fasilitas_aset.idAset = aset.idAset
                                  LEFT JOIN fasilitas ON reparasi_fasilitas_aset.idFasilitas = fasilitas.idFasilitas
                                  " . $where_sql . "
                                  ORDER BY komponen.idKomponen ASC";
                    $query = mysqli_query($koneksi, $query_sql);
                    $no = 1;

                    while ($data = mysqli_fetch_array($query)) {
                        $nama_barang = $data['namaAset'] ?: $data['namaFasilitas'];
                        if (!$nama_barang) {
                            $nama_barang = 'Barang tidak ditemukan';
                        }
                    ?>
                        <tr>
                            <td class="fw-bold"><?= $no++; ?></td>
                            <td class="fw-bold text-astar"><?= $data['idKomponen']; ?></td>
                            <td class="text-start">
                                <span class="fw-bold"><?= $data['idReparasi']; ?></span><br>
                                <small class="text-muted"><?= $nama_barang; ?></small>
                            </td>
                            <td><?= $data['namaKomponen']; ?></td>
                            <td><?= $data['spesifikasiKomponen'] ?: '-'; ?></td>
                            <td>
                                <?php if ($data['kondisiKomponen'] == 'Sangat Baik'): ?>
                                    <span class="badge bg-success rounded-pill px-3">Sangat Baik</span>
                                <?php else: ?>
                                    <span class="badge bg-primary rounded-pill px-3">Layak Pakai</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($data['statusKomponen'] == 'Tersedia'): ?>
                                    <span class="badge bg-success rounded-pill px-3">Tersedia</span>
                                <?php elseif ($data['statusKomponen'] == 'Sudah Dipakai'): ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3">Sudah Dipakai</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-pill px-3">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($data['statusKomponen'] != 'Nonaktif'): ?>
                                    <a href="edit.php?id=<?= $data['idKomponen']; ?>" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-pencil-square"></i></a>
                                    <button type="button" class="btn btn-danger btn-sm fw-bold" onclick="konfirmasiHapus('delete.php?id=<?= $data['idKomponen']; ?>')">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php if (!$query || mysqli_num_rows($query) == 0): ?>
                        <tr>
                            <td colspan="8" class="py-4 text-muted fst-italic">Tidak ada data komponen yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>