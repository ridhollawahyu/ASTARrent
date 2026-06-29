<?php
// Panggil koneksi database
require 'database.php';

// =========================================================================
// 🛡️ SECURITY PATCH: MENCEGAH BACK-BUTTON SETELAH LOGOUT
// Memaksa browser (Chrome/Safari) untuk TIDAK menyimpan cache halaman ini.
// Jadi pas di-Back, browser wajib ngecek session ke server lagi.
// =========================================================================
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// 1. FUNGSI READ (Menampilkan Data)
// Contoh pakai: $data_mhs = ambil_data("SELECT * FROM mahasiswa");
function ambil_data($query)
{
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// 2. FUNGSI CREATE (Tambah Data Mahasiswa)
function tambah_mahasiswa($data)
{
    global $conn;
    $nim = htmlspecialchars($data["nim"]);
    $nama = htmlspecialchars($data["nama"]);
    $prodi = htmlspecialchars($data["prodi"]);
    $email = htmlspecialchars($data["email"]);
    // Enkripsi Password (Validasi Wajib)
    $password = password_hash($data["password"], PASSWORD_DEFAULT);

    $query = "INSERT INTO mahasiswa (nimMahasiswa, namaMahasiswa, kodeProdi_mahasiswa, emailMahasiswa, passMahasiswa) 
              VALUES ('$nim', '$nama', '$prodi', '$email', '$password')";

    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn); // Mengembalikan nilai 1 jika sukses, -1 jika gagal
}

// 3. FUNGSI VALIDASI LOGIN SSO
function validasi_login($username, $password, $role)
{
    global $conn;
    // Logika SSO akan dibuat di proses_login.php
}

// FUNGSI 4: BIKIN ID OTOMATIS (VARCHAR 20)
// Cara pakai: $id_baru = generate_id("AST", "aset", "idAset");
function generate_id($prefix, $nama_tabel, $nama_pk)
{
    global $koneksi; // Pastikan nama variabel koneksi Anda sama!

    // Cari ID paling besar (Contoh: AST-005)
    $query = "SELECT MAX($nama_pk) as max_id FROM $nama_tabel WHERE $nama_pk LIKE '$prefix-%'";
    $hasil = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($hasil);

    if ($data['max_id']) {
        // Jika sudah ada data, potong angkanya (ambil dari karakter ke-4) dan tambah 1
        $angka = (int) substr($data['max_id'], strlen($prefix) + 1);
        $angka++;
    } else {
        // Jika tabel masih kosong
        $angka = 1;
    }

    // Format ulang jadi 5 digit angka (Contoh: AST-00006)
    $id_baru = $prefix . "-" . sprintf("%05s", $angka);

    return $id_baru;
}

// FUNGSI 5: UBAH DATA (UPDATE)
function ubah_data($nama_tabel, $data_array, $nama_pk, $id_nilai)
{
    global $koneksi;
    $set_query = [];
    foreach ($data_array as $kolom => $nilai) {
        $nilai_aman = mysqli_real_escape_string($koneksi, $nilai);
        $set_query[] = "$kolom = '$nilai_aman'";
    }
    $set_string = implode(", ", $set_query);
    $id_aman = mysqli_real_escape_string($koneksi, $id_nilai);

    $query = "UPDATE $nama_tabel SET $set_string WHERE $nama_pk = '$id_aman'";
    mysqli_query($koneksi, $query);
    return mysqli_affected_rows($koneksi);
}

/**
 * FUNGSI 6: GENERATE NIM MAHASISWA OTOMATIS
 * Logika: Mapping Prodi -> Ambil Tahun -> Cari MAX NIM -> Tambah 1
 */
function generate_nim_mahasiswa($nama_prodi)
{
    global $koneksi; // Sesuaikan dengan nama variabel koneksi Anda

    // 1. Mapping Nama Prodi ke Kode Angka (01 - 09)
    $map_prodi = [
        'P3P' => '01',
        'TPM' => '02',
        'MIN' => '03',
        'MOT' => '04',
        'MEK' => '05',
        'TKB' => '06',
        'TAB' => '07',
        'TRL' => '08',
        'RPL' => '09'
    ];

    // Ambil kode angka, jika tidak ada default ke '00'
    $kode_angka = isset($map_prodi[$nama_prodi]) ? $map_prodi[$nama_prodi] : '00';

    // 2. Ambil Tahun Saat Ini (Misal: 2026)
    $tahun = date("Y");
    $prefix = $kode_angka . $tahun; // Hasil: "092026"

    // 3. Cari NIM Terakhir di Database berdasarkan Prefix
    $query = mysqli_query($koneksi, "SELECT MAX(nimMahasiswa) AS max_nim FROM mahasiswa WHERE nimMahasiswa LIKE '$prefix%'");
    $data = mysqli_fetch_assoc($query);

    // 4. Logika Penambahan (Auto Increment Custom)
    if ($data['max_nim']) {
        // Potong 4 angka terakhir, ubah ke integer, lalu tambah 1
        $urutan = (int) substr($data['max_nim'], 6, 4);
        $urutan++;
    } else {
        // Jika belum ada mahasiswa di prodi & tahun tersebut
        $urutan = 1;
    }

    // 5. Gabungkan kembali (Format: 0920260001)
    return $prefix . sprintf("%04s", $urutan);
}

