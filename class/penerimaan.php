<?php
require_once "database.php";

class Penerimaan extends Database {

    public function getAll() {
        $sql = "SELECT * FROM v_transaksi_penerimaan";
        $result = $this->conn->query($sql);
        return $result;
    }
}
?>
