<?php 
include "../../koneksi.php";

// --- Ambil margin aktif ---
$margin_q = mysqli_query($koneksi, "SELECT persen FROM margin_penjualan WHERE status=1 LIMIT 1");
$margin_row = mysqli_fetch_assoc($margin_q);
$margin_persen = $margin_row ? $margin_row['persen'] : 0;
$ppn = 10;

// --- Proses simpan penjualan ---
if (isset($_POST['simpan'])) {
  $iduser = 2; // nanti diganti dari session
  $tgl = date('Y-m-d');

  mysqli_query($koneksi, "INSERT INTO penjualan (tgl, iduser) VALUES ('$tgl', '$iduser')");
  $idpenjualan = mysqli_insert_id($koneksi);

  $barang = $_POST['idbarang'];
  $jumlah = $_POST['jumlah'];
  $stok_valid = true;
  $pesan_error = "";

  foreach ($barang as $i => $idbarang) {
    if (!empty($idbarang) && $jumlah[$i] > 0) {
      // Ambil harga dasar & stok dari barang
      $barang_row = mysqli_fetch_assoc(mysqli_query($koneksi, "
        SELECT b.harga, COALESCE(SUM(k.masuk - k.keluar),0) AS stok
        FROM barang b
        LEFT JOIN kartu_stok k ON b.idbarang=k.idbarang
        WHERE b.idbarang=$idbarang
        GROUP BY b.idbarang
      "));
      
      $harga_dasar = $barang_row['harga'];
      $stok_tersedia = $barang_row['stok'];

      if ($stok_tersedia < $jumlah[$i]) {
        $stok_valid = false;
        $pesan_error .= "Barang ID {$idbarang} stok hanya $stok_tersedia. ";
      }
    }
  }

  if (!$stok_valid) {
    mysqli_query($koneksi, "DELETE FROM penjualan WHERE idpenjualan=$idpenjualan");
    echo "<script>alert('Transaksi gagal! $pesan_error');window.location='penjualan.php';</script>";
    exit();
  }

  // --- Jika stok valid, lanjut simpan ---
  foreach ($barang as $i => $idbarang) {
    if (!empty($idbarang) && $jumlah[$i] > 0) {
      $barang_row = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT harga FROM barang WHERE idbarang=$idbarang"));
      $harga_dasar = $barang_row['harga'];
      $harga_jual = $harga_dasar * (1 + $margin_persen / 100) * (1 + $ppn / 100);

      // Simpan detail penjualan
      mysqli_query($koneksi, "
        INSERT INTO detail_penjualan (idpenjualan, idbarang, jumlah, harga)
        VALUES ('$idpenjualan', '$idbarang', '{$jumlah[$i]}', '$harga_jual')
      ");

      // Update stok
      $cek_stok = mysqli_query($koneksi, "SELECT COALESCE(SUM(masuk - keluar), 0) AS stok FROM kartu_stok WHERE idbarang=$idbarang");
      $stok_row = mysqli_fetch_assoc($cek_stok);
      $stok_sekarang = $stok_row['stok'] ?? 0;
      $stok_baru = $stok_sekarang - $jumlah[$i];

      mysqli_query($koneksi, "
        INSERT INTO kartu_stok (jenis_transaksi, keluar, stock, idtransaksi, idbarang)
        VALUES ('J', '{$jumlah[$i]}', '$stok_baru', '$idpenjualan', '$idbarang')
      ");
    }
  }

  echo "<script>alert('Penjualan berhasil disimpan!');window.location='penjualan.php';</script>";
  exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Transaksi Penjualan</title>
  <link rel="stylesheet" href="style.css">

</head>

<body>
<?php include "includes/header.php"; ?>
<div class="container">
  <?php include "includes/sidebar.php"; ?>

  <main class="content">
    <div class="page-title-box">
      <h2>💳 Transaksi Penjualan</h2>
      <p>Mencatat penjualan barang dengan perhitungan margin dan PPN otomatis</p>
    </div>

    <!-- 🔹 Form Penjualan -->
    <form method="POST">
      <div id="barang-list">
        <div class="barang-item">
          <select name="idbarang[]" required onchange="updateSatuan(this)">
            <option value="">-- Pilih Barang --</option>
            <?php
              $barang = mysqli_query($koneksi, "
                SELECT b.idbarang, b.nama, s.nama_satuan
                FROM barang b
                JOIN satuan s ON b.idsatuan=s.idsatuan
                WHERE b.status=1
              ");
              while ($b = mysqli_fetch_assoc($barang)) {
                echo "<option value='{$b['idbarang']}' data-satuan='{$b['nama_satuan']}'>{$b['nama']}</option>";
              }
            ?>
          </select>
          <input type="text" name="satuan[]" placeholder="Satuan" readonly>
          <input type="number" name="jumlah[]" placeholder="Jumlah" min="1" required>
        </div>
      </div>

      <button type="button" class="btn-tambah" onclick="tambahBarang()">+ Tambah Barang</button>
      <br><br>
      <button type="submit" name="simpan" class="btn btn-aktif">💾 Simpan Penjualan</button>
    </form>

    <!-- 🔹 Riwayat Penjualan -->
    <h3 style="margin-top:40px;">Riwayat Penjualan</h3>
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tanggal</th>
          <th>Petugas</th>
          <th>Total</th>
          <th>Detail</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $riwayat = mysqli_query($koneksi, "
          SELECT p.idpenjualan, p.tgl, u.username, SUM(dp.subtotal) AS total
          FROM penjualan p
          JOIN user u ON p.iduser=u.iduser
          JOIN detail_penjualan dp ON dp.idpenjualan=p.idpenjualan
          GROUP BY p.idpenjualan, p.tgl, u.username
          ORDER BY p.idpenjualan DESC
        ");

        if (mysqli_num_rows($riwayat) > 0) {
          while ($r = mysqli_fetch_assoc($riwayat)) {
            echo "
            <tr>
              <td>{$r['idpenjualan']}</td>
              <td>{$r['tgl']}</td>
              <td>{$r['username']}</td>
              <td>Rp ".number_format($r['total'],0,',','.')."</td>
              <td><a href='detail_penjualan.php?id={$r['idpenjualan']}' class='btn btn-edit'>Lihat Detail</a></td>
            </tr>";
          }
        } else {
          echo "<tr><td colspan='5' style='text-align:center;'>Belum ada data penjualan</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </main>
</div>

<script>
function tambahBarang() {
  const list = document.getElementById('barang-list');
  const item = document.querySelector('.barang-item').cloneNode(true);
  item.querySelectorAll('input').forEach(input => input.value = '');
  list.appendChild(item);
}
function updateSatuan(select) {
  const satuan = select.options[select.selectedIndex].getAttribute('data-satuan');
  select.parentNode.querySelector('input[name="satuan[]"]').value = satuan || '';
}
</script>

<?php include "includes/footer.php"; ?>
</body>
</html>