/**
 * FUNGSI 7: FORMAT & VALIDASI NOMOR TELEPON
 * Mencegah nomor dobel nol (misal +620812...)
 */
function format_no_telp($kode_negara, $nomor)
{
    // Bersihkan karakter selain angka
    $nomor_bersih = preg_replace('/[^0-9]/', '', $nomor);

    // Jika diawali angka 0 (misal 0812) -> buang 0 nya
    if (substr($nomor_bersih, 0, 1) == '0') {
        $nomor_bersih = substr($nomor_bersih, 1);
    }
    // Jika diawali angka 62 (karena iseng ngetik +62) -> buang 62 nya
    else if (substr($nomor_bersih, 0, 2) == '62') {
        $nomor_bersih = substr($nomor_bersih, 2);
    }

    // Gabungkan kode negara asli dengan nomor yang sudah bersih
    return $kode_negara . $nomor_bersih;
}

/**
 * FUNGSI 8: GENERATE KOMPONEN INPUT TELEPON (HTML + JS)
 * Bisa dipakai di form Create maupun Edit (tinggal kirim parameternya)
 */
function buat_input_telp($val_telp = '', $val_kode = '+62')
{
    $id_unik = "telp_" . rand(1000, 9999);
    $pilihan = ['+62' => '+62 (Indonesia)', '+1'  => '+1 (USA/Canada)', '+60' => '+60 (Malaysia)'];

    $html = '
    <div class="input-group">
        <div class="custom-dropdown-container" id="container_' . $id_unik . '" style="width: 100px;">
            <!-- PERBAIKAN: Hapus kata required di sini -->
            <input type="hidden" name="kode_negara" id="input_' . $id_unik . '" value="' . $val_kode . '"> 
            
            <div class="custom-dropdown-selected" onclick="toggleDropdown(\'' . $id_unik . '\')" style="border-top-right-radius: 0; border-bottom-right-radius: 0; height: 100%; display: flex; align-items: center; justify-content: space-between;">
                <span id="text_' . $id_unik . '">' . $val_kode . '</span>
                <i class="bi bi-chevron-down" style="font-size: 12px;"></i>
            </div>
            
            <div class="custom-dropdown-options shadow" id="options_' . $id_unik . '" style="width: 220px;">';
    foreach ($pilihan as $val => $label) {
        $aktif = ($val == $val_kode) ? 'active' : '';
        $html .= '<div class="custom-dropdown-item ' . $aktif . '" onclick="selectTelpOption(\'' . $id_unik . '\', \'' . $val . '\')">' . $label . '</div>';
    }
    $html .= '
            </div>
        </div>
        <input type="text" name="no_telp" class="form-control" required maxlength="12" style="border: 2px solid #e0e6ed; border-left: none; background-color: #f9fbfd; color: #1d4197; font-weight: 500;" oninput="this.value = this.value.replace(/[^0-9]/g, \'\');" placeholder="81234567890" value="' . $val_telp . '">
    </div>
    <script>
        if (typeof selectTelpOption === "undefined") {
            function selectTelpOption(id, nilai) {
                document.getElementById("text_" + id).innerText = nilai;
                document.getElementById("input_" + id).value = nilai;
                document.getElementById("options_" + id).style.display = "none";
                let opsi = document.getElementById("options_" + id).children;
                for(let i=0; i < opsi.length; i++){ opsi[i].classList.remove("active"); }
                event.target.classList.add("active");
            }
        }
    </script>
    ';
    return $html;
}


/**
 * FUNGSI 9: SET NOTIFIKASI POP-UP GLOBAL
 * Dipakai setelah proses Insert/Update/Delete selesai
 * Tipe: 'success' atau 'error'
 */
function set_notifikasi($tipe, $pesan)
{
    $_SESSION['notif_tipe'] = $tipe;
    $_SESSION['notif_pesan'] = $pesan;
    session_write_close();
}

/**
 * FUNGSI 8: UPDATE STATUS MAHASISWA OTOMATIS (SMART LOGIC)
 * Mengecek: Jika Denda > 0 ATAU Jam Minus > 0, maka Dibekukan. Jika nol semua, Normal.
 * Panggil fungsi ini setiap kali ada perubahan data denda/jam minus!
 */
