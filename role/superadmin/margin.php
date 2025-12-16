<?php 
include "../../koneksi.php";

// --- Proses ubah status aktif/nonaktif ---
if (isset($_GET['toggle'])) {
  $idmargin = $_GET['toggle'];
  $status = $_GET['status'];

  if ($status == 1) {
    // Jika mengaktifkan margin baru → nonaktifkan semua margin lain
    mysqli_query($koneksi, "UPDATE margin_penjualan SET status = 0");
  }

  // Update margin yang dipilih
  mysqli_query($koneksi, "UPDATE margin_penjualan SET status = $status WHERE idmargin = $idmargin");

  header("Location: margin.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Margin Penjualan - Superadmin</title>
  <link rel="stylesheet" href="style.css">

</head>

<body>
<?php include "includes/header.php"; ?>

<div class="container">
  <?php include "includes/sidebar.php"; ?>

  <main class="content">
    <div class="page-title-box">
      <h2>💰 Margin Penjualan</h2>
      <p>Atur persentase margin keuntungan penjualan</p>
    </div>

    <!-- 🔹 Search dan Filter -->
    <form method="GET" class="search-bar">
      <input 
        type="text" 
        name="keyword" 
        placeholder="Cari margin atau user..." 
        value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>"
      >
      <select name="status">
        <option value="">Semua</option>
        <option value="1" <?php if(isset($_GET['status']) && $_GET['status']=='1') echo 'selected'; ?>>Aktif</option>
        <option value="0" <?php if(isset($_GET['status']) && $_GET['status']=='0') echo 'selected'; ?>>Nonaktif</option>
      </select>
      <button type="submit" class="btn-search">Cari</button>
    </form>

    <!-- 🔹 Tabel Margin -->
    <table class="data-table">
      <thead>
        <tr>
          <th>ID Margin</th>
          <th>Persentase</th>
          <th>Dibuat Oleh</th>
          <th>Tanggal</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $keyword = $_GET['keyword'] ?? '';
          $status = $_GET['status'] ?? '';

          $query = "
            SELECT 
              m.idmargin,
              m.persen,
              m.created_at,
              u.username,
              CASE 
                WHEN m.status = 1 THEN 'Aktif'
                ELSE 'Nonaktif'
              END AS status
            FROM margin_penjualan m
            LEFT JOIN user u ON m.iduser = u.iduser
            WHERE 1=1
          ";

          if (!empty($keyword)) {
            $query .= " AND (m.persen LIKE '%$keyword%' OR u.username LIKE '%$keyword%')";
          }
          if ($status !== '') {
            $query .= " AND m.status = $status";
          }

          $query .= " ORDER BY m.created_at DESC";
          $result = mysqli_query($koneksi, $query);

          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              $statusText = $row['status'];
              $btnLabel = $statusText == 'Aktif' ? 'Nonaktifkan' : 'Aktifkan';
              $btnColor = $statusText == 'Aktif' ? 'btn-nonaktif' : 'btn-aktif';
              $newStatus = $statusText == 'Aktif' ? 0 : 1;

              echo "
              <tr>
                <td>{$row['idmargin']}</td>
                <td>{$row['persen']}%</td>
                <td>{$row['username']}</td>
                <td>{$row['created_at']}</td>
                <td><span class='status ".($statusText=='Aktif'?'aktif':'nonaktif')."'>{$statusText}</span></td>
                <td>
                  <a href='margin.php?toggle={$row['idmargin']}&status=$newStatus' class='btn $btnColor'>$btnLabel</a>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='6' style='text-align:center;'>Tidak ada data margin</td></tr>";
          }
        ?>
      </tbody>
    </table>
  </main>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>