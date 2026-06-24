<?php
session_start();
include '../../../config/database.php';
include '../../../config/functions.php';

/** @var mysqli $koneksi */

$role_diizinkan = ['Staff GA', 'Super Admin'];
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], $role_diizinkan, true)) {
    set_notifikasi('error', 'Akses Ditolak! Halaman ini khusus Staff GA.');
    header('Location: ../../00_auth/login.php');
    exit;
}

if (isset($_POST['submit'])) {
    $id_otomatis = generate_id('KMP', 'komponen', 'idKomponen');
    $id_reparasi = mysqli_real_escape_string($koneksi, trim($_POST['idReparasi']));
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['namaKomponen']));
    $spesifikasi = mysqli_real_escape_string($koneksi, trim($_POST['spesifikasiKomponen']));
    $kondisi = mysqli_real_escape_string($koneksi, trim($_POST['kondisiKomponen']));
    $tanggal_masuk = date('Y-m-d H:i:s');

    if ($id_reparasi == '') {
        set_notifikasi('error', 'Sumber reparasi wajib dipilih.');
    } elseif (!reparasi_rusak_total_valid($id_reparasi)) {
        set_notifikasi('error', 'Sumber reparasi tidak valid. Komponen hanya boleh berasal dari reparasi Rusak Total.');
    } elseif (strlen($nama) < 3) {
        set_notifikasi('error', 'Nama komponen minimal 3 karakter.');
    } elseif (!kondisi_komponen_valid($kondisi)) {
        set_notifikasi('error', 'Kondisi komponen tidak valid.');
    } elseif (komponen_duplikat($id_reparasi, $nama)) {
        set_notifikasi('error', 'Komponen dengan nama yang sama sudah ada pada sumber reparasi ini.');
    } else {
        $query_simpan = "INSERT INTO komponen (idKomponen, idReparasi, namaKomponen, spesifikasiKomponen, kondisiKomponen, tanggalMasuk)
                         VALUES ('$id_otomatis', '$id_reparasi', '$nama', '$spesifikasi', '$kondisi', '$tanggal_masuk')";

        if (mysqli_query($koneksi, $query_simpan)) {
            set_notifikasi('success', "Sukses! Komponen baru ditambahkan dengan ID: $id_otomatis");
            header('Location: index.php');
            exit;
        } else {
            set_notifikasi('error', 'Gagal menyimpan data komponen ke database! ' . mysqli_error($koneksi));
        }
    }
}

include '../../../components/header.php';
$pilihan_reparasi = ambil_pilihan_reparasi_rusak_total();
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-cpu-fill me-2"></i>Tambah Komponen Hasil Bongkar</h5>
            </div>
            <div class="card-body p-4">

                <div class="alert py-2 mb-4" style="background-color: #e8f0fe; color: #1d4197; border: 1px solid #c2d5ff;" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Info:</strong> ID Komponen akan di-generate otomatis (KMP-00001). Komponen hanya dicatat dari reparasi <b>Rusak Total</b>.
                </div>

                <?php if (count($pilihan_reparasi) == 0): ?>
                    <div class="alert alert-warning fw-bold">
                        Belum ada data reparasi dengan klasifikasi Rusak Total. Buat data reparasi Rusak Total terlebih dahulu sebelum menambah komponen.
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Sumber Reparasi Rusak Total</label>
                        <?php
                        $opsi_reparasi = ['' => '-- Pilih Sumber Reparasi --'] + $pilihan_reparasi;
                        echo buat_dropdown_astar('idReparasi', $opsi_reparasi);
                        ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Nama Komponen</label>
                        <input type="text" name="namaKomponen" class="form-control" required placeholder="Contoh: Lampu Proyektor, Kabel HDMI, Adaptor Laptop">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Spesifikasi Komponen</label>
                        <textarea name="spesifikasiKomponen" class="form-control" rows="3" placeholder="Contoh: Lampu proyektor Epson EB-X400, masih menyala normal"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-astar fw-bold">Kondisi Komponen</label>
                        <?php
                        $opsi_kondisi = [
                            'Sangat Baik' => 'Sangat Baik',
                            'Layak Pakai' => 'Layak Pakai'
                        ];
                        echo buat_dropdown_astar('kondisiKomponen', $opsi_kondisi);
                        ?>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="submit" class="btn btn-astar px-5" <?= count($pilihan_reparasi) == 0 ? 'disabled' : ''; ?>>Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../../components/footer.php'; ?>
