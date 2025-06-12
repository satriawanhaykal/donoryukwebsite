<?php
// donoryuk_backend/api/login.php
session_start(); // Mulai sesi PHP

// --- PENTING: BARIS DEBUGGING INI HARUS DIHAPUS SAAT PRODUKSI ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR BARIS DEBUGGING ---

header('Content-Type: application/json'); // Respons dalam format JSON
header('Access-Control-Allow-Origin: *'); // Izinkan akses dari semua origin (untuk pengembangan)
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'); // Tambahkan Authorization dan X-Requested-With

// Tangani preflight request untuk CORS (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php'; // Sertakan file koneksi database

// Inisialisasi objek Database dan dapatkan koneksi PDO
$database = new Database();
$db = $database->getConnection(); // Menggunakan $db untuk koneksi PDO

$data = json_decode(file_get_contents("php://input")); // Ambil data JSON dari frontend

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Email dan password harus diisi.']);
    exit();
}

// Gunakan PDO, jadi tidak perlu real_escape_string secara manual
$email = $data->email;
$password = $data->password;

// Query SQL menggunakan placeholder PDO (:email)
$sql = "SELECT id, fullname, email, password, role FROM users WHERE email = :email LIMIT 0,1";
$stmt = $db->prepare($sql); // Gunakan $db (objek PDO) untuk prepare
$stmt->bindParam(':email', $email); // Bind parameter email
$stmt->execute();

$num_rows = $stmt->rowCount(); // Gunakan rowCount() untuk PDO

if ($num_rows === 1) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Gunakan fetch_assoc untuk PDO

    // Verifikasi password yang di-hash
    if (password_verify($password, $user['password'])) {
        // Login berhasil
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role']; // Simpan peran pengguna di sesi

        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil!',
            'user' => [
                'id' => $user['id'],
                'fullname' => $user['fullname'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else {
        // Password salah
        http_response_code(401); // Unauthorized
        echo json_encode(['success' => false, 'message' => 'Email atau kata sandi salah.']);
    }
} else {
    // Pengguna tidak ditemukan
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Email atau kata sandi salah.']);
}

// Koneksi PDO akan otomatis ditutup saat script selesai dieksekusi,
// tidak perlu $stmt->close() atau $db->close()
?>