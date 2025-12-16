<?php
require_once "database.php";

class Pengadaan extends Database {

    public function getAll() {
        $sql = "SELECT p.idpengadaan, v.nama_vendor, u.username, p.tgl
                FROM pengadaan p
                JOIN vendor v ON p.idvendor = v.idvendor
                JOIN user u ON p.iduser = u.iduser";
        $result = $this->conn->query($sql);
        return $result;
    }
}
?>
