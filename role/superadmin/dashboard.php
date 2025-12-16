<?php
include "../../koneksi.php";
include "includes/header.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Superadmin</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container">
  <?php include "includes/sidebar.php"; ?>

  <main class="content">
    <div class="page-title-box">
      <h2>🏠 Dashboard</h2>
      <p>Selamat datang di halaman utama sistem manajemen RSHP.</p>
    </div>

    <!-- 🔹 Statistik Kunci -->
    <div class="grid">
      <?php
      // Jumlah data master
      $total_barang = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM barang"))['jml'];
      $total_vendor = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM vendor"))['jml'];
      $total_user   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM user"))['jml'];
      $total_satuan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM satuan"))['jml'];

      // Transaksi
      $pengadaan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengadaan"))['jml'];
      $penerimaan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM penerimaan"))['jml'];
      $penjualan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM penjualan"))['jml'];

      // Total uang
      $total_pengeluaran = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT fn_total_pengeluaran() AS total"))['total'];
      $total_pemasukan   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT fn_total_pemasukan() AS total"))['total'];
      ?>

      <div class="card">
        <h4>🧾 Barang</h4>
        <p><?php echo $total_barang; ?></p>
      </div>
      <div class="card">
        <h4>🏢 Vendor</h4>
        <p><?php echo $total_vendor; ?></p>
      </div>
      <div class="card">
        <h4>👤 User</h4>
        <p><?php echo $total_user; ?></p>
      </div>
      <div class="card">
        <h4>📦 Satuan</h4>
        <p><?php echo $total_satuan; ?></p>
      </div>
      <div class="card">
        <h4>📋 Pengadaan</h4>
        <p><?php echo $pengadaan; ?></p>
      </div>
      <div class="card">
        <h4>📥 Penerimaan</h4>
        <p><?php echo $penerimaan; ?></p>
      </div>
      <div class="card">
        <h4>💰 Penjualan</h4>
        <p><?php echo $penjualan; ?></p>
      </div>
    </div>

    <!-- 🔹 Total Keuangan -->
    <h3 style="margin-top:40px;">💹 Ringkasan Keuangan</h3>
    <table class="data-table">
      <thead>
        <tr>
          <th>Kategori</th>
          <th>Total (Rp)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Pemasukan</strong></td>
          <td style="color:#1b5e20;">Rp <?php echo number_format($total_pemasukan, 2, ',', '.'); ?></td>
        </tr>
        <tr>
          <td><strong>Pengeluaran</strong></td>
          <td style="color:#b71c1c;">Rp <?php echo number_format($total_pengeluaran, 2, ',', '.'); ?></td>
        </tr>
        <tr style="background:#f0f4ff;">
          <td><strong>Keuntungan Bersih</strong></td>
          <td style="color:#0d47a1;">
            <b>Rp <?php echo number_format($total_pemasukan - $total_pengeluaran, 2, ',', '.'); ?></b>
          </td>
        </tr>
      </tbody>
    </table>
  </main>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>