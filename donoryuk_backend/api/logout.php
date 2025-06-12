<?php
// donoryuk_backend/api/logout.php
session_start();

// --- PENTING: BARIS DEBUGGING INI HARUS DIHAPUS SAAT PRODUKSI ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- AKHIR BARIS DEBUGGING ---

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Hapus semua variabel sesi
$_SESSION = array();

// Hapus sesi cookie. Ini akan menghancurkan sesi, dan bukan hanya data sesi.
// Perhatikan: Ini akan menghancurkan sesi, dan bukan hanya data sesi!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Terakhir, hancurkan sesi.
session_destroy();

http_response_code(200); // OK
echo json_encode(['success' => true, 'message' => 'Logout berhasil.']);

// Hapus kurung kurawal penutup `}` yang berlebihan di akhir file
?>