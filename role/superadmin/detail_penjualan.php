<?php
include "../../koneksi.php";
include "includes/sidebar.php";

$id = intval($_GET['id']);

$q = mysqli_query($koneksi, "
  SELECT b.nama AS nama_barang, s.nama_satuan, dp.jumlah, dp.harga, dp.subtotal
  FROM detail_penjualan dp
  JOIN barang b ON dp.idbarang=b.idbarang
  JOIN satuan s ON b.idsatuan=s.idsatuan
  WHERE dp.idpenjualan=$id
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Detail Penjualan #<?php echo $id; ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    .btn-back {
      display: inline-block;
      background: #007bff;
      color: #fff;
      padding: 8px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      margin-bottom: 15px;
    }
    .btn-back:hover {
      background: #0056b3;
    }
  </style>
</head>

<body>
  <div class="content">
    <div class="page-title-box">
      <h2>🧾 Detail Penjualan #<?php echo $id; ?></h2>
    </div>

    <a href="penjualan.php" class="btn-back"> Back </a>

    <table class="data-table">
      <thead>
        <tr>
          <th>Barang</th>
          <th>Satuan</th>
          <th>Jumlah</th>
          <th>Harga</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if (mysqli_num_rows($q) > 0) {
          while ($r = mysqli_fetch_assoc($q)) {
            echo "
            <tr>
              <td>{$r['nama_barang']}</td>
              <td>{$r['nama_satuan']}</td>
              <td>{$r['jumlah']}</td>
              <td>Rp " . number_format($r['harga'], 0, ',', '.') . "</td>
              <td>Rp " . number_format($r['subtotal'], 0, ',', '.') . "</td>
            </tr>";
          }
        } else {
          echo "<tr><td colspan='5' style='text-align:center;'>Tidak ada detail penjualan.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</body>
</html>