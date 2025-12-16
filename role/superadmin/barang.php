<?php 
include "../../koneksi.php";

// --- Proses ubah status (aktif/nonaktif) ---
if (isset($_GET['toggle'])) {
  $idbarang = $_GET['toggle'];
  $status = $_GET['status'];

  $query = "UPDATE barang SET status = $status WHERE idbarang = $idbarang";
  mysqli_query($koneksi, $query);

  header("Location: barang.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Barang - Superadmin</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "includes/header.php"; ?>

<div class="container">
  <?php include "includes/sidebar.php"; ?>

  <main class="content">

    <div class="page-title-box">
      <h2>📦 Data Barang</h2>
      <p>Menampilkan daftar seluruh barang di sistem apotek</p>
    </div>

    <!-- 🔹 Search + Filter -->
    <form method="GET" class="search-form" 
          style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      
      <input 
        type="text" 
        name="keyword" 
        placeholder="Cari barang..." 
        value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>" 
        style="padding:8px; border:1px solid #7cc6f0; border-radius:20px; width:220px;"
      >

      <select name="status" onchange="this.form.submit()" 
              style="padding:8px; border-radius:20px; border:1px solid #7cc6f0; color:#333;">
        <option value="">Semua</option>
        <option value="1" <?php if(isset($_GET['status']) && $_GET['status']=='1') echo 'selected'; ?>>Aktif</option>
        <option value="0" <?php if(isset($_GET['status']) && $_GET['status']=='0') echo 'selected'; ?>>Nonaktif</option>
      </select>
    </form>


    <!-- 🔹 Tabel Data Barang -->
    <table class="data-table">
      <thead>
        <tr>
          <th>ID Barang</th>
          <th>Nama Barang</th>
          <th>Satuan</th>
          <th>Harga (Rp)</th>
          <th>Stok Tersedia</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $keyword = isset($_GET['keyword']) ?? '';
          $filter_status = $_GET['filter_status'] ?? '';

          $query = "SELECT * FROM v_barang_lengkap WHERE 1=1";
          if (!empty($keyword)) {
            $query .= " AND nama_barang LIKE '%$keyword%'";
          }
          if (!empty($filter_status)) {
            $query .= " AND status = '$filter_status'";
          }

          $query .= " ORDER BY idbarang ASC";
          $result = mysqli_query($koneksi, $query);

          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              $status = $row['status_barang'];
              $btnLabel = $status == 'Aktif' ? 'Nonaktifkan' : 'Aktifkan';
              $btnColor = $status == 'Aktif' ? 'btn-nonaktif' : 'btn-aktif';
              $newStatus = $status == 'Aktif' ? 0 : 1;

              echo "
              <tr>
                <td>{$row['idbarang']}</td>
                <td>{$row['nama_barang']}</td>
                <td>{$row['nama_satuan']}</td>
                <td>Rp ".number_format($row['harga'], 0, ',', '.')."</td>
                <td>{$row['stok_tersedia']}</td>
                <td><span class='status ".($status=='Aktif'?'aktif':'nonaktif')."'>{$status}</span></td>
                <td>
                  <a href='barang.php?toggle={$row['idbarang']}&status=$newStatus' class='btn $btnColor'>$btnLabel</a>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='7' style='text-align:center;'>Tidak ada data barang</td></tr>";
          }
        ?>
      </tbody>
    </table>
  </main>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>