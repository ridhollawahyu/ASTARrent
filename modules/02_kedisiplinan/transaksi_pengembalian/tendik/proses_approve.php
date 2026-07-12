<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

/** @var mysqli $koneksi */

// Validasi Hak Akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    set_notifikasi('error', 'Akses Ditolak! Akses ini hanya bisa dilakukan oleh Tenaga Pendidik.');
    header('Location: ../../../00_auth/login.php');
    exit;
} elseif ((isset($_SESSION['login']) || $_SESSION['role'] === 'Tenaga Pendidik') && $_SESSION['status'] === 'Nonaktif') {
    set_notifikasi('error', 'Akses Ditolak! Akun kamu sudah di Nonaktifkan.');
    header('Location: ../../../00_auth/login.php');
}

// =====================================================================
// 1. PROSES SIMPAN KE DATABASE (JIKA TOMBOL SUBMIT DITEKAN)
// =====================================================================
if (isset($_POST['submit_pengembalian'])) {

    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $kondisi_fisik = mysqli_real_escape_string($koneksi, $_POST['kondisi_fisik']);
    $catatan       = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $jam_terlambat = (int)$_POST['jam_terlambat'];
    $id_tendik     = $_SESSION['id'];
    $waktu_sekarang = date('Y-m-d H:i:s');

    // Ambil info dasar dari transaksi ini
    $q_cek = mysqli_query($koneksi, "SELECT nimMahasiswa, idAset, idFasilitas FROM transaksi_peminjaman WHERE idPeminjaman = '$id_peminjaman'");
    $data_pjm = mysqli_fetch_assoc($q_cek);

    // SISTEM PAKAR: Dapatkan ID Sanksi otomatis (Baik telat maupun tepat waktu, sanksi dihitung otomatis)

    $id_sanksi = dapatkan_sanksi_otomatis($jam_terlambat, $kondisi_fisik);

    // Insert ke tabel Pengembalian
    $id_pengembalian = generate_id('KMB', 'transaksi_pengembalian', 'idPengembalian');
    $q_insert = "INSERT INTO transaksi_pengembalian (idPengembalian, idPeminjaman, idPengurus, idSanksi, tanggalPengembalian, kondisiFisik, catatanPengembalian) 
                 VALUES ('$id_pengembalian', '$id_peminjaman', '$id_tendik', '$id_sanksi', '$waktu_sekarang', '$kondisi_fisik', '$catatan')";

    if (mysqli_query($koneksi, $q_insert)) {

        // Aksi 1: Tutup transaksi Peminjaman
        mysqli_query($koneksi, "UPDATE transaksi_peminjaman SET statusPeminjaman = 'Selesai' WHERE idPeminjaman = '$id_peminjaman'");

        // Aksi 2: Update Ketersediaan Barang
        $status_barang = ($kondisi_fisik == 'Normal' || $kondisi_fisik == 'Berfungsi') ? 'Tersedia' : 'Tidak Tersedia';
        if (!empty($data_pjm['idAset'])) {
            perbarui_status_barang('aset', $data_pjm['idAset'], $status_barang, $kondisi_fisik);
        } else {
            perbarui_status_barang('fasilitas', $data_pjm['idFasilitas'], $status_barang, $kondisi_fisik);
        }

        // Aksi 3: Terapkan Sanksi ke Mahasiswa (Kecuali SNK-00001/Aman)
        if ($id_sanksi !== 'NULL') {
            terapkan_sanksi_mahasiswa($data_pjm['nimMahasiswa'], $id_sanksi);
        }

        // Aksi 4: Lempar ke GA jika barang rusak
        buat_tiket_reparasi_otomatis($id_tendik, $data_pjm['idAset'], $data_pjm['idFasilitas'], $kondisi_fisik, $catatan);

        set_notifikasi('success', 'Transaksi berhasil ditutup! Sistem telah menyesuaikan sanksi dan status barang.');
        header('Location: index.php');
        exit;
    } else {
        set_notifikasi('error', 'Gagal memproses data!');
    }
}

// =====================================================================
// 2. MENAMPILKAN HALAMAN FORM (JIKA DIAKSES DARI TOMBOL PROSES)
// =====================================================================
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id_pjm_get = mysqli_real_escape_string($koneksi, $_GET['id']);
$dept_tendik = $_SESSION['departemen'];

// Validasi Keamanan Row-Level
if (!validasi_otoritas_tendik($id_pjm_get, $dept_tendik)) {
    set_notifikasi('error', 'Akses Ditolak! Mahasiswa bukan dari Prodi Anda.');
    header('Location: index.php');
    exit;
}

