<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Sertakan file koneksi database dan definisi objek Pendonor
// Path ini SUDAH BENAR berdasarkan struktur folder Anda
include_once '../config/database.php'; // Mengakses config/database.php dari api/
include_once '../objects/pendonor.php'; // Mengakses objects/pendonor.php dari api/

// Inisialisasi objek Database dan dapatkan koneksi
$database = new Database();
$db = $database->getConnection();

// Inisialisasi objek Pendonor dengan koneksi database
$pendonor = new Pendonor($db);

// Mendapatkan data POST yang dikirim dari frontend (JSON)
$data = json_decode(file_get_contents("php://input"));

// Memastikan data yang diterima tidak kosong dan lengkap
if (
    !empty($data->fullname) &&
    !empty($data->nik) &&
    !empty($data->birthdate) &&
    !empty($data->gender) &&
    !empty($data->blood_group) &&
    !empty($data->rhesus) &&
    !empty($data->phone) &&
    !empty($data->address)
) {
    // Mengisi properti objek pendonor dengan data dari request
    $pendonor->fullname = $data->fullname;
    $pendonor->nik = $data->nik;
    $pendonor->birthdate = $data->birthdate;
    $pendonor->gender = $data->gender;
    $pendonor->blood_group = $data->blood_group;
    $pendonor->rhesus = $data->rhesus;
    $pendonor->phone = $data->phone;
    $pendonor->address = $data->address;

    // last_donor_date adalah opsional, set null jika tidak ada atau kosong
    $pendonor->last_donor_date = isset($data->last_donor_date) && !empty($data->last_donor_date) ? $data->last_donor_date : null;

    // Cek apakah NIK sudah terdaftar di database
    if ($pendonor->nikExists()) {
        // Jika NIK sudah ada, kirim respons error
        http_response_code(400); // Bad Request
        echo json_encode(array("success" => false, "message" => "Pendaftaran gagal. NIK sudah terdaftar. Silakan gunakan NIK yang berbeda atau hubungi admin jika ini adalah kesalahan."));
        exit(); // Hentikan eksekusi script
    }

    // Mencoba membuat record pendonor baru di database
    if ($pendonor->create()) {
        // Jika berhasil, kirim respons sukses
        http_response_code(201); // Created
        echo json_encode(array("success" => true, "message" => "Pendaftaran pendonor berhasil! Terima kasih telah mendaftar."));
    } else {
        // Jika gagal (misalnya karena masalah koneksi DB atau query), kirim respons error
        http_response_code(503); // Service Unavailable
        echo json_encode(array("success" => false, "message" => "Tidak dapat mendaftar pendonor. Terjadi masalah pada server. Silakan coba lagi nanti."));
    }
} else {
    // Jika data yang dikirim dari frontend tidak lengkap
    http_response_code(400); // Bad Request
    echo json_encode(array("success" => false, "message" => "Tidak dapat mendaftar pendonor. Data yang dikirim tidak lengkap."));
}
?>