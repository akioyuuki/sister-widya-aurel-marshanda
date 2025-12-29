<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
?>

<h2>Dashboard</h2>
<p>Halo, <?= $_SESSION['nama']; ?></p>
<p>Role: <?= $_SESSION['role']; ?></p>

<?php if ($_SESSION['role'] == 'admin') { ?>
  <p><b>Menu Admin</b></p>
  <ul>
    <li>Lihat Semua Pesanan</li>
    <li>Kelola Produk</li>
  </ul>
<?php } else { ?>
  <p><b>Menu User</b></p>
  <ul>
    <li>History Pesanan</li>
  </ul>
<?php } ?>

<a href="logout.php">Logout</a>
