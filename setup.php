<?php
// ==========================================
// AUTO-INSTALLER DATABASE ASTARrent
// ==========================================

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "astarrent_db";

// 1. KONEKSI AWAL
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// 2. BUAT DATABASE
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $db_name");
mysqli_select_db($conn, $db_name);

echo "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9; color: #333;'>";
echo "<h2 style='color: #1d4197; text-align: center;'>⚙️ Proses Setup Database ASTARrent...</h2><hr>";

// 3. ARRAY QUERY PEMBUATAN TABEL (Sesuai XML PDM)
$tables = [
    // 1. MASTER USERS
    "CREATE TABLE IF NOT EXISTS users (
        idUser VARCHAR(20) PRIMARY KEY NOT NULL,
        namaUser VARCHAR(100) NOT NULL,
        noTelp_user VARCHAR(15) NOT NULL,
        jabatanUser ENUM('Tenaga Pendidik', 'Staff GA', 'Kepala GA', 'Finance', 'Super Admin') NOT NULL,
        emailUser VARCHAR(100) NOT NULL,
        passUser VARCHAR(255) NOT NULL,
        kodeDepartemen ENUM('P3P', 'TPM', 'MIN', 'MOT', 'MEK', 'TKB', 'TAB', 'TRL', 'RPL', 'GA', 'FIN', 'SA') NOT NULL,
        statusUser ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif' NOT NULL
    )",

    // 2. MASTER MAHASISWA
    "CREATE TABLE IF NOT EXISTS mahasiswa (
        nimMahasiswa VARCHAR(20) PRIMARY KEY NOT NULL,
        namaMahasiswa VARCHAR(100) NOT NULL,
        kodeProdi_mahasiswa ENUM('P3P', 'TPM', 'MIN', 'MOT', 'MEK', 'TKB', 'TAB', 'TRL', 'RPL') NOT NULL,
        noTelp_mahasiswa VARCHAR(15) NOT NULL,
        emailMahasiswa VARCHAR(100) NOT NULL,
        passMahasiswa VARCHAR(255) NOT NULL,
        jamMinus_mahasiswa INT DEFAULT 0 NOT NULL,
        dendaMahasiswa INT DEFAULT 0 NOT NULL,
        statusMahasiswa ENUM('Normal', 'Dibekukan', 'Nonaktif') DEFAULT 'Normal' NOT NULL
    )",

    // 3. MASTER SUPPLIER
    "CREATE TABLE IF NOT EXISTS supplier (
        idSupplier VARCHAR(20) PRIMARY KEY NOT NULL,
        namaSupplier VARCHAR(100) NOT NULL,
        noTelp_supplier VARCHAR(15) NOT NULL,
        emailSupplier VARCHAR(100) NOT NULL,
        passSupplier VARCHAR(255) NOT NULL,
        jumlahTugas_aktif INT DEFAULT 0 NOT NULL,
        statusSupplier ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif' NOT NULL
    )",

    // 4. MASTER SANKSI
    "CREATE TABLE IF NOT EXISTS sanksi (
        idSanksi VARCHAR(20) PRIMARY KEY NOT NULL,
        namaSanksi VARCHAR(100) NOT NULL,
        sanksi_jamMinus INT DEFAULT 0 NOT NULL,
        sanksi_denda INT DEFAULT 0 NOT NULL,
        klasifikasi_waktu ENUM('Tepat Waktu', 'Telat < 24 Jam', 'Telat 1-3 Hari', 'Telat > 3 Hari', 'Manual') NOT NULL,
        klasifikasi_kondisi ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi', 'Manual') NOT NULL,
        statusSanksi ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif' NOT NULL
    )",

    // 5. MASTER KATEGORI
    "CREATE TABLE IF NOT EXISTS kategori (
        idKategori VARCHAR(20) PRIMARY KEY NOT NULL,
        namaKategori VARCHAR(100) NOT NULL,
        statusKategori ENUM('Aktif', 'Nonaktif', 'Draft') DEFAULT 'Aktif' NOT NULL,
        tipeKategori ENUM('Aset', 'Fasilitas Akademik', 'Fasilitas Non-Akademik') NOT NULL,
        idPembuat VARCHAR(20) NOT NULL,
        FOREIGN KEY (idPembuat) REFERENCES users(idUser) ON UPDATE CASCADE
    )",

    // 6. TRANSAKSI PENGADAAN
    "CREATE TABLE IF NOT EXISTS transaksi_pengadaan (
        idPengadaan VARCHAR(20) PRIMARY KEY NOT NULL,
        idKategori VARCHAR(20) NOT NULL,
        idTendik VARCHAR(20) NOT NULL,
        idKepalaGA VARCHAR(20) NULL,
        idSupplier VARCHAR(20) NULL,
        idFinance VARCHAR(20) NULL,
        namaKebutuhan VARCHAR(255) NOT NULL,
        tanggalPengadaan DATETIME NOT NULL,
        jumlah INT NOT NULL,
        totalBiaya INT DEFAULT 0 NOT NULL,
        alasanKebutuhan TEXT NOT NULL,
        statusPengadaan ENUM('Draft', 'Disetujui GA', 'Harga Diinput Supplier', 'Disetujui Finance', 'Ditolak') DEFAULT 'Draft' NOT NULL,
        dokumen_pengajuan VARCHAR(255) NULL,
        dokumen_penawaran VARCHAR(255) NULL,
        alasanPenolakan_pengadaan TEXT NULL,
        FOREIGN KEY (idKategori) REFERENCES kategori(idKategori) ON UPDATE CASCADE,
        FOREIGN KEY (idTendik) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idKepalaGA) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idSupplier) REFERENCES supplier(idSupplier) ON UPDATE CASCADE,
        FOREIGN KEY (idFinance) REFERENCES users(idUser) ON UPDATE CASCADE
    )",

    // 6.5 DETAIL PENGADAAN VENDOR
    "CREATE TABLE IF NOT EXISTS detail_pengadaan_vendor (
        idDetail VARCHAR(20) PRIMARY KEY NOT NULL,
        idPengadaan VARCHAR(20) NOT NULL,
        namaVendor VARCHAR(100) NOT NULL,
        spesifikasi VARCHAR(255) NOT NULL,
        stok INT NOT NULL,
        hargaSatuan INT NOT NULL,
        estimasiTiba INT NOT NULL,
        statusPilihan ENUM('Menunggu', 'Terpilih', 'Ditolak') DEFAULT 'Menunggu' NOT NULL,
        tanggalJatuhTempo DATETIME NULL,
        statusKedatangan ENUM('Belum Tiba', 'Sudah Tiba') DEFAULT 'Belum Tiba' NOT NULL,
        FOREIGN KEY (idPengadaan) REFERENCES transaksi_pengadaan(idPengadaan) ON UPDATE CASCADE
    )",

    // 7. MASTER ASET
    "CREATE TABLE IF NOT EXISTS aset (
        idAset VARCHAR(20) PRIMARY KEY NOT NULL,
        idKategori VARCHAR(20) NOT NULL,
        idPengadaan VARCHAR(20) NULL,
        namaAset VARCHAR(100) NOT NULL,
        kondisiAset ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi') DEFAULT 'Normal' NOT NULL,
        ketersediaanAset ENUM('Tersedia', 'Dipinjam', 'Tidak Tersedia', 'Sedang Diperbaiki', 'Nonaktif') DEFAULT 'Tersedia' NOT NULL,
        FOREIGN KEY (idKategori) REFERENCES kategori(idKategori) ON UPDATE CASCADE,
        FOREIGN KEY (idPengadaan) REFERENCES transaksi_pengadaan(idPengadaan) ON UPDATE CASCADE
    )",

    // 8. MASTER FASILITAS
    "CREATE TABLE IF NOT EXISTS fasilitas (
        idFasilitas VARCHAR(20) PRIMARY KEY NOT NULL,
        idPengelola VARCHAR(20) NOT NULL,
        idKategori VARCHAR(20) NOT NULL,
        namaFasilitas VARCHAR(100) NOT NULL,
        lokasiFasilitas VARCHAR(100) NOT NULL,
        tipeFasilitas ENUM('Akademik', 'Non-Akademik') NOT NULL,
        kondisiFasilitas ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi') DEFAULT 'Normal' NOT NULL,
        ketersediaanFasilitas ENUM('Tersedia', 'Dipinjam', 'Tidak Tersedia', 'Sedang Diperbaiki', 'Nonaktif') DEFAULT 'Tersedia' NOT NULL,
        FOREIGN KEY (idPengelola) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idKategori) REFERENCES kategori(idKategori) ON UPDATE CASCADE
    )",

    // 9. TRANSAKSI PEMINJAMAN
    "CREATE TABLE IF NOT EXISTS transaksi_peminjaman (
        idPeminjaman VARCHAR(20) PRIMARY KEY NOT NULL,
        nimMahasiswa VARCHAR(20) NOT NULL,
        idPenyetuju VARCHAR(20) NULL,
        idAset VARCHAR(20) NULL,
        idFasilitas VARCHAR(20) NULL,
        tanggalPengajuan DATETIME NOT NULL,
        tanggalPeminjaman DATETIME NULL,
        tanggalRencana_kembali DATETIME NOT NULL,
        keperluan VARCHAR(255) NOT NULL,
        statusPeminjaman ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai') DEFAULT 'Menunggu' NOT NULL,
        alasanPenolakan_pengadaan TEXT NULL,
        FOREIGN KEY (nimMahasiswa) REFERENCES mahasiswa(nimMahasiswa) ON UPDATE CASCADE,
        FOREIGN KEY (idPenyetuju) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idAset) REFERENCES aset(idAset) ON UPDATE CASCADE,
        FOREIGN KEY (idFasilitas) REFERENCES fasilitas(idFasilitas) ON UPDATE CASCADE
    )",

    // 10. TRANSAKSI PENGEMBALIAN
    "CREATE TABLE IF NOT EXISTS transaksi_pengembalian (
        idPengembalian VARCHAR(20) PRIMARY KEY NOT NULL,
        idPeminjaman VARCHAR(20) NOT NULL UNIQUE,
        idPengurus VARCHAR(20) NOT NULL,
        idSanksi VARCHAR(20) NULL,
        tanggalPengembalian DATETIME NOT NULL,
        kondisiFisik ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi') NOT NULL,
        catatanPengembalian TEXT NULL,
        FOREIGN KEY (idPeminjaman) REFERENCES transaksi_peminjaman(idPeminjaman) ON UPDATE CASCADE,
        FOREIGN KEY (idPengurus) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idSanksi) REFERENCES sanksi(idSanksi) ON UPDATE CASCADE
    )",

    // 11. TRANSAKSI REPARASI
    "CREATE TABLE IF NOT EXISTS reparasi_fasilitas_aset (
        idReparasi VARCHAR(20) PRIMARY KEY NOT NULL,
        idPelapor VARCHAR(20) NOT NULL,
        idTeknisi VARCHAR(20) NULL,
        idAset VARCHAR(20) NULL,
        idFasilitas VARCHAR(20) NULL,
        tanggalLapor DATETIME NOT NULL,
        tanggalReparasi DATETIME NULL,
        tanggalSelesai DATETIME NULL,
        klasifikasiKerusakan ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi') NOT NULL,
        catatanReparasi TEXT NOT NULL,
        statusReparasi ENUM('Menunggu GA', 'Sedang Dikerjakan', 'Selesai', 'Dikanibal') DEFAULT 'Menunggu GA' NOT NULL,
        FOREIGN KEY (idPelapor) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idTeknisi) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idAset) REFERENCES aset(idAset) ON UPDATE CASCADE,
        FOREIGN KEY (idFasilitas) REFERENCES fasilitas(idFasilitas) ON UPDATE CASCADE
    )",

    // 12. MASTER KOMPONEN (Kanibal)
    "CREATE TABLE IF NOT EXISTS komponen (
        idKomponen VARCHAR(20) PRIMARY KEY NOT NULL,
        idReparasi VARCHAR(20) NOT NULL,
        namaKomponen VARCHAR(100) NOT NULL,
        spesifikasiKomponen VARCHAR(255) NULL,
        kondisiKomponen ENUM('Sangat Baik', 'Layak Pakai') NOT NULL,
        statusKomponen ENUM('Tersedia', 'Sudah Dipakai') DEFAULT 'Tersedia' NOT NULL,
        tanggalMasuk DATETIME NOT NULL,
        FOREIGN KEY (idReparasi) REFERENCES reparasi_fasilitas_aset(idReparasi) ON UPDATE CASCADE
    )"
];

