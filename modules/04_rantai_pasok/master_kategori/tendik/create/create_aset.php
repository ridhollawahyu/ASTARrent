<?php
// --- FILE: modules/04_rantai_pasok/master_kategori/create/create_aset.php ---
error_reporting(0);
ini_set('display_errors', 0);
session_start();

ob_start();
require '../../../../../config/database.php';
require '../../../../../config/functions.php';

$response = ['status' => 'error', 'pesan' => 'Unknown Error'];

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'Tenaga Pendidik') {
    $response['pesan'] = 'Akses Ditolak!';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Panggil fungsi dengan mengirimkan nama dan ID Tendik
    $response = tambah_kategori_draft($_POST['nama_kategori'], $_SESSION['id']);
}

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
exit;
