<?php
// Konfigurasi koneksi ke database
$host = "localhost"; // Host MySQL
$username = "lufiweb_usersmk"; // Username MySQL
$password = "P4s5Word&"; // Password MySQL
$database = "lufiweb_smk"; // Nama Database

// Buat koneksi ke database
$conn = mysqli_connect($host, $username, $password, $database);

// Periksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Jika koneksi berhasil
echo "Koneksi ke database berhasil";
?>