<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';
require_once '../objects/pendonor.php';

$database = new Database();
$db = $database->getConnection();

$pendonor = new Pendonor($db);

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

$stmt = $pendonor->readAll();
$num = $stmt->rowCount();

if($num > 0){
    $pendonor_arr = array();
    $pendonor_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
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
            "registration_date" => $registration_date,
            "hospital_id" => $hospital_id,
            "preferred_time" => $preferred_time,
            "email" => $email, // Menambahkan email ke output JSON
            "hospital_name" => $hospital_name ?? null
        );

        array_push($pendonor_arr["records"], $pendonor_item);
    }

    http_response_code(200);
    echo json_encode(array("success" => true, "data" => $pendonor_arr["records"]));
} else {
    http_response_code(404);
    echo json_encode(array("success" => false, "message" => "Tidak ada pendonor ditemukan."));
}
?>