// Eksekusi Pembuatan Tabel
$berhasil = 0;
foreach ($tables as $index => $sql) {
    if (mysqli_query($conn, $sql)) {
        $berhasil++;
    } else {
        echo "<p style='color:red;'>Gagal membuat tabel ke-" . ($index + 1) . ": " . mysqli_error($conn) . "</p>";
    }
}

// ==========================================
// 4. PEMBUATAN 9 LOGIKA DATABASE (TRIGGERS, UDF, SP)
// ==========================================
$db_logic = [
    // --- 1. TRIGGERS ---
    "DROP TRIGGER IF EXISTS trg_update_status_mahasiswa",
    "CREATE TRIGGER trg_update_status_mahasiswa BEFORE UPDATE ON mahasiswa FOR EACH ROW
    BEGIN
        IF NEW.jamMinus_mahasiswa > 0 OR NEW.dendaMahasiswa > 0 THEN
            SET NEW.statusMahasiswa = 'Dibekukan';
        ELSE
            SET NEW.statusMahasiswa = 'Normal';
        END IF;
    END",

    "DROP TRIGGER IF EXISTS trg_otomasi_ketersediaan_barang",
    "CREATE TRIGGER trg_otomasi_ketersediaan_barang AFTER UPDATE ON transaksi_peminjaman FOR EACH ROW
    BEGIN
        IF NEW.statusPeminjaman = 'Disetujui' AND OLD.statusPeminjaman = 'Menunggu' THEN
            IF NEW.idAset IS NOT NULL THEN
                UPDATE aset SET ketersediaanAset = 'Dipinjam' WHERE idAset = NEW.idAset;
            ELSEIF NEW.idFasilitas IS NOT NULL THEN
                UPDATE fasilitas SET ketersediaanFasilitas = 'Dipinjam' WHERE idFasilitas = NEW.idFasilitas;
            END IF;
        END IF;
    END",

    "DROP TRIGGER IF EXISTS trg_hitung_beban_supplier",
    "CREATE TRIGGER trg_hitung_beban_supplier AFTER UPDATE ON transaksi_pengadaan FOR EACH ROW
    BEGIN
        IF NEW.statusPengadaan = 'Disetujui GA' AND OLD.statusPengadaan = 'Draft' THEN
            UPDATE supplier SET jumlahTugas_aktif = jumlahTugas_aktif + 1 WHERE idSupplier = NEW.idSupplier;
        ELSEIF NEW.statusPengadaan = 'Harga Diinput Supplier' AND OLD.statusPengadaan = 'Disetujui GA' THEN
            UPDATE supplier SET jumlahTugas_aktif = GREATEST(0, jumlahTugas_aktif - 1) WHERE idSupplier = NEW.idSupplier;
        END IF;
    END",

    // --- 2. UDF (USER DEFINED FUNCTIONS) ---
    "DROP FUNCTION IF EXISTS udf_cek_email_ganda",
    "CREATE FUNCTION udf_cek_email_ganda(p_email VARCHAR(100)) RETURNS INT DETERMINISTIC
    BEGIN
        DECLARE v_count INT DEFAULT 0;
        SELECT COUNT(*) INTO v_count FROM (
            SELECT emailMahasiswa AS email FROM mahasiswa
            UNION ALL
            SELECT emailUser AS email FROM users
            UNION ALL
            SELECT emailSupplier AS email FROM supplier
        ) AS gabungan WHERE email = p_email;
        RETURN IF(v_count > 0, 1, 0);
    END",

    "DROP FUNCTION IF EXISTS udf_hitung_jam_telat",
    "CREATE FUNCTION udf_hitung_jam_telat(p_waktu_rencana DATETIME, p_waktu_aktual DATETIME) RETURNS INT DETERMINISTIC
    BEGIN
        DECLARE v_jam_telat INT DEFAULT 0;
        IF p_waktu_aktual > p_waktu_rencana THEN
            SET v_jam_telat = TIMESTAMPDIFF(HOUR, p_waktu_rencana, p_waktu_aktual);
        END IF;
        RETURN v_jam_telat;
    END",

    "DROP FUNCTION IF EXISTS udf_generate_id_aset",
    "CREATE FUNCTION udf_generate_id_aset() RETURNS VARCHAR(20) READS SQL DATA
    BEGIN
        DECLARE v_max_id VARCHAR(20);
        DECLARE v_angka INT;
        SELECT MAX(idAset) INTO v_max_id FROM aset WHERE idAset LIKE 'AST-%';
        IF v_max_id IS NOT NULL THEN
            SET v_angka = CAST(SUBSTRING(v_max_id, 5) AS UNSIGNED) + 1;
        ELSE
            SET v_angka = 1;
        END IF;
        RETURN CONCAT('AST-', LPAD(v_angka, 5, '0'));
    END",

    // --- 3. STORED PROCEDURES (SP) ---
    "DROP PROCEDURE IF EXISTS sp_tolak_peminjaman_bentrok",
    "CREATE PROCEDURE sp_tolak_peminjaman_bentrok(IN p_id_peminjaman VARCHAR(20), IN p_id_aset VARCHAR(20), IN p_id_fasilitas VARCHAR(20))
    BEGIN
        IF p_id_aset IS NOT NULL THEN
            UPDATE transaksi_peminjaman SET statusPeminjaman = 'Ditolak', alasanPenolakan_peminjaman = 'Barang telah dipinjam orang lain (Double Booking)' 
            WHERE idAset = p_id_aset AND statusPeminjaman = 'Menunggu' AND idPeminjaman != p_id_peminjaman;
        ELSEIF p_id_fasilitas IS NOT NULL THEN
            UPDATE transaksi_peminjaman SET statusPeminjaman = 'Ditolak', alasanPenolakan_peminjaman = 'Fasilitas telah di-booking orang lain (Double Booking)' 
            WHERE idFasilitas = p_id_fasilitas AND statusPeminjaman = 'Menunggu' AND idPeminjaman != p_id_peminjaman;
        END IF;
    END",

    "DROP PROCEDURE IF EXISTS sp_terapkan_sanksi_mahasiswa",
    "CREATE PROCEDURE sp_terapkan_sanksi_mahasiswa(IN p_nim VARCHAR(20), IN p_id_sanksi VARCHAR(20))
    BEGIN
        DECLARE v_jam INT DEFAULT 0;
        DECLARE v_denda INT DEFAULT 0;
        IF p_id_sanksi IS NOT NULL AND p_id_sanksi != 'NULL' THEN
            SELECT sanksi_jamMinus, sanksi_denda INTO v_jam, v_denda FROM sanksi WHERE idSanksi = p_id_sanksi;
            UPDATE mahasiswa SET jamMinus_mahasiswa = jamMinus_mahasiswa + v_jam, dendaMahasiswa = dendaMahasiswa + v_denda 
            WHERE nimMahasiswa = p_nim;
        END IF;
    END",

    "DROP PROCEDURE IF EXISTS sp_buat_tiket_reparasi",
    "CREATE PROCEDURE sp_buat_tiket_reparasi(IN p_id_reparasi_baru VARCHAR(20), IN p_pelapor VARCHAR(20), IN p_id_aset VARCHAR(20), IN p_id_fasilitas VARCHAR(20), IN p_kerusakan VARCHAR(50), IN p_catatan TEXT)
    BEGIN
        DECLARE v_id_tiket_lama VARCHAR(20);
        IF p_kerusakan != 'Normal' THEN
            SELECT idReparasi INTO v_id_tiket_lama FROM reparasi_fasilitas_aset WHERE (idAset = p_id_aset OR idFasilitas = p_id_fasilitas) AND statusReparasi = 'Menunggu GA' LIMIT 1;
            IF v_id_tiket_lama IS NOT NULL THEN
                UPDATE reparasi_fasilitas_aset SET klasifikasiKerusakan = p_kerusakan, catatanReparasi = CONCAT(catatanReparasi, '\n[Update]: ', p_catatan) WHERE idReparasi = v_id_tiket_lama;
            ELSE
                INSERT INTO reparasi_fasilitas_aset (idReparasi, idPelapor, idAset, idFasilitas, tanggalLapor, klasifikasiKerusakan, statusReparasi, catatanReparasi) 
                VALUES (p_id_reparasi_baru, p_pelapor, p_id_aset, p_id_fasilitas, NOW(), p_kerusakan, 'Menunggu GA', p_catatan);
            END IF;
        END IF;
    END"
];

