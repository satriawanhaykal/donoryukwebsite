<?php
class Database {
    private $host = "localhost";
    private $db_name = "donor424_donor_yuk_db";
    private $username = "donor424_root";
    private $password = "qJpENkesa^+8B,y("; // Pastikan password ini benar dan aman
    public $conn;

    // Mendapatkan koneksi database
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            // Set error mode ke exception untuk penanganan error yang lebih baik
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Dalam lingkungan produksi, sebaiknya log error ini daripada menampilkannya langsung
            // error_log("Connection error: " . $exception->getMessage(), 0);
            die("Koneksi database gagal: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>