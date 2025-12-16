<?php include "../../koneksi.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Penerimaan Barang - Admin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "includes/header.php"; ?>
<div class="container">
  <?php include "includes/sidebar.php"; ?>

  <main class="content">
    <h3>📦 Data Penerimaan Barang</h3>
    <p>Daftar barang yang telah diterima dari vendor.</p>

    <table class="data-table">
      <thead>
        <tr>
          <th>ID Penerimaan</th>
          <th>Tanggal</th>
          <th>Vendor</th>
          <th>Nama Barang</th>
          <th>Jumlah Terima</th>
          <th>Petugas</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $sql = "SELECT * FROM v_transaksi_penerimaan ORDER BY tgl DESC";
          $result = mysqli_query($koneksi, $sql);
          if(mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)){
              echo "<tr>
                      <td>{$row['idpenerimaan']}</td>
                      <td>{$row['tgl']}</td>
                      <td>{$row['nama_vendor']}</td>
                      <td>{$row['nama_barang']}</td>
                      <td>{$row['jumlah_terima']}</td>
                      <td>{$row['username']}</td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='6' style='text-align:center;'>Belum ada penerimaan barang</td></tr>";
          }
        ?>
      </tbody>
    </table>
  </main>
</div>
<?php include "includes/footer.php"; ?>
</body>
</html>