<?php
$host = "localhost";
$user = "donor424_root";
$password = "qJpENkesa^+8B,y("; // ganti sesuai
$database = "donor424_donor_yuk_db";

$koneksi = mysqli_connect($host, $user, $password, $database);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
} else {
    echo "Koneksi berhasil!";
}
?>