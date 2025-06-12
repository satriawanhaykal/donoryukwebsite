<?php
// donoryuk_backend/api/register.php
session_start();

// --- PENTING: BARIS DEBUGGING INI HARUS DIHAPUS SAAT PRODUKSI ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR BARIS DEBUGGING ---

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS'); // Tambahkan OPTIONS untuk preflight CORS
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'); // Tambahkan headers yang relevan

// Tangani preflight request untuk CORS (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

// Inisialisasi objek Database dan dapatkan koneksi PDO
$database = new Database();
$db = $database->getConnection(); // Menggunakan $db untuk koneksi PDO

$data = json_decode(file_get_contents("php://input"));

// Pastikan data lengkap
if (!isset($data->fullname) || !isset($data->email) || !isset($data->password)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Semua kolom harus diisi.']);
    exit();
}

// Gunakan PDO, jadi tidak perlu real_escape_string secara manual
$fullname = $data->fullname;
$email = $data->email;
$password = $data->password;

// Validasi format email (opsional, tapi bagus untuk preventif)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
    exit();
}

// Hash password sebelum disimpan
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Cek apakah email sudah terdaftar
$sql_check = "SELECT id FROM users WHERE email = :email LIMIT 0,1"; // Gunakan placeholder PDO
$stmt_check = $db->prepare($sql_check); // Gunakan $db (objek PDO)
$stmt_check->bindParam(':email', $email); // Bind parameter email
$stmt_check->execute();

if ($stmt_check->rowCount() > 0) { // Gunakan rowCount() untuk PDO
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar.']);
    exit(); // Hentikan eksekusi setelah respons
}

// Insert user baru
$sql_insert = "INSERT INTO users (fullname, email, password, role) VALUES (:fullname, :email, :password, 'user')"; // Default role 'user'
$stmt_insert = $db->prepare($sql_insert); // Gunakan $db (objek PDO)

// Bind parameter untuk insert
$stmt_insert->bindParam(':fullname', $fullname);
$stmt_insert->bindParam(':email', $email);
$stmt_insert->bindParam(':password', $hashedPassword);

if ($stmt_insert->execute()) {
    // Pendaftaran berhasil
    $user_id = $db->lastInsertId(); // Dapatkan ID yang baru dibuat dari PDO

    // Otomatis login user setelah pendaftaran (opsional)
    $_SESSION['user_id'] = $user_id;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'user';

    http_response_code(201); // Created
    echo json_encode([
        'success' => true,
        'message' => 'Pendaftaran berhasil! Anda sekarang dapat login.',
        'user' => [
            'id' => $user_id,
            'fullname' => $fullname,
            'email' => $email,
            'role' => 'user'
        ]
    ]);
} else {
    // Jika ada error saat insert
    http_response_code(503); // Service Unavailable
    // Gunakan errorInfo() untuk mendapatkan detail error PDO
    echo json_encode(['success' => false, 'message' => 'Pendaftaran gagal: ' . implode(" ", $stmt_insert->errorInfo())]);
}

// Koneksi PDO akan otomatis ditutup saat script selesai dieksekusi,
// tidak perlu $stmt->close() atau $db->close()
?>