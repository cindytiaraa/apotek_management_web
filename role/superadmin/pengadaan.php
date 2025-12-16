<?php 
include "../../koneksi.php";

// --- Simpan Pengadaan ---
if (isset($_POST['simpan'])) {
  $idvendor = $_POST['idvendor'];
  $iduser = 1; // nanti diambil dari session
  $tgl = date('Y-m-d');

  // simpan pengadaan
  $query_pengadaan = "INSERT INTO pengadaan (tgl, idvendor, iduser, status) 
                      VALUES ('$tgl', '$idvendor', '$iduser', 'proses')";
  mysqli_query($koneksi, $query_pengadaan);
  $idpengadaan = mysqli_insert_id($koneksi);

  // detail barang
  $barang = $_POST['idbarang'];
  $jumlah = $_POST['jumlah'];
  $harga = $_POST['harga'];

  for ($i = 0; $i < count($barang); $i++) {
  $idBarang = intval($barang[$i]);
  $jumlahBarang = intval($jumlah[$i]);

    // kalau harga kosong, ambil dari tabel barang
    if (empty($harga[$i]) || $harga[$i] == 0) {
      $ambilHarga = mysqli_query($koneksi, 
        "SELECT harga FROM barang WHERE idbarang = $idBarang");
      $hargaRow = mysqli_fetch_assoc($ambilHarga);
      $hargaBarang = $hargaRow ? $hargaRow['harga'] : 0;
    } else {
      $hargaBarang = floatval($harga[$i]);
    }

    $query_detail = "
      INSERT INTO detail_pengadaan (idpengadaan, idbarang, jumlah, harga, ppn)
      VALUES ('$idpengadaan', '$idBarang', '$jumlahBarang', '$hargaBarang', 10.00)
    ";
    mysqli_query($koneksi, $query_detail);
  }

  echo "<script>alert('Data pengadaan berhasil disimpan!');window.location='pengadaan.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transaksi Pengadaan</title>
  <link rel="stylesheet" href="style.css">
</head>

<script>
function showDetail(idpengadaan) {
  fetch("get_detail_pengadaan.php?idpengadaan=" + idpengadaan)
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#detailTable tbody");
      tbody.innerHTML = "";
      if (data.length === 0) {
        tbody.innerHTML = "<tr><td colspan='5'>Tidak ada detail pengadaan</td></tr>";
      } else {
        data.forEach(row => {
          tbody.innerHTML += `
            <tr>
              <td>${row.nama_barang}</td>
              <td>${row.jumlah}</td>
              <td>Rp ${parseInt(row.harga).toLocaleString('id-ID')}</td>
              <td>${row.ppn}%</td>
              <td>Rp ${parseInt(row.subtotal).toLocaleString('id-ID')}</td>
            </tr>`;
        });
      }
      document.getElementById("modalDetail").style.display = "block";
    });
}

function closeModal() {
  document.getElementById("modalDetail").style.display = "none";
}
</script>

<body>
<?php include "includes/header.php"; ?>
<div class="container">
  <?php include "includes/sidebar.php"; ?>

  <main class="content">
    <div class="page-title-box">
      <h2>🧾 Form Pengadaan Barang</h2>
      <p>Tambah data pengadaan barang berdasarkan vendor dan stok</p>
    </div>

    <form method="POST">
      <div class="form-group">
        <label for="idvendor">Pilih Vendor:</label>
        <select name="idvendor" required>
          <option value="">-- Pilih Vendor --</option>
          <?php
            $vendor = mysqli_query($koneksi, "SELECT * FROM vendor WHERE status=1");
            while ($v = mysqli_fetch_assoc($vendor)) {
              echo "<option value='{$v['idvendor']}'>{$v['nama_vendor']}</option>";
            }
          ?>
        </select>
      </div>

      <div id="barang-list">
        <div class="barang-item">
          <label>Barang:</label>
          <select name="idbarang[]" class="select-barang" required>
            <option value="">-- Pilih Barang --</option>
            <?php
              $barang = mysqli_query($koneksi, "SELECT idbarang, nama FROM barang WHERE status=1");
              while ($b = mysqli_fetch_assoc($barang)) {
                echo "<option value='{$b['idbarang']}'>{$b['nama']}</option>";
              }
            ?>
          </select>

          <label>Jumlah:</label>
          <input type="number" name="jumlah[]" min="1" required>

          <label>Harga (Rp):</label>
          <input type="number" name="harga[]" class="harga-input" min="0" step="0.01" required readonly>
        </div>
      </div>

      <button type="button" class="btn btn-tambah" onclick="tambahBarang()">+ Tambah Barang</button>
      <br><br>
      <button type="submit" name="simpan" class="btn btn-simpan">💾 Simpan Pengadaan</button>
    </form>

    <br>
    <hr>
        <h3>📋 Data Pengadaan Terbaru</h3>
    <table class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tanggal</th>
          <th>Vendor</th>
          <th>Total</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $result = mysqli_query($koneksi, "
            SELECT p.idpengadaan, p.tgl, v.nama_vendor, 
            IFNULL(SUM(dp.subtotal), 0) AS total, p.status 
            FROM pengadaan p
            JOIN vendor v ON v.idvendor = p.idvendor
            LEFT JOIN detail_pengadaan dp ON dp.idpengadaan = p.idpengadaan
            GROUP BY p.idpengadaan, v.nama_vendor, p.tgl, p.status
            ORDER BY p.idpengadaan DESC LIMIT 10
          ");
          while ($r = mysqli_fetch_assoc($result)) {
            echo "
              <tr>
                <td>{$r['idpengadaan']}</td>
                <td>{$r['tgl']}</td>
                <td>{$r['nama_vendor']}</td>
                <td>Rp ".number_format($r['total'],0,',','.')."</td>
                <td>{$r['status']}</td>
                <td><button class='btn btn-info' onclick='showDetail({$r['idpengadaan']})'>Detail</button></td>
              </tr>
            ";
          }
        ?>
      </tbody>
    </table>

      <!-- Modal Detail Pengadaan -->
      <div id="modalDetail" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="close-btn" onclick="closeModal()">&times;</span>
          <h3>🧾 Detail Pengadaan</h3>
          <table class="data-table" id="detailTable">
            <thead>
              <tr>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>PPN (%)</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
  </main>
</div>

<?php include "includes/footer.php"; ?>

<script>
// duplikasi form barang
function tambahBarang() {
  const list = document.getElementById('barang-list');
  const item = document.querySelector('.barang-item').cloneNode(true);
  item.querySelectorAll('input').forEach(input => input.value = '');
  list.appendChild(item);
}

// ambil harga otomatis dari DB
document.addEventListener("change", function(e){
  if (e.target.classList.contains("select-barang")) {
    const select = e.target;
    const hargaInput = select.closest(".barang-item").querySelector(".harga-input");
    const idbarang = select.value;

    if (idbarang) {
      fetch("get_harga.php?idbarang=" + idbarang)
        .then(response => response.json())
        .then(data => {
          hargaInput.value = data.harga;
        });
    } else {
      hargaInput.value = '';
    }
  }
});
</script>
</body>
</html>