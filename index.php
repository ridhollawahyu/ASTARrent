<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTARrent - Sistem Logistik ASTRAtech</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-blue: #1d4197;
            --secondary-blue: #2a5bd4;
            --white: #FFFFFF;
            --accent-yellow: #ffc107;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--primary-blue);
            overflow-x: hidden;
        }

        /* ------------------------------------------- */
        /* EFEK BACKGROUND GAMBAR & OVERLAY KACA BIRU  */
        /* ------------------------------------------- */
        .hero-section {
            min-height: 100vh;
            /* Latar belakang kampus dengan gradasi modern */
            background: linear-gradient(135deg, rgba(29, 65, 151, 0.9) 0%, rgba(42, 91, 212, 0.75) 100%),
                url('assets/images/bg_kampus.png') center/cover no-repeat fixed;
            display: flex;
            align-items: center;
            padding: 40px 0;
            position: relative;
        }

        /* ------------------------------------------- */
        /* CSS TOMBOL LOGIN MODERN                     */
        /* ------------------------------------------- */
        .btn-custom {
            background-color: var(--white);
            color: #000;
            font-weight: 700;
            border-radius: 50px;
            padding: 14px 35px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgb(255, 255, 255, 0.4);
            border: 2px solid var(--white);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-custom:hover {
            background-color: transparent;
            color: var(--white);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        /* ------------------------------------------- */
        /* KARTU FITUR (GLASSMORPHISM STYLE)           */
        /* ------------------------------------------- */
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 25px;
            height: 100%;
            transition: all 0.4s ease;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: var(--white);
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .icon-box {
            width: 55px;
            height: 55px;
            background: rgba(255, 255, 255, 0.95);
            color: var(--primary-blue);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .feature-card:hover .icon-box {
            transform: scale(1.1) rotate(5deg);
        }

        .badge-mahasiswa {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            letter-spacing: 1px;
            backdrop-filter: blur(5px);
        }
    </style>
</head>

<body>

    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">

                <!-- BAGIAN KIRI: Teks Utama & Tombol Login Mahasiswa -->
                <div class="col-lg-6 mb-5 mb-lg-0 text-white pe-lg-5">

                    <img src="assets/images/full_logo_white.png" alt="Logo ASTARrent" height="80" class="mb-4" style="filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.3)); transform: scale(1.5); transform-origin: left center;">

                    <div class="mb-4 d-inline-block badge-mahasiswa text-uppercase fw-bold" style=" position: relative; top: -87px; left: -224px;">
                        <i class="bi bi-mortarboard-fill me-2 text-warning"></i> Platform Khusus Mahasiswa
                    </div>

                    <h1 class="display-4 fw-bold mb-3" style="line-height: 1.2;">
                        Pinjam Fasilitas & Aset Kini Makin Mudah!
                    </h1>
                    <p class="fs-5 mb-5" style="font-weight: 300; color: rgba(255,255,255,0.85);">
                        ASTARrent hadir untuk membantu Anda memesan ruang kelas, meminjam kamera, hingga proyektor secara <b>cepat, mudah, dan paperless</b> (tanpa kertas).
                    </p>

                    <a href="modules/00_auth/login.php" class="btn btn-custom">
                        Masuk ke Sistem ASTARrent <i class="bi bi-box-arrow-in-right"></i>
                    </a>

                    <div class="mt-4" style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">
                        *Gunakan NIM dan Password akademik Anda untuk masuk ke dalam sistem.
                    </div>
                </div>

                <!-- BAGIAN KANAN: Kartu Fitur (Fokus Benefit Mahasiswa) -->
                <div class="col-lg-6">
                    <div class="row g-4">

                        <!-- Kartu 1: Peminjaman -->
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="icon-box"><i class="bi bi-laptop"></i></div>
                                <h5 class="fw-bold">Reservasi Sekali Klik</h5>
                                <p class="mb-0 opacity-75" style="font-size: 13px; font-weight: 300;">Cari dan pinjam alat elektronik atau fasilitas kampus secara real-time langsung dari genggaman Anda.</p>
                            </div>
                        </div>

                        <!-- Kartu 2: Kedisiplinan -->
                        <div class="col-md-6">
                            <div class="feature-card mt-md-4">
                                <div class="icon-box"><i class="bi bi-shield-check"></i></div>
                                <h5 class="fw-bold">Pemantauan Disiplin</h5>
                                <p class="mb-0 opacity-75" style="font-size: 13px; font-weight: 300;">Sistem cerdas kami akan memantau waktu pinjam Anda secara transparan untuk menghindari jam minus dan denda.</p>
                            </div>
                        </div>

                        <!-- Kartu 3: Pembongkaran (Ubah istilah Kanibal) -->
                        <div class="col-md-6">
                            <div class="feature-card">
                                <div class="icon-box"><i class="bi bi-tools"></i></div>
                                <h5 class="fw-bold">Pembongkaran Aset</h5>
                                <p class="mb-0 opacity-75" style="font-size: 13px; font-weight: 300;">Pengelolaan cerdas dengan membongkar aset rusak menjadi suku cadang, guna memastikan fasilitas praktikum selalu prima.</p>
                            </div>
                        </div>

                        <!-- Kartu 4: Pengadaan -->
                        <div class="col-md-6">
                            <div class="feature-card mt-md-4">
                                <div class="icon-box"><i class="bi bi-cart-check-fill"></i></div>
                                <h5 class="fw-bold">Stok Selalu Update</h5>
                                <p class="mb-0 opacity-75" style="font-size: 13px; font-weight: 300;">Terhubung langsung dengan e-Procurement kampus. Jika barang kurang, sistem otomatis mengajukan pengadaan baru.</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>