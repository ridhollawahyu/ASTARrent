<?php
// ==========================================
// AUTO-INSTALLER DATABASE ASTARrent (WITH 20+ DUMMY DATA PER TABLE)
// ==========================================

$host = "localhost";
$user = "root";
$pass = ""; // Sesuaikan jika ada password XAMPP
$db_name = "astarrent_db";

$conn = mysqli_connect($host, $user, $pass);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $db_name");
mysqli_select_db($conn, $db_name);

echo "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9; color: #333;'>";
echo "<h2 style='color: #1d4197; text-align: center;'>⚙️ Proses Setup & Seeding Database ASTARrent...</h2><hr>";

// ==========================================
// 1. PEMBUATAN 13 TABEL (SKEMA RELASIONAL)
// ==========================================
$tables = [
    "CREATE TABLE IF NOT EXISTS users (idUser VARCHAR(20) PRIMARY KEY NOT NULL, namaUser VARCHAR(100) NOT NULL, noTelp_user VARCHAR(15) NOT NULL, jabatanUser ENUM('Tenaga Pendidik', 'Staff GA', 'Kepala GA', 'Finance', 'Super Admin') NOT NULL, emailUser VARCHAR(100) NOT NULL, passUser VARCHAR(255) NOT NULL, kodeDepartemen ENUM('P3P', 'TPM', 'MIN', 'MOT', 'MEK', 'TKB', 'TAB', 'TRL', 'RPL', 'GA', 'FIN', 'SA') NOT NULL, statusUser ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif' NOT NULL)",
    "CREATE TABLE IF NOT EXISTS mahasiswa (nimMahasiswa VARCHAR(20) PRIMARY KEY NOT NULL, namaMahasiswa VARCHAR(100) NOT NULL, kodeProdi_mahasiswa ENUM('P3P', 'TPM', 'MIN', 'MOT', 'MEK', 'TKB', 'TAB', 'TRL', 'RPL') NOT NULL, noTelp_mahasiswa VARCHAR(15) NOT NULL, emailMahasiswa VARCHAR(100) NOT NULL, passMahasiswa VARCHAR(255) NOT NULL, jamMinus_mahasiswa INT DEFAULT 0 NOT NULL, dendaMahasiswa INT DEFAULT 0 NOT NULL, statusMahasiswa ENUM('Normal', 'Dibekukan', 'Nonaktif') DEFAULT 'Normal' NOT NULL)",
    "CREATE TABLE IF NOT EXISTS supplier (idSupplier VARCHAR(20) PRIMARY KEY NOT NULL, namaSupplier VARCHAR(100) NOT NULL, noTelp_supplier VARCHAR(15) NOT NULL, emailSupplier VARCHAR(100) NOT NULL, passSupplier VARCHAR(255) NOT NULL, jumlahTugas_aktif INT DEFAULT 0 NOT NULL, statusSupplier ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif' NOT NULL)",
    "CREATE TABLE IF NOT EXISTS sanksi (idSanksi VARCHAR(20) PRIMARY KEY NOT NULL, namaSanksi VARCHAR(100) NOT NULL, sanksi_jamMinus INT DEFAULT 0 NOT NULL, sanksi_denda INT DEFAULT 0 NOT NULL, klasifikasi_waktu ENUM('Tepat Waktu', 'Telat < 24 Jam', 'Telat 1-3 Hari', 'Telat > 3 Hari', 'Manual') NOT NULL, klasifikasi_kondisi ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi', 'Manual') NOT NULL, statusSanksi ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif' NOT NULL)",
    "CREATE TABLE IF NOT EXISTS kategori (idKategori VARCHAR(20) PRIMARY KEY NOT NULL, namaKategori VARCHAR(100) NOT NULL, statusKategori ENUM('Aktif', 'Nonaktif', 'Draft') DEFAULT 'Aktif' NOT NULL, tipeKategori ENUM('Aset', 'Fasilitas Akademik', 'Fasilitas Non-Akademik') NOT NULL, idPembuat VARCHAR(20) NOT NULL, FOREIGN KEY (idPembuat) REFERENCES users(idUser) ON UPDATE CASCADE)",
    "CREATE TABLE IF NOT EXISTS transaksi_pengadaan (idPengadaan VARCHAR(20) PRIMARY KEY NOT NULL, idKategori VARCHAR(20) NOT NULL, idTendik VARCHAR(20) NOT NULL, idKepalaGA VARCHAR(20) NULL, idSupplier VARCHAR(20) NULL, idFinance VARCHAR(20) NULL, namaKebutuhan VARCHAR(255) NOT NULL, tanggalPengadaan DATETIME NOT NULL, jumlah INT NOT NULL, totalBiaya INT DEFAULT 0 NOT NULL, alasanKebutuhan TEXT NOT NULL, statusPengadaan ENUM('Draft', 'Disetujui GA', 'Harga Diinput Supplier', 'Disetujui Finance', 'Ditolak') DEFAULT 'Draft' NOT NULL, dokumen_pengajuan VARCHAR(255) NULL, dokumen_penawaran VARCHAR(255) NULL, alasanPenolakan_pengadaan TEXT NULL, FOREIGN KEY (idKategori) REFERENCES kategori(idKategori) ON UPDATE CASCADE, FOREIGN KEY (idTendik) REFERENCES users(idUser) ON UPDATE CASCADE, FOREIGN KEY (idKepalaGA) REFERENCES users(idUser) ON UPDATE CASCADE, FOREIGN KEY (idSupplier) REFERENCES supplier(idSupplier) ON UPDATE CASCADE, FOREIGN KEY (idFinance) REFERENCES users(idUser) ON UPDATE CASCADE)",
    "CREATE TABLE IF NOT EXISTS detail_pengadaan_vendor (idDetail VARCHAR(20) PRIMARY KEY NOT NULL, idPengadaan VARCHAR(20) NOT NULL, namaVendor VARCHAR(100) NOT NULL, spesifikasi VARCHAR(255) NOT NULL, stok INT NOT NULL, hargaSatuan INT NOT NULL, estimasiTiba INT NOT NULL, statusPilihan ENUM('Menunggu', 'Terpilih', 'Ditolak') DEFAULT 'Menunggu' NOT NULL, tanggalJatuhTempo DATETIME NULL, statusKedatangan ENUM('Belum Tiba', 'Sudah Tiba') DEFAULT 'Belum Tiba' NOT NULL, FOREIGN KEY (idPengadaan) REFERENCES transaksi_pengadaan(idPengadaan) ON UPDATE CASCADE)",
    "CREATE TABLE IF NOT EXISTS aset (idAset VARCHAR(20) PRIMARY KEY NOT NULL, idKategori VARCHAR(20) NOT NULL, idPengadaan VARCHAR(20) NULL, namaAset VARCHAR(100) NOT NULL, kondisiAset ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi') DEFAULT 'Normal' NOT NULL, ketersediaanAset ENUM('Tersedia', 'Dipinjam', 'Tidak Tersedia', 'Sedang Diperbaiki', 'Nonaktif') DEFAULT 'Tersedia' NOT NULL, FOREIGN KEY (idKategori) REFERENCES kategori(idKategori) ON UPDATE CASCADE, FOREIGN KEY (idPengadaan) REFERENCES transaksi_pengadaan(idPengadaan) ON UPDATE CASCADE)",
    "CREATE TABLE IF NOT EXISTS fasilitas (idFasilitas VARCHAR(20) PRIMARY KEY NOT NULL, idPengelola VARCHAR(20) NOT NULL, idKategori VARCHAR(20) NOT NULL, namaFasilitas VARCHAR(100) NOT NULL, lokasiFasilitas VARCHAR(100) NOT NULL, tipeFasilitas ENUM('Akademik', 'Non-Akademik') NOT NULL, kondisiFasilitas ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi') DEFAULT 'Normal' NOT NULL, ketersediaanFasilitas ENUM('Tersedia', 'Dipinjam', 'Tidak Tersedia', 'Sedang Diperbaiki', 'Nonaktif') DEFAULT 'Tersedia' NOT NULL, FOREIGN KEY (idPengelola) REFERENCES users(idUser) ON UPDATE CASCADE, FOREIGN KEY (idKategori) REFERENCES kategori(idKategori) ON UPDATE CASCADE)",
    "CREATE TABLE IF NOT EXISTS transaksi_peminjaman (idPeminjaman VARCHAR(20) PRIMARY KEY NOT NULL, nimMahasiswa VARCHAR(20) NOT NULL, idPenyetuju VARCHAR(20) NULL, idAset VARCHAR(20) NULL, idFasilitas VARCHAR(20) NULL, tanggalPengajuan DATETIME NOT NULL, tanggalPeminjaman DATETIME NULL, tanggalRencana_kembali DATETIME NOT NULL, keperluan VARCHAR(255) NOT NULL, statusPeminjaman ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai') DEFAULT 'Menunggu' NOT NULL, alasanPenolakan_peminjaman TEXT NULL, FOREIGN KEY (nimMahasiswa) REFERENCES mahasiswa(nimMahasiswa) ON UPDATE CASCADE, FOREIGN KEY (idPenyetuju) REFERENCES users(idUser) ON UPDATE CASCADE, FOREIGN KEY (idAset) REFERENCES aset(idAset) ON UPDATE CASCADE, FOREIGN KEY (idFasilitas) REFERENCES fasilitas(idFasilitas) ON UPDATE CASCADE)",
    "CREATE TABLE IF NOT EXISTS transaksi_pengembalian (idPengembalian VARCHAR(20) PRIMARY KEY NOT NULL, idPeminjaman VARCHAR(20) NOT NULL UNIQUE, idPengurus VARCHAR(20) NOT NULL, idSanksi VARCHAR(20) NULL, tanggalPengembalian DATETIME NOT NULL, kondisiFisik ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi') NOT NULL, catatanPengembalian TEXT NULL, FOREIGN KEY (idPeminjaman) REFERENCES transaksi_peminjaman(idPeminjaman) ON UPDATE CASCADE, FOREIGN KEY (idPengurus) REFERENCES users(idUser) ON UPDATE CASCADE, FOREIGN KEY (idSanksi) REFERENCES sanksi(idSanksi) ON UPDATE CASCADE)",
    "CREATE TABLE IF NOT EXISTS reparasi_fasilitas_aset (idReparasi VARCHAR(20) PRIMARY KEY NOT NULL, idPelapor VARCHAR(20) NOT NULL, idTeknisi VARCHAR(20) NULL, idAset VARCHAR(20) NULL, idFasilitas VARCHAR(20) NULL, tanggalLapor DATETIME NOT NULL, tanggalReparasi DATETIME NULL, tanggalSelesai DATETIME NULL, klasifikasiKerusakan ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi') NOT NULL, catatanReparasi TEXT NOT NULL, statusReparasi ENUM('Menunggu GA', 'Sedang Dikerjakan', 'Selesai', 'Dikanibal') DEFAULT 'Menunggu GA' NOT NULL, FOREIGN KEY (idPelapor) REFERENCES users(idUser) ON UPDATE CASCADE, FOREIGN KEY (idTeknisi) REFERENCES users(idUser) ON UPDATE CASCADE, FOREIGN KEY (idAset) REFERENCES aset(idAset) ON UPDATE CASCADE, FOREIGN KEY (idFasilitas) REFERENCES fasilitas(idFasilitas) ON UPDATE CASCADE)",
    "CREATE TABLE IF NOT EXISTS komponen (idKomponen VARCHAR(20) PRIMARY KEY NOT NULL, idReparasi VARCHAR(20) NOT NULL, namaKomponen VARCHAR(100) NOT NULL, spesifikasiKomponen VARCHAR(255) NULL, kondisiKomponen ENUM('Sangat Baik', 'Layak Pakai') NOT NULL, statusKomponen ENUM('Tersedia', 'Sudah Dipakai', 'Nonaktif') DEFAULT 'Tersedia' NOT NULL, tanggalMasuk DATETIME NOT NULL, FOREIGN KEY (idReparasi) REFERENCES reparasi_fasilitas_aset(idReparasi) ON UPDATE CASCADE)"
];

