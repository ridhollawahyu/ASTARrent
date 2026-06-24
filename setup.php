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
        idUser VARCHAR(20) PRIMARY KEY,
        namaUser VARCHAR(100) NOT NULL,
        noTelp_user VARCHAR(15) NOT NULL,
        jabatanUser ENUM('Tenaga Pendidik', 'Staff GA', 'Kepala GA', 'Finance', 'Super Admin') NOT NULL,
        emailUser VARCHAR(100) NOT NULL,
        passUser VARCHAR(255) NOT NULL,
        kodeDepartemen ENUM('P3P', 'TPM', 'MIN', 'MOT', 'MEK', 'TKB', 'TAB', 'TRL', 'RPL', 'GA', 'FIN', 'SA') NOT NULL,
        statusUser ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif'
    )",

    // 2. MASTER MAHASISWA
    "CREATE TABLE IF NOT EXISTS mahasiswa (
        nimMahasiswa VARCHAR(20) PRIMARY KEY,
        namaMahasiswa VARCHAR(100) NOT NULL,
        kodeProdi_mahasiswa ENUM('P3P', 'TPM', 'MIN', 'MOT', 'MEK', 'TKB', 'TAB', 'TRL', 'RPL') NOT NULL,
        noTelp_mahasiswa VARCHAR(15) NOT NULL,
        emailMahasiswa VARCHAR(100) NOT NULL,
        passMahasiswa VARCHAR(255) NOT NULL,
        jamMinus_mahasiswa INT DEFAULT 0,
        dendaMahasiswa INT DEFAULT 0,
        statusMahasiswa ENUM('Normal', 'Dibekukan', 'Nonaktif') DEFAULT 'Normal'
    )",

    // 3. MASTER SUPPLIER
    "CREATE TABLE IF NOT EXISTS supplier (
        idSupplier VARCHAR(20) PRIMARY KEY,
        namaSupplier VARCHAR(100) NOT NULL,
        noTelp_supplier VARCHAR(15) NOT NULL,
        emailSupplier VARCHAR(100) NOT NULL,
        passSupplier VARCHAR(255) NOT NULL,
        jumlahTugas_aktif INT DEFAULT 0,
        statusSupplier ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif'
    )",

    // 4. MASTER SANKSI
    "CREATE TABLE IF NOT EXISTS sanksi (
        idSanksi VARCHAR(20) PRIMARY KEY,
        namaSanksi VARCHAR(100) NOT NULL,
        sanksi_jamMinus INT DEFAULT 0,
        sanksi_denda INT DEFAULT 0,
        statusSanksi ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif'
    )",

    // 5. MASTER KATEGORI
    "CREATE TABLE IF NOT EXISTS kategori (
        idKategori VARCHAR(20) PRIMARY KEY,
        namaKategori VARCHAR(100) NOT NULL,
        statusKategori ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
        tipeKategori ENUM('Aset', 'Fasilitas') NOT NULL
    )",

    // 6. TRANSAKSI PENGADAAN
    "CREATE TABLE IF NOT EXISTS transaksi_pengadaan (
        idPengadaan VARCHAR(20) PRIMARY KEY,
        idKategori VARCHAR(20) NOT NULL,
        idTendik VARCHAR(20) NOT NULL,
        idKepalaGA VARCHAR(20) NULL,
        idSupplier VARCHAR(20) NULL,
        idFinance VARCHAR(20) NULL,
        namaKebutuhan VARCHAR(255) NOT NULL,
        tanggalPengadaan DATETIME NOT NULL,
        jumlah INT NOT NULL,
        alasanKebutuhan TEXT NOT NULL,
        statusPengadaan ENUM('Draft', 'Disetujui GA', 'Harga Diinput Supplier', 'Disetujui Finance', 'Ditolak') DEFAULT 'Draft',
        dokumen_pengajuan VARCHAR(255) NULL,
        dokumen_penawaran VARCHAR(255) NULL,
        FOREIGN KEY (idKategori) REFERENCES kategori(idKategori) ON UPDATE CASCADE,
        FOREIGN KEY (idTendik) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idKepalaGA) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idSupplier) REFERENCES supplier(idSupplier) ON UPDATE CASCADE,
        FOREIGN KEY (idFinance) REFERENCES users(idUser) ON UPDATE CASCADE
    )",

    // 7. MASTER ASET
    "CREATE TABLE IF NOT EXISTS aset (
        idAset VARCHAR(20) PRIMARY KEY,
        idKategori VARCHAR(20) NOT NULL,
        idPengadaan VARCHAR(20) NULL,
        namaAset VARCHAR(100) NOT NULL,
        kondisiAset ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi') DEFAULT 'Normal',
        ketersediaanAset ENUM('Tersedia', 'Dipinjam', 'Tidak Tersedia', 'Sedang Diperbaiki') DEFAULT 'Tersedia',
        FOREIGN KEY (idKategori) REFERENCES kategori(idKategori) ON UPDATE CASCADE,
        FOREIGN KEY (idPengadaan) REFERENCES transaksi_pengadaan(idPengadaan) ON UPDATE CASCADE
    )",

    // 8. MASTER FASILITAS
    "CREATE TABLE IF NOT EXISTS fasilitas (
        idFasilitas VARCHAR(20) PRIMARY KEY,
        idTendik VARCHAR(20) NOT NULL,
        idKategori VARCHAR(20) NOT NULL,
        namaFasilitas VARCHAR(100) NOT NULL,
        lokasiFasilitas VARCHAR(100) NOT NULL,
        kondisiFasilitas ENUM('Normal', 'Berfungsi', 'Tidak Berfungsi') DEFAULT 'Normal',
        ketersediaanFasilitas ENUM('Tersedia', 'Dipinjam', 'Tidak Tersedia', 'Sedang Diperbaiki') DEFAULT 'Tersedia',
        FOREIGN KEY (idTendik) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idKategori) REFERENCES kategori(idKategori) ON UPDATE CASCADE
    )",

    // 9. TRANSAKSI PEMINJAMAN
    "CREATE TABLE IF NOT EXISTS transaksi_peminjaman (
        idPeminjaman VARCHAR(20) PRIMARY KEY,
        nimMahasiswa VARCHAR(20) NOT NULL,
        idTendik VARCHAR(20) NULL,
        idAset VARCHAR(20) NULL,
        idFasilitas VARCHAR(20) NULL,
        tanggalPengajuan DATETIME NOT NULL,
        tanggalPeminjaman DATETIME NULL,
        tanggalRencana_kembali DATETIME NOT NULL,
        keperluan VARCHAR(255) NOT NULL,
        statusPeminjaman ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai') DEFAULT 'Menunggu',
        FOREIGN KEY (nimMahasiswa) REFERENCES mahasiswa(nimMahasiswa) ON UPDATE CASCADE,
        FOREIGN KEY (idTendik) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idAset) REFERENCES aset(idAset) ON UPDATE CASCADE,
        FOREIGN KEY (idFasilitas) REFERENCES fasilitas(idFasilitas) ON UPDATE CASCADE
    )",

    // 10. TRANSAKSI PENGEMBALIAN
    "CREATE TABLE IF NOT EXISTS transaksi_pengembalian (
        idPengembalian VARCHAR(20) PRIMARY KEY,
        idPeminjaman VARCHAR(20) NOT NULL UNIQUE,
        idTendik VARCHAR(20) NOT NULL,
        idSanksi VARCHAR(20) NULL,
        tanggalPengembalian DATETIME NOT NULL,
        kondisiFisik TEXT NOT NULL,
        catatanPengembalian TEXT NULL,
        FOREIGN KEY (idPeminjaman) REFERENCES transaksi_peminjaman(idPeminjaman) ON UPDATE CASCADE,
        FOREIGN KEY (idTendik) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idSanksi) REFERENCES sanksi(idSanksi) ON UPDATE CASCADE
    )",

    // 11. TRANSAKSI REPARASI
    "CREATE TABLE IF NOT EXISTS reparasi_fasilitas_aset (
        idReparasi VARCHAR(20) PRIMARY KEY,
        idTendik VARCHAR(20) NOT NULL,
        idStaffGA VARCHAR(20) NULL,
        idAset VARCHAR(20) NULL,
        idFasilitas VARCHAR(20) NULL,
        tanggalLapor DATETIME NOT NULL,
        tanggalReparasi DATETIME NULL,
        tanggalSelesai DATETIME NULL,
        klasifikasiKerusakan ENUM('Rusak Ringan', 'Rusak Sedang', 'Rusak Berat', 'Rusak Total') NOT NULL,
        catatanReparasi TEXT NULL,
        statusReparasi ENUM('Menunggu GA', 'Sedang Dikerjakan', 'Selesai', 'Dikanibal') DEFAULT 'Menunggu GA',
        FOREIGN KEY (idTendik) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idStaffGA) REFERENCES users(idUser) ON UPDATE CASCADE,
        FOREIGN KEY (idAset) REFERENCES aset(idAset) ON UPDATE CASCADE,
        FOREIGN KEY (idFasilitas) REFERENCES fasilitas(idFasilitas) ON UPDATE CASCADE
    )",

    // 12. MASTER KOMPONEN (Kanibal)
    "CREATE TABLE IF NOT EXISTS komponen (
        idKomponen VARCHAR(20) PRIMARY KEY,
        idReparasi VARCHAR(20) NOT NULL,
        namaKomponen VARCHAR(100) NOT NULL,
        spesifikasiKomponen VARCHAR(255) NULL,
        kondisiKomponen ENUM('Sangat Baik', 'Layak Pakai') NOT NULL,
        statusKomponen ENUM('Tersedia', 'Sudah Dipakai') DEFAULT 'Tersedia',
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
('SA-001', 'IT Pusat ASTRAtech', '0812', 'Super Admin', 'admin@astratech.ac.id', '$password_default', 'SA'),
('TDK-001', 'Bapak Budi', '0813', 'Tenaga Pendidik', 'budi@astratech.ac.id', '$password_default', 'TRPL')";
mysqli_query($conn, $q_users);

