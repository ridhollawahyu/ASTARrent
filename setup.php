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

// 4. DATA SEEDING (Menggunakan VARCHAR 20)
$password_default = password_hash("12345", PASSWORD_DEFAULT);

// A. Insert Users
$q_users = "INSERT IGNORE INTO users (idUser, namaUser, noTelp_user, jabatanUser, emailUser, passUser, kodeDepartemen) VALUES
('SA-00000', 'IT Pusat ASTRAtech', '+62812212121', 'Super Admin', 'admin@astratech.ac.id', '$password_default', 'SA'),
('SA-00001', 'Calistung', '+62812212222', 'Super Admin', 'cadmin@astratech.ac.id', '$password_default', 'SA'),
('GA-00001', 'Ridzal', '+62812212211', 'Kepala GA', 'kGA@astratech.ac.id', '$password_default', 'GA'),
('GA-00002', 'Rimba', '+62812211122', 'Staff GA', 'sGA@astratech.ac.id', '$password_default', 'GA'),
('FIN-00001', 'Samba', '+62812211212', 'Finance', 'FIN@astratech.ac.id', '$password_default', 'FIN'),
('TDK-00001', 'Bapak Budi', '+62813313131', 'Tenaga Pendidik', 'budi@astratech.ac.id', '$password_default', 'RPL')";
mysqli_query($conn, $q_users);

// A. Insert Supplier
$q_suppliers = "INSERT IGNORE INTO supplier (idSupplier, namaSupplier, noTelp_supplier, emailSupplier, passSupplier) VALUES
('SPL-00001', 'Dola', '+62813313333', 'dola@astratech.ac.id', '$password_default')";
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
if ($berhasil == count($tables)) {
    echo "<div style='background-color: #d4edda; border-left: 5px solid #28a745; padding: 15px; margin: 20px 0;'>";
    echo "<h3>🎉 DATABASE SIAP 100%!</h3>";
    echo "<p>Seluruh 12 tabel dengan <b>VARCHAR(20)</b> telah berhasil dibuat sesuai dengan XML PDM Anda.</p>";
    echo "<p><b>Data Dummy yang disuntikkan:</b></p>";
    echo "<ul>
            <li>User: Banyak</li>
            <li>NIM: 0920260001 (Mahasiswa)</li>
            <li>Aset: AST-00001 (Proyektor)</li>
            <li>Sanksi: Banyak</li>
          </ul>";
    echo "<p>Silakan gas koding Modul Peminjamannya! <b>Semoga Review 1 hari ini lancar jaya!</b> 🚀</p>";
    echo "</div>";
}

echo "</div>"; // Tutup div style
