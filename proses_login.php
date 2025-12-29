<?php
session_start();
include 'koneksi.php';

$email = $_POST['email'];
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$data = mysqli_fetch_assoc($query);

if ($data && $password == $data['password']) {

    $_SESSION['login'] = true;
    $_SESSION['user_id'] = $data['id'];   // 🔴 INI YANG KURANG
    $_SESSION['role'] = $data['role'];

    header("Location: index.php");
    exit;
} else {
    echo "<script>alert('Email atau password salah');</script>";
}
