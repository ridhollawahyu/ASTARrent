<?php
// --- FILE: modules/04_rantai_pasok/transaksi_pengadaan/finance/approve.php ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../../../config/database.php';
include '../../../../config/functions.php';

require '../../../../vendor/autoload.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Finance') {
    header('Location: ../../../00_auth/login.php');
    exit;
}
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_pengadaan = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_finance = $_SESSION['id'];

// Ambil Detail Data
$q_detail = mysqli_query($koneksi, "
    SELECT tp.*, k.idKategori, k.namaKategori, k.statusKategori, u.namaUser as namaTendik 
    FROM transaksi_pengadaan tp 
    JOIN kategori k ON tp.idKategori = k.idKategori 
    JOIN users u ON tp.idTendik = u.idUser 
    WHERE tp.idPengadaan = '$id_pengadaan' AND tp.statusPengadaan = 'Harga Diinput Supplier'
");
$data = mysqli_fetch_assoc($q_detail);
if (!$data) {
    set_notifikasi('error', 'Data tidak ditemukan atau sudah dicairkan!');
    header('Location: index.php');
    exit;
}

$kebutuhan_jumlah = (int)$data['jumlah'];
$id_kategori_aset = $data['idKategori'];
$nama_kebutuhan_aset = $data['namaKebutuhan'];

// Ambil Data JSON Vendor
$explode = explode('|||VENDOR|||', $data['alasanKebutuhan']);
$json_vendor = isset($explode[1]) ? trim($explode[1]) : '[]';
$array_vendor = json_decode($json_vendor, true);

// ==============================================================================
// PROSES PENGESAHAN OLEH FINANCE
// ==============================================================================
if (isset($_POST['submit_cairkan'])) {
    $keputusan = $_POST['keputusan'];

    if ($keputusan === 'tolak') {
        $alasan_tolak = mysqli_real_escape_string($koneksi, trim($_POST['alasan_tolak']));

        mysqli_query($koneksi, "UPDATE transaksi_pengadaan 
                                SET statusPengadaan = 'Ditolak', 
                                    idFinance = '$id_finance',
                                    alasanPenolakan_pengadaan = '$alasan_tolak' 
                                WHERE idPengadaan = '$id_pengadaan'");

        if ($data['statusKategori'] === 'Draft') {
            mysqli_query($koneksi, "UPDATE kategori SET statusKategori = 'Nonaktif' WHERE idKategori = '$id_kategori_aset'");
        }
        set_notifikasi('success', 'Pengadaan berhasil ditolak oleh Finance.');
    } elseif ($keputusan === 'setuju') {

        $vendor_terpilih = isset($_POST['vendor_check']) ? $_POST['vendor_check'] : [];

        // Hitung total stok dari toko yang dicentang
        $total_dibeli_server = 0;
        foreach ($vendor_terpilih as $idx) {
            $total_dibeli_server += (int)$array_vendor[$idx]['stok']; // Langsung ambil stok asli
        }

        // Backend Validation
        if ($total_dibeli_server < $kebutuhan_jumlah) {
            set_notifikasi('error', 'Total stok toko yang dipilih tidak memenuhi syarat request minimal (' . $kebutuhan_jumlah . ' Unit).');
            echo "<script>window.location='approve.php?id=$id_pengadaan';</script>";
            exit;
        }

        // 1. UBAH STATUS TRANSAKSI
        mysqli_query($koneksi, "UPDATE transaksi_pengadaan 
                                SET statusPengadaan = 'Disetujui Finance', 
                                    idFinance = '$id_finance' 
                                WHERE idPengadaan = '$id_pengadaan'");

        // 2. SAHKAN KATEGORI JIKA DRAFT
        if ($data['statusKategori'] === 'Draft') {
            mysqli_query($koneksi, "UPDATE kategori SET statusKategori = 'Aktif' WHERE idKategori = '$id_kategori_aset'");
        }

        // 3. SIHIR LOOPING: KELAHIRAN ASET BARU!
        $jumlah_aset_lahir = 0;
        foreach ($vendor_terpilih as $idx) {
            $qty_beli = (int)$array_vendor[$idx]['stok'];
            $nama_toko = mysqli_real_escape_string($koneksi, $array_vendor[$idx]['toko']);

            // Format Nama Aset: "Proyektor Epson FHD (Toko ABC)"
            $nama_aset_baru = $nama_kebutuhan_aset . " (" . $nama_toko . ")";

            for ($i = 0; $i < $qty_beli; $i++) {
                $id_aset_baru = generate_id('AST', 'aset', 'idAset');

                $q_lahir = "INSERT INTO aset (idAset, idKategori, idPengadaan, namaAset, kondisiAset, ketersediaanAset) 
                            VALUES ('$id_aset_baru', '$id_kategori_aset', '$id_pengadaan', '$nama_aset_baru', 'Normal', 'Tersedia')";
                mysqli_query($koneksi, $q_lahir);
                $jumlah_aset_lahir++;
            }
        }

        // 4. GENERATE ULANG KEDUA PDF UNTUK TANDA TANGAN FINANCE
        buat_pdf_pengajuan($id_pengadaan);
        buat_pdf_penawaran($id_pengadaan);

        set_notifikasi('success', "Sukses! Dana dicairkan dan $jumlah_aset_lahir Aset baru berhasil ditambahkan ke database otomatis.");
    }

    echo "<script>window.location='index.php';</script>";
    exit;
}

include '../../../../components/header.php';
?>

<div class="row justify-content-center mb-5 mt-4">
    <div class="col-md-11">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header text-white d-flex align-items-center" style="background-color: #1d4197; border-radius: 15px 15px 0 0;">
                <h5 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2"></i>Persetujuan Finance (Pencairan Dana)</h5>
            </div>
            <div class="card-body p-4">

                <!-- INFO DOKUMEN PDF -->
                <div class="bg-light p-4 rounded border mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h6 class="fw-bold text-astar mb-2"><i class="bi bi-info-circle-fill me-2"></i>Kebutuhan Aset:</h6>
                            <h5 class="fw-bold text-dark mb-1"><?= $data['namaKategori'] ?> - <?= $data['namaKebutuhan'] ?></h5>
                            <p class="text-muted mb-0">Total yang dibutuhkan minimal: <span class="fw-bold text-danger fs-5" id="target_kebutuhan"><?= $kebutuhan_jumlah ?></span> <span class="fw-bold text-danger">Unit</span></p>
                        </div>
                        <div class="col-md-5 text-md-end mt-3 mt-md-0">
                            <a href="../../../../uploads/dokumen_pengajuan/<?= $data['dokumen_pengajuan'] ?>" target="_blank" class="btn btn-outline-danger fw-bold shadow-sm mb-2 w-100">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Baca Proposal Pengajuan
                            </a>
                            <a href="../../../../uploads/dokumen_penawaran/<?= $data['dokumen_penawaran'] ?>" target="_blank" class="btn btn-outline-danger fw-bold shadow-sm w-100">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Baca Perbandingan Vendor
                            </a>
                        </div>
                    </div>
                </div>

                <form action="" method="POST" id="form_finance">

                    <div class="mb-4 p-4 rounded" style="background-color: #f4f6f9; border: 2px dashed #c2d5ff;">
                        <label class="form-label fw-bold text-astar mb-3"><i class="bi bi-hammer me-2"></i>Keputusan Finance <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="keputusan" id="aksi_setuju" value="setuju" checked onchange="toggleKeputusan()">
                                <label class="btn btn-outline-astar w-100 py-3 fw-bold text-start" for="aksi_setuju" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-check-circle-fill fs-4 d-block mb-1"></i> Cairkan Dana (Pilih Vendor)
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="keputusan" id="aksi_tolak" value="tolak" onchange="toggleKeputusan()">
                                <label class="btn btn-outline-danger w-100 py-3 fw-bold text-start" for="aksi_tolak" style="border-radius: 10px; border-width: 2px;">
                                    <i class="bi bi-x-circle-fill fs-4 d-block mb-1"></i> Tolak Pengadaan
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL VENDOR (JIKA SETUJU) -->
                    <div id="panel_vendor" class="mb-4">
                        <h6 class="fw-bold text-astar mb-3"><i class="bi bi-shop me-2"></i>Pilih Penawaran Vendor (Memborong Seluruh Stok)</h6>
                        <div class="table-responsive border rounded">
                            <table class="table table-hover align-middle text-center mb-0">
                                <thead style="background-color: #e8f0fe; color: #1d4197;">
                                    <tr>
                                        <th width="5%"><i class="bi bi-check2-square"></i></th>
                                        <th class="text-start">Nama Toko</th>
                                        <th>Harga Satuan</th>
                                        <th width="15%">Stok Tersedia</th>
                                        <th>Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($array_vendor as $index => $v):
                                        $total_harga = $v['harga'] * $v['stok'];
                                    ?>
                                        <tr>
                                            <td>
                                                <!-- Checkbox membawa attribute data-stok dan data-harga untuk hitung realtime JS -->
                                                <input type="checkbox" name="vendor_check[]" value="<?= $index ?>" class="form-check-input chk-vendor" style="transform: scale(1.5);" data-stok="<?= $v['stok'] ?>" data-harga="<?= $total_harga ?>" onchange="updateTotal()">
                                            </td>
                                            <td class="text-start fw-bold text-secondary"><?= $v['toko'] ?></td>
                                            <td class="text-muted">Rp <?= number_format($v['harga'], 0, ',', '.') ?></td>
                                            <td><span class="badge bg-secondary" style="font-size: 14px;"><?= $v['stok'] ?> Unit</span></td>
                                            <td class="fw-bold text-primary">Rp <?= number_format($total_harga, 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot style="background-color: #fdfdfd;">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold text-danger">Total yang akan di-ACC :</td>
                                        <td>
                                            <input type="text" id="display_total_unit" class="form-control text-center fw-bold text-danger border-danger" value="0 Unit" readonly>
                                        </td>
                                        <td>
                                            <input type="text" id="display_total_rp" class="form-control text-center fw-bold text-danger border-danger" value="Rp 0" readonly>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- PERINGATAN (CLASS D-BLOCK TELAH DIHAPUS) -->
                        <small id="peringatan_qty" class="text-danger fw-bold mt-2" style="display: block;">
                            <i class="bi bi-exclamation-triangle-fill"></i> Total unit toko yang dipilih belum memenuhi target kebutuhan minimal (<?= $kebutuhan_jumlah ?> Unit).
                        </small>
                    </div>

                    <!-- PANEL TOLAK -->
                    <div id="panel_tolak" class="mb-4 p-4 rounded border" style="display: none; background-color: #fff3f3; border-color: #f5c6cb !important;">
                        <label class="form-label fw-bold text-danger"><i class="bi bi-chat-left-text-fill me-1"></i> Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="alasan_tolak" id="input_alasan_tolak" class="form-control border-danger" rows="3" placeholder="Jelaskan alasan pengadaan ini ditolak agar Tendik mengetahuinya..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between mt-4 border-top pt-4">
                        <a href="index.php" class="btn btn-light border fw-bold text-secondary px-4">Batal</a>
                        <button type="submit" id="btn_submit" name="submit_cairkan" class="btn btn-secondary px-5 fw-bold shadow-sm" disabled>
                            Simpan & Cetak Aset <i class="bi bi-magic ms-1"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
    const targetKebutuhan = parseInt(<?= $kebutuhan_jumlah ?>);

    function toggleKeputusan() {
        let isSetuju = document.getElementById('aksi_setuju').checked;
        let panelVendor = document.getElementById('panel_vendor');
        let panelTolak = document.getElementById('panel_tolak');
        let inputAlasanTolak = document.getElementById('input_alasan_tolak');
        let btnSubmit = document.getElementById('btn_submit');

        if (isSetuju) {
            panelVendor.style.display = 'block';
            panelTolak.style.display = 'none';
            inputAlasanTolak.removeAttribute('required');
            updateTotal(); // Jalankan validasi JS
        } else {
            panelVendor.style.display = 'none';
            panelTolak.style.display = 'block';
            inputAlasanTolak.setAttribute('required', 'required');

            // Tombol selalu aktif jika tolak
            btnSubmit.disabled = false;
            // Bersihkan class warna sebelumnya agar rapi
            btnSubmit.classList.remove('btn-secondary', 'btn-astar');
            btnSubmit.classList.add('btn-danger');
            btnSubmit.innerHTML = 'Tolak Pengadaan <i class="bi bi-x-lg ms-1"></i>';
        }
    }

    function updateTotal() {
        let checkboxes = document.querySelectorAll('.chk-vendor');
        let totalUnit = 0;
        let totalRp = 0;

        checkboxes.forEach(function(chk) {
            if (chk.checked) {
                totalUnit += parseInt(chk.getAttribute('data-stok')) || 0;
                totalRp += parseInt(chk.getAttribute('data-harga')) || 0;
            }
        });

        // Tampilkan Unit & Rupiah
        document.getElementById('display_total_unit').value = totalUnit + " Unit";
        document.getElementById('display_total_rp').value = "Rp " + new Intl.NumberFormat('id-ID').format(totalRp);

        let btnSubmit = document.getElementById('btn_submit');
        let peringatan = document.getElementById('peringatan_qty');

        // VALIDASI JS
        if (totalUnit < targetKebutuhan) {
            btnSubmit.disabled = true;
            btnSubmit.classList.remove('btn-astar', 'btn-danger');
            btnSubmit.classList.add('btn-secondary');

            // Tampilkan peringatan merah
            peringatan.style.display = 'block';
        } else {
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('btn-secondary', 'btn-danger');
            btnSubmit.classList.add('btn-astar');

            // Sembunyikan peringatan merah
            peringatan.style.display = 'none';
            btnSubmit.innerHTML = 'Cairkan & Lahirkan Aset <i class="bi bi-magic ms-1"></i>';
        }
    }

    window.onload = toggleKeputusan;
</script>

<?php include '../../../../components/footer.php'; ?>