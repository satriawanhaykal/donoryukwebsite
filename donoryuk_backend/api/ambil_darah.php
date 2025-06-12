<?php
header('Content-Type: application/json');

// Koneksi DB (jika ada file database.php)
require_once '../config/database.php'; // Ubah path sesuai struktur kamu

// Ambil input JSON
$inputJSON = file_get_contents("php://input");
$data = json_decode($inputJSON, true);

// Validasi apakah JSON valid
if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "Input JSON tidak valid atau kosong.",
        "debug" => $inputJSON
    ]);
    exit;
}

// Daftar field wajib
$required_fields = [
    'user_name', 'user_address', 'user_age',
    'blood_group', 'quantity_taken', 'hospital_id',
    'hospital_name', 'hospital_address'
];

// Cek semua field wajib
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        echo json_encode([
            "success" => false,
            "message" => "Data pengambilan darah tidak lengkap. Field kosong: $field",
        ]);
        exit;
    }
}

// Ambil data dari request
$user_name = $data['user_name'];
$user_address = $data['user_address'];
$user_age = (int) $data['user_age'];
$blood_group = $data['blood_group'];
$quantity_taken = (int) $data['quantity_taken'];
$hospital_id = $data['hospital_id'];
$hospital_name = $data['hospital_name'];
$hospital_address = $data['hospital_address'];
$hospital_phone = $data['hospital_phone'] ?? '';
$hospital_hours = $data['hospital_hours'] ?? '';
$hospital_latitude = $data['hospital_latitude'] ?? '';
$hospital_longitude = $data['hospital_longitude'] ?? '';

// --- Simulasi penyimpanan ke DB atau logika lain ---
// Jika kamu ingin menyimpan ke database, gunakan PDO seperti ini:
// $stmt = $pdo->prepare("INSERT INTO pengambilan_darah (...) VALUES (...)");

$response = [
    "success" => true,
    "message" => "Pengambilan darah berhasil diajukan.",
    "transaction_details" => [
        "user_name" => $user_name,
        "user_address" => $user_address,
        "user_age" => $user_age,
        "blood_group" => $blood_group,
        "quantity_taken" => $quantity_taken,
        "hospital_id" => $hospital_id,
        "hospital_name" => $hospital_name,
        "hospital_address" => $hospital_address,
        "hospital_phone" => $hospital_phone,
        "hospital_hours" => $hospital_hours,
        "hospital_latitude" => $hospital_latitude,
        "hospital_longitude" => $hospital_longitude
    ]
];

echo json_encode($response);
exit;
?>
