<?php 
include "../../koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Vendor</title>
  <link rel="stylesheet" href="style.css">
    
</head>

<body>
<?php include "includes/header.php"; ?>
<div class="container">
  <?php include "includes/sidebar.php"; ?>

  <main class="content">
    <div class="page-title-box">
      <h2>🏭 Data Vendor</h2>
      <p>Daftar vendor penyedia barang beserta status keaktifannya.</p>
    </div>

    <!-- 🔹 Search dan Filter (1 baris) -->
    <form method="GET" class="search-filter">
      <input 
        type="text" 
        name="keyword" 
        placeholder="Cari vendor..." 
        value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>"
      >
      <select name="status">
        <option value="">Semua Status</option>
        <option value="1" <?php if(isset($_GET['status']) && $_GET['status']=='1') echo 'selected'; ?>>Aktif</option>
        <option value="0" <?php if(isset($_GET['status']) && $_GET['status']=='0') echo 'selected'; ?>>Nonaktif</option>
      </select>
      <select name="badan_hukum">
        <option value="">Semua Jenis</option>
        <option value="Y" <?php if(isset($_GET['badan_hukum']) && $_GET['badan_hukum']=='Y') echo 'selected'; ?>>Badan Hukum</option>
        <option value="N" <?php if(isset($_GET['badan_hukum']) && $_GET['badan_hukum']=='N') echo 'selected'; ?>>Perorangan</option>
      </select>
      <button type="submit" class="btn-search">Cari</button>
    </form>

    <!-- 🔹 Tabel Data Vendor -->
    <table class="data-table">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Vendor</th>
          <th>Badan Hukum</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $where = "WHERE 1=1";
        $keyword = $_GET['keyword'] ?? '';
        $status = $_GET['status'] ?? '';
        $badan = $_GET['badan_hukum'] ?? '';

        if (!empty($keyword)) $where .= " AND nama_vendor LIKE '%$keyword%'";
        if ($status !== '') $where .= " AND status = '$status'";
        if (!empty($badan)) $where .= " AND badan_hukum = '$badan'";

        $q = mysqli_query($koneksi, "
          SELECT idvendor, nama_vendor, badan_hukum, status 
          FROM vendor
          $where
          ORDER BY nama_vendor ASC
        ");

        if (mysqli_num_rows($q) > 0) {
          $no = 1;
          while ($r = mysqli_fetch_assoc($q)) {
            $badanTxt = ($r['badan_hukum'] == 'Y') ? 'Badan Hukum' : 'Perorangan';
            $statusTxt = ($r['status'] == 1) ? 'Aktif' : 'Nonaktif';
            $statusClass = ($r['status'] == 1) ? 'aktif' : 'nonaktif';

            echo "
            <tr>
              <td>$no</td>
              <td>{$r['nama_vendor']}</td>
              <td>$badanTxt</td>
              <td><span class='status $statusClass'>$statusTxt</span></td>
              <td>
                <a href='edit_vendor.php?id={$r['idvendor']}' class='btn btn-edit'>Edit</a>
                <a href='hapus_vendor.php?id={$r['idvendor']}' onclick=\"return confirm('Hapus vendor ini?')\" class='btn btn-delete'>Hapus</a>
              </td>
            </tr>";
            $no++;
          }
        } else {
          echo "<tr><td colspan='5' style='text-align:center;'>Tidak ada vendor ditemukan</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </main>
</div>

<?php include "includes/footer.php"; ?>
</body>
</html>