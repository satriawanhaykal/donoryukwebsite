<?php
// donoryuk_backend/api/manage_pendonor.php
session_start();

// --- PENTING: BARIS DEBUGGING INI HARUS DIHAPUS SAAT PRODUKSI ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR BARIS DEBUGGING ---

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS"); // Menambahkan POST untuk update, DELETE
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

// Verifikasi admin (disarankan)
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Untuk GET, tidak perlu admin (jika ada fitur public readOne)
// Untuk POST (update) dan DELETE, wajib admin
if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE') && !isAdmin()) {
    http_response_code(403); // Forbidden
    echo json_encode(array("success" => false, "message" => "Akses ditolak. Hanya admin yang diizinkan untuk mengubah atau menghapus data pendonor."));
    exit();
}

// Mendapatkan ID dari URL jika ada (untuk GET satu pendonor atau DELETE)
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Mendapatkan data dari body request untuk POST/DELETE
$data = json_decode(file_get_contents("php://input"));

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($id) {
            // Mengambil satu pendonor
            $pendonor->id = $id;
            if ($pendonor->readOne()) {
                $pendonor_item = array(
                    "id" => $pendonor->id,
                    "fullname" => $pendonor->fullname,
                    "nik" => $pendonor->nik,
                    "birthdate" => $pendonor->birthdate,
                    "gender" => $pendonor->gender,
                    "blood_group" => $pendonor->blood_group,
                    "rhesus" => $pendonor->rhesus,
                    "phone" => $pendonor->phone,
                    "address" => $pendonor->address,
                    "last_donor_date" => $pendonor->last_donor_date,
                    "registration_date" => $pendonor->registration_date
                );
                http_response_code(200);
                echo json_encode(array("success" => true, "data" => $pendonor_item));
            } else {
                http_response_code(404);
                echo json_encode(array("success" => false, "message" => "Pendonor tidak ditemukan."));
            }
        } else {
            // Ini akan ditangani oleh get_pendonor.php, bukan di sini.
            // Jika Anda ingin manage_pendonor.php juga bisa getAll, Anda bisa pindahkan logika readAll() dari get_pendonor.php ke sini.
            // Untuk saat ini, asumsikan get_pendonor.php untuk getAll.
            http_response_code(400);
            echo json_encode(array("success" => false, "message" => "Permintaan GET tidak valid. Mohon berikan ID untuk detail pendonor."));
        }
        break;

    case 'POST': // Untuk update pendonor
        // Pastikan data yang diperlukan untuk update tersedia
        if (
            !empty($data->id) &&
            !empty($data->fullname) &&
            !empty($data->nik) &&
            !empty($data->birthdate) &&
            !empty($data->gender) &&
            !empty($data->blood_group) &&
            !empty($data->rhesus) &&
            !empty($data->phone) &&
            !empty($data->address)
        ) {
            $pendonor->id = $data->id;
            $pendonor->fullname = $data->fullname;
            $pendonor->nik = $data->nik;
            $pendonor->birthdate = $data->birthdate;
            $pendonor->gender = $data->gender;
            $pendonor->blood_group = $data->blood_group;
            $pendonor->rhesus = $data->rhesus;
            $pendonor->phone = $data->phone;
            $pendonor->address = $data->address;
            $pendonor->last_donor_date = isset($data->last_donor_date) && !empty($data->last_donor_date) ? $data->last_donor_date : null;

            // Optional: Cek NIK unik saat update (jika NIK diubah ke NIK yang sudah ada)
            // Anda perlu fungsi nikExistsExceptId(nik, id) di kelas Pendonor jika NIK bisa diubah
            // Untuk sederhana, kita asumsikan NIK tidak diubah atau unik sudah ditangani.

            if ($pendonor->update()) {
                http_response_code(200); // OK
                echo json_encode(array("success" => true, "message" => "Data pendonor berhasil diperbarui."));
            } else {
                http_response_code(503); // Service Unavailable
                echo json_encode(array("success" => false, "message" => "Gagal memperbarui data pendonor."));
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(array("success" => false, "message" => "Data tidak lengkap untuk pembaruan pendonor."));
        }
        break;

    case 'DELETE':
        // Pastikan ID tersedia
        if (!empty($data->id)) {
            $pendonor->id = $data->id;

            if ($pendonor->delete()) {
                http_response_code(200); // OK
                echo json_encode(array("success" => true, "message" => "Pendonor berhasil dihapus."));
            } else {
                http_response_code(503); // Service Unavailable
                echo json_encode(array("success" => false, "message" => "Gagal menghapus pendonor atau pendonor tidak ditemukan."));
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(array("success" => false, "message" => "ID pendonor tidak disediakan untuk dihapus."));
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(array("success" => false, "message" => "Metode permintaan tidak didukung."));
        break;
}
?>