<?php
// donoryuk_backend/api/get_permintaan_darah.php
session_start();

// --- PENTING: BARIS DEBUGGING INI HARUS DIHAPUS SAAT PRODUKSI ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR BARIS DEBUGGING ---

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';
require_once '../objects/permintaan_darah.php'; // Pastikan objek ini ada

$database = new Database();
$db = $database->getConnection();

$permintaan_darah_obj = new PermintaanDarah($db);

// Verifikasi admin (disarankan)
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Jika tidak ada sistem login, Anda mungkin perlu mengomentari bagian ini sementara
// if (!isAdmin()) {
//     http_response_code(403); // Forbidden
//     echo json_encode(array("success" => false, "message" => "Akses ditolak. Hanya admin yang diizinkan."));
//     exit();
// }

$stmt = $permintaan_darah_obj->readAll(); // Asumsi ada metode readAll() di PermintaanDarah
$num = $stmt->rowCount();

if($num > 0){
    $requests_arr = array();
    $requests_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // Extract data, ensure proper naming as in your database
        extract($row);

        $request_item = array(
            "id" => $id,
            "hospital_id" => $hospital_id,
            "user_name" => $user_name,
            "user_address" => $user_address,
            "user_age" => $user_age,
            "blood_group" => $blood_group,
            "quantity_taken" => $quantity_taken,
            "transaction_id" => $transaction_id,
            "status" => $status,
            "request_date" => $request_date,
            // Tambahkan nama RS jika di-join di readAll() PermintaanDarah
            "hospital_name" => isset($hospital_name) ? $hospital_name : 'N/A'
        );
        array_push($requests_arr["records"], $request_item);
    }

    http_response_code(200);
    echo json_encode(array("success" => true, "data" => $requests_arr["records"]));
} else {
    http_response_code(404);
    echo json_encode(array("success" => false, "message" => "Tidak ada permintaan pengambilan darah ditemukan."));
}
?>