$berhasil_logic = 0;
foreach ($db_logic as $sql) {
    if (mysqli_query($conn, $sql)) {
        if (strpos($sql, 'DROP') === false) {
            $berhasil_logic++;
        }
    } else {
        echo "<p style='color:red;'>Gagal membuat Logic Database: " . mysqli_error($conn) . "</p>";
    }
}

// 4. DATA SEEDING (Menggunakan VARCHAR 20)
$password_default = password_hash("12345", PASSWORD_DEFAULT);

// A. Insert Users
$q_users = "INSERT IGNORE INTO users (idUser, namaUser, noTelp_user, jabatanUser, emailUser, passUser, kodeDepartemen) VALUES
('SA-00000', 'IT Pusat ASTRAtech', '+62812212121', 'Super Admin', 'admin@astratech.ac.id', '$password_default', 'SA'),
('GA-00001', 'Ridzal', '+62812212211', 'Kepala GA', 'kGA@astratech.ac.id', '$password_default', 'GA'),
('GA-00002', 'Rimba', '+62812211122', 'Staff GA', 'sGA@astratech.ac.id', '$password_default', 'GA'),
('FIN-00001', 'Samba', '+62812211212', 'Finance', 'FIN@astratech.ac.id', '$password_default', 'FIN'),
('TDK-00001', 'Bapak Budi', '+62813313131', 'Tenaga Pendidik', 'tendik@astratech.ac.id', '$password_default', 'RPL')";
mysqli_query($conn, $q_users);

