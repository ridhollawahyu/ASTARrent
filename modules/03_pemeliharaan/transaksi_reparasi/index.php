<?php
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses: Hanya Staff GA
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Staff GA') {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../../00_auth/login.php');
    exit;
}

// Filter status
$filter_status = '';
$status_terpilih = '';

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status_terpilih = mysqli_real_escape_string($koneksi, $_GET['status']);
    $filter_status = "AND r.statusReparasi = '$status_terpilih'";
}

// Query daftar tiket reparasi
$query_sql = "
    SELECT r.*,
           a.namaAset, f.namaFasilitas,
           u.namaUser AS namaTendik
    FROM reparasi_fasilitas_aset r
    LEFT JOIN aset a ON r.idAset = a.idAset
    LEFT JOIN fasilitas f ON r.idFasilitas = f.idFasilitas
    LEFT JOIN users u ON r.idTendik = u.idUser
    WHERE 1=1 $filter_status
    ORDER BY 
        CASE r.statusReparasi WHEN 'Menunggu GA' THEN 1 WHEN 'Sedang Dikerjakan' THEN 2 ELSE 3 END,
        r.tanggalLapor ASC
";
$queryReparasi = mysqli_query($koneksi, $query_sql);

include '../../../components/header.php';
?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
        <h5 class="mb-0 text-white fw-bold"><i class="bi bi-tools me-2"></i>Antrian Reparasi Aset & Fasilitas</h5>
        <div>
            <a href="../../dashboards/staffga_home.php" class="btn btn-outline-light btn-sm fw-bold"><i class="bi bi-arrow-left"></i> Dashboard</a>
        </div>
    </div>

    <div class="card-body p-4">

        <!-- INFO BOX -->
        <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff; border-left: 4px solid #1d4197;">
            <i class="bi bi-shield-check-fill me-2"></i>
            <strong>Staff GA sebagai QC:</strong> Anda bertugas menginspeksi aset/fasilitas yang dilaporkan <strong>Tidak Berfungsi</strong> oleh Tendik. Tentukan keparahan dan tindakan (perbaiki atau kanibal).
        </div>

        <!-- FILTER STATUS -->
        <form method="GET" action="index.php" class="row g-2 align-items-center mb-4 pb-3 border-bottom">
            <div class="col-auto">
                <label class="col-form-label fw-bold" style="color: #1d4197;"><i class="bi bi-funnel-fill me-1"></i> Filter Status:</label>
            </div>
            <div class="col-md-3">
                <?php
                $opsi_status = [
                    ''                  => '-- Semua Status --',
                    'Menunggu GA'       => '⏳ Menunggu Inspeksi',
                    'Sedang Dikerjakan' => '🔧 Sedang Dikerjakan',
                    'Selesai'           => '✅ Selesai',
                    'Dikanibal'         => '♻️ Dikanibal',
                ];
                echo buat_dropdown_astar('status', $opsi_status, $status_terpilih, false);
                ?>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn fw-bold text-white px-4" style="background-color: #1d4197; border-radius: 8px;">Terapkan</button>
                <a href="index.php" class="btn btn-light fw-bold px-4" style="border: 2px solid #e0e6ed; border-radius: 8px; color: #1d4197;">Reset</a>
            </div>
        </form>

        <!-- TABEL DATA REPARASI -->
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead style="background-color: #f4f6f9; color: #1d4197;">
                    <tr>
                        <th width="5%">No.</th>
                        <th>ID Reparasi</th>
                        <th class="text-start">Barang Bermasalah</th>
                        <th>Dilaporkan Oleh</th>
                        <th>Tgl. Lapor</th>
                        <th>Klasifikasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($data = mysqli_fetch_assoc($queryReparasi)):
                        $nama_barang  = !empty($data['idAset'])
                            ? '<span class="badge bg-secondary me-1">Aset</span>' . htmlspecialchars($data['namaAset'])
                            : '<span class="badge bg-info text-dark me-1">Fasilitas</span>' . htmlspecialchars($data['namaFasilitas']);

                        // Warna badge status
                        $badge_status = match ($data['statusReparasi']) {
                            'Menunggu GA'       => 'bg-danger',
                            'Sedang Dikerjakan' => 'bg-warning text-dark',
                            'Selesai'           => 'bg-success',
                            'Dikanibal'         => 'bg-secondary',
                            default             => 'bg-light text-dark',
                        };

                        // Label klasifikasi
                        $label_klasifikasi = $data['klasifikasiKerusakan']
                            ? '<span class="badge bg-dark">' . htmlspecialchars($data['klasifikasiKerusakan']) . '</span>'
                            : '<span class="text-muted fst-italic">Belum Diinspeksi</span>';
                    ?>
                        <tr>
                            <td class="fw-bold"><?= $no++ ?></td>
                            <td><code class="text-primary fw-bold"><?= htmlspecialchars($data['idReparasi']) ?></code></td>
                            <td class="text-start fw-semibold"><?= $nama_barang ?></td>
                            <td class="text-muted"><?= htmlspecialchars($data['namaTendik']) ?></td>
                            <td><?= date('d M Y, H:i', strtotime($data['tanggalLapor'])) ?></td>
                            <td><?= $label_klasifikasi ?></td>
                            <td>
                                <span class="badge <?= $badge_status ?> rounded-pill px-3 py-2">
                                    <?= htmlspecialchars($data['statusReparasi']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($data['statusReparasi'] === 'Menunggu GA'): ?>
                                    <a href="proses_inspeksi.php?id=<?= $data['idReparasi'] ?>"
                                        class="btn text-white btn-sm fw-bold px-3"
                                        style="background-color: #1d4197; border-radius: 8px;">
                                        <i class="bi bi-search me-1"></i> Inspeksi
                                    </a>
                                <?php elseif ($data['statusReparasi'] === 'Sedang Dikerjakan'): ?>
                                    <a href="selesai_reparasi.php?id=<?= $data['idReparasi'] ?>"
                                        class="btn btn-success btn-sm fw-bold px-3" style="border-radius: 8px;">
                                        <i class="bi bi-check2-circle me-1"></i> Selesaikan
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted fst-italic" style="font-size: 0.85rem;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($queryReparasi) == 0): ?>
                        <tr>
                            <td colspan="8" class="py-5 text-center text-muted fst-italic">
                                <i class="bi bi-check-circle text-success" style="font-size:2rem;"></i><br>
                                Tidak ada tiket reparasi<?= $status_terpilih ? ' dengan status ini' : '' ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>