// B. Insert Mahasiswa TRPL
$q_mhs = "INSERT IGNORE INTO mahasiswa (nimMahasiswa, namaMahasiswa, kodeProdi_mahasiswa, noTelp_mahasiswa, emailMahasiswa, passMahasiswa) VALUES 
('0920260001', 'Andi Pratama', 'TRPL', '0814', 'andi@student.astratech.ac.id', '$password_default')";
mysqli_query($conn, $q_mhs);

// C. Insert Kategori
$q_kategori = "INSERT IGNORE INTO kategori (idKategori, namaKategori, tipeKategori) VALUES 
('KTG-001', 'Proyektor', 'Aset'),
('KTG-002', 'Ruang Kelas', 'Fasilitas'),
('KTG-003', 'Laptop', 'Aset')";
mysqli_query($conn, $q_kategori);

// D. Insert Aset Dummy (Biar Mahasiswa bisa langsung klik pinjam pas demo!)
$q_aset = "INSERT IGNORE INTO aset (idAset, idKategori, namaAset) VALUES 
('AST-001', 'KTG-001', 'Proyektor Epson EB-X400')";
mysqli_query($conn, $q_aset);

// Cek Hasil
if ($berhasil == count($tables)) {
    echo "<div style='background-color: #d4edda; border-left: 5px solid #28a745; padding: 15px; margin: 20px 0;'>";
    echo "<h3>🎉 DATABASE SIAP 100%!</h3>";
    echo "<p>Seluruh 12 tabel dengan <b>VARCHAR(20)</b> telah berhasil dibuat sesuai dengan XML PDM Anda.</p>";
    echo "<p><b>Data Dummy yang disuntikkan:</b></p>";
    echo "<ul>
            <li>User: TDK-001 (Tendik)</li>
            <li>NIM: 0920260001 (Mahasiswa)</li>
            <li>Aset: AST-001 (Proyektor)</li>
          </ul>";
    echo "<p>Silakan gas koding Modul Peminjamannya! <b>Semoga Review 1 hari ini lancar jaya!</b> 🚀</p>";
    echo "</div>";
}

echo "</div>"; // Tutup div style