function perbarui_status_mahasiswa($nim)
{
    global $koneksi;

    // 1. Ambil data denda dan jam minus terbaru dari database
    $query = mysqli_query($koneksi, "SELECT jamMinus_mahasiswa, dendaMahasiswa FROM mahasiswa WHERE nimMahasiswa = '$nim'");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        $jam = (int)$data['jamMinus_mahasiswa'];
        $denda = (int)$data['dendaMahasiswa'];

        // 2. Logika Cerdas penentuan status
        if ($jam > 0 || $denda > 0) {
            $status_baru = 'Dibekukan';
        } else {
            $status_baru = 'Normal';
        }

        // 3. Update otomatis ke database
        mysqli_query($koneksi, "UPDATE mahasiswa SET statusMahasiswa = '$status_baru' WHERE nimMahasiswa = '$nim'");
    }
}

/**
 * FUNGSI 10: DROPDOWN INTERAKTIF TEMA ASTARRENT (CUSTOM UI - ANTI MAC/SAFARI DEFAULT)
 * Menghasilkan dropdown yang 100% bisa diwarnai sesuai tema!
 */
function buat_dropdown_astar($nama_input, $array_pilihan, $nilai_lama = '', $wajib = true)
{
    $label_terpilih = "-- Pilih --";
    foreach ($array_pilihan as $val => $label) {
        if ($val == $nilai_lama) {
            $label_terpilih = $label;
            break;
        }
    }
    $id_unik = "drop_" . rand(1000, 9999);

    $html = '
    <div class="custom-dropdown-container" id="container_' . $id_unik . '">
        <!-- PERBAIKAN: Hapus kata required di sini -->
        <input type="hidden" name="' . $nama_input . '" id="input_' . $id_unik . '" value="' . $nilai_lama . '">
        
        <div class="custom-dropdown-selected" onclick="toggleDropdown(\'' . $id_unik . '\')">
            <span id="text_' . $id_unik . '">' . $label_terpilih . '</span>
            <i class="bi bi-chevron-down float-end"></i>
        </div>
        <div class="custom-dropdown-options shadow" id="options_' . $id_unik . '">';
    foreach ($array_pilihan as $val => $label) {
        $aktif = ($val == $nilai_lama) ? 'active' : '';
        $html .= '<div class="custom-dropdown-item ' . $aktif . '" onclick="selectOption(\'' . $id_unik . '\', \'' . $val . '\', \'' . $label . '\')">' . $label . '</div>';
    }
    $html .= '
        </div>
    </div>
    ';
    return $html;
}

/**
 * FUNGSI 11: CEK EMAIL GANDA (CROSS-TABLE VALIDATION)
 * Mengecek apakah email sudah dipakai di tabel Mahasiswa, Users, atau Supplier.
 * Return TRUE jika duplikat (tidak boleh dipakai), FALSE jika aman.
 */
function cek_email_ganda($email_input)
{
    global $koneksi;
    $email_aman = mysqli_real_escape_string($koneksi, $email_input);

    // Sihir SQL UNION: Menggabungkan 3 kolom email dari 3 tabel berbeda menjadi 1 daftar
    $query = "
        SELECT email FROM (
            SELECT emailMahasiswa AS email FROM mahasiswa
            UNION ALL
            SELECT emailUser AS email FROM users
            UNION ALL
            SELECT emailSupplier AS email FROM supplier
        ) AS semua_email 
        WHERE email = '$email_aman'
    ";

    $result = mysqli_query($koneksi, $query);

    // Jika hasilnya lebih dari 0, berarti email ketemu di salah satu tabel!
    if (mysqli_num_rows($result) > 0) {
        return true; // DITOLAK (Duplikat)
    }

    return false; // AMAN
}

/**
 * FUNGSI 12: MENGAMBIL DATA KATEGORI DARI DATABASE UNTUK DROPDOWN
 * $tipe = 'Aset' atau 'Fasilitas'
 */
function ambil_pilihan_kategori($tipe)
{
    global $koneksi;
    // Hanya ambil kategori yang Aktif dan tipenya sesuai
    $query = mysqli_query($koneksi, "SELECT idKategori, namaKategori FROM kategori WHERE tipeKategori = '$tipe' AND statusKategori = 'Aktif'");

    $pilihan = [];
    while ($row = mysqli_fetch_assoc($query)) {
        // Format array: ['ID_Kategori' => 'Nama Kategori']
        $pilihan[$row['idKategori']] = $row['namaKategori'];
    }
    return $pilihan;
}

/**
 * FUNGSI 13: UPDATE STATUS KETERSEDIAAN & KONDISI BARANG (STATE MACHINE)
 * Digunakan saat: Peminjaman di-Approve, Reparasi Diproses, Reparasi Selesai, atau Barang Rusak Total.
 * $tipe_barang = 'aset' atau 'fasilitas'
 */