// Ambil detail untuk ditampilkan di form
$query_detail = mysqli_query($koneksi, "
    SELECT tp.*, m.namaMahasiswa, m.nimMahasiswa, a.namaAset, f.namaFasilitas,
    TIMESTAMPDIFF(HOUR, tp.tanggalRencana_kembali, NOW()) AS jam_terlambat
    FROM transaksi_peminjaman tp JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa
    LEFT JOIN aset a ON tp.idAset = a.idAset LEFT JOIN fasilitas f ON tp.idFasilitas = f.idFasilitas
    WHERE tp.idPeminjaman = '$id_pjm_get' AND tp.statusPeminjaman = 'Disetujui'
");

if (mysqli_num_rows($query_detail) == 0) {
    header('Location: index.php');
    exit;
}

$detail = mysqli_fetch_assoc($query_detail);
$tipe_barang = !empty($detail['idAset']) ? 'Aset' : 'Fasilitas';
$nama_barang = ($tipe_barang == 'Aset') ? $detail['namaAset'] : $detail['namaFasilitas'];
$jam_terlambat = (int)$detail['jam_terlambat'];

include '../../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-clipboard2-check me-2"></i>Form Inspeksi Pengembalian</h5>
            </div>
            <div class="card-body p-4">

                <div class="bg-light p-3 rounded border mb-4">
                    <p class="mb-1"><strong>Mahasiswa:</strong> <?= $detail['namaMahasiswa'] ?> (<?= $detail['nimMahasiswa'] ?>)</p>
                    <p class="mb-1"><strong>Barang/Fasilitas:</strong> <span class="badge bg-secondary"><?= $tipe_barang ?></span> <?= $nama_barang ?></p>
                    <p class="mb-0"><strong>Status Waktu:</strong>
                        <?php if ($jam_terlambat > 0): ?>
                            <span class="text-danger fw-bold"><i class="bi bi-alarm-fill"></i> Terlambat <?= format_waktu_terlambat($jam_terlambat) ?></span>
                        <?php else: ?>
                            <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Tepat Waktu</span>
                        <?php endif; ?>
                    </p>
                </div>

                <form action="" method="POST">
                    <input type="hidden" name="id_peminjaman" value="<?= $id_pjm_get ?>">
                    <input type="hidden" name="jam_terlambat" value="<?= $jam_terlambat ?>">

                    <!-- DROPDOWN KONDISI (OTOMATIS MENYESUAIKAN TIPE BARANG DARI PHP) -->
                    <div class="mb-3">
                        <label class="form-label text-astar fw-bold">Kondisi Fisik <?= $tipe_barang ?> <span class="text-danger">*</span></label>
                        <?php
                        // Logika Cerdas: Beda Aset dan Fasilitas
                        if ($tipe_barang == 'Aset') {
                            $opsi_kondisi = ['Normal' => 'Normal (Aman)', 'Berfungsi' => 'Berfungsi', 'Tidak Berfungsi' => 'Tidak Berfungsi'];
                        } else {
                            // Fasilitas tidak ada opsi Rusak Total
                            $opsi_kondisi = ['Normal' => 'Normal (Aman)', 'Berfungsi' => 'Berfungsi', 'Tidak Berfungsi' => 'Tidak Berfungsi'];
                        }

                        echo buat_dropdown_astar('kondisi_fisik', $opsi_kondisi, 'Normal');
                        ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Catatan Inspeksi</label>
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Jelaskan kondisi barang saat dikembalikan..."></textarea>
                        <small class="text-danger mt-1">*Jika barang rusak, Sistem otomatis membuatkan Tiket Reparasi untuk GA dan memberikan sanksi.</small>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>

                        <!-- TOMBOL TRIGGER POP-UP MODAL -->
                        <button type="button" class="btn btn-astar px-5 fw-bold" data-bs-toggle="modal" data-bs-target="#modalKonfirmasiKembali">
                            Selesaikan & Tutup <i class="bi bi-send-check ms-1"></i>
                        </button>

                        <!-- TOMBOL SUBMIT ASLI (DISEMBUNYIKAN) -->
                        <button type="submit" name="submit_pengembalian" id="btnSubmitAsli" class="d-none"></button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL KONFIRMASI PENGEMBALIAN (TEMA ASTARRENT)          -->
<!-- ======================================================= -->
<div class="modal fade" id="modalKonfirmasiKembali" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">

            <div class="modal-header text-white" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bi bi-question-diamond-fill me-2"></i> Konfirmasi Inspeksi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center p-4">
                <i class="bi bi-exclamation-circle text-warning mb-3" style="font-size: 3rem;"></i>
                <h5 class="text-dark fw-bold mb-2">Tutup Transaksi Ini?</h5>
                <p class="text-secondary mb-0">Pastikan kondisi fisik yang dilaporkan sudah sesuai. Aksi otomatis sistem (Sanksi & Reparasi) <b>tidak dapat dibatalkan</b>.</p>
            </div>

            <div class="modal-footer justify-content-center border-0 pb-4 px-4">
                <button type="button" class="btn btn-light fw-bold px-4 text-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Periksa Lagi</button>
                <!-- Tombol ini memicu JavaScript untuk menekan tombol submit asli yang tersembunyi -->
                <button type="button" class="btn text-white fw-bold px-4" style="background-color: #1d4197; border-radius: 8px;" onclick="document.getElementById('btnSubmitAsli').click();">
                    Ya, Konfirmasi
                </button>
            </div>

        </div>
    </div>
</div>

<?php include '../../../../components/footer.php'; ?>