// A. Insert Supplier
$q_suppliers = "INSERT IGNORE INTO supplier (idSupplier, namaSupplier, noTelp_supplier, emailSupplier, passSupplier) VALUES
('SPL-00001', 'Dola', '+62813313333', 'supplier@astratech.ac.id', '$password_default')";
mysqli_query($conn, $q_suppliers);

// B. Insert Mahasiswa TRPL
$q_mhs = "INSERT IGNORE INTO mahasiswa (nimMahasiswa, namaMahasiswa, kodeProdi_mahasiswa, noTelp_mahasiswa, emailMahasiswa, passMahasiswa) VALUES
('0920260001', 'Andi Pratama', 'RPL', '+6281414141', 'andi@student.astratech.ac.id', '$password_default')";
mysqli_query($conn, $q_mhs);

// C. Insert Kategori
$q_kategori = "INSERT IGNORE INTO kategori (idKategori, namaKategori, tipeKategori, idPembuat) VALUES
('KTG-00001', 'Proyektor', 'Aset', 'TDK-00001'),
('KTG-00002', 'Ruang Kelas', 'Fasilitas Akademik', 'GA-00002'),
('KTG-00003', 'Laptop', 'Aset', 'TDK-00001'),
('KTG-00004', 'Komunal', 'Fasilitas Non-Akademik', 'GA-00002')";
mysqli_query($conn, $q_kategori);

