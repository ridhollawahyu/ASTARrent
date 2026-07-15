<!DOCTYPE html>
<html lang="id">
<?php error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SSO - ASTARrent</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #1d4197;
            --white: #FFFFFF;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Style untuk Video Background */
        .video-bg {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
            filter: brightness(1);
            /* Menggelapkan video agar form login menonjol */
        }

        /* Style Kotak Login */
        .login-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            max-width: 400px;
            width: 100%;
        }

        .btn-astar {
            background-color: var(--primary-blue);
            color: var(--white);
            font-weight: bold;
        }

        .btn-astar:hover {
            background-color: #152d6b;
            color: var(--white);
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center">

    <!-- Video Looping -->
    <video autoplay loop muted playsinline class="video-bg">
        <source src="../../assets/media/bg_login_sso.mp4" type="video/mp4">
    </video>

    <!-- Kotak Login -->
    <div class="login-box text-center">
        <!-- LOGO PROJECT ANDA DI SINI -->
        <img src="../../assets/images/full_logo_blue.png" alt="Logo ASTARrent" style="max-height: 80px; margin-bottom: 20px;">

        <h4 style="color: var(--primary-blue); font-weight: bold;">Halaman Login</h4>
        <p class="text-muted mb-4">Sistem Manajemen Aset & Fasilitas ASTRAtech</p>

        <form action="proses_login.php" method="POST">

            <!-- TAMBAHAN: DROPDOWN TIPE AKUN -->
            <div class="mb-3 text-start">
                <label class="form-label fw-bold" style="color: var(--primary-blue);">NIM / Email</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan ID atau NIM" required>
            </div>

            <div class="mb-4 text-start">
                <label class="form-label fw-bold" style="color: var(--primary-blue);">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
            </div>

            <!-- PENTING: Tambahkan name="login" di tombol ini! -->
            <button type="submit" name="login" class="btn btn-astar w-100 py-2">Masuk ke Sistem</button>
        </form>
    </div>

    <!-- ============================================== -->
    <!-- MODAL ALERT NOTIFIKASI KHUSUS HALAMAN LOGIN    -->
    <!-- ============================================== -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                <div class="modal-header" id="alertHeader" style="color: white; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <h5 class="modal-title fw-bold" id="alertTitle">Notifikasi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <i id="alertIcon" class="mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-dark fw-bold mb-2" id="alertMessage"></h5>
                </div>
                <div class="modal-footer justify-content-center border-0 pb-4">
                    <button type="button" class="btn px-5 fw-bold text-white" data-bs-dismiss="modal" style="background-color: #1d4197;">OK, Mengerti</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT BOOTSTRAP (WAJIB ADA) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php
    // MENGAKTIFKAN MODAL ALERT JIKA ADA SESSION DARI PHP
    if (isset($_SESSION['notif_pesan'])):
        $tipe = $_SESSION['notif_tipe'];
        $pesan = $_SESSION['notif_pesan'];

        $bgColor = ($tipe == 'success') ? '#198754' : '#dc3545'; // Hijau atau Merah
        $iconClass = ($tipe == 'success') ? 'bi bi-check-circle-fill text-success' : 'bi bi-x-circle-fill text-danger';
        $title = ($tipe == 'success') ? 'Berhasil!' : 'Akses Ditolak!';
    ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById('alertHeader').style.backgroundColor = '<?= $bgColor ?>';
                document.getElementById('alertTitle').innerText = '<?= $title ?>';
                document.getElementById('alertIcon').className = '<?= $iconClass ?>';
                document.getElementById('alertMessage').innerText = '<?= $pesan ?>';

                var alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
                alertModal.show();
            });
        </script>
    <?php
        unset($_SESSION['notif_tipe']);
        unset($_SESSION['notif_pesan']);
    endif;
    ?>
</body>

</html>