function perbarui_status_barang($tipe_barang, $id_barang, $ketersediaan_baru, $kondisi_baru = null)
{
    global $koneksi;

    $kolom_pk = ($tipe_barang == 'aset') ? 'idAset' : 'idFasilitas';
    $kolom_sedia = ($tipe_barang == 'aset') ? 'ketersediaanAset' : 'ketersediaanFasilitas';
    $kolom_kondisi = ($tipe_barang == 'aset') ? 'kondisiAset' : 'kondisiFasilitas';

    // JIKA TENDIK MELAPORKAN BARANG ITU "RUSAK TOTAL" DI HALAMAN EDIT
    // Maka otomatis Ketersediaannya diubah jadi "Tidak Tersedia" (Soft Delete)
    if ($kondisi_baru == 'Rusak Total') {
        $ketersediaan_baru = 'Tidak Tersedia';
    }

    $query_update = "UPDATE $tipe_barang SET $kolom_sedia = '$ketersediaan_baru'";

    if ($kondisi_baru !== null) {
        $query_update .= ", $kolom_kondisi = '$kondisi_baru'";
    }

    $query_update .= " WHERE $kolom_pk = '$id_barang'";

    mysqli_query($koneksi, $query_update);
}

/**
 * FUNGSI 14: AMBIL BARANG TERSEDIA (Untuk Form Peminjaman)
 * $tipe = 'aset' atau 'fasilitas'
 */
function ambil_barang_tersedia($tipe)
{
    global $koneksi;

    $tabel = ($tipe == 'aset') ? 'aset' : 'fasilitas';
    $pk = ($tipe == 'aset') ? 'idAset' : 'idFasilitas';
    $nama = ($tipe == 'aset') ? 'namaAset' : 'namaFasilitas';
    $ketersediaan = ($tipe == 'aset') ? 'ketersediaanAset' : 'ketersediaanFasilitas';

    $query = mysqli_query($koneksi, "SELECT $pk, $nama FROM $tabel WHERE $ketersediaan = 'Tersedia'");

    $pilihan = [];
    while ($row = mysqli_fetch_assoc($query)) {
        // Tampilan di dropdown: "AST-00001 - Proyektor Epson"
        $pilihan[$row[$pk]] = $row[$pk] . " - " . $row[$nama];
    }
    return $pilihan;
}

/**
 * FUNGSI 15: AUTO-TOLAK PEMINJAMAN KEDALUWARSA
 * Berjalan otomatis untuk membatalkan request yang 'Menunggu' 
 * tapi waktu 'Rencana Kembali'-nya sudah terlewat.
 */
function validasi_kadaluwarsa_peminjaman()
{
    global $koneksi;

    // Zone Time Jakarta
    date_default_timezone_set('Asia/Jakarta');
    // Ambil tanggal dan jam saat ini di server
    $waktu_sekarang = date('Y-m-d H:i:s');

    // Query Sihir: Otomatis ubah jadi Ditolak jika waktu sekarang >= waktu rencana kembali
    // (Hanya berlaku untuk yang statusnya masih 'Menunggu')
    $query = "UPDATE transaksi_peminjaman 
              SET statusPeminjaman = 'Ditolak' 
              WHERE statusPeminjaman = 'Menunggu' AND tanggalRencana_kembali <= '$waktu_sekarang'";

    mysqli_query($koneksi, $query);
}

/**
 * FUNGSI 16: GENERATE INPUT TANGGAL & JAM (DATETIME-LOCAL)
 * Dilengkapi Smart Calendar: Min (Sekarang + 1 Jam), Max (Akhir Semester)
 */