// D. Insert Aset Dummy (Biar Mahasiswa bisa langsung klik pinjam pas demo!)
$q_aset = "INSERT IGNORE INTO aset (idAset, idKategori, namaAset) VALUES
('AST-00001', 'KTG-00001', 'Proyektor Epson EB-X400')";
mysqli_query($conn, $q_aset);

// E. Insert Sanksi Dummy (Biar Mahasiswa bisa langsung klik pinjam pas demo!)
$q_sanksi = "INSERT IGNORE INTO sanksi (idSanksi, namaSanksi, sanksi_jamMinus, sanksi_denda, klasifikasi_waktu, klasifikasi_kondisi, statusSanksi) VALUES
('SNK-00000', 'Tepat Waktu + Normal (Aman)', 0, 0, 'Tepat Waktu', 'Normal', 'Aktif'),
('SNK-00001', 'Tepat Waktu + Rusak (Masih Berfungsi)', 2, 50000, 'Tepat Waktu', 'Berfungsi', 'Aktif'),
('SNK-00002', 'Tepat Waktu + Rusak (Tidak Berfungsi)', 10, 500000, 'Tepat Waktu', 'Tidak Berfungsi', 'Aktif'),
('SNK-00003', 'Telat < 24 Jam (Barang Normal)', 2, 0, 'Telat < 24 Jam', 'Normal', 'Aktif'),
('SNK-00004', 'Telat < 24 Jam + Rusak (Masih Berfungsi)', 4, 50000, 'Telat < 24 Jam', 'Berfungsi', 'Aktif'),
('SNK-00005', 'Telat < 24 Jam + Rusak (Tidak Berfungsi)', 12, 500000, 'Telat < 24 Jam', 'Tidak Berfungsi', 'Aktif'),
('SNK-00006', 'Telat 1-3 Hari (Barang Normal)', 5, 25000, 'Telat 1-3 Hari', 'Normal', 'Aktif'),
('SNK-00007', 'Telat 1-3 Hari + Rusak (Masih Berfungsi)', 7, 75000, 'Telat 1-3 Hari', 'Berfungsi', 'Aktif'),
('SNK-00008', 'Telat 1-3 Hari + Rusak (Tidak Berfungsi)', 15, 525000, 'Telat 1-3 Hari', 'Tidak Berfungsi', 'Aktif'),
('SNK-00009', 'Telat > 3 Hari (Barang Normal)', 10, 75000, 'Telat > 3 Hari', 'Normal', 'Aktif'),
('SNK-00010', 'Telat > 3 Hari + Rusak (Masih Berfungsi)', 12, 125000, 'Telat > 3 Hari', 'Berfungsi', 'Aktif'),
('SNK-00011', 'Telat > 3 Hari + Rusak (Tidak Berfungsi)', 20, 575000, 'Telat > 3 Hari', 'Tidak Berfungsi', 'Aktif'),
('SNK-00012', 'Batal Sepihak / No-Show', 3, 0, 'Manual', 'Manual', 'Aktif'),
('SNK-00013', 'Meninggalkan Fasilitas Berantakan', 5, 0, 'Manual', 'Manual', 'Aktif'),
('SNK-00014', 'Penyalahgunaan Hak Pinjam', 30, 500000, 'Manual', 'Manual', 'Aktif'),
('SNK-00015', 'Kehilangan Aset Kampus', 50, 3500000, 'Manual', 'Manual', 'Aktif')";
mysqli_query($conn, $q_sanksi);

// Cek Hasil
if ($berhasil == count($tables) && $berhasil_logic == 9) {
    echo "<div style='background-color: #d4edda; border-left: 5px solid #28a745; padding: 15px; margin: 20px 0;'>";
    echo "<h3>🎉 INSTALASI DATABASE (ENTERPRISE) SELESAI 100%!</h3>";
    echo "<p>Berhasil membuat: <br>✅ <b>13 Tabel Database</b><br>✅ <b>3 Triggers</b><br>✅ <b>3 User Defined Functions (UDF)</b><br>✅ <b>3 Stored Procedures (SP)</b></p>";
    echo "<p>Data Dummy User, Mahasiswa, Aset, dan Sanksi telah disuntikkan!</p>";
    echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #1d4197; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Buka Halaman Login</a>";
    echo "</div>";
} else {
    echo "<div style='background-color: #f8d7da; border-left: 5px solid #dc3545; padding: 15px; margin: 20px 0;'>";
    echo "<h3>⚠️ Peringatan</h3>";
    echo "<p>Tabel ter-install: $berhasil / " . count($tables) . "</p>";
    echo "<p>Logic DB ter-install: $berhasil_logic / 9</p>";
    echo "</div>";
}

echo "</div>"; // Tutup div style
