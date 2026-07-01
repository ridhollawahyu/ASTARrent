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
if (empty($_GET['id_rep']) || empty($_GET['tipe']) || empty($_GET['id_brg'])) {
    set_notifikasi('error', 'Terjadi Kesalahan! Coba lagi.');
    header('Location: index.php');
    exit;
}

$id_rep = mysqli_real_escape_string($koneksi, $_GET['id_rep']);
$id_brg = mysqli_real_escape_string($koneksi, $_GET['id_brg']);
$tipe_barang = strtolower(mysqli_real_escape_string($koneksi, $_GET['tipe']));

// Ambil Nama Barang untuk Header
$query_detail = mysqli_query($koneksi, "SELECT r.*, a.namaAset, f.namaFasilitas FROM reparasi_fasilitas_aset r LEFT JOIN aset a ON r.idAset = a.idAset LEFT JOIN fasilitas f ON r.idFasilitas = f.idFasilitas WHERE r.idReparasi = '$id_rep'");
$detail = mysqli_fetch_assoc($query_detail);
$nama_barang = ($tipe_barang === 'aset') ? $detail['namaAset'] : $detail['namaFasilitas'];

// =========================================================================
// PROSES LOGIKA PENYELESAIAN (PERBAIKI vs KANIBAL)
// =========================================================================
if (isset($_POST['selesai'])) {
    $tindakan_akhir = $_POST['tindakan_akhir']; // 'perbaiki' atau 'kanibal'
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $waktu = date('Y-m-d H:i:s');

    if ($tindakan_akhir == 'kanibal') {
        // ====================================================
        // JIKA DIKANIBAL: LOOPING DATA KOMPONEN YANG DIINPUT
        // ====================================================
        $nama_komp = $_POST['komp_nama'];
        $spek_komp = $_POST['komp_spek'];
        $kond_komp = $_POST['komp_kondisi'];
        $jumlah_masuk = 0;

        for ($i = 0; $i < count($nama_komp); $i++) {
            if (!empty(trim($nama_komp[$i]))) {
                $id_komp_baru = generate_id('KMP', 'komponen', 'idKomponen');
                $nama_k = mysqli_real_escape_string($koneksi, $nama_komp[$i]);
                $spek_k = mysqli_real_escape_string($koneksi, $spek_komp[$i]);
                $kond_k = mysqli_real_escape_string($koneksi, $kond_komp[$i]);

                $q_komp = "INSERT INTO komponen (idKomponen, idReparasi, namaKomponen, tanggalMasuk, spesifikasiKomponen, kondisiKomponen, statusKomponen) 
                           VALUES ('$id_komp_baru', '$id_rep', '$nama_k', '$waktu', '$spek_k', '$kond_k', 'Tersedia')";
                mysqli_query($koneksi, $q_komp);
                $jumlah_masuk++;
            }
        }

        // Update Status Reparasi & Aset Induk (Mati Permanen)
        mysqli_query($koneksi, "UPDATE reparasi_fasilitas_aset SET statusReparasi = 'Dikanibal', tanggalSelesai = '$waktu', catatanReparasi = CONCAT(IFNULL(catatanReparasi, ''), '\n[Dikanibal]: $catatan') WHERE idReparasi = '$id_rep'");

        // Matikan Aset
        perbarui_status_barang('aset', $id_brg, 'Nonaktif', 'Tidak Berfungsi');

        set_notifikasi('success', "Aset dikanibal! $jumlah_masuk Komponen baru berhasil ditambahkan ke gudang suku cadang.");
    } else {
        // ====================================================
        // JIKA DIPERBAIKI: KEMBALIKAN KE STATUS TERSDIA & NORMAL
        // ====================================================
        $kondisi_akhir = $_POST['kondisi_akhir'];
        mysqli_query($koneksi, "UPDATE reparasi_fasilitas_aset SET statusReparasi = 'Selesai', tanggalSelesai = '$waktu', catatanReparasi = CONCAT(IFNULL(catatanReparasi, ''), '\n[Diperbaiki]: $catatan') WHERE idReparasi = '$id_rep'");

        // Barang hidup lagi!
        perbarui_status_barang($tipe_barang, $id_brg, 'Tersedia', $kondisi_akhir);

        set_notifikasi('success', "Reparasi Selesai! Barang kembali ke status Tersedia dengan kondisi $kondisi_akhir.");
    }

    echo "<script>window.location='index.php';</script>";
    exit;
}