$berhasil = 0;
foreach ($tables as $index => $sql) {
    if (mysqli_query($conn, $sql)) $berhasil++;
}

// ==========================================
// 2. PEMBUATAN 9 LOGIKA DATABASE (TRIGGERS, UDF, SP)
// ==========================================
$db_logic = [
    // --- TRIGGERS ---
    "DROP TRIGGER IF EXISTS trg_update_status_mahasiswa",
    "CREATE TRIGGER trg_update_status_mahasiswa BEFORE UPDATE ON mahasiswa FOR EACH ROW BEGIN IF NEW.jamMinus_mahasiswa > 0 OR NEW.dendaMahasiswa > 0 THEN SET NEW.statusMahasiswa = 'Dibekukan'; ELSE SET NEW.statusMahasiswa = 'Normal'; END IF; END",

    "DROP TRIGGER IF EXISTS trg_otomasi_ketersediaan_barang",
    "CREATE TRIGGER trg_otomasi_ketersediaan_barang AFTER UPDATE ON transaksi_peminjaman FOR EACH ROW BEGIN IF NEW.statusPeminjaman = 'Disetujui' AND OLD.statusPeminjaman = 'Menunggu' THEN IF NEW.idAset IS NOT NULL THEN UPDATE aset SET ketersediaanAset = 'Dipinjam' WHERE idAset = NEW.idAset; ELSEIF NEW.idFasilitas IS NOT NULL THEN UPDATE fasilitas SET ketersediaanFasilitas = 'Dipinjam' WHERE idFasilitas = NEW.idFasilitas; END IF; END IF; END",

    "DROP TRIGGER IF EXISTS trg_hitung_beban_supplier",
    "CREATE TRIGGER trg_hitung_beban_supplier AFTER UPDATE ON transaksi_pengadaan FOR EACH ROW BEGIN IF NEW.statusPengadaan = 'Disetujui GA' AND OLD.statusPengadaan = 'Draft' THEN UPDATE supplier SET jumlahTugas_aktif = jumlahTugas_aktif + 1 WHERE idSupplier = NEW.idSupplier; ELSEIF NEW.statusPengadaan = 'Harga Diinput Supplier' AND OLD.statusPengadaan = 'Disetujui GA' THEN UPDATE supplier SET jumlahTugas_aktif = GREATEST(0, jumlahTugas_aktif - 1) WHERE idSupplier = NEW.idSupplier; END IF; END",

    // --- UDF ---
    "DROP FUNCTION IF EXISTS udf_cek_email_ganda",
    "CREATE FUNCTION udf_cek_email_ganda(p_email VARCHAR(100)) RETURNS INT DETERMINISTIC BEGIN DECLARE v_count INT DEFAULT 0; SELECT COUNT(*) INTO v_count FROM ( SELECT emailMahasiswa AS email FROM mahasiswa UNION ALL SELECT emailUser AS email FROM users UNION ALL SELECT emailSupplier AS email FROM supplier ) AS gabungan WHERE email = p_email; RETURN IF(v_count > 0, 1, 0); END",

    "DROP FUNCTION IF EXISTS udf_hitung_jam_telat",
    "CREATE FUNCTION udf_hitung_jam_telat(p_waktu_rencana DATETIME, p_waktu_aktual DATETIME) RETURNS INT DETERMINISTIC BEGIN DECLARE v_jam_telat INT DEFAULT 0; IF p_waktu_aktual > p_waktu_rencana THEN SET v_jam_telat = TIMESTAMPDIFF(HOUR, p_waktu_rencana, p_waktu_aktual); END IF; RETURN v_jam_telat; END",

    "DROP FUNCTION IF EXISTS udf_generate_id_aset",
    "CREATE FUNCTION udf_generate_id_aset() RETURNS VARCHAR(20) READS SQL DATA BEGIN DECLARE v_max_id VARCHAR(20); DECLARE v_angka INT; SELECT MAX(idAset) INTO v_max_id FROM aset WHERE idAset LIKE 'AST-%'; IF v_max_id IS NOT NULL THEN SET v_angka = CAST(SUBSTRING(v_max_id, 5) AS UNSIGNED) + 1; ELSE SET v_angka = 1; END IF; RETURN CONCAT('AST-', LPAD(v_angka, 5, '0')); END",

    // --- SP ---
    "DROP PROCEDURE IF EXISTS sp_tolak_peminjaman_bentrok",
    "CREATE PROCEDURE sp_tolak_peminjaman_bentrok(IN p_id_peminjaman VARCHAR(20), IN p_id_aset VARCHAR(20), IN p_id_fasilitas VARCHAR(20)) BEGIN IF p_id_aset IS NOT NULL THEN UPDATE transaksi_peminjaman SET statusPeminjaman = 'Ditolak', alasanPenolakan_peminjaman = 'Barang telah dipinjam orang lain (Double Booking)' WHERE idAset = p_id_aset AND statusPeminjaman = 'Menunggu' AND idPeminjaman != p_id_peminjaman; ELSEIF p_id_fasilitas IS NOT NULL THEN UPDATE transaksi_peminjaman SET statusPeminjaman = 'Ditolak', alasanPenolakan_peminjaman = 'Fasilitas telah di-booking orang lain (Double Booking)' WHERE idFasilitas = p_id_fasilitas AND statusPeminjaman = 'Menunggu' AND idPeminjaman != p_id_peminjaman; END IF; END",

    "DROP PROCEDURE IF EXISTS sp_terapkan_sanksi_mahasiswa",
    "CREATE PROCEDURE sp_terapkan_sanksi_mahasiswa(IN p_nim VARCHAR(20), IN p_id_sanksi VARCHAR(20)) BEGIN DECLARE v_jam INT DEFAULT 0; DECLARE v_denda INT DEFAULT 0; IF p_id_sanksi IS NOT NULL AND p_id_sanksi != 'NULL' THEN SELECT sanksi_jamMinus, sanksi_denda INTO v_jam, v_denda FROM sanksi WHERE idSanksi = p_id_sanksi; UPDATE mahasiswa SET jamMinus_mahasiswa = jamMinus_mahasiswa + v_jam, dendaMahasiswa = dendaMahasiswa + v_denda WHERE nimMahasiswa = p_nim; END IF; END",

    "DROP PROCEDURE IF EXISTS sp_buat_tiket_reparasi",
    "CREATE PROCEDURE sp_buat_tiket_reparasi(IN p_id_reparasi_baru VARCHAR(20), IN p_pelapor VARCHAR(20), IN p_id_aset VARCHAR(20), IN p_id_fasilitas VARCHAR(20), IN p_kerusakan VARCHAR(50), IN p_catatan TEXT) BEGIN DECLARE v_id_tiket_lama VARCHAR(20); IF p_kerusakan != 'Normal' THEN SELECT idReparasi INTO v_id_tiket_lama FROM reparasi_fasilitas_aset WHERE (idAset = p_id_aset OR idFasilitas = p_id_fasilitas) AND statusReparasi = 'Menunggu GA' LIMIT 1; IF v_id_tiket_lama IS NOT NULL THEN UPDATE reparasi_fasilitas_aset SET klasifikasiKerusakan = p_kerusakan, catatanReparasi = CONCAT(catatanReparasi, '\n[Update]: ', p_catatan) WHERE idReparasi = v_id_tiket_lama; ELSE INSERT INTO reparasi_fasilitas_aset (idReparasi, idPelapor, idAset, idFasilitas, tanggalLapor, klasifikasiKerusakan, statusReparasi, catatanReparasi) VALUES (p_id_reparasi_baru, p_pelapor, p_id_aset, p_id_fasilitas, NOW(), p_kerusakan, 'Menunggu GA', p_catatan); END IF; END IF; END"
];

