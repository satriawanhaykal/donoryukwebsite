<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';
include_once '../objects/pendonor.php';

$database = new Database();
$db = $database->getConnection();

$pendonor = new Pendonor($db);

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->fullname) &&
    !empty($data->nik) &&
    !empty($data->birthdate) &&
    !empty($data->gender) &&
    !empty($data->blood_group) &&
    !empty($data->rhesus) &&
    !empty($data->phone) &&
    !empty($data->email) && // Menambahkan validasi untuk email
    !empty($data->address) &&
    !empty($data->hospital_id) &&
    !empty($data->preferred_time)
) {
    $pendonor->fullname = $data->fullname;
    $pendonor->nik = $data->nik;
    $pendonor->birthdate = $data->birthdate;
    $pendonor->gender = $data->gender;
    $pendonor->blood_group = $data->blood_group;
    $pendonor->rhesus = $data->rhesus;
    $pendonor->phone = $data->phone;
    $pendonor->email = $data->email; // Menambahkan assign untuk email
    $pendonor->address = $data->address;
    $pendonor->last_donor_date = isset($data->last_donor_date) && !empty($data->last_donor_date) ? $data->last_donor_date : null;

    $pendonor->hospital_id = $data->hospital_id;
    $pendonor->preferred_time = $data->preferred_time;

    if ($pendonor->nikExists()) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Pendaftaran gagal. NIK sudah terdaftar. Silakan gunakan NIK yang berbeda atau hubungi admin jika ini adalah kesalahan."));
        exit();
    }

    if ($pendonor->create()) {
        http_response_code(201);
        echo json_encode(array("success" => true, "message" => "Pendaftaran pendonor berhasil! Terima kasih telah mendaftar."));
    } else {
        http_response_code(503);
        echo json_encode(array("success" => false, "message" => "Tidak dapat mendaftar pendonor. Terjadi masalah pada server. Silakan coba lagi nanti."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Tidak dapat mendaftar pendonor. Data yang dikirim tidak lengkap."));
}
?>