include '../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-9">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-check2-circle me-2"></i>Selesaikan Pengerjaan: <?= htmlspecialchars($nama_barang) ?></h5>
            </div>
            <div class="card-body p-4">

                <form action="" method="POST">

                    <!-- PILIHAN KEPUTUSAN (RADIO BUTTON) -->
                    <div class="mb-4 p-4 rounded" style="background-color: #f4f6f9; border: 2px dashed #c2d5ff;">
                        <label class="form-label fw-bold text-astar mb-3"><i class="bi bi-hammer me-2"></i>Keputusan Akhir Pekerjaan <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <?php
                            // LOGIKA VALIDASI KANIBAL (Sangat Cerdas!)
                            // Kanibal HANYA diizinkan jika Tipe = Aset DAN Klasifikasi Kerusakannya = 'Tidak Berfungsi'
                            $bisa_kanibal = ($tipe_barang === 'aset' && $detail['klasifikasiKerusakan'] === 'Tidak Berfungsi');

                            // Jika bisa kanibal, tombol dibagi dua (col-md-6). Jika tidak, tombol perbaiki jadi full (col-md-12).
                            $kolom_class = $bisa_kanibal ? 'col-md-6' : 'col-md-12';
                            $detail = $bisa_kanibal ? 'btn btn-outline-astar w-100 py-3 fw-bold text-start' : 'btn btn-outline-astar w-100 py-3 fw-bold text-center';
                            ?>
                            <div class="<?= $kolom_class ?>">
                                <input type="radio" class="btn-check" name="tindakan_akhir" id="aksi_perbaiki" value="perbaiki" checked onchange="toggleTindakan()">
                                <label class="<?= $detail ?>" for="aksi_perbaiki" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-tools fs-4 d-block mb-1"></i> Berhasil Diperbaiki
                                </label>
                            </div>
                            <?php if ($bisa_kanibal): ?>
                                <div class="col-md-6">
                                    <input type="radio" class="btn-check" name="tindakan_akhir" id="aksi_kanibal" value="kanibal" onchange="toggleTindakan()">
                                    <label class="btn btn-outline-danger w-100 py-3 fw-bold text-start" for="aksi_kanibal" style="border-radius: 10px; border-width: 2px;">
                                        <i class="bi bi-recycle fs-4 d-block mb-1"></i> Gagal Diperbaiki (Dibongkar)
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- PANEL 1: JIKA DIPERBAIKI -->
                    <div id="panel_perbaiki">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-astar">Kondisi Fisik Pasca Servis <span class="text-danger">*</span></label>
                            <?php
                            $opsi_kondisi_akhir = ['Normal' => 'Normal Sempurna', 'Berfungsi' => 'Berfungsi (Masih ada minus)'];
                            // Panggil dropdown tema dari functions.php
                            echo buat_dropdown_astar('kondisi_akhir', $opsi_kondisi_akhir, 'Normal');
                            ?>
                        </div>
                    </div>

                    <!-- PANEL 2: JIKA DIKANIBAL -->
                    <div id="panel_kanibal" style="display: none;">
                        <div class="alert alert-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> PERHATIAN: Aset akan di-Soft Delete (Ketersediaan: Nonaktif)!</div>

                        <label class="form-label fw-bold text-danger">Input Komponen yang Diselamatkan: <span class="text-danger">*</span></label>

                        <div id="komponen_container">
                            <div class="row g-2 mb-3 komponen-row align-items-center">
                                <div class="col-md-4">
                                    <!-- HAPUS REQUIRED HARDCODE DI SINI, AKAN DIATUR OLEH JS -->
                                    <input type="text" name="komp_nama[]" class="form-control komp-input" style="border: 2px solid #e0e6ed;" placeholder="Nama Komponen (Misal: RAM 8GB)">
                                </div>
                                <div class="col-md-4">
                                    <!-- HAPUS REQUIRED HARDCODE DI SINI, AKAN DIATUR OLEH JS -->
                                    <input type="text" name="komp_spek[]" class="form-control komp-input" style="border: 2px solid #e0e6ed;" placeholder="Spesifikasi">
                                </div>
                                <div class="col-md-3">
                                    <?php
                                    $opsi_kondisi_komponen = ['Sangat Baik' => 'Sangat Baik', 'Layak Pakai' => 'Layak Pakai'];
                                    echo buat_dropdown_danger('komp_kondisi[]', $opsi_kondisi_komponen, 'Sangat Baik');
                                    ?>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger w-100 fw-bold" onclick="hapusBaris(this)"><i class="bi bi-x-lg"></i></button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-danger btn-sm fw-bold mb-4 px-4 py-2 shadow-sm" onclick="tambahBaris()"><i class="bi bi-plus-circle-fill me-2"></i>Tambah Form Komponen</button>
                    </div>

                    <!-- CATATAN DIGABUNG DI LUAR PANEL AGAR SELALU MUNCUL & TIDAK BENTROK -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-astar">Catatan Penyelesaian <span class="text-danger">*</span></label>
                        <textarea name="catatan" class="form-control" rows="3" required placeholder="Catat detail perbaikan, atau alasan kenapa barang harus dikanibal..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4 border-top pt-3">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" name="selesai" class="btn btn-astar px-5 fw-bold shadow">Selesaikan Pekerjaan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    function tampilkanAlertJS(tipe, pesan) {
        let bgColor = (tipe === 'success') ? '#198754' : '#dc3545';
        let iconClass = (tipe === 'success') ? 'bi bi-check-circle-fill text-success' : 'bi bi-x-circle-fill text-danger';
        let title = (tipe === 'success') ? 'Berhasil!' : 'Terjadi Kesalahan!';

        document.getElementById('alertHeader').style.backgroundColor = bgColor;
        document.getElementById('alertTitle').innerText = title;
        document.getElementById('alertIcon').className = iconClass;
        document.getElementById('alertMessage').innerText = pesan;

        var alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
        alertModal.show();
    }

    function toggleTindakan() {
        let isKanibal = document.getElementById('aksi_kanibal') ? document.getElementById('aksi_kanibal').checked : false;

        document.getElementById('panel_kanibal').style.display = isKanibal ? 'block' : 'none';
        document.getElementById('panel_perbaiki').style.display = isKanibal ? 'none' : 'block';

        // LOGIKA CERDAS: Cabut atau pasang atribut required tergantung panel yang aktif
        let kompInputs = document.querySelectorAll('.komp-input');
        kompInputs.forEach(function(input) {
            if (isKanibal) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });
    }

    function tambahBaris() {
        let container = document.getElementById('komponen_container');
        let idUnik = 'drop_' + Math.floor(Math.random() * 9000 + 1000);

        let dropdown_html = `
        <div class="custom-dropdanger-container" id="container_${idUnik}">
            <input type="hidden" name="komp_kondisi[]" id="input_${idUnik}" value="Sangat Baik">
            <div class="custom-dropdanger-selected" onclick="toggleDropdown('${idUnik}')">
                <span id="text_${idUnik}">Sangat Baik</span>
                <i class="bi bi-chevron-down float-end"></i>
            </div>
            <div class="custom-dropdanger-options shadow" id="options_${idUnik}">
                <div class="custom-dropdanger-item active" onclick="selectOption('${idUnik}', 'Sangat Baik', 'Sangat Baik')">Sangat Baik</div>
                <div class="custom-dropdanger-item" onclick="selectOption('${idUnik}', 'Layak Pakai', 'Layak Pakai')">Layak Pakai</div>
            </div>
        </div>`;

        let html_baru = `
            <div class="row g-2 mb-3 komponen-row align-items-center">
                <div class="col-md-4"><input type="text" name="komp_nama[]" class="form-control komp-input" style="border: 2px solid #e0e6ed;" placeholder="Nama Komponen" required></div>
                <div class="col-md-4"><input type="text" name="komp_spek[]" class="form-control komp-input" style="border: 2px solid #e0e6ed;" placeholder="Spesifikasi" required></div>
                <div class="col-md-3">${dropdown_html}</div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 fw-bold" onclick="hapusBaris(this)"><i class="bi bi-x-lg"></i></button></div>
            </div>`;

        container.insertAdjacentHTML('beforeend', html_baru);
    }

    function hapusBaris(btn) {
        let row = btn.closest('.komponen-row');
        if (document.querySelectorAll('.komponen-row').length > 1) {
            row.remove();
        } else {
            tampilkanAlertJS('error', 'Minimal harus ada 1 komponen yang diselamatkan jika memilih Kanibal!');
        }
    }

    // Panggil saat pertama kali load agar menyesuaikan state default (Perbaiki)
    window.onload = function() {
        toggleTindakan();
    };
</script>

<?php include '../../../components/footer.php'; ?>