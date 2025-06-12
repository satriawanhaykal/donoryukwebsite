<?php
// Include file koneksi
include_once "donoryuk_backend/config/database.php";

// Jalankan koneksi
$conn = mysqli_connect("localhost", "donor424_root", "zq.A8QvR]%Qu_P5=", "donor424_donor_yuk_db");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

echo "Koneksi ke database berhasil!";
?>
