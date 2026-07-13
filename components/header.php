<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTARrent Workspace</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery CDN (Sangat dibutuhkan untuk DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables Bootstrap 5 CSS & JS CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Font Google Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS Tema ASTARrent Modern -->
    <style>
        :root {
            --primary-blue: #1d4197;
            --secondary-blue: #2a5bd4;
            --white: #FFFFFF;
            --light-bg: #f4f6f9;
            --danger: #dc3545
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
            /* Menggunakan font Poppins agar sangat modern */
        }

        /* ------------------------------------------- */
        /* NAVBAR MODERN STYLING                       */
        /* ------------------------------------------- */
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            box-shadow: 0 4px 15px rgba(29, 65, 151, 0.2);
            padding: 12px 0;
        }

        /* Profil Kapsul (User Pill) ala Aplikasi Startup */
        .user-pill {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50px;
            padding: 5px 15px 5px 5px;
            display: flex;
            align-items: center;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-avatar {
            background: var(--white);
            color: var(--primary-blue);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Tombol Logout Elegan */
        .btn-logout {
            border-radius: 20px;
            font-weight: 600;
            padding: 6px 20px;
            transition: all 0.3s ease;
            border: 1.5px solid rgba(255, 255, 255, 0.7);
        }

        .btn-logout:hover {
            background-color: #ff4d4f;
            /* Merah soft saat di-hover */
            color: white !important;
            border-color: #ff4d4f;
            transform: translateY(-2px);
        }

        /* ------------------------------------------- */
        /* CSS UMUM & DROPDOWN CUSTOM ASTARRENT        */
        /* ------------------------------------------- */
        .text-astar {
            color: var(--primary-blue) !important;
        }

        .bg-astar {
            background-color: var(--primary-blue) !important;
            color: var(--white);
        }

        .btn-astar {
            background-color: var(--primary-blue);
            color: var(--white);
            border: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-astar:hover {
            background-color: #152d6b;
            color: var(--white);
            transform: translateY(-2px);
        }

        .select-astar {
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            padding: 10px 15px;
            color: #1d4197;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            background-color: #f9fbfd;
        }

        .select-astar:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.25rem rgba(29, 65, 151, 0.25);
            background-color: #ffffff;
            outline: 0;
        }

        .btn-outline-astar {
            color: #1d4197 !important;
            background-color: #ffffff !important;
            border: 2px solid #1d4197 !important;
            transition: all 0.3s ease;
        }

        .btn-outline-astar:hover {
            color: #1d4197 !important;
            background-color: #ffffff !important;
            border: 2px solid #1d4197 !important;
        }

        .btn-check:checked+.btn-outline-astar {
            color: #fff !important;
            background-color: #1d4197 !important;
            border: 2px solid #1d4197 !important;
            box-shadow: 0 4px 12px rgba(29, 65, 151, 0.3) !important;
        }

        /* Custom Dropdown Container (Anti-Safari Bug) */
        .custom-dropdown-container {
            position: relative;
            width: 100%;
            user-select: none;
        }

        .custom-dropdown-selected {
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            padding: 10px 15px;
            color: #1d4197;
            font-weight: 500;
            background-color: #f9fbfd;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-dropdown-selected:hover {
            border-color: var(--primary-blue);
        }

        .custom-dropdown-options {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: #ffffff;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            margin-top: 5px;
            z-index: 999;
            max-height: 250px;
            overflow-y: auto;
        }

        .custom-dropdown-item {
            padding: 10px 15px;
            color: #333;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .custom-dropdown-item:hover,
        .custom-dropdown-item.active {
            background-color: var(--primary-blue);
            color: #ffffff;
            font-weight: bold;
        }

        /* Custom Dropdown Danger Container (Anti-Safari Bug) */
        .custom-dropdanger-container {
            position: relative;
            width: 100%;
            user-select: none;
        }

        .custom-dropdanger-selected {
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            padding: 10px 15px;
            color: #dc3545;
            font-weight: 500;
            background-color: #f9fbfd;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-dropdanger-selected:hover {
            border-color: var(--danger);
        }

        .custom-dropdanger-options {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: #ffffff;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            margin-top: 5px;
            z-index: 999;
            max-height: 250px;
            overflow-y: auto;
        }

        .custom-dropdanger-item {
            padding: 10px 15px;
            color: #333;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .custom-dropdanger-item:hover,
        .custom-dropdanger-item.active {
            background-color: var(--danger);
            color: #ffffff;
            font-weight: bold;
        }
    </style>
    <script>
        window.onpageshow = function(event) {
            // Jika halaman diload dari memori cache browser (tombol back)
            if (event.persisted) {
                // Paksa refresh halaman dari server!
                window.location.reload();
            }
        };
    </script>
    <!-- SCRIPT WAJIB UNTUK DROPDOWN CUSTOM ASTARRENT -->
    <script>
        function toggleDropdown(id) {
            let opsibox = document.getElementById('options_' + id);
            if (opsibox.style.display === 'block') {
                opsibox.style.display = 'none';
            } else {
                // Tutup semua jenis dropdown (biru maupun merah)
                document.querySelectorAll('.custom-dropdown-options, .custom-dropdanger-options').forEach(el => el.style.display = 'none');
                opsibox.style.display = 'block';
            }
        }

        function selectOption(id, nilai, label) {
            document.getElementById('text_' + id).innerText = label;
            document.getElementById('input_' + id).value = nilai;
            document.getElementById('options_' + id).style.display = 'none';

            let opsi = document.getElementById('options_' + id).children;
            for (let i = 0; i < opsi.length; i++) {
                opsi[i].classList.remove('active');
            }
            event.target.classList.add('active');
        }

        document.addEventListener('click', function(event) {
            // Deteksi jika klik BUKAN di elemen dropdown biru ATAU merah
            if (!event.target.closest('.custom-dropdown-container') && !event.target.closest('.custom-dropdanger-container')) {
                document.querySelectorAll('.custom-dropdown-options, .custom-dropdanger-options').forEach(el => el.style.display = 'none');
            }
        });
    </script>
</head>

<body>

    <!-- NAVBAR MODERN ASTARRENT -->
    <?php
    $link_dashboard = "/astarrent/index.php";

    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'Super Admin') {
            $link_dashboard = "/astarrent/modules/dashboards/superadmin_home.php";
        } else if ($_SESSION['role'] == 'Tenaga Pendidik') {
            $link_dashboard = "/astarrent/modules/dashboards/tendik_home.php";
        } else if ($_SESSION['role'] == 'Mahasiswa') {
            $link_dashboard = "/astarrent/modules/dashboards/mahasiswa_home.php";
        } else if ($_SESSION['role'] == 'Staff GA') {
            $link_dashboard = "/astarrent/modules/dashboards/staffga_home.php";
        } else if ($_SESSION['role'] == 'Kepala GA') {
            $link_dashboard = "/astarrent/modules/dashboards/kepalaga_home.php";
        } else {
            $link_dashboard = "/astarrent/modules/dashboards/finance_home.php";
        }
    }
    ?>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top mb-4">
        <div class="container">
            <!-- LOGO PROJECT -->
            <a class="navbar-brand d-flex align-items-center" href="<?= $link_dashboard ?>">
                <img src="/ASTARrent/assets/images/full_logo_white.png" alt="Logo" height="45" class="me-2 d-inline-block align-text-top" style="filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.2)); transform: scale(1.45); transform-origin: left center;">
            </a>

            <!-- PROFIL & TOMBOL LOGOUT -->
            <div class="d-flex text-white align-items-center">

                <!-- Otomatis mendeteksi role yang sedang login -->
                <?php if (isset($_SESSION['role'])): ?>
                    <div class="user-pill me-3 d-none d-md-flex">
                        <div class="user-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <!-- Akan menampilkan: Mahasiswa, Tenaga Pendidik, dll -->
                        <span class="fs-6 fw-medium me-2"><?= $_SESSION['role']; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Tombol Logout Modern (Memicu Pop-Up) -->
                <button type="button" class="btn btn-outline-light btn-sm btn-logout shadow-sm" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="bi bi-power"></i> Keluar
                </button>
            </div>
        </div>
    </nav>

    <!-- Container Utama Mulai Di Sini -->
    <div class="container pb-5">