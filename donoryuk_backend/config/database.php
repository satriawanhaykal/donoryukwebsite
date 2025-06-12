<?php
// donoryuk_backend/config/database.php

// --- PENTING: BARIS DEBUGGING INI HARUS DIHAPUS SAAT PRODUKSI ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR BARIS DEBUGGING ---

class Database{

    // Kredensial database - PASTIKAN SESUAI DENGAN PENGATURAN LARAGON/XAMPP ANDA
    private $host = "localhost";
    private $db_name = "donor_yuk_db"; // PASTIKAN NAMA DATABASE INI SESUAI
    private $username = "root";       // USERNAME DATABASE ANDA
    private $password = "";           // PASSWORD DATABASE ANDA (kosong jika default Laragon/XAMPP)
    public $conn; // Variabel untuk menyimpan objek koneksi PDO

    // Metode untuk mendapatkan koneksi database
    public function getConnection(){

        $this->conn = null; // Reset koneksi sebelumnya

        try{
            // Buat objek PDO untuk koneksi ke MySQL
            // DSN (Data Source Name): "mysql:host=localhost;dbname=nama_db_anda"
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8"); // Set encoding ke UTF-8
            
            // Set mode error PDO untuk menampilkan exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }catch(PDOException $exception){
            // Tangani error jika koneksi gagal
            error_log("Koneksi database gagal: " . $exception->getMessage()); // Catat error ke log server
            // Jangan gunakan die() dalam API, lebih baik kembalikan respons JSON error
            // Untuk debugging awal, bisa pakai die(), tapi hapus saat produksi
            die("Koneksi database gagal: " . $exception->getMessage());
        }

        return $this->conn; // Kembalikan objek koneksi PDO
    }
}
?>