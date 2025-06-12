<?php
// donoryuk_backend/api/hospitals.php
session_start();

// --- PENTING: BARIS DEBUGGING INI HARUS DIHAPUS SAAT PRODUKSI ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR BARIS DEBUGGING ---

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';
require_once '../objects/hospital.php'; // Pastikan ini sudah ada dan benar path-nya

// Mendapatkan koneksi database menggunakan kelas Database (PDO)
$database = new Database();
$conn = $database->getConnection(); // Inisialisasi $conn di sini

// Inisialisasi objek Hospital dengan koneksi database
$hospital_obj = new Hospital($conn); // Gunakan nama variabel yang berbeda dari kelas

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

// Otorisasi untuk semua operasi kecuali GET (membaca)
if ($method !== 'GET' && !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Anda bukan admin.']);
    http_response_code(403); // Forbidden
    exit();
}

switch ($method) {
    case 'GET':
        $hospital_id = isset($_GET['id']) ? $_GET['id'] : null; // Ambil ID jika ada
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        if ($hospital_id) { // Jika ada ID, ambil detail satu rumah sakit
            $hospital_obj->id = $hospital_id; // Set ID ke objek hospital_obj

            if ($hospital_obj->readOne()) { // Panggil metode readOne dari objek Hospital
                $hospital_arr = array(
                    "id" => $hospital_obj->id,
                    "name" => $hospital_obj->name,
                    "address" => $hospital_obj->address,
                    "phone" => $hospital_obj->phone,
                    "hours" => $hospital_obj->hours,
                    "latitude" => (float)$hospital_obj->latitude, // Pastikan ini float
                    "longitude" => (float)$hospital_obj->longitude, // Pastikan ini float
                    "blood_stock" => $hospital_obj->getBloodStock($hospital_obj->id) // Panggil metode untuk stok
                );
                echo json_encode(['success' => true, 'hospital' => $hospital_arr]); // Mengembalikan singular 'hospital'
                http_response_code(200);
            } else {
                echo json_encode(['success' => false, 'message' => 'Rumah sakit tidak ditemukan.']);
                http_response_code(404);
            }
        } elseif ($action === 'getAll') { // Jika tidak ada ID dan action adalah getAll, ambil semua rumah sakit
            $stmt = $hospital_obj->readAll(); // Panggil metode readAll dari objek Hospital
            $num = $stmt->rowCount();

            if ($num > 0) {
                $hospitals_arr = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // $row['id'] akan otomatis tersedia dari fetch_assoc
                    $row['blood_stock'] = $hospital_obj->getBloodStock($row['id']); // Ambil stok untuk setiap RS
                    $hospitals_arr[] = $row;
                }
                echo json_encode(['success' => true, 'hospitals' => $hospitals_arr]);
                http_response_code(200);
            } else {
                echo json_encode(['success' => false, 'message' => 'Tidak ada rumah sakit ditemukan.']);
                http_response_code(404);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Aksi GET tidak valid atau ID rumah sakit tidak disediakan.']);
            http_response_code(400); // Bad Request
        }
        break;

    case 'POST': // Digunakan untuk menambah dan mengedit (jika ID disertakan)
        // Perbaikan: Gunakan PDO bindParam dan variabel objek
        $hospital_obj->id = isset($data['id']) ? $data['id'] : null;
        $hospital_obj->name = $data['name'];
        $hospital_obj->address = $data['address'];
        $hospital_obj->phone = isset($data['phone']) ? $data['phone'] : null;
        $hospital_obj->hours = isset($data['hours']) ? $data['hours'] : null;
        $hospital_obj->latitude = isset($data['latitude']) ? $data['latitude'] : null; // Tambah
        $hospital_obj->longitude = isset($data['longitude']) ? $data['longitude'] : null; // Tambah


        if ($hospital_obj->id) { // Update
            if ($hospital_obj->update()) { // Anda perlu membuat metode update di Hospital.php
                echo json_encode(['success' => true, 'message' => 'Rumah sakit berhasil diperbarui.']);
                http_response_code(200);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui rumah sakit atau tidak ada perubahan.']);
                http_response_code(500); // Atau 404 jika tidak ditemukan
            }
        } else { // Tambah
            if ($hospital_obj->create()) { // Anda perlu membuat metode create di Hospital.php
                echo json_encode(['success' => true, 'message' => 'Rumah sakit berhasil ditambahkan.', 'id' => $hospital_obj->id]);
                http_response_code(201); // Created
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan rumah sakit.']);
                http_response_code(500);
            }
        }
        break;

    case 'DELETE':
        // Perbaikan: Gunakan PDO bindParam dan variabel objek
        if (isset($data['id']) && !empty($data['id'])) {
            $hospital_obj->id = $data['id'];

            // Hapus stok darah terkait terlebih dahulu (jika onDelete CASCADE tidak diatur di DB)
            // Ini akan membutuhkan metode di Hospital.php atau query langsung di sini
            $delete_stock_query = "DELETE FROM blood_stock WHERE hospital_id = :hospital_id";
            $stmt_delete_stock = $conn->prepare($delete_stock_query);
            $stmt_delete_stock->bindParam(':hospital_id', $hospital_obj->id, PDO::PARAM_INT);
            $stmt_delete_stock->execute();


            if ($hospital_obj->delete()) { // Anda perlu membuat metode delete di Hospital.php
                echo json_encode(['success' => true, 'message' => 'Rumah sakit dan stok darah terkait berhasil dihapus.']);
                http_response_code(200);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus rumah sakit atau tidak ditemukan.']);
                http_response_code(500); // Atau 404
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID rumah sakit tidak disediakan.']);
            http_response_code(400);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Metode permintaan tidak didukung.']);
        http_response_code(405); // Method Not Allowed
        break;
}
// Hapus kurung kurawal penutup `}` yang berlebihan di akhir file