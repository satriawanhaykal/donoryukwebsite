<?php
// donoryuk_backend/api/get_pendonor.php
session_start();

// --- PENTING: BARIS DEBUGGING INI HARUS DIHAPUS SAAT PRODUKSI ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR BARIS DEBUGGING ---

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET"); // Metode yang diizinkan hanya GET
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Periksa jika ini adalah permintaan OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Sertakan file koneksi database dan objek Pendonor
require_once '../config/database.php';
require_once '../objects/pendonor.php';

// Inisialisasi objek Database dan dapatkan koneksi
$database = new Database();
$db = $database->getConnection();

// Inisialisasi objek Pendonor
$pendonor = new Pendonor($db);

// Verifikasi apakah pengguna adalah admin (opsional, tapi disarankan untuk halaman admin)
// Anda perlu logika login yang mengisi $_SESSION['role']
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Jika tidak ada sistem login, Anda mungkin perlu mengomentari bagian ini sementara
// if (!isAdmin()) {
//     http_response_code(403); // Forbidden
//     echo json_encode(array("success" => false, "message" => "Akses ditolak. Hanya admin yang diizinkan."));
//     exit();
// }


// Query untuk membaca semua pendonor
// Asumsi Anda sudah menambahkan metode readAll() di objects/pendonor.php
$stmt = $pendonor->readAll();
$num = $stmt->rowCount();

// Cek jika ada pendonor yang ditemukan
if($num > 0){
    $pendonor_arr = array();
    $pendonor_arr["records"] = array();

    // Ambil setiap baris
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // 'extract($row)' akan membuat variabel ($id, $fullname, dll.) dari array $row
        extract($row);

        $pendonor_item = array(
            "id" => $id,
            "fullname" => $fullname,
            "nik" => $nik,
            "birthdate" => $birthdate,
            "gender" => $gender,
            "blood_group" => $blood_group,
            "rhesus" => $rhesus,
            "phone" => $phone,
            "address" => $address,
            "last_donor_date" => $last_donor_date,
            "registration_date" => $registration_date
        );

        array_push($pendonor_arr["records"], $pendonor_item);
    }

    // Set response code - 200 OK
    http_response_code(200);

    // Tampilkan data dalam format JSON
    echo json_encode(array("success" => true, "data" => $pendonor_arr["records"]));
} else {
    // Jika tidak ada pendonor ditemukan
    http_response_code(404); // Not Found
    echo json_encode(array("success" => false, "message" => "Tidak ada pendonor ditemukan."));
}
?>