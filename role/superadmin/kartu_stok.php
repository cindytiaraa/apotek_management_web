<?php 
include "../../koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kartu Stok Barang</title>
  <link rel="stylesheet" href="style.css">

</head>

<body>
<?php include "includes/header.php"; ?>
<div class="container">
  <?php include "includes/sidebar.php"; ?>

  <main class="content">
    <div class="page-title-box">
      <h2>📋 Kartu Stok Barang</h2>
      <p>Menampilkan total masuk, keluar, dan stok akhir setiap barang.</p>
    </div>

    <!-- 🔹 Search + Filter -->
    <form method="GET" class="search-filter">
      <input 
        type="text" 
        name="keyword" 
        placeholder="Cari barang..." 
        value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>"
      >
      <select name="jenis">
        <option value="">Semua Transaksi</option>
        <option value="P" <?php if(isset($_GET['jenis']) && $_GET['jenis']=='P') echo 'selected'; ?>>Penerimaan</option>
        <option value="J" <?php if(isset($_GET['jenis']) && $_GET['jenis']=='J') echo 'selected'; ?>>Penjualan</option>
      </select>
      <button type="submit" class="btn-search">Cari</button>
    </form>

    <!-- 🔹 Tabel Kartu Stok -->
    <table class="data-table">
      <thead>
        <tr>
          <th>ID Barang</th>
          <th>Nama Barang</th>
          <th>Satuan</th>
          <th>Stok Akhir</th>
          <th>Riwayat Perubahan</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // 🔸 Ambil filter
        $keyword = $_GET['keyword'] ?? '';
        $jenis = $_GET['jenis'] ?? '';

        // 🔸 Buat kondisi filter
        $where = "WHERE 1=1";
        if (!empty($keyword)) {
          $where .= " AND b.nama LIKE '%$keyword%'";
        }
        if (!empty($jenis)) {
          $where .= " AND ks.jenis_transaksi = '$jenis'";
        }

        // 🔸 Query stok akhir per barang
        $q = mysqli_query($koneksi, "
          SELECT 
            b.idbarang,
            b.nama AS nama_barang,
            s.nama_satuan,
            COALESCE(SUM(ks.masuk - ks.keluar), 0) AS stok_akhir
          FROM barang b
          JOIN satuan s ON b.idsatuan = s.idsatuan
          LEFT JOIN kartu_stok ks ON ks.idbarang = b.idbarang
          $where
          GROUP BY b.idbarang, b.nama, s.nama_satuan
          ORDER BY b.idbarang ASC
        ");

        if (mysqli_num_rows($q) > 0) {
          while ($r = mysqli_fetch_assoc($q)) {
            // 🔸 Ambil riwayat terbaru (5 terakhir)
            $riwayat = mysqli_query($koneksi, "
              SELECT 
                jenis_transaksi, 
                (masuk - keluar) AS perubahan, 
                DATE_FORMAT(created_at, '%d %M %Y') AS tanggal 
              FROM kartu_stok 
              WHERE idbarang = {$r['idbarang']}
              ".(!empty($jenis) ? "AND jenis_transaksi = '$jenis'" : "")."
              ORDER BY created_at DESC 
              LIMIT 5
            ");

            echo "
            <tr>
              <td>{$r['idbarang']}</td>
              <td>{$r['nama_barang']}</td>
              <td>{$r['nama_satuan']}</td>
              <td><b>{$r['stok_akhir']}</b></td>
              <td>
            ";

            if (mysqli_num_rows($riwayat) > 0) {
              echo "<ul style='list-style:none; padding-left:0; margin:0;'>";
              while ($rw = mysqli_fetch_assoc($riwayat)) {
                $sign = ($rw['perubahan'] >= 0) ? '+' : '';
                $color = ($rw['perubahan'] >= 0) ? '#28a745' : '#dc3545';
                $jenisTxt = ($rw['jenis_transaksi'] == 'P') ? 'Penerimaan' : 'Penjualan';
                echo "<li style='color:$color; font-size:13px;'>
                        {$rw['tanggal']} — {$jenisTxt} ({$sign}{$rw['perubahan']})
                      </li>";
              }
              echo "</ul>";
            } else {
              echo "<i style='color:#999;'>Belum ada riwayat</i>";
            }

            echo "</td></tr>";
          }
        } else {
          echo "<tr><td colspan='5' style='text-align:center;'>Tidak ada data stok ditemukan.</td></tr>";
        }
        ?>
      </tbody>
    </table>
    
  </main>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>