$berhasil_logic = 0;
foreach ($db_logic as $sql) {
    if (mysqli_query($conn, $sql)) {
        if (strpos($sql, 'DROP') === false) $berhasil_logic++;
    }
}

// ==========================================
// 3. MASSIVE DATA SEEDING (20 DATA PER TABEL)
// ==========================================
$pass = password_hash("123456", PASSWORD_DEFAULT);
$waktu = date('Y-m-d H:i:s');
$waktu_lalu = date('Y-m-d H:i:s', strtotime('-2 days'));
$waktu_depan = date('Y-m-d H:i:s', strtotime('+2 days'));

// 1. MASTER USERS (20 Data - Mencakup Semua Role Sesuai UAT)
$q_users = "INSERT IGNORE INTO users (idUser, namaUser, noTelp_user, jabatanUser, emailUser, passUser, kodeDepartemen, statusUser) VALUES
('SA-00000', 'IT Pusat ASTRAtech', '+628111', 'Super Admin', 'admin@astratech.ac.id', '$pass', 'SA', 'Aktif'),
('SA-00001', 'Budi Admin', '+628112', 'Super Admin', 'cadmin@astratech.ac.id', '$pass', 'SA', 'Aktif'),
('SA-00002', 'Dimas Nonaktif', '+628113', 'Super Admin', 'nonaktif@astra.id', '$pass', 'SA', 'Nonaktif'),
('GA-00001', 'Ridzal Kepala', '+628211', 'Kepala GA', 'kGA@astratech.ac.id', '$pass', 'GA', 'Aktif'),
('GA-00002', 'Pak Kepala GA 2', '+628212', 'Kepala GA', 'kga2@astratech.ac.id', '$pass', 'GA', 'Aktif'),
('GA-00003', 'Rimba Staff', '+628311', 'Staff GA', 'sGA@astratech.ac.id', '$pass', 'GA', 'Aktif'),
('GA-00004', 'Didi Teknisi', '+628312', 'Staff GA', 'didi@astratech.ac.id', '$pass', 'GA', 'Aktif'),
('GA-00005', 'Joko Teknisi', '+628313', 'Staff GA', 'joko@astratech.ac.id', '$pass', 'GA', 'Aktif'),
('FIN-00001', 'Samba Finance', '+628411', 'Finance', 'FIN@astratech.ac.id', '$pass', 'FIN', 'Aktif'),
('FIN-00002', 'Sari Keuangan', '+628412', 'Finance', 'sari@astratech.ac.id', '$pass', 'FIN', 'Aktif'),
('FIN-00003', 'Rina Keuangan', '+628413', 'Finance', 'rina@astratech.ac.id', '$pass', 'FIN', 'Aktif'),
('TDK-00001', 'Bapak Budi (RPL)', '+628511', 'Tenaga Pendidik', 'tendik@astratech.ac.id', '$pass', 'RPL', 'Aktif'),
('TDK-00002', 'Ibu Siska (TPM)', '+628512', 'Tenaga Pendidik', 'siska@astratech.ac.id', '$pass', 'TPM', 'Aktif'),
('TDK-00003', 'Pak Yanto (MEK)', '+628513', 'Tenaga Pendidik', 'yanto@astratech.ac.id', '$pass', 'MEK', 'Aktif'),
('TDK-00004', 'Bu Rina (P3P)', '+628514', 'Tenaga Pendidik', 'rina.p3p@astratech.ac.id', '$pass', 'P3P', 'Aktif'),
('TDK-00005', 'Pak Dedi (MIN)', '+628515', 'Tenaga Pendidik', 'dedi@astratech.ac.id', '$pass', 'MIN', 'Aktif'),
('TDK-00006', 'Pak Agus (MOT)', '+628516', 'Tenaga Pendidik', 'agus@astratech.ac.id', '$pass', 'MOT', 'Aktif'),
('TDK-00007', 'Bu Citra (TKB)', '+628517', 'Tenaga Pendidik', 'citra@astratech.ac.id', '$pass', 'TKB', 'Aktif'),
('TDK-00008', 'Pak Eko (TAB)', '+628518', 'Tenaga Pendidik', 'eko@astratech.ac.id', '$pass', 'TAB', 'Aktif'),
('TDK-00009', 'Bu Fani (TRL)', '+628519', 'Tenaga Pendidik', 'fani@astratech.ac.id', '$pass', 'TRL', 'Aktif')";
mysqli_query($conn, $q_users);

