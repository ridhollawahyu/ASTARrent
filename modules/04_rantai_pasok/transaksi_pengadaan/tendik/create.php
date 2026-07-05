<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

require '../../../../vendor/autoload.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    header('Location: ../../../00_auth/login.php');
    exit;
}

if (isset($_GET['batal_draft']) && isset($_SESSION['draft_kategori_id'])) {
    $id_draft_hapus = $_SESSION['draft_kategori_id'];
    mysqli_query($koneksi, "DELETE FROM kategori WHERE idKategori = '$id_draft_hapus'");
    unset($_SESSION['draft_kategori_id']);
    unset($_SESSION['draft_kategori_nama']);
    set_notifikasi('success', 'Draft kategori berhasil dibatalkan dan dihapus.');
    header('Location: create.php');
    exit;
}

if (isset($_POST['submit'])) {

    $id_pengadaan = generate_id('PGD', 'transaksi_pengadaan', 'idPengadaan');
    $id_tendik = $_SESSION['id'];
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $nama_kebutuhan = mysqli_real_escape_string($koneksi, trim($_POST['nama_kebutuhan']));
    $jumlah = (int)$_POST['jumlah'];
    $alasan = mysqli_real_escape_string($koneksi, trim($_POST['alasan']));
    $tgl_sekarang = date('Y-m-d H:i:s');

    // Siapkan Nama File PDF
    $format_tgl_file = date('Ymd');
    $nama_file_pdf = "Pengajuan_{$id_pengadaan}_{$format_tgl_file}.pdf";
    $folder_simpan = "../../../../uploads/dokumen_pengajuan/";
    if (!is_dir($folder_simpan)) mkdir($folder_simpan, 0777, true);

    // 1. INSERT KE DATABASE TERLEBIH DAHULU
    $query_insert = "INSERT INTO transaksi_pengadaan 
                    (idPengadaan, idKategori, idTendik, namaKebutuhan, tanggalPengadaan, jumlah, alasanKebutuhan, statusPengadaan, dokumen_pengajuan) 
                    VALUES 
                    ('$id_pengadaan', '$kategori', '$id_tendik', '$nama_kebutuhan', '$tgl_sekarang', $jumlah, '$alasan', 'Draft', '$nama_file_pdf')";

    if (mysqli_query($koneksi, $query_insert)) {

        // 2. SETELAH DATA ADA DI DATABASE, BARU GENERATE PDF AJAIB KITA!
        buat_pdf_pengajuan($id_pengadaan);

        unset($_SESSION['draft_kategori_id']);
        unset($_SESSION['draft_kategori_nama']);

        set_notifikasi('success', "Request Terkirim! TTD Tendik berhasil disematkan.");
        echo "<script>window.location='index.php';</script>";
        exit;
    } else {
        set_notifikasi('error', 'Gagal memproses data ke database!');
    }
}

include '../../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text-fill me-2"></i>Formulir Pengajuan Pengadaan Aset</h5>
            </div>
            <div class="card-body p-4">

                <div class="alert py-3 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff; border-left: 5px solid #1d4197;" role="alert">
                    <i class="bi bi-magic me-2 fs-5"></i>
                    <strong>Sistem E-Procurement Aktif:</strong> Cukup isi form di bawah, sistem kami akan <b>otomatis mencetaknya menjadi PDF Proposal Resmi</b> berkop surat!
                </div>

                <form action="" method="POST">

                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label class="form-label text-astar fw-bold">
                                Kategori Aset <span class="text-danger">*</span>

                                <!-- ======================================================== -->
                                <!-- PERBAIKAN UI: TOMBOL BATAL DRAFT MEMANGGIL MODAL CONFIRM -->
                                <!-- ======================================================== -->
                                <?php if (isset($_SESSION['draft_kategori_id'])): ?>
                                    <span onclick="konfirmasiHapus('?batal_draft=true')" class="badge bg-danger ms-1 shadow-sm" style="cursor: pointer; transition: 0.2s;" title="Hapus Draft Kategori">
                                        <i class="bi bi-trash-fill me-1"></i>Hapus Draft
                                    </span>
                                <?php endif; ?>

                            </label>

                            <?php
                            // MENGAMBIL DATA KATEGORI
                            $pilihan_kategori = ambil_pilihan_kategori('Aset');
                            $nilai_default = '';

                            // JIKA ADA SESSION DRAFT, PAKSA MENJADI PILIHAN DEFAULT
                            if (isset($_SESSION['draft_kategori_id'])) {
                                $draft_id = $_SESSION['draft_kategori_id'];
                                $draft_nama = $_SESSION['draft_kategori_nama'] . ' (Draft Baru)';

                                $pilihan_kategori[$draft_id] = $draft_nama;
                                $nilai_default = $draft_id;
                            } else {
                                // JIKA TIDAK ADA DRAFT, MUNCULKAN TOMBOL TAMBAH BARU
                                $pilihan_kategori['kategori_baru'] = '+ Tambah Kategori Aset Baru...';
                            }

                            echo buat_dropdown_astar('kategori', $pilihan_kategori, $nilai_default);
                            ?>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label text-astar fw-bold">Nama & Merk Aset Yang Dibutuhkan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kebutuhan" class="form-control" required placeholder="Contoh: Proyektor Epson Resolusi FHD">
                        </div>
                    </div>

                    <div class="mb-3 w-50">
                        <label class="form-label text-astar fw-bold">Jumlah Unit <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="jumlah" class="form-control fw-bold fs-5 text-astar" required min="1" max="100" placeholder="0">
                            <span class="input-group-text bg-light fw-bold text-secondary">Unit</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Alasan Kebutuhan & Tujuan <span class="text-danger">*</span></label>
                        <textarea name="alasan" class="form-control" rows="4" required placeholder="Jelaskan secara rinci mengapa aset ini dibutuhkan..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="submit" class="btn btn-astar px-5 fw-bold shadow-sm">
                            <i class="bi bi-printer me-2"></i> Generate PDF & Ajukan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../../../components/footer.php'; ?>