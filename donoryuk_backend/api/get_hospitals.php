<?php
// donoryuk_backend/api/get_hospitals.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Sesuaikan untuk produksi: ganti * dengan domain frontend Anda
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Tangani preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Sertakan file koneksi database dan objek Hospital
include_once '../config/database.php'; // Sesuaikan path jika berbeda
include_once '../objects/hospital.php'; // Sesuaikan path jika berbeda

// Inisialisasi objek Database dan dapatkan koneksi
$database = new Database();
$db = $database->getConnection();

// Inisialisasi objek Hospital
$hospital = new Hospital($db);

// Query untuk membaca semua rumah sakit
$stmt = $hospital->readAll();
$num = $stmt->rowCount();

$hospitals_arr = array();
$hospitals_arr["hospitals"] = array();

if ($num > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row); // Akan membuat variabel $id, $name, $address, dll.
        $hospital_item = array(
            "id" => $id,
            "name" => $name,
            "address" => $address,
            "phone" => $phone,
            "hours" => $hours,
            "latitude" => $latitude,
            "longitude" => $longitude
        );
        array_push($hospitals_arr["hospitals"], $hospital_item);
    }
    http_response_code(200);
    echo json_encode(array("success" => true, "hospitals" => $hospitals_arr["hospitals"]));
} else {
    http_response_code(404);
    echo json_encode(array("success" => false, "message" => "Tidak ada rumah sakit ditemukan."));
}

$db = null; // Tutup koneksi
?>