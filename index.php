<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTARrent - Sistem Logistik ASTRAtech</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-blue: #1d4197;
            --secondary-blue: #2a5bd4;
            --white: #FFFFFF;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--primary-blue);
        }

        /* ------------------------------------------- */
        /* EFEK BACKGROUND GAMBAR & OVERLAY KACA BIRU  */
        /* ------------------------------------------- */
        .hero-section {
            min-height: 100vh;
            /* Pastikan Anda sudah punya file bg_kampus.jpg di folder assets/images/ */
            background: linear-gradient(135deg, rgba(29, 65, 151, 0.85) 0%, rgba(42, 91, 212, 0.75) 100%),
                url('assets/images/bg_kampus.png') center/cover no-repeat fixed;
            display: flex;
            align-items: center;
            padding: 40px 0;
        }

        /* ------------------------------------------- */
        /* CSS TOMBOL DAN KARTU (SAMA SEPERTI DASHBOARD)*/
        /* ------------------------------------------- */
        .btn-custom {
            background-color: var(--white);
            color: var(--primary-blue);
            font-weight: 700;
            border-radius: 50px;
            padding: 12px 35px;
            transition: 0.3s;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            border: 2px solid transparent;
        }

        .btn-custom:hover {
            background-color: transparent;
            color: var(--white);
            border: 2px solid var(--white);
            transform: translateY(-3px);
        }

        /* Kartu Fitur Persis Seperti Menu Dashboard */
        .feature-card {
            background-color: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            padding: 25px;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .icon-box {
            width: 60px;
            height: 60px;
            background-color: #e8f0fe;
            color: #1d4197;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">

                <!-- BAGIAN KIRI: Teks Utama & Tombol Login -->
                <div class="col-lg-6 mb-5 mb-lg-0 text-white pe-lg-5">
                    <!-- Gunakan Logo Putih agar menyatu dengan background gelap -->
                    <img src="assets/images/full_logo_white.png" alt="Logo ASTARrent" height="80" class="mb-4" style="filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.3)); transform: scale(1.5); transform-origin: left center;">

                    <h1 class="display-4 fw-bold mb-3" style="line-height: 1.2;">
                        Sistem Manajemen<br>Aset & Fasilitas
                    </h1>
                    <p class="fs-5 mb-5 opacity-75" style="font-weight: 300;">
                        Platform terintegrasi ASTRAtech untuk reservasi fasilitas, pelaporan kerusakan, hingga pengadaan aset dengan alur birokrasi paperless.
                    </p>

                    <a href="modules/00_auth/login.php" class="btn btn-custom fs-5">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Sistem ASTARrent
                    </a>
                </div>

                <!-- BAGIAN KANAN: Kartu Fitur (Desain mirip Dashboard Menu) -->
                <div class="col-lg-6">
                    <div class="row g-4">

                        <!-- Kartu 01 -->
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="icon-box"><i class="bi bi-cart-plus-fill"></i></div>
                                <h5 class="fw-bold text-dark">Reservasi Cepat</h5>
                                <p class="text-secondary mb-0" style="font-size: 14px;">Peminjaman alat elektronik dan ruangan kelas dalam satu klik oleh mahasiswa.</p>
                            </div>
                        </div>

                        <!-- Kartu 2 -->
                        <div class="col-md-6">
                            <div class="feature-card mt-md-4">
                                <div class="icon-box"><i class="bi bi-shield-lock-fill"></i></div>
                                <h5 class="fw-bold text-dark">Sistem Disiplin</h5>
                                <p class="text-secondary mb-0" style="font-size: 14px;">Otomatisasi sanksi dan pembekuan akun untuk menjaga integritas pengembalian aset.</p>
                            </div>
                        </div>

                        <!-- Kartu 3 -->
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="icon-box"><i class="bi bi-tools"></i></div>
                                <h5 class="fw-bold text-dark">Kanibalisasi Aset</h5>
                                <p class="text-secondary mb-0" style="font-size: 14px;">Menyelamatkan komponen berharga dari aset yang rusak total untuk efisiensi kampus.</p>
                            </div>
                        </div>

                        <!-- Kartu 4 -->
                        <div class="col-md-6">
                            <div class="feature-card mt-md-4">
                                <div class="icon-box"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                                <h5 class="fw-bold text-dark">E-Procurement</h5>
                                <p class="text-secondary mb-0" style="font-size: 14px;">Pengadaan aset baru melalui sistem tender paperless yang terhubung langsung ke Finance.</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>