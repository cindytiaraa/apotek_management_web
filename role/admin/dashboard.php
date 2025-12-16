<?php

session_start();
if (!isset($_SESSION['idrole']) || $_SESSION['idrole'] != 2) {
  header("Location: ../login.php");
  exit;
}

include "../../koneksi.php";
include "includes/header.php";
date_default_timezone_set('Asia/Jakarta');

// --- Simpan Transaksi Penjualan ---
if (isset($_POST['simpan_penjualan'])) {
  $iduser = 2; // sementara, nanti ambil dari session login
  $tgl = date('Y-m-d');

  // buat transaksi penjualan
  mysqli_query($koneksi, "INSERT INTO penjualan (tgl, iduser, total) VALUES ('$tgl', '$iduser', 0)");
  $idpenjualan = mysqli_insert_id($koneksi);

  $total = 0;
  foreach ($_POST['barang'] as $i => $idbarang) {
    $jumlah = intval($_POST['jumlah'][$i]);
    if ($jumlah <= 0) continue;

    // ambil harga barang dari DB
    $qbarang = mysqli_query($koneksi, "SELECT harga FROM barang WHERE idbarang=$idbarang");
    $harga = mysqli_fetch_assoc($qbarang)['harga'];

    // ambil margin aktif
    $margin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT persen FROM margin_penjualan WHERE status=1 LIMIT 1"))['persen'];
    $ppn = 10;

    // hitung harga jual per barang
    $harga_jual = $harga * (1 + $margin/100) * (1 + $ppn/100);
    $subtotal = $harga_jual * $jumlah;

    $total += $subtotal;

    // simpan ke detail_penjualan
    mysqli_query($koneksi, "
      INSERT INTO detail_penjualan (idpenjualan, idbarang, jumlah, harga)
      VALUES ($idpenjualan, $idbarang, $jumlah, $harga_jual)
    ");
  }

  // update total transaksi
  mysqli_query($koneksi, "UPDATE penjualan SET total = $total WHERE idpenjualan = $idpenjualan");

  echo "<script>alert('Transaksi penjualan berhasil disimpan!');window.location='dashboard.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Penjualan - Admin Apotek Kasih Ibu</title>
  <span> Halo, <?= $_SESSION['username'] ?> (<? $_SESSION['idrole']==1?'Superadmin':'Admin'?>) </span>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-box {
      background: #fff;
      border: 1px solid #b8e2f2;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      margin-bottom: 30px;
    }
    .form-box h3 { color: #007bff; margin-bottom: 15px; }
    .form-grid { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .form-grid select, .form-grid input {
      padding: 6px;
      border-radius: 6px;
      border: 1px solid #b8e2f2;
    }
    .btn-add {
      background: #36a2ff; color: white; border: none;
      border-radius: 6px; padding: 6px 12px; cursor: pointer;
    }
    .btn-add:hover { background: #007bff; }

    .btn-simpan {
      background: #007bff;
      color: #fff;
      border: none;
      border-radius: 20px;
      padding: 8px 18px;
      font-weight: bold;
      cursor: pointer;
    }
    .btn-simpan:hover { background: #0056b3; }

    table { border-collapse: collapse; width: 100%; background: #fff; }
    th, td { border: 1px solid #d8ecf5; padding: 8px; text-align: left; }
    th { background: #f0f8ff; color: #007bff; }
  </style>
</head>

<body>
<main class="content">
  <div class="page-title-box">
    <h2>💳 Transaksi Penjualan</h2>
    <p>Halaman utama kasir untuk mencatat transaksi penjualan harian.</p>
  </div>

  <!-- 🔹 Form Transaksi Penjualan -->
  <div class="form-box">
    <h3>🧾 Tambah Transaksi Baru</h3>
    <form method="POST">
      <div id="barang-container">
        <div class="form-grid">
          <select name="barang[]" required>
            <option value="">-- Pilih Barang --</option>
            <?php
              $barang = mysqli_query($koneksi, "SELECT idbarang, nama FROM barang WHERE status=1 ORDER BY nama ASC");
              while ($b = mysqli_fetch_assoc($barang)) {
                echo "<option value='{$b['idbarang']}'>{$b['nama']}</option>";
              }
            ?>
          </select>
          <input type="number" name="jumlah[]" placeholder="Jumlah" min="1" required>
          <button type="button" class="btn-add" onclick="tambahBarang()">+</button>
        </div>
      </div>
      <div style="text-align:center; margin-top:15px;">
        <button type="submit" name="simpan_penjualan" class="btn-simpan">💾 Simpan Transaksi</button>
      </div>
    </form>
  </div>

  <!-- 🔹 Riwayat Penjualan Hari Ini -->
  <h3 style="color:#007bff;">📅 Riwayat Penjualan Hari Ini (<?= date('d M Y') ?>)</h3>
  <table>
    <thead>
      <tr>
        <th>ID Penjualan</th>
        <th>Waktu</th>
        <th>Kasir</th>
        <th>Total (Rp)</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $hari_ini = date('Y-m-d');
      $riwayat = mysqli_query($koneksi, "
        SELECT p.idpenjualan, p.tgl, u.username, p.total 
        FROM penjualan p
        JOIN user u ON p.iduser = u.iduser
        WHERE p.tgl = '$hari_ini'
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
          </tr>";
        }
      } else {
        echo "<tr><td colspan='4' style='text-align:center;'>Belum ada transaksi hari ini</td></tr>";
      }
      ?>
    </tbody>
  </table>
</main>

<script>
function tambahBarang() {
  const container = document.getElementById('barang-container');
  const div = document.createElement('div');
  div.classList.add('form-grid');
  div.innerHTML = `
    <select name="barang[]" required>
      <option value="">-- Pilih Barang --</option>
      <?php
        $barang = mysqli_query($koneksi, "SELECT idbarang, nama FROM barang WHERE status=1 ORDER BY nama ASC");
        while ($b = mysqli_fetch_assoc($barang)) {
          echo "<option value='{$b['idbarang']}'>{$b['nama']}</option>";
        }
      ?>
    </select>
    <input type="number" name="jumlah[]" placeholder="Jumlah" min="1" required>
    <button type="button" class="btn-add" onclick="this.parentElement.remove()">−</button>
  `;
  container.appendChild(div);
}
</script>

<?php include "includes/footer.php"; ?>
</body>
</html>