// 2. MASTER SUPPLIER (20 Data)
$q_suppliers = "INSERT IGNORE INTO supplier (idSupplier, namaSupplier, noTelp_supplier, emailSupplier, passSupplier, statusSupplier) VALUES
('SPL-00001', 'Dola (Tim Internal)', '+628611', 'supplier@astratech.ac.id', '$pass', 'Aktif'),
('SPL-00002', 'Budi Supplier', '+628612', 'budi.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00003', 'Citra Supplier', '+628613', 'citra.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00004', 'Deni Supplier', '+628614', 'deni.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00005', 'Eka Supplier', '+628615', 'eka.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00006', 'Fajar Supplier', '+628616', 'fajar.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00007', 'Gita Supplier', '+628617', 'gita.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00008', 'Hadi Supplier', '+628618', 'hadi.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00009', 'Indra Supplier', '+628619', 'indra.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00010', 'Joko Supplier', '+628620', 'joko.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00011', 'Kiki Supplier', '+628621', 'kiki.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00012', 'Lina Supplier', '+628622', 'lina.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00013', 'Mira Supplier', '+628623', 'mira.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00014', 'Nina Supplier', '+628624', 'nina.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00015', 'Oki Supplier', '+628625', 'oki.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00016', 'Putri Supplier', '+628626', 'putri.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00017', 'Qori Supplier', '+628627', 'qori.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00018', 'Rina Supplier', '+628628', 'rina.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00019', 'Sari Supplier', '+628629', 'sari.sup@vendor.com', '$pass', 'Aktif'),
('SPL-00020', 'Tono Supplier', '+628630', 'tono.sup@vendor.com', '$pass', 'Aktif')";
mysqli_query($conn, $q_suppliers);

// 3. MASTER MAHASISWA (20 Data)
$q_mhs = "INSERT IGNORE INTO mahasiswa (nimMahasiswa, namaMahasiswa, kodeProdi_mahasiswa, noTelp_mahasiswa, emailMahasiswa, passMahasiswa, jamMinus_mahasiswa, dendaMahasiswa, statusMahasiswa) VALUES
('0920260001', 'Andi Pratama (RPL)', 'RPL', '+628711', 'andi@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0920260002', 'Rafa Wahida (RPL)', 'RPL', '+628712', 'rafa@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0920260003', 'Mhs Dibekukan (RPL)', 'RPL', '+628713', 'mhsbeku@student.astratech.ac.id', '$pass', 5, 50000, 'Dibekukan'),
('0220260001', 'Beni (TPM)', 'TPM', '+628714', 'beni@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0220260002', 'Sari (TPM)', 'TPM', '+628715', 'sari.tpm@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0320260001', 'Caca (MIN)', 'MIN', '+628716', 'caca@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0420260001', 'Dedi (MOT)', 'MOT', '+628717', 'dedi.mot@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0520260001', 'Eka (MEK)', 'MEK', '+628718', 'eka.mek@student.astratech.ac.id', '$pass', 2, 0, 'Dibekukan'),
('0620260001', 'Fani (TKB)', 'TKB', '+628719', 'fani.tkb@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0720260001', 'Gita (TAB)', 'TAB', '+628720', 'gita@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0820260001', 'Hadi (TRL)', 'TRL', '+628721', 'hadi@student.astratech.ac.id', '$pass', 0, 100000, 'Dibekukan'),
('0120260001', 'Indra (P3P)', 'P3P', '+628722', 'indra@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0920260004', 'Joko (RPL)', 'RPL', '+628723', 'joko.rpl@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0920260005', 'Kiki (RPL)', 'RPL', '+628724', 'kiki@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0920260006', 'Lina (RPL)', 'RPL', '+628725', 'lina@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0220260003', 'Mira (TPM)', 'TPM', '+628726', 'mira@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0320260002', 'Nina (MIN)', 'MIN', '+628727', 'nina@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0420260002', 'Oki (MOT)', 'MOT', '+628728', 'oki@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0520260002', 'Putri (MEK)', 'MEK', '+628729', 'putri@student.astratech.ac.id', '$pass', 0, 0, 'Normal'),
('0620260002', 'Qori (TKB)', 'TKB', '+628730', 'qori@student.astratech.ac.id', '$pass', 0, 0, 'Normal')";
mysqli_query($conn, $q_mhs);

// 4. MASTER KATEGORI (20 Data)
$q_kategori = "INSERT IGNORE INTO kategori (idKategori, namaKategori, tipeKategori, idPembuat, statusKategori) VALUES
('KTG-00001', 'Proyektor', 'Aset', 'TDK-00001', 'Aktif'),
('KTG-00002', 'Ruang Kelas', 'Fasilitas Akademik', 'TDK-00001', 'Aktif'),
('KTG-00003', 'Laptop', 'Aset', 'TDK-00001', 'Aktif'),
('KTG-00004', 'Fasilitas Publik / Komunal', 'Fasilitas Non-Akademik', 'GA-00003', 'Aktif'),
('KTG-00005', 'Laboratorium Komputer', 'Fasilitas Akademik', 'TDK-00001', 'Aktif'),
('KTG-00006', 'Kamera DSLR', 'Aset', 'TDK-00001', 'Aktif'),
('KTG-00007', 'Drone Aerial', 'Aset', 'TDK-00001', 'Aktif'),
('KTG-00008', 'Audio System', 'Aset', 'TDK-00001', 'Aktif'),
('KTG-00009', 'Lighting Studio', 'Aset', 'TDK-00001', 'Aktif'),
('KTG-00010', 'Meja Gambar', 'Aset', 'TDK-00001', 'Aktif'),
('KTG-00011', 'Ruang Rapat', 'Fasilitas Akademik', 'TDK-00001', 'Aktif'),
('KTG-00012', 'Laboratorium Mesin', 'Fasilitas Akademik', 'TDK-00001', 'Aktif'),
('KTG-00013', 'Laboratorium Otomotif', 'Fasilitas Akademik', 'TDK-00001', 'Aktif'),
('KTG-00014', 'Lapangan Basket', 'Fasilitas Non-Akademik', 'GA-00003', 'Aktif'),
('KTG-00015', 'Lapangan Futsal', 'Fasilitas Non-Akademik', 'GA-00003', 'Aktif'),
('KTG-00016', 'Aula Serbaguna', 'Fasilitas Non-Akademik', 'GA-00003', 'Aktif'),
('KTG-00017', 'Gedung Olahraga (GOR)', 'Fasilitas Non-Akademik', 'GA-00003', 'Aktif'),
('KTG-00018', 'Perpustakaan Pusat', 'Fasilitas Non-Akademik', 'GA-00003', 'Aktif'),
('KTG-00019', 'Loker Penyimpanan', 'Aset', 'TDK-00001', 'Aktif'),
('KTG-00020', 'Server Rack', 'Aset', 'TDK-00001', 'Aktif')";
mysqli_query($conn, $q_kategori);

