<?php
// donoryuk_backend/api/blood_stock.php
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

// Mendapatkan koneksi database menggunakan kelas Database (PDO)
$database = new Database();
$conn = $database->getConnection(); // Inisialisasi $conn di sini

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true); // true for associative array

// Otorisasi: Semua operasi pada stok darah membutuhkan admin
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Anda bukan admin.']);
    http_response_code(403); // Forbidden
    exit();
}

switch ($method) {
    case 'POST': // Tambah atau Update stok darah
        // Perbaikan: Gunakan PDO bindParam, bukan real_escape_string
        $hospital_id = $data['hospital_id'];
        $blood_group = $data['blood_group'];
        $quantity = $data['quantity'];

        // Validasi
        if (empty($hospital_id) || empty($blood_group) || !is_numeric($quantity) || $quantity < 0) {
            echo json_encode(['success' => false, 'message' => 'Data stok darah tidak valid.']);
            http_response_code(400);
            exit();
        }

        // Cek apakah stok darah untuk golongan ini sudah ada untuk rumah sakit ini
        $checkSql = "SELECT id FROM blood_stock WHERE hospital_id = :hospital_id AND blood_group = :blood_group";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':hospital_id', $hospital_id);
        $checkStmt->bindParam(':blood_group', $blood_group);
        $checkStmt->execute();
        $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC); // Perbaikan: Gunakan fetch untuk PDO

        if ($checkResult) { // Jika ada hasil, berarti stok sudah ada
            // Update stok yang sudah ada
            $sql = "UPDATE blood_stock SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':id', $checkResult['id'], PDO::PARAM_INT);
            $message = 'Stok darah berhasil diperbarui.';
        } else {
            // Tambah stok baru
            $sql = "INSERT INTO blood_stock (hospital_id, blood_group, quantity) VALUES (:hospital_id, :blood_group, :quantity)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':hospital_id', $hospital_id, PDO::PARAM_INT);
            $stmt->bindParam(':blood_group', $blood_group);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $message = 'Stok darah berhasil ditambahkan.';
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => $message]);
            http_response_code(200);
        } else {
            // Perbaikan: Ambil error PDO
            echo json_encode(['success' => false, 'message' => 'Gagal mengelola stok darah: ' . implode(" ", $stmt->errorInfo())]);
            http_response_code(500);
        }
        break;

    case 'DELETE':
        // Perbaikan: Gunakan PDO bindParam
        $hospital_id = $data['hospital_id'];
        $blood_group = $data['blood_group'];

        if (empty($hospital_id) || empty($blood_group)) {
            echo json_encode(['success' => false, 'message' => 'ID Rumah sakit dan golongan darah harus disediakan.']);
            http_response_code(400);
            exit();
        }

        $sql = "DELETE FROM blood_stock WHERE hospital_id = :hospital_id AND blood_group = :blood_group";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':hospital_id', $hospital_id, PDO::PARAM_INT);
        $stmt->bindParam(':blood_group', $blood_group);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) { // Perbaikan: Gunakan rowCount() untuk PDO
                echo json_encode(['success' => true, 'message' => 'Stok darah berhasil dihapus.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Stok darah tidak ditemukan.']);
                http_response_code(404);
            }
        } else {
            // Perbaikan: Ambil error PDO
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus stok darah: ' . implode(" ", $stmt->errorInfo())]);
            http_response_code(500);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Metode permintaan tidak didukung.']);
        http_response_code(405); // Method Not Allowed
        break;
}
// Hapus kurung kurawal penutup `}` yang berlebihan di akhir file