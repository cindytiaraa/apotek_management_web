<?php 
include "../../koneksi.php";

// --- PROSES AKSI (TERIMA / TOLAK) ---
if (isset($_POST['aksi'])) {
  $idpengadaan = intval($_POST['idpengadaan']);
  $iduser = 1; // nanti diganti dari session login
  $tgl = date('Y-m-d');
  $aksi = $_POST['aksi'];
  $alasan = mysqli_real_escape_string($koneksi, $_POST['alasan'] ?? '');
  $total = 0;

  if ($aksi == 'terima') {
    // Hitung total dari detail pengadaan
    $qtotal = mysqli_query($koneksi, "SELECT SUM(subtotal) AS total FROM detail_pengadaan WHERE idpengadaan=$idpengadaan");
    $total = mysqli_fetch_assoc($qtotal)['total'] ?? 0;

    // Simpan ke tabel penerimaan
    mysqli_query($koneksi, "
      INSERT INTO penerimaan (tgl, idpengadaan, iduser, total_terima, status)
      VALUES ('$tgl', '$idpengadaan', '$iduser', '$total', 'Diterima')
    ");
    $idpenerimaan = mysqli_insert_id($koneksi);

    // Simpan detail barang
    mysqli_query($koneksi, "
      INSERT INTO detail_penerimaan (idpenerimaan, idbarang, jumlah_terima, harga_terima)
      SELECT $idpenerimaan, idbarang, jumlah, harga
      FROM detail_pengadaan WHERE idpengadaan=$idpengadaan
    ");

    // Update status pengadaan
    mysqli_query($koneksi, "UPDATE pengadaan SET status='selesai' WHERE idpengadaan=$idpengadaan");

  } elseif ($aksi == 'tolak') {
    // Hitung total barang diterima (dari input)
    $total = 0;
    foreach ($_POST['idbarang'] as $i => $idbarang) {
      $jumlah = intval($_POST['jumlah'][$i]);
      $harga  = floatval($_POST['harga'][$i]);
      $total += $jumlah * $harga;
    }

    // Simpan ke penerimaan
    mysqli_query($koneksi, "
      INSERT INTO penerimaan (tgl, idpengadaan, iduser, total_terima, status, alasan)
      VALUES ('$tgl', '$idpengadaan', '$iduser', '$total', 'Ditolak', '$alasan')
    ");
    $idpenerimaan = mysqli_insert_id($koneksi);

    // Simpan detail barang yang diterima sebagian
    foreach ($_POST['idbarang'] as $i => $idbarang) {
      $jumlah = intval($_POST['jumlah'][$i]);
      $harga  = floatval($_POST['harga'][$i]);
      mysqli_query($koneksi, "
        INSERT INTO detail_penerimaan (idpenerimaan, idbarang, jumlah_terima, harga_terima)
        VALUES ($idpenerimaan, $idbarang, $jumlah, $harga)
      ");
    }
  }

  echo "<script>alert('Aksi penerimaan berhasil disimpan!');window.location='penerimaan.php';</script>";
  exit;
}

// --- HAPUS RIWAYAT ---
if (isset($_GET['hapus'])) {
  $id = intval($_GET['hapus']);
  mysqli_query($koneksi, "DELETE FROM penerimaan WHERE idpenerimaan = $id");
  echo "<script>alert('Riwayat penerimaan dihapus!');window.location='penerimaan.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Penerimaan Barang</title>
  <link rel="stylesheet" href="style.css">
  
</head>

<body>
<?php include "includes/header.php"; ?>
<div class="container">
  <?php include "includes/sidebar.php"; ?>
  <main class="content">
    <div class="page-title-box">
      <h2>📥 Penerimaan Barang</h2>
      <p>Meninjau, menyetujui, atau menolak hasil pengadaan</p>
    </div>

    <!-- 🔹 DAFTAR PENGADAAN -->
    <h3>Daftar Pengadaan Menunggu Penerimaan</h3>
    <table class="data-table">
      <thead><tr>
        <th>ID</th><th>Tanggal</th><th>Vendor</th><th>Total</th><th>Status</th><th>Aksi</th>
      </tr></thead>
      <tbody>
      <?php
      $result = mysqli_query($koneksi, "
        SELECT p.idpengadaan, p.tgl, v.nama_vendor, IFNULL(SUM(dp.subtotal),0) AS total, p.status
        FROM pengadaan p
        JOIN vendor v ON p.idvendor=v.idvendor
        LEFT JOIN detail_pengadaan dp ON dp.idpengadaan=p.idpengadaan
        WHERE p.status='proses'
        GROUP BY p.idpengadaan, p.tgl, v.nama_vendor, p.status
        ORDER BY p.idpengadaan DESC
      ");

      if (mysqli_num_rows($result) > 0) {
        while ($r = mysqli_fetch_assoc($result)) {
          echo "
          <tr>
            <td>{$r['idpengadaan']}</td>
            <td>{$r['tgl']}</td>
            <td>{$r['nama_vendor']}</td>
            <td>Rp ".number_format($r['total'],0,',','.')."</td>
            <td><span class='status nonaktif'>{$r['status']}</span></td>
            <td><button class='btn-detail' onclick='toggleDetail({$r['idpengadaan']})'>Detail</button></td>
          </tr>";

          $detail = mysqli_query($koneksi, "
            SELECT b.idbarang, b.nama AS nama_barang, dp.jumlah, dp.harga, dp.ppn, dp.subtotal
            FROM detail_pengadaan dp
            JOIN barang b ON dp.idbarang=b.idbarang
            WHERE dp.idpengadaan={$r['idpengadaan']}
          ");

          echo "
          <tr id='detail-{$r['idpengadaan']}' class='detail-row' style='display:none;'>
            <td colspan='6'>
              <table class='detail-table'>
                <thead><tr>
                  <th>Nama Barang</th><th>Jumlah</th><th>Harga</th><th>PPN</th><th>Subtotal</th>
                </tr></thead><tbody>";
                  while ($d = mysqli_fetch_assoc($detail)) {
                    echo "<tr>
                      <td>{$d['nama_barang']}</td>
                      <td>{$d['jumlah']}</td>
                      <td>Rp ".number_format($d['harga'],0,',','.')."</td>
                      <td>{$d['ppn']}%</td>
                      <td>Rp ".number_format($d['subtotal'],0,',','.')."</td>
                    </tr>";
                  }
          echo "</tbody></table>
              <form method='POST' class='form-aksi'>
                <input type='hidden' name='idpengadaan' value='{$r['idpengadaan']}'>
                <button type='submit' name='aksi' value='terima' class='btn btn-aktif'>Terima</button>
                <button type='button' class='btn btn-nonaktif' onclick='openRejectModal({$r['idpengadaan']})'>Tolak</button>
              </form>
            </td>
          </tr>";
        }
      } else {
        echo "<tr><td colspan='6' style='text-align:center;'>Tidak ada pengadaan menunggu penerimaan</td></tr>";
      }
      ?>
      </tbody>
    </table>

    <!-- 🔹 RIWAYAT -->
    <h3 style="margin-top:40px;">Riwayat Penerimaan</h3>
    <table class="data-table">
      <thead><tr>
        <th>ID</th><th>Tanggal</th><th>Vendor</th><th>Total</th><th>Status</th><th>Alasan</th><th>Aksi</th>
      </tr></thead>
      <tbody>
      <?php
      $riwayat = mysqli_query($koneksi, "
        SELECT pn.idpenerimaan, pn.tgl, v.nama_vendor, pn.total_terima, pn.status, pn.alasan
        FROM penerimaan pn
        JOIN pengadaan p ON pn.idpengadaan=p.idpengadaan
        JOIN vendor v ON p.idvendor=v.idvendor
        ORDER BY pn.idpenerimaan DESC
      ");
      if (mysqli_num_rows($riwayat) > 0) {
        while ($r = mysqli_fetch_assoc($riwayat)) {
          $color = ($r['status']=='Diterima') ? 'aktif' : 'nonaktif';
          echo "
          <tr>
            <td>{$r['idpenerimaan']}</td>
            <td>{$r['tgl']}</td>
            <td>{$r['nama_vendor']}</td>
            <td>Rp ".number_format($r['total_terima'],0,',','.')."</td>
            <td><span class='status $color'>{$r['status']}</span></td>
            <td>".($r['alasan'] ?: '-')."</td>
            <td><button class='btn-detail' onclick='toggleDetail(\"r{$r['idpenerimaan']}\")'>Detail</button></td>
          </tr>";

          $detailr = mysqli_query($koneksi, "
            SELECT b.nama AS nama_barang, dpn.jumlah_terima, dpn.harga_terima, dpn.subtotal
            FROM detail_penerimaan dpn
            JOIN barang b ON dpn.idbarang=b.idbarang
            WHERE dpn.idpenerimaan={$r['idpenerimaan']}
          ");

          echo "
          <tr id='detail-r{$r['idpenerimaan']}' class='detail-row' style='display:none;'>
            <td colspan='7'>
              <table class='detail-table'>
                <thead><tr>
                  <th>Nama Barang</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th>
                </tr></thead><tbody>";
                  while ($dr = mysqli_fetch_assoc($detailr)) {
                    echo "<tr>
                      <td>{$dr['nama_barang']}</td>
                      <td>{$dr['jumlah_terima']}</td>
                      <td>Rp ".number_format($dr['harga_terima'],0,',','.')."</td>
                      <td>Rp ".number_format($dr['subtotal'],0,',','.')."</td>
                    </tr>";
                  }
          echo "</tbody></table>
              <div style='text-align:center; margin-top:10px;'>
                <a href='penerimaan.php?hapus={$r['idpenerimaan']}' class='btn btn-delete' onclick=\"return confirm('Hapus riwayat ini?')\">Hapus</a>
              </div>
            </td>
          </tr>";
        }
      } else {
        echo "<tr><td colspan='7' style='text-align:center;'>Belum ada riwayat penerimaan</td></tr>";
      }
      ?>
      </tbody>
    </table>
  </main>
</div>

<!-- PENOLAKAN -->
<div id="rejectModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeRejectModal()">&times;</span>
    <h3>❌ Tolak Pengadaan</h3>
    <form method="POST" id="rejectForm">
      <input type="hidden" name="idpengadaan" id="reject_id">
      <table>
        <thead><tr><th>Barang</th><th>Jumlah Diterima</th><th>Harga</th></tr></thead>
        <tbody id="rejectItems"></tbody>
      </table>
      <textarea name="alasan" placeholder="Tuliskan alasan penolakan..." required></textarea>
      <button type="submit" name="aksi" value="tolak" class="btn btn-nonaktif" style="margin-top:10px;">Kirim Penolakan</button>
    </form>
  </div>
</div>

<script>
function toggleDetail(id) {
  const row = document.getElementById('detail-' + id);
  row.style.display = (row && row.style.display === 'none') ? 'table-row' : 'none';
}
function openRejectModal(id) {
  document.getElementById('rejectModal').style.display = 'flex';
  document.getElementById('reject_id').value = id;

  // Fetch detail barang
  fetch('get_detail_pengadaan.php?id=' + id)
    .then(res => res.json())
    .then(data => {
      let tbody = document.getElementById('rejectItems');
      tbody.innerHTML = '';
      data.forEach(item => {
        tbody.innerHTML += `
          <tr>
            <td>
              ${item.nama_barang}
              <input type="hidden" name="idbarang[]" value="${item.idbarang}">
              <input type="hidden" name="harga[]" value="${item.harga}">
            </td>
            <td><input type="number" name="jumlah[]" value="${item.jumlah}" min="0" max="${item.jumlah}" required></td>
            <td>Rp ${Number(item.harga).toLocaleString()}</td>
          </tr>`;
      });
    });
}
function closeRejectModal() {
  document.getElementById('rejectModal').style.display = 'none';
}
window.onclick = function(e) {
  const modal = document.getElementById('rejectModal');
  if (e.target === modal) modal.style.display = 'none';
}
</script>

<?php include "includes/footer.php"; ?>
</body>
</html>