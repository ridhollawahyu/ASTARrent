<?php
// --- FILE: modules/02_kedisiplinan/pelunasan_sanksi/tendik/proses_lunas.php ---
session_start();
require '../../../../config/database.php';
require '../../../../config/functions.php';

if (isset($_POST['submit_lunas']) && $_SESSION['role'] === 'Tenaga Pendidik') {
    global $koneksi;
    $nim = mysqli_real_escape_string($koneksi, $_POST['nim']);
    $dept = $_SESSION['departemen'];

    $q_cek = mysqli_query($koneksi, "SELECT nimMahasiswa FROM mahasiswa WHERE nimMahasiswa = '$nim' AND kodeProdi_mahasiswa = '$dept'");

    if (mysqli_num_rows($q_cek) > 0) {
        // HANYA MENGUBAH JAM MINUS
        mysqli_query($koneksi, "UPDATE mahasiswa SET jamMinus_mahasiswa = 0 WHERE nimMahasiswa = '$nim'");

        // PANGGIL FUNGSI SISTEM PAKAR UNTUK MENGUBAH STATUS SECARA OTOMATIS!
        perbarui_status_mahasiswa($nim);

        set_notifikasi('success', 'Jam Minus berhasil dihapus! Status akun Mahasiswa diperbarui.');
    } else {
        set_notifikasi('error', 'Akses Ditolak! Mahasiswa bukan dari Prodi Anda.');
    }
    header('Location: index.php');
    exit;
}
header('Location: index.php');
exit;
