<?php
session_start();
include 'koneksi.php';

/* ===============================
   CEK LOGIN (WAJIB)
================================ */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #4f46e5;
            color: white;
        }
        h2 {
            margin-bottom: 20px;
        }
        .logout {
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>

<h2>Dashboard User</h2>

<a class="logout" href="logout.php">Logout</a>

<table>
    <tr>
        <th>ID Pesanan</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Total</th>
        <th>Status</th>
        <th>Tanggal</th>
    </tr>

<?php
$query = mysqli_query($conn, "
    SELECT id, nama, email, total, status, created_at
    FROM pesanan
    WHERE user_id = '$user_id'
    ORDER BY id DESC
");

if (mysqli_num_rows($query) == 0) {
    echo "<tr><td colspan='6'>Belum ada transaksi</td></tr>";
} else {
    while ($row = mysqli_fetch_assoc($query)) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['nama']}</td>
            <td>{$row['email']}</td>
            <td>Rp {$row['total']}</td>
            <td>{$row['status']}</td>
            <td>{$row['created_at']}</td>
        </tr>";
    }
}
?>

</table>

</body>
</html>
