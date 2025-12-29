<?php
session_start();
include 'koneksi.php';

if (!$conn) {
    die("Koneksi gagal");
}

// CEGAT kalau belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id      = $_SESSION['user_id'];
$depositor    = $_POST['depositor'];
$bankPengirim = $_POST['bankPengirim'];
$nama         = $_POST['nama'];
$email        = $_POST['email'];
$telepon      = $_POST['telepon'];
$alamat       = $_POST['alamat'];
$total        = $_POST['total'];

$query = "INSERT INTO pesanan 
(user_id, depositor, bank_pengirim, nama, email, telepon, alamat, total, status, created_at)
VALUES
('$user_id', '$depositor', '$bankPengirim', '$nama', '$email', '$telepon', '$alamat', '$total', 'pending', NOW())";

if (mysqli_query($conn, $query)) {
    header("Location: dashboard_user.php");
    exit;
} else {
    echo mysqli_error($conn);
}
?>
