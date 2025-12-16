<?php
require_once "database.php";

class Penjualan extends Database {

    public function getAll() {
        $sql = "SELECT * FROM v_transaksi_penjualan";
        $result = $this->conn->query($sql);
        return $result;
    }
}
?>

