<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../../config/functions.php';

/** @var mysqli $koneksi */

if (isset($_POST["login"])) {
    $id_login = $_POST["username"];
    $password = $_POST["password"];

    // 1. CEK DI TABEL USERS DULU (Karyawan/Tendik/GA/Finance/SA)
    $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE emailUser = '$id_login'");

    if (mysqli_num_rows($cek_user) === 1) {
        $row = mysqli_fetch_assoc($cek_user);

        // Verifikasi password
        if (password_verify($password, $row["passUser"])) {
            $_SESSION["login"] = true;
            $_SESSION["role"] = $row["jabatanUser"];
            $_SESSION["id"] = $row["idUser"];
            $_SESSION["departemen"] = $row["kodeDepartemen"];
            $_SESSION["status"] = $row["statusUser"];

            // Routing sesuai jabatan
            if ($row["jabatanUser"] == "Super Admin") {
                header("Location: ../dashboards/superadmin_home.php");
            } else if ($row["jabatanUser"] == "Tenaga Pendidik") {
                header("Location: ../dashboards/tendik_home.php");
            } else if ($row["jabatanUser"] == "Staff GA") {
                header("Location: ../dashboards/staffga_home.php");
            } else if ($row["jabatanUser"] == "Kepala GA") {
                header("Location: ../dashboards/kepalaga_home.php");
            } else {
                header("Location: ../dashboards/finance_home.php");
            }
            exit;
        }
    }

    // 2. JIKA BUKAN USER, CEK TABEL SUPPLIER (BARU!)
    $cek_supplier = mysqli_query($koneksi, "SELECT * FROM supplier WHERE emailSupplier = '$id_login'");
    if (mysqli_num_rows($cek_supplier) === 1) {
        $row = mysqli_fetch_assoc($cek_supplier);
        if (password_verify($password, $row["passSupplier"])) {
            $_SESSION["login"] = true;
            $_SESSION["role"] = "Supplier";
            $_SESSION["id"] = $row["idSupplier"];
            $_SESSION["status"] = $row["statusSupplier"];
            header("Location: ../dashboards/supplier_home.php");
            exit;
        }
    }

    // 3. JIKA BUKAN USER, CEK DI TABEL MAHASISWA
    else {
        $cek_mhs = mysqli_query($koneksi, "SELECT * FROM mahasiswa WHERE nimMahasiswa = '$id_login' OR emailMahasiswa = '$id_login'");

        if (mysqli_num_rows($cek_mhs) === 1) {
            $row = mysqli_fetch_assoc($cek_mhs);

            // Verifikasi password
            if (password_verify($password, $row["passMahasiswa"])) {

                // Blokir jika disanksi
                // if ($row["status_peminjaman"] == "Dibekukan") {
                //     set_notifikasi('error', 'Akun kamu dibekukan!');
                //     header("Location: login.php");
                //     exit;
                // }

                $_SESSION["login"] = true;
                $_SESSION["role"] = "Mahasiswa";
                $_SESSION["id"] = $row["nimMahasiswa"];
                $_SESSION["status"] = $row["statusMahasiswa"];

                header("Location: ../dashboards/mahasiswa_home.php");
                exit;
            }
        }
    }

    // 3. JIKA DUA-DUANYA GAGAL (ID tidak ada atau Pass salah)
    set_notifikasi('error', "Gagal! Email/NIM atau Password Salah!");
    header("Location: login.php");
} else {
    header("Location: login.php");
}
