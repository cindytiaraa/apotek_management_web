<?php
include "../../koneksi.php";
header('Content-Type: application/json');

// Ambil parameter ID (dari id atau idpengadaan)
$id = isset($_GET['idpengadaan']) ? intval($_GET['idpengadaan']) : intval($_GET['id']);
$rows = [];

$q = mysqli_query($koneksi, "
  SELECT 
    dp.idbarang,
    b.nama AS nama_barang,
    s.nama_satuan,
    dp.jumlah,
    dp.harga
  FROM detail_pengadaan dp
  JOIN barang b ON dp.idbarang = b.idbarang
  JOIN satuan s ON b.idsatuan = s.idsatuan
  WHERE dp.idpengadaan = $id
");

while ($r = mysqli_fetch_assoc($q)) {
  $rows[] = $r;
}

echo json_encode($rows);
?>