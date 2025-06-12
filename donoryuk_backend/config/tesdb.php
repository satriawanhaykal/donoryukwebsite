<?php
// donoryuk_backend/api/test_db.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = ""; // GANTI DENGAN PASSWORD ROOT MYSQL ANDA JIKA ADA
$dbname = "donor_yuk_db";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "Koneksi ke database '$dbname' berhasil!";
$conn->close();
?>