function buat_input_datetime($nama_input, $nilai_lama = '', $wajib = true)
{
    // 1. Pastikan zona waktu WIB
    date_default_timezone_set('Asia/Jakarta');

    $waktu_sekarang = time(); // Detik saat ini

    // ==============================================================
    // 2. ATURAN MINIMAL (Batas Bawah): Waktu saat ini ditambah 1 Jam
    // ==============================================================
    // strtotime('+1 hour') otomatis nambahin 1 jam dari sekarang
    $waktu_minimal = date('Y-m-d\TH:i', strtotime('+1 hour', $waktu_sekarang));

    // ==============================================================
    // 3. ATURAN MAKSIMAL (Batas Atas): Tanggal Akhir Semester
    // ==============================================================
    $bulan_sekarang = date('n'); // Ambil angka bulan (1 sampai 12)
    $tahun_sekarang = date('Y'); // Ambil tahun sekarang

    // Asumsi Kalender Kampus:
    // Semester Genap = Maret (3) sampai Agustus (8)
    // Semester Ganjil = September (9) sampai Februari (2)

    if ($bulan_sekarang >= 3 && $bulan_sekarang <= 8) {
        // Jika sekarang bulan 3 s/d 8, berarti batasnya 31 Agustus tahun ini
        $waktu_maksimal = $tahun_sekarang . '-08-31T23:59';
        $nama_semester = "Genap";
    } else {
        // Jika Semester Ganjil, batasnya akhir bulan Februari
        // Kalau sekarang bulan Sept-Des, batas Feb-nya di tahun DEPAN (+1)
        $tahun_batas = ($bulan_sekarang >= 9) ? $tahun_sekarang + 1 : $tahun_sekarang;

        // date('Y-m-t') otomatis mencari tanggal terakhir di bulan tersebut (bisa 28 atau 29 Februari)
        $waktu_maksimal = date('Y-m-t\T23:59', strtotime($tahun_batas . '-02-01'));
        $nama_semester = "Ganjil";
    }

    // ==============================================================
    // 4. SET NILAI TAMPILAN DI KOTAK FORM
    // ==============================================================
    if (empty($nilai_lama)) {
        // Jika form buat pinjam baru, otomatis munculkan waktu Minimal (Sekarang + 1 Jam)
        $waktu_tampil = $waktu_minimal;
    } else {
        // Jika form Edit, tampilkan dari database
        $waktu_tampil = date('Y-m-d\TH:i', strtotime($nilai_lama));
    }

    $required = $wajib ? 'required' : '';

    // ==============================================================
    // 5. CETAK HTML KALENDERNYA (Suntikkan atribut min dan max)
    // ==============================================================
    // Tambahkan min="..." dan max="..." agar HTML5 mengunci kalendernya
    $html = '<input type="datetime-local" name="' . $nama_input . '" class="form-control bg-light" 
              value="' . $waktu_tampil . '" 
              min="' . $waktu_minimal . '" 
              max="' . $waktu_maksimal . '" 
              ' . $required . ' style="border: 2px solid #e0e6ed; color: #1d4197; font-weight: 500;">';

    // Tambahkan teks petunjuk di bawah kotaknya agar mahasiswa tahu batasnya!
    $html .= '<small class="text-danger mt-1 d-block" style="font-size:11px;">
                <i class="bi bi-info-circle-fill"></i> Batas akhir peminjaman: <b>' . date('d M Y', strtotime($waktu_maksimal)) . '</b> (Akhir Semester ' . $nama_semester . ').
              </small>';

    return $html;
}

/**
 * FUNGSI 17: VALIDASI OTORITAS TENDIK (ROW-LEVEL SECURITY)
 * Memastikan Tendik hanya bisa memproses transaksi dari mahasiswa prodi yang sama.
 * Return TRUE jika prodi sama (Aman), FALSE jika prodi beda (Hacker/Bypass).
 */
function validasi_otoritas_tendik($id_peminjaman, $kode_departemen_tendik)
{
    global $koneksi;

    // Query ajaib: Intip prodi mahasiswa dari dalam ID Peminjaman
    $query = "SELECT m.kodeProdi_mahasiswa 
              FROM transaksi_peminjaman tp
              JOIN mahasiswa m ON tp.nimMahasiswa = m.nimMahasiswa
              WHERE tp.idPeminjaman = '$id_peminjaman'";

    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);

    // Jika data ketemu dan prodi mahasiswa SAMA dengan departemen tendik
    if ($data && $data['kodeProdi_mahasiswa'] == $kode_departemen_tendik) {
        return true;  // AMAN, Silakan proses!
    }

    return false; // DITOLAK! Beda Prodi!
}

/**
 * FUNGSI 18: AUTO-REJECT BARANG BENTROK (DOUBLE BOOKING)
 * Jika 1 mahasiswa di-Approve, otomatis tolak mahasiswa lain yang request barang yang sama.
 */
function tolak_peminjaman_bentrok($id_aset, $id_fasilitas, $id_peminjaman_yg_menang)
{
    global $koneksi;

    // 1. Tentukan barang apa yang lagi direbutin (Pakai logika XOR kita)
    if (!empty($id_aset)) {
        $kondisi_barang = "idAset = '$id_aset'";
    } else {
        $kondisi_barang = "idFasilitas = '$id_fasilitas'";
    }

    // 2. Query Sapu Jagat (Auto-Reject)
    // Arti query ini: "Ubah status jadi DITOLAK untuk barang yang sama, 
    // yang statusnya masih MENUNGGU, KECUALI ID transaksi si pemenang!"
    $query = "UPDATE transaksi_peminjaman 
              SET statusPeminjaman = 'Ditolak' 
              WHERE $kondisi_barang 
              AND statusPeminjaman = 'Menunggu' 
              AND idPeminjaman != '$id_peminjaman_yg_menang'";

    mysqli_query($koneksi, $query);
}