// 5. MASTER ASET (20 Data)
$q_aset = "INSERT IGNORE INTO aset (idAset, idKategori, namaAset, ketersediaanAset, kondisiAset) VALUES
('AST-00001', 'KTG-00001', 'Proyektor Epson EB-X400 (RPL)', 'Tersedia', 'Normal'),
('AST-00002', 'KTG-00003', 'Laptop Asus ROG (RPL)', 'Tersedia', 'Normal'),
('AST-00003', 'KTG-00006', 'Kamera Canon 90D', 'Dipinjam', 'Normal'),
('AST-00004', 'KTG-00007', 'DJI Mavic Pro 3', 'Sedang Diperbaiki', 'Berfungsi'),
('AST-00005', 'KTG-00008', 'Mic Wireless Rode', 'Tersedia', 'Normal'),
('AST-00006', 'KTG-00009', 'Godox LED Panel', 'Tidak Tersedia', 'Tidak Berfungsi'),
('AST-00007', 'KTG-00010', 'Meja Arsitek A1', 'Tersedia', 'Normal'),
('AST-00008', 'KTG-00019', 'Loker Besi 12 Pintu', 'Tersedia', 'Normal'),
('AST-00009', 'KTG-00020', 'Cisco Server Rack 42U', 'Tersedia', 'Normal'),
('AST-00010', 'KTG-00001', 'Proyektor BenQ EX', 'Tersedia', 'Normal'),
('AST-00011', 'KTG-00003', 'MacBook Pro M2', 'Tersedia', 'Normal'),
('AST-00012', 'KTG-00006', 'Sony Alpha A7', 'Tersedia', 'Normal'),
('AST-00013', 'KTG-00007', 'DJI Mini 3', 'Tersedia', 'Normal'),
('AST-00014', 'KTG-00008', 'Speaker JBL 1000', 'Tersedia', 'Normal'),
('AST-00015', 'KTG-00009', 'Ring Light A', 'Tersedia', 'Normal'),
('AST-00016', 'KTG-00010', 'Meja Arsitek A2', 'Tersedia', 'Normal'),
('AST-00017', 'KTG-00019', 'Loker Kayu 6 Pintu', 'Tersedia', 'Normal'),
('AST-00018', 'KTG-00020', 'HP Server Rack 24U', 'Tersedia', 'Normal'),
('AST-00019', 'KTG-00001', 'Proyektor Sony X1', 'Tersedia', 'Normal'),
('AST-00020', 'KTG-00003', 'Lenovo ThinkPad', 'Tersedia', 'Normal')";
mysqli_query($conn, $q_aset);

// 6. MASTER FASILITAS (20 Data)
$q_fasilitas = "INSERT IGNORE INTO fasilitas (idFasilitas, idKategori, idPengelola, namaFasilitas, lokasiFasilitas, tipeFasilitas, ketersediaanFasilitas, kondisiFasilitas) VALUES
('FSL-00001', 'KTG-00002', 'TDK-00001', 'Kelas TRPL 1B', 'Gedung A, Lt 2', 'Akademik', 'Tersedia', 'Normal'),
('FSL-00002', 'KTG-00005', 'TDK-00001', 'Lab Komputer Mac', 'Gedung B, Lt 3', 'Akademik', 'Tersedia', 'Normal'),
('FSL-00003', 'KTG-00011', 'TDK-00001', 'Ruang Rapat Dosen', 'Gedung Utama, Lt 1', 'Akademik', 'Dipinjam', 'Normal'),
('FSL-00004', 'KTG-00012', 'TDK-00001', 'Lab Bubut Mesin', 'Gedung C, Lt 1', 'Akademik', 'Sedang Diperbaiki', 'Berfungsi'),
('FSL-00005', 'KTG-00013', 'TDK-00001', 'Lab Otomotif Dasar', 'Gedung C, Lt 2', 'Akademik', 'Tersedia', 'Normal'),
('FSL-00006', 'KTG-00004', 'GA-00003', 'Komunal Hijau', 'Taman Depan', 'Non-Akademik', 'Tersedia', 'Normal'),
('FSL-00007', 'KTG-00014', 'GA-00003', 'Lapangan Basket Utama', 'Area Olahraga', 'Non-Akademik', 'Tersedia', 'Normal'),
('FSL-00008', 'KTG-00015', 'GA-00003', 'Lapangan Futsal A', 'Area Olahraga', 'Non-Akademik', 'Tersedia', 'Normal'),
('FSL-00009', 'KTG-00016', 'GA-00003', 'Aula Serbaguna', 'Gedung Serbaguna', 'Non-Akademik', 'Dipinjam', 'Normal'),
('FSL-00010', 'KTG-00017', 'GA-00003', 'GOR Indoor', 'Gedung Olahraga', 'Non-Akademik', 'Tersedia', 'Normal'),
('FSL-00011', 'KTG-00002', 'TDK-00001', 'Kelas TRPL 2A', 'Gedung A, Lt 3', 'Akademik', 'Tersedia', 'Normal'),
('FSL-00012', 'KTG-00005', 'TDK-00001', 'Lab Komputer PC', 'Gedung B, Lt 2', 'Akademik', 'Tersedia', 'Normal'),
('FSL-00013', 'KTG-00011', 'TDK-00001', 'Ruang Sidang', 'Gedung Utama, Lt 2', 'Akademik', 'Tersedia', 'Normal'),
('FSL-00014', 'KTG-00012', 'TDK-00001', 'Lab Las', 'Gedung C, Lt 1', 'Akademik', 'Tersedia', 'Normal'),
('FSL-00015', 'KTG-00013', 'TDK-00001', 'Lab Motor', 'Gedung C, Lt 2', 'Akademik', 'Tersedia', 'Normal'),
('FSL-00016', 'KTG-00004', 'GA-00003', 'Komunal Merah', 'Taman Belakang', 'Non-Akademik', 'Tersedia', 'Normal'),
('FSL-00017', 'KTG-00014', 'GA-00003', 'Lapangan Basket B', 'Area Olahraga', 'Non-Akademik', 'Tersedia', 'Normal'),
('FSL-00018', 'KTG-00015', 'GA-00003', 'Lapangan Futsal B', 'Area Olahraga', 'Non-Akademik', 'Tersedia', 'Normal'),
('FSL-00019', 'KTG-00016', 'GA-00003', 'Ruang Teater', 'Gedung Serbaguna', 'Non-Akademik', 'Tersedia', 'Normal'),
('FSL-00020', 'KTG-00018', 'GA-00003', 'Perpus Lantai 1', 'Gedung Perpus', 'Non-Akademik', 'Tersedia', 'Normal')";
mysqli_query($conn, $q_fasilitas);

