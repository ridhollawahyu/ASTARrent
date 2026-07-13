<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: ../00_auth/login.php');
    exit;
}

$role = $_SESSION['role'] ?? '';

if ($role === 'Kepala GA') {
    header('Location: kepala_ga/index.php');
    exit;
} elseif ($role === 'Staff GA') {
    header('Location: staffga/index.php');
    exit;
} elseif ($role === 'Finance') {
    header('Location: finance/index.php');
    exit;
} elseif ($role === 'Tenaga Pendidik') {
    header('Location: tendik/index.php');
    exit;
} else {
    // Default fallback
    echo "Akses Ditolak!";
    exit;
}
?>