/**
 * FUNGSI 19: TERAPKAN SANKSI KE MAHASISWA
 * Mesin ini akan menyedot nilai denda/jam dari Master Sanksi, 
 * lalu menambahkannya ke akun mahasiswa, dan otomatis membekukannya!
 */
function terapkan_sanksi_mahasiswa($nim, $id_sanksi)
{
    global $koneksi;

    // Kalau Tendik tidak milih sanksi (barangnya aman), matikan mesin ini.
    if (empty($id_sanksi)) {
        return;
    }

    // 1. Tanya ke Master Sanksi: "Sanksi ini denda dan jam minusnya berapa?"
    $q_sanksi = mysqli_query($koneksi, "SELECT sanksi_jamMinus, sanksi_denda FROM sanksi WHERE idSanksi = '$id_sanksi'");
    $sanksi = mysqli_fetch_assoc($q_sanksi);

    if ($sanksi) {
        $tambah_jam = (int)$sanksi['sanksi_jamMinus'];
        $tambah_denda = (int)$sanksi['sanksi_denda'];

        // 2. Tambahkan (Akumulasikan) ke profil mahasiswa tersebut
        // Sintaks "jamMinus_mahasiswa + $tambah_jam" itu artinya: Nilai lama ditambah nilai baru
        mysqli_query($koneksi, "UPDATE mahasiswa 
                                SET jamMinus_mahasiswa = jamMinus_mahasiswa + $tambah_jam, 
                                    dendaMahasiswa = dendaMahasiswa + $tambah_denda 
                                WHERE nimMahasiswa = '$nim'");

        // Karena dendanya nambah, fungsi ini bakal otomatis ngubah status mahasiswa jadi 'Dibekukan'
        perbarui_status_mahasiswa($nim);
    }
}

/**
 * FUNGSI 20: ROBOT PEMBUAT TIKET REPARASI OTOMATIS
 * Akan berjalan HANYA JIKA barang dikembalikan dalam keadaan rusak.
 */
function buat_tiket_reparasi_otomatis($id_pelapor, $id_aset, $id_fasilitas, $tingkat_rusak, $catatan_kerusakan = '')
{
    global $koneksi;
    if ($tingkat_rusak == 'Normal') {
        return;
    }

    $id_reparasi_baru = generate_id('REP', 'reparasi_fasilitas_aset', 'idReparasi');
    $waktu_lapor = date('Y-m-d H:i:s');
    $val_aset = !empty($id_aset) ? "'$id_aset'" : "NULL";
    $val_fasilitas = !empty($id_fasilitas) ? "'$id_fasilitas'" : "NULL";

    // Sisipkan catatan kerusakan di kolom klasifikasi (atau jika ada kolom khusus catatan)
    $query = "INSERT INTO reparasi_fasilitas_aset 
              (idReparasi, idTendik, idAset, idFasilitas, tanggalLapor, klasifikasiKerusakan, statusReparasi) 
              VALUES 
              ('$id_reparasi_baru', '$id_pelapor', $val_aset, $val_fasilitas, '$waktu_lapor', '$tingkat_rusak', 'Menunggu GA')";

    mysqli_query($koneksi, $query);
}

/**
 * FUNGSI 21: PENGHITUNG KETERLAMBATAN OTOMATIS
 * Membandingkan waktu sekarang dengan Rencana Kembali
 */
function hitung_keterlambatan($tgl_rencana_kembali)
{
    date_default_timezone_set('Asia/Jakarta');
    $waktu_sekarang = time(); // Waktu detik ini
    $waktu_rencana = strtotime($tgl_rencana_kembali);

    // Jika belum lewat batas waktu
    if ($waktu_sekarang <= $waktu_rencana) {
        return ['kategori' => 'Tepat Waktu', 'teks' => 'Aman / Tepat Waktu', 'warna' => 'success'];
    }

    // Jika telat, hitung selisih jamnya
    $selisih_detik = $waktu_sekarang - $waktu_rencana;
    $selisih_jam = floor($selisih_detik / 3600);

    if ($selisih_jam < 24) {
        return ['kategori' => 'Telat < 24 Jam', 'teks' => "Terlambat $selisih_jam Jam", 'warna' => 'warning'];
    } elseif ($selisih_jam <= 72) {
        $hari = floor($selisih_jam / 24);
        return ['kategori' => 'Telat 1-3 Hari', 'teks' => "Terlambat $hari Hari", 'warna' => 'danger'];
    } else {
        $hari = floor($selisih_jam / 24);
        return ['kategori' => 'Telat > 3 Hari', 'teks' => "Terlambat Parah ($hari Hari!)", 'warna' => 'dark'];
    }
}

/**
 * FUNGSI 22: PENCARI SANKSI KOMBO OTOMATIS
 * Menggabungkan Waktu dan Kondisi Fisik untuk mencari ID Sanksi di Database
 */
function cari_id_sanksi_otomatis($kategori_waktu, $kondisi_fisik)
{
    global $koneksi;

    // Jika semuanya aman, tidak ada sanksi (NULL)
    if ($kategori_waktu == 'Tepat Waktu' && $kondisi_fisik == 'Normal') {
        return "NULL";
    }

    // RAKIT KATA KUNCI PENCARIAN (Menyesuaikan dengan nama di tabel Sanksi kita)
    if ($kategori_waktu == 'Tepat Waktu') {
        $keyword = "Tepat Waktu + $kondisi_fisik";
    } elseif ($kondisi_fisik == 'Normal') {
        $keyword = "$kategori_waktu (Barang Aman/Normal)";
    } else {
        $keyword = "$kategori_waktu + $kondisi_fisik";
    }

    // Cari di database sanksi mana yang namanya mirip dengan keyword
    $query = mysqli_query($koneksi, "SELECT idSanksi FROM sanksi WHERE namaSanksi LIKE '%$keyword%' LIMIT 1");
    if ($row = mysqli_fetch_assoc($query)) {
        return "'" . $row['idSanksi'] . "'";
    }

    return "NULL"; // Jaga-jaga kalau sanksi tidak ketemu
}

/**
 * FUNGSI 23: MATRIKS OTOMATISASI SANKSI (VERSI REVISI: Normal, Berfungsi, Tidak Berfungsi)
 * Menghitung Sanksi Tepat Waktu (A) maupun Terlambat (B, C, D)
 */
function dapatkan_sanksi_otomatis($jam_terlambat, $kondisi_fisik)
{
    // Matriks Sanksi (Sesuai ID Sanksi SQL terbaru)
    // SKS-000 adalah kode bantuan jika Tepat Waktu & Normal (Tidak ada sanksi)
    $matrix = [
        'A' => ['Normal' => 'SNK-00000', 'Berfungsi' => 'SNK-00001', 'Tidak Berfungsi' => 'SNK-00002'],
        'B' => ['Normal' => 'SNK-00003', 'Berfungsi' => 'SNK-00004', 'Tidak Berfungsi' => 'SNK-00005'],
        'C' => ['Normal' => 'SNK-00006', 'Berfungsi' => 'SNK-00007', 'Tidak Berfungsi' => 'SNK-00008'],
        'D' => ['Normal' => 'SNK-00009', 'Berfungsi' => 'SNK-00010', 'Tidak Berfungsi' => 'SNK-00011']
    ];

    $hari_telat = floor($jam_terlambat / 24);

    // Penentuan Kategori Waktu
    if ($jam_terlambat <= 0) {
        $kategori = 'A'; // Tepat waktu
    } elseif ($jam_terlambat < 24) {
        $kategori = 'B'; // Telat < 24 Jam
    } elseif ($hari_telat >= 1 && $hari_telat <= 3) {
        $kategori = 'C'; // Telat 1 - 3 Hari
    } else {
        $kategori = 'D'; // Telat > 3 Hari
    }

    // Return ID Sanksi. Jika data tidak wajar/tidak ketemu, kembalikan aman (SNK-000)
    return isset($matrix[$kategori][$kondisi_fisik]) ? $matrix[$kategori][$kondisi_fisik] : 'SNK-00000';
}

/**
 * FUNGSI 24: AMBIL PILIHAN REPARASI RUSAK TOTAL UNTUK MASTER KOMPONEN
 * Dipakai saat Staff GA mencatat komponen hasil bongkar aset atau fasilitas rusak total.
 */
function ambil_pilihan_reparasi_rusak_total()
{
    global $koneksi;
    $opsi = [];

    $query = mysqli_query($koneksi, "SELECT r.idReparasi, r.klasifikasiKerusakan, r.statusReparasi, r.tanggalLapor,
                                            a.namaAset, f.namaFasilitas
                                     FROM reparasi_fasilitas_aset r
                                     LEFT JOIN aset a ON r.idAset = a.idAset
                                     LEFT JOIN fasilitas f ON r.idFasilitas = f.idFasilitas
                                     WHERE r.klasifikasiKerusakan = 'Tidak Berfungsi'
                                     ORDER BY r.tanggalLapor DESC, r.idReparasi DESC");

    while ($row = mysqli_fetch_assoc($query)) {
        $nama_barang = $row['namaAset'] ?: $row['namaFasilitas'];
        if (!$nama_barang) {
            $nama_barang = 'Barang tidak ditemukan';
        }

        $opsi[$row['idReparasi']] = $row['idReparasi'] . ' - ' . $nama_barang . ' (' . $row['statusReparasi'] . ')';
    }

    return $opsi;
}

/**
 * FUNGSI 25: VALIDASI REPARASI RUSAK TOTAL
 */
function reparasi_rusak_total_valid($id_reparasi)
{
    global $koneksi;
    $id_reparasi = mysqli_real_escape_string($koneksi, $id_reparasi);

    $query = mysqli_query($koneksi, "SELECT idReparasi FROM reparasi_fasilitas_aset
                                     WHERE idReparasi = '$id_reparasi'
                                     AND klasifikasiKerusakan = 'Rusak Total'
                                     LIMIT 1");

    return mysqli_num_rows($query) > 0;
}

/**
 * FUNGSI 26: VALIDASI KONDISI KOMPONEN
 */
function kondisi_komponen_valid($kondisi)
{
    $opsi = ['Sangat Baik', 'Layak Pakai'];
    return in_array($kondisi, $opsi, true);
}

/**
 * FUNGSI 27: VALIDASI STATUS KOMPONEN
 */
function status_komponen_valid($status)
{
    $opsi = ['Tersedia', 'Sudah Dipakai', 'Nonaktif'];
    return in_array($status, $opsi, true);
}

/**
 * FUNGSI 28: CEK DUPLIKASI KOMPONEN PADA SUMBER REPARASI YANG SAMA
 */
function komponen_duplikat($id_reparasi, $nama_komponen, $id_kecuali = null)
{
    global $koneksi;

    $id_reparasi = mysqli_real_escape_string($koneksi, $id_reparasi);
    $nama_komponen = mysqli_real_escape_string($koneksi, trim($nama_komponen));

    $where_kecuali = '';
    if ($id_kecuali !== null) {
        $id_kecuali = mysqli_real_escape_string($koneksi, $id_kecuali);
        $where_kecuali = " AND idKomponen != '$id_kecuali'";
    }

    $query = mysqli_query($koneksi, "SELECT idKomponen FROM komponen
                                     WHERE idReparasi = '$id_reparasi'
                                     AND LOWER(namaKomponen) = LOWER('$nama_komponen')
                                     AND statusKomponen != 'Nonaktif'
                                     $where_kecuali
                                     LIMIT 1");

    return mysqli_num_rows($query) > 0;
}

/**
 * FUNGSI 29: SCRIPT DINAMIS JABATAN & DEPARTEMEN (CLEAN CODE)
 * Menyembunyikan kerumitan JavaScript dari file create/edit.
 * Logika: Jika Tendik -> Muncul Dropdown Prodi. Jika bukan -> Text Auto-Fill Terkunci.
 */
function script_dinamis_jabatan_dept()
{
    $html = '
    <script>
        // Event Listener Global untuk mengawasi klik pada Dropdown Custom ASTARrent
        document.addEventListener("click", function(e) {
            
            // Mengecek apakah yang diklik adalah opsi dari sebuah Dropdown
            if (e.target.classList.contains("custom-dropdown-item")) {
                
                // Memastikan bahwa yang diklik adalah Dropdown "Jabatan"
                let container = e.target.closest(".custom-dropdown-container");
                let inputHidden = container.querySelector("input[name=\'jabatan\']");
                
                if (inputHidden) {
                    let jabatan = inputHidden.value;
                    let boxAuto = document.getElementById("dept_autofill_container");
                    let boxDrop = document.getElementById("dept_dropdown_container");
                    let textAuto = document.getElementById("dept_autofill_text");
                    let valAuto = document.getElementById("dept_autofill_value");

                    // Reset tampilan ke Auto-Fill terlebih dahulu
                    boxAuto.style.display = "block";
                    boxDrop.style.display = "none";
                    valAuto.value = ""; 

                    // LOGIKA CERDAS: Isi otomatis berdasarkan Jabatan
                    if (jabatan === "Staff GA" || jabatan === "Kepala GA") {
                        textAuto.value = "GA - General Affair";
                        valAuto.value = "GA";
                    } else if (jabatan === "Finance") {
                        textAuto.value = "FIN - Finance";
                        valAuto.value = "FIN";
                    } else if (jabatan === "Super Admin") {
                        textAuto.value = "SA - System Admin";
                        valAuto.value = "SA";
                    } else if (jabatan === "Tenaga Pendidik") {
                        // Khusus Tendik: Matikan Auto-Fill, Nyalakan Dropdown Prodi!
                        boxAuto.style.display = "none";
                        boxDrop.style.display = "block";
                        textAuto.value = ""; 
                    } else {
                        textAuto.value = "Otomatis terisi...";
                    }
                }
            }
        });
    </script>
    ';
    return $html;
}