// 7. SANKSI MASTER (16 UAT + 4 Tambahan = 20 Data)
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
('SNK-00015', 'Kehilangan Aset Kampus', 50, 3500000, 'Manual', 'Manual', 'Aktif'),
('SNK-00016', 'Merusak Segel Aset', 20, 100000, 'Manual', 'Manual', 'Aktif'),
('SNK-00017', 'Membawa Aset Keluar Kampus', 40, 500000, 'Manual', 'Manual', 'Aktif'),
('SNK-00018', 'Meminjamkan ke Orang Lain', 25, 200000, 'Manual', 'Manual', 'Aktif'),
('SNK-00019', 'Makan/Minum di Lab', 5, 50000, 'Manual', 'Manual', 'Aktif')";
mysqli_query($conn, $q_sanksi);

// 8. TRANSAKSI PEMINJAMAN (20 Data Berbagai Status)
// Aset: AST-00003 s/d AST-00020
// Fasilitas: FSL-00003 s/d FSL-00020
$q_pjm = "INSERT IGNORE INTO transaksi_peminjaman (idPeminjaman, nimMahasiswa, idPenyetuju, idAset, idFasilitas, tanggalPengajuan, tanggalPeminjaman, tanggalRencana_kembali, keperluan, statusPeminjaman) VALUES
('PJM-00001', '0920260001', NULL, 'AST-00010', NULL, '$waktu_lalu', NULL, '$waktu_depan', 'Pinjam Proyektor Kelas', 'Menunggu'),
('PJM-00002', '0920260002', NULL, NULL, 'FSL-00010', '$waktu_lalu', NULL, '$waktu_depan', 'Pinjam GOR untuk Futsal', 'Menunggu'),
('PJM-00003', '0220260001', NULL, 'AST-00011', NULL, '$waktu_lalu', NULL, '$waktu_depan', 'Tugas Akhir TPM', 'Menunggu'),
('PJM-00004', '0320260001', NULL, NULL, 'FSL-00011', '$waktu_lalu', NULL, '$waktu_depan', 'Kelas Tambahan', 'Menunggu'),
('PJM-00005', '0420260001', NULL, 'AST-00012', NULL, '$waktu_lalu', NULL, '$waktu_depan', 'Dokumentasi Acara', 'Menunggu'),
('PJM-00006', '0920260001', 'TDK-00001', 'AST-00003', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_depan', 'Tugas Fotografi', 'Disetujui'),
('PJM-00007', '0920260002', 'GA-00003', NULL, 'FSL-00009', '$waktu_lalu', '$waktu_lalu', '$waktu_depan', 'Seminar BEM', 'Disetujui'),
('PJM-00008', '0220260001', 'TDK-00001', NULL, 'FSL-00003', '$waktu_lalu', '$waktu_lalu', '$waktu_depan', 'Rapat Hima', 'Disetujui'),
('PJM-00009', '0320260001', 'TDK-00001', 'AST-00013', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_depan', 'Lomba Video', 'Disetujui'),
('PJM-00010', '0420260001', 'GA-00003', NULL, 'FSL-00016', '$waktu_lalu', '$waktu_lalu', '$waktu_depan', 'Kumpul Angkatan', 'Disetujui'),
('PJM-00011', '0920260004', 'TDK-00001', 'AST-00014', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Acara Musik', 'Selesai'),
('PJM-00012', '0920260005', 'GA-00003', NULL, 'FSL-00017', '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Latihan Basket', 'Selesai'),
('PJM-00013', '0220260002', 'TDK-00001', 'AST-00015', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Penerangan Video', 'Selesai'),
('PJM-00014', '0320260002', 'GA-00003', NULL, 'FSL-00018', '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Sparring Futsal', 'Selesai'),
('PJM-00015', '0420260002', 'TDK-00001', 'AST-00016', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Tugas Gambar', 'Selesai'),
('PJM-00016', '0520260002', 'TDK-00001', 'AST-00017', NULL, '$waktu_lalu', NULL, '$waktu_depan', 'Coba Pinjam', 'Ditolak'),
('PJM-00017', '0620260002', 'GA-00003', NULL, 'FSL-00019', '$waktu_lalu', NULL, '$waktu_depan', 'Nonton Film', 'Ditolak'),
('PJM-00018', '0720260001', 'TDK-00001', 'AST-00018', NULL, '$waktu_lalu', NULL, '$waktu_depan', 'Setup Server', 'Ditolak'),
('PJM-00019', '0820260001', 'GA-00003', NULL, 'FSL-00020', '$waktu_lalu', NULL, '$waktu_depan', 'Belajar Kelompok', 'Ditolak'),
('PJM-00020', '0120260001', 'TDK-00001', 'AST-00019', NULL, '$waktu_lalu', NULL, '$waktu_depan', 'Presentasi', 'Ditolak')";
mysqli_query($conn, $q_pjm);

// 9. TRANSAKSI PENGEMBALIAN (Hanya yang Selesai PJM-00011 s/d PJM-00015)
$q_kmb = "INSERT IGNORE INTO transaksi_pengembalian (idPengembalian, idPeminjaman, idPengurus, idSanksi, tanggalPengembalian, kondisiFisik, catatanPengembalian) VALUES
('KMB-00001', 'PJM-00011', 'TDK-00001', 'SNK-00000', '$waktu_lalu', 'Normal', 'Aman'),
('KMB-00002', 'PJM-00012', 'GA-00003', 'SNK-00000', '$waktu_lalu', 'Normal', 'Aman'),
('KMB-00003', 'PJM-00013', 'TDK-00001', 'SNK-00004', '$waktu_lalu', 'Berfungsi', 'Lampu agak redup'),
('KMB-00004', 'PJM-00014', 'GA-00003', 'SNK-00000', '$waktu_lalu', 'Normal', 'Aman'),
('KMB-00005', 'PJM-00015', 'TDK-00001', 'SNK-00000', '$waktu_lalu', 'Normal', 'Aman')";
mysqli_query($conn, $q_kmb);

// 10. TRANSAKSI PENGADAAN (20 Data Berbagai Status)
$q_pgd = "INSERT IGNORE INTO transaksi_pengadaan (idPengadaan, idKategori, idTendik, idKepalaGA, idSupplier, idFinance, namaKebutuhan, tanggalPengadaan, jumlah, totalBiaya, alasanKebutuhan, statusPengadaan) VALUES
('PGD-00001', 'KTG-00003', 'TDK-00001', NULL, NULL, NULL, 'Laptop Asus Vivobook Baru', '$waktu_lalu', 5, 0, 'Kebutuhan Dosen', 'Draft'),
('PGD-00002', 'KTG-00001', 'TDK-00001', NULL, NULL, NULL, 'Proyektor Epson', '$waktu_lalu', 2, 0, 'Ganti rusak', 'Draft'),
('PGD-00003', 'KTG-00008', 'TDK-00001', NULL, NULL, NULL, 'Speaker Aktif', '$waktu_lalu', 1, 0, 'Acara BEM', 'Draft'),
('PGD-00004', 'KTG-00006', 'TDK-00001', NULL, NULL, NULL, 'Lensa Kamera', '$waktu_lalu', 2, 0, 'Humas', 'Draft'),
('PGD-00005', 'KTG-00007', 'TDK-00001', NULL, NULL, NULL, 'Baling Drone', '$waktu_lalu', 4, 0, 'Cadangan', 'Draft'),
('PGD-00006', 'KTG-00003', 'TDK-00001', 'GA-00001', 'SPL-00001', NULL, 'MacBook Air M2', '$waktu_lalu', 3, 0, 'Pimpinan', 'Disetujui GA'),
('PGD-00007', 'KTG-00019', 'TDK-00001', 'GA-00001', 'SPL-00001', NULL, 'Loker Besi', '$waktu_lalu', 10, 0, 'Mahasiswa', 'Disetujui GA'),
('PGD-00008', 'KTG-00020', 'TDK-00001', 'GA-00001', 'SPL-00001', NULL, 'Server Blade', '$waktu_lalu', 1, 0, 'Pusat Data', 'Disetujui GA'),
('PGD-00009', 'KTG-00010', 'TDK-00001', 'GA-00001', 'SPL-00001', NULL, 'Kursi Gambar', '$waktu_lalu', 20, 0, 'Lab Arsi', 'Disetujui GA'),
('PGD-00010', 'KTG-00009', 'TDK-00001', 'GA-00001', 'SPL-00001', NULL, 'Lampu Studio', '$waktu_lalu', 2, 0, 'Lab Foto', 'Disetujui GA'),
('PGD-00011', 'KTG-00001', 'TDK-00001', 'GA-00001', 'SPL-00001', NULL, 'Layar Proyektor', '$waktu_lalu', 5, 0, 'Kelas Baru', 'Harga Diinput Supplier'),
('PGD-00012', 'KTG-00008', 'TDK-00001', 'GA-00001', 'SPL-00001', NULL, 'Mic Clip On', '$waktu_lalu', 5, 0, 'Wawancara', 'Harga Diinput Supplier'),
('PGD-00013', 'KTG-00003', 'TDK-00001', 'GA-00001', 'SPL-00001', NULL, 'Mouse Wireless', '$waktu_lalu', 50, 0, 'Lab RPL', 'Harga Diinput Supplier'),
('PGD-00014', 'KTG-00003', 'TDK-00001', 'GA-00001', 'SPL-00001', NULL, 'Keyboard Mech', '$waktu_lalu', 20, 0, 'Lab RPL', 'Harga Diinput Supplier'),
('PGD-00015', 'KTG-00019', 'TDK-00001', 'GA-00001', 'SPL-00001', NULL, 'Kunci Loker', '$waktu_lalu', 100, 0, 'Cadangan', 'Harga Diinput Supplier'),
('PGD-00016', 'KTG-00003', 'TDK-00001', 'GA-00001', 'SPL-00001', 'FIN-00001', 'Monitor 24 Inch', '$waktu_lalu', 10, 15000000, 'Lab RPL', 'Disetujui Finance'),
('PGD-00017', 'KTG-00006', 'TDK-00001', 'GA-00001', 'SPL-00001', 'FIN-00001', 'SD Card 128GB', '$waktu_lalu', 5, 1000000, 'Fotografi', 'Disetujui Finance'),
('PGD-00018', 'KTG-00008', 'TDK-00001', 'GA-00001', 'SPL-00001', 'FIN-00001', 'Kabel Audio', '$waktu_lalu', 10, 500000, 'Studio', 'Disetujui Finance'),
('PGD-00019', 'KTG-00001', 'TDK-00001', 'GA-00001', 'SPL-00001', 'FIN-00001', 'Kabel HDMI 10m', '$waktu_lalu', 5, 750000, 'Kelas', 'Disetujui Finance'),
('PGD-00020', 'KTG-00003', 'TDK-00001', 'GA-00001', NULL, NULL, 'iPad Pro', '$waktu_lalu', 2, 0, 'Dosen', 'Ditolak')";
mysqli_query($conn, $q_pgd);

// 11. DETAIL PENGADAAN VENDOR (Untuk PGD-00011 s/d 00019)
$q_dtl = "INSERT IGNORE INTO detail_pengadaan_vendor (idDetail, idPengadaan, namaVendor, spesifikasi, stok, hargaSatuan, estimasiTiba, statusPilihan, statusKedatangan) VALUES
('DTL-00001', 'PGD-00011', 'Toko Alpha', 'Ori 100%', 5, 1000000, 3, 'Menunggu', 'Belum Tiba'),
('DTL-00002', 'PGD-00011', 'Toko Beta', 'Garansi 1 Thn', 5, 1100000, 2, 'Menunggu', 'Belum Tiba'),
('DTL-00003', 'PGD-00012', 'Toko Alpha', 'Ori 100%', 5, 200000, 3, 'Menunggu', 'Belum Tiba'),
('DTL-00004', 'PGD-00012', 'Toko Beta', 'Garansi 1 Thn', 5, 210000, 2, 'Menunggu', 'Belum Tiba'),
('DTL-00005', 'PGD-00013', 'Toko Alpha', 'Ori 100%', 50, 100000, 3, 'Menunggu', 'Belum Tiba'),
('DTL-00006', 'PGD-00013', 'Toko Beta', 'Garansi 1 Thn', 50, 110000, 2, 'Menunggu', 'Belum Tiba'),
('DTL-00007', 'PGD-00014', 'Toko Alpha', 'Ori 100%', 20, 500000, 3, 'Menunggu', 'Belum Tiba'),
('DTL-00008', 'PGD-00014', 'Toko Beta', 'Garansi 1 Thn', 20, 550000, 2, 'Menunggu', 'Belum Tiba'),
('DTL-00009', 'PGD-00015', 'Toko Alpha', 'Ori 100%', 100, 10000, 3, 'Menunggu', 'Belum Tiba'),
('DTL-00010', 'PGD-00015', 'Toko Beta', 'Garansi 1 Thn', 100, 12000, 2, 'Menunggu', 'Belum Tiba'),
('DTL-00011', 'PGD-00016', 'Toko C', 'Terpilih Finance', 10, 1500000, 5, 'Terpilih', 'Sudah Tiba'),
('DTL-00012', 'PGD-00017', 'Toko C', 'Terpilih Finance', 5, 200000, 5, 'Terpilih', 'Sudah Tiba'),
('DTL-00013', 'PGD-00018', 'Toko C', 'Terpilih Finance', 10, 50000, 5, 'Terpilih', 'Sudah Tiba'),
('DTL-00014', 'PGD-00019', 'Toko C', 'Terpilih Finance', 5, 150000, 5, 'Terpilih', 'Sudah Tiba')";
mysqli_query($conn, $q_dtl);

// 12. REPARASI FASILITAS ASET (20 Data)
// Rusak: AST-00004 (Berfungsi), AST-00006 (Tdk Berfungsi), FSL-00004 (Berfungsi)
$q_rep = "INSERT IGNORE INTO reparasi_fasilitas_aset (idReparasi, idPelapor, idTeknisi, idAset, idFasilitas, tanggalLapor, tanggalReparasi, tanggalSelesai, klasifikasiKerusakan, catatanReparasi, statusReparasi) VALUES
('REP-00001', 'TDK-00001', NULL, 'AST-00004', NULL, '$waktu_lalu', NULL, NULL, 'Berfungsi', 'Baling agak macet', 'Menunggu GA'),
('REP-00002', 'TDK-00001', NULL, 'AST-00006', NULL, '$waktu_lalu', NULL, NULL, 'Tidak Berfungsi', 'Mati total konslet', 'Menunggu GA'),
('REP-00003', 'TDK-00001', NULL, NULL, 'FSL-00004', '$waktu_lalu', NULL, NULL, 'Berfungsi', 'Mesin agak bising', 'Menunggu GA'),
('REP-00004', 'TDK-00001', NULL, 'AST-00012', NULL, '$waktu_lalu', NULL, NULL, 'Berfungsi', 'Flash tidak nyala', 'Menunggu GA'),
('REP-00005', 'GA-00003', NULL, NULL, 'FSL-00016', '$waktu_lalu', NULL, NULL, 'Berfungsi', 'Lampu taman mati 1', 'Menunggu GA'),
('REP-00006', 'TDK-00001', 'GA-00004', 'AST-00013', NULL, '$waktu_lalu', '$waktu_lalu', NULL, 'Tidak Berfungsi', 'Baterai drop', 'Sedang Dikerjakan'),
('REP-00007', 'TDK-00001', 'GA-00004', NULL, 'FSL-00012', '$waktu_lalu', '$waktu_lalu', NULL, 'Berfungsi', 'AC Kurang Dingin', 'Sedang Dikerjakan'),
('REP-00008', 'TDK-00001', 'GA-00005', 'AST-00014', NULL, '$waktu_lalu', '$waktu_lalu', NULL, 'Tidak Berfungsi', 'Kabel putus', 'Sedang Dikerjakan'),
('REP-00009', 'TDK-00001', 'GA-00005', NULL, 'FSL-00013', '$waktu_lalu', '$waktu_lalu', NULL, 'Berfungsi', 'Meja goyang', 'Sedang Dikerjakan'),
('REP-00010', 'GA-00003', 'GA-00004', NULL, 'FSL-00017', '$waktu_lalu', '$waktu_lalu', NULL, 'Berfungsi', 'Ring basket miring', 'Sedang Dikerjakan'),
('REP-00011', 'TDK-00001', 'GA-00004', 'AST-00015', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Tidak Berfungsi', 'Ganti LED', 'Selesai'),
('REP-00012', 'TDK-00001', 'GA-00005', 'AST-00016', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Berfungsi', 'Kencangkan baut', 'Selesai'),
('REP-00013', 'GA-00003', 'GA-00004', NULL, 'FSL-00018', '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Berfungsi', 'Ganti jaring', 'Selesai'),
('REP-00014', 'GA-00003', 'GA-00005', NULL, 'FSL-00019', '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Berfungsi', 'Ganti kursi', 'Selesai'),
('REP-00015', 'TDK-00001', 'GA-00004', 'AST-00017', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Tidak Berfungsi', 'Cat ulang', 'Selesai'),
('REP-00016', 'TDK-00001', 'GA-00004', 'AST-00006', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Tidak Berfungsi', 'Mati total dibongkar', 'Dikanibal'),
('REP-00017', 'TDK-00001', 'GA-00005', 'AST-00018', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Tidak Berfungsi', 'Terbakar', 'Dikanibal'),
('REP-00018', 'TDK-00001', 'GA-00004', 'AST-00019', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Tidak Berfungsi', 'Pecah', 'Dikanibal'),
('REP-00019', 'TDK-00001', 'GA-00005', 'AST-00020', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Tidak Berfungsi', 'LCD pecah', 'Dikanibal'),
('REP-00020', 'TDK-00001', 'GA-00004', 'AST-00001', NULL, '$waktu_lalu', '$waktu_lalu', '$waktu_lalu', 'Tidak Berfungsi', 'Board gosong', 'Dikanibal')";
mysqli_query($conn, $q_rep);

// 13. MASTER KOMPONEN (20 Data)
$q_komp = "INSERT IGNORE INTO komponen (idKomponen, idReparasi, namaKomponen, spesifikasiKomponen, kondisiKomponen, statusKomponen, tanggalMasuk) VALUES
('KMP-00001', 'REP-00016', 'Lensa Proyektor', 'Ori Epson', 'Sangat Baik', 'Tersedia', '$waktu_lalu'),
('KMP-00002', 'REP-00016', 'Kipas Proyektor', 'DC 12V', 'Layak Pakai', 'Tersedia', '$waktu_lalu'),
('KMP-00003', 'REP-00016', 'Kabel Power', 'AC 220V', 'Sangat Baik', 'Sudah Dipakai', '$waktu_lalu'),
('KMP-00004', 'REP-00016', 'Casing Proyektor', 'Plastik ABS', 'Layak Pakai', 'Tersedia', '$waktu_lalu'),
('KMP-00005', 'REP-00017', 'RAM 16GB', 'DDR4', 'Sangat Baik', 'Tersedia', '$waktu_lalu'),
('KMP-00006', 'REP-00017', 'SSD 512GB', 'NVMe M2', 'Sangat Baik', 'Tersedia', '$waktu_lalu'),
('KMP-00007', 'REP-00017', 'Baterai Laptop', '4000mAh', 'Layak Pakai', 'Sudah Dipakai', '$waktu_lalu'),
('KMP-00008', 'REP-00017', 'Keyboard Modul', 'QWERTY', 'Layak Pakai', 'Tersedia', '$waktu_lalu'),
('KMP-00009', 'REP-00018', 'Lensa Kamera', '50mm', 'Sangat Baik', 'Tersedia', '$waktu_lalu'),
('KMP-00010', 'REP-00018', 'Baterai Kamera', 'LP-E6', 'Layak Pakai', 'Tersedia', '$waktu_lalu'),
('KMP-00011', 'REP-00018', 'Strap Kamera', 'Canvas', 'Sangat Baik', 'Sudah Dipakai', '$waktu_lalu'),
('KMP-00012', 'REP-00018', 'Tutup Lensa', 'Plastik', 'Sangat Baik', 'Tersedia', '$waktu_lalu'),
('KMP-00013', 'REP-00019', 'Baling Drone', 'Plastik', 'Layak Pakai', 'Tersedia', '$waktu_lalu'),
('KMP-00014', 'REP-00019', 'Motor Brushless', '12V', 'Sangat Baik', 'Tersedia', '$waktu_lalu'),
('KMP-00015', 'REP-00019', 'Baterai Drone', 'Lipo 3S', 'Layak Pakai', 'Sudah Dipakai', '$waktu_lalu'),
('KMP-00016', 'REP-00019', 'Remote Controller', '2.4GHz', 'Sangat Baik', 'Tersedia', '$waktu_lalu'),
('KMP-00017', 'REP-00020', 'Kabel XLR', '5 Meter', 'Sangat Baik', 'Tersedia', '$waktu_lalu'),
('KMP-00018', 'REP-00020', 'Jack Audio', '6.5mm', 'Layak Pakai', 'Tersedia', '$waktu_lalu'),
('KMP-00019', 'REP-00020', 'Grill Speaker', 'Besi', 'Sangat Baik', 'Sudah Dipakai', '$waktu_lalu'),
('KMP-00020', 'REP-00020', 'Baut Speaker', 'Set', 'Layak Pakai', 'Tersedia', '$waktu_lalu')";
mysqli_query($conn, $q_komp);

// Cek Hasil
if ($berhasil == count($tables) && $berhasil_logic == 9) {
    echo "<div style='background-color: #d4edda; border-left: 5px solid #28a745; padding: 15px; margin: 20px 0;'>";
    echo "<h3>🎉 INSTALASI & SEEDING DATABASE (ENTERPRISE) SELESAI 100%!</h3>";
    echo "<p>Berhasil membuat: <br>✅ <b>13 Tabel Database</b><br>✅ <b>3 Triggers</b><br>✅ <b>3 User Defined Functions (UDF)</b><br>✅ <b>3 Stored Procedures (SP)</b></p>";
    echo "<p>Telah disuntikkan masing-masing <b>20+ Data Dummy</b> ke setiap tabel (Master & Transaksi)! Dashboard langsung penuh.</p>";
    echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #1d4197; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Buka Halaman Login</a>";
    echo "</div>";
} else {
    echo "<div style='background-color: #f8d7da; border-left: 5px solid #dc3545; padding: 15px; margin: 20px 0;'>";
    echo "<h3>⚠️ Peringatan</h3>";
    echo "<p>Tabel ter-install: $berhasil / " . count($tables) . "</p>";
    echo "<p>Logic DB ter-install: $berhasil_logic / 9</p>";
    echo "</div>";
}

echo "</div>";
