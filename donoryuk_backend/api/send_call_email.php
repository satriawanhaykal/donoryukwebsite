<?php
// donoryuk_backend/api/send_call_email.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Sesuaikan untuk produksi: https://donoryuk.xyz
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';
include_once '../objects/pendonor.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';


$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

// Pastikan ada otorisasi admin yang kuat di lingkungan produksi
if (!isset($_SERVER['HTTP_AUTHORIZATION']) || $_SERVER['HTTP_AUTHORIZATION'] !== 'Bearer admin') {
    http_response_code(403);
    echo json_encode(array("success" => false, "message" => "Akses ditolak."));
    exit();
}

if (
    !empty($data->pendonor_id) &&
    !empty($data->fullname) &&
    !empty($data->email) &&
    !empty($data->hospital_name) &&
    !empty($data->preferred_time)
) {
    $pendonor_id = $data->pendonor_id;
    $fullname = $data->fullname;
    $recipient_email = $data->email; // Ini adalah email calon pendonor (reciever)
    $hospital_name = $data->hospital_name;
    $preferred_time = $data->preferred_time;

    if (empty($recipient_email) || !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Email penerima tidak valid atau kosong."));
        exit();
    }

    $mail = new PHPMailer(true);
    try {
        // KONFIGURASI SMTP UNTUK RUMAHWEB DENGAN ALAMAT EMAIL ANDA
        $mail->isSMTP();
        $mail->Host       = 'mail.donoryuk.xyz';   // Host SMTP Rumahweb Anda
        $mail->SMTPAuth   = true;
        $mail->Username   = 'admin@donoryuk.com';  // Email pengirim Anda (yang ada di hosting Rumahweb)
        $mail->Password   = 'admin123'; // Kata sandi untuk email admin@donoryuk.com
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Untuk port 465
        $mail->Port       = 465;                      // Port standar untuk SMTPS

        // Jika 465 tidak berhasil, coba port 587 dengan STARTTLS:
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        // $mail->Port       = 587;


        // Pengirim dan Penerima
        $mail->setFrom('admin@donoryuk.com', 'Admin Donor Yuk!'); // Ini alamat yang akan terlihat sebagai pengirim
        $mail->addAddress($recipient_email, $fullname);     // Ini alamat email calon pendonor (penerima)

        // Konten Email (tidak ada perubahan)
        $mail->isHTML(true);
        $mail->Subject = 'Panggilan Donor Darah Anda - Donor Yuk!';
        $mail->Body    = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { width: 80%; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                    .header { background-color: #dc3545; color: white; padding: 10px 0; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { padding: 20px; }
                    .footer { text-align: center; font-size: 0.8em; color: #777; margin-top: 20px; }
                    .button { display: inline-block; background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Panggilan Donor Darah</h2>
                    </div>
                    <div class='content'>
                        <p>Yth. Bapak/Ibu <strong>{$fullname}</strong>,</p>
                        <p>Terima kasih atas kesediaan Anda untuk menjadi pendonor darah.</p>
                        <p>Dengan hormat, kami memanggil Anda untuk melakukan donor darah dengan detail sebagai berikut:</p>
                        <ul>
                            <li><strong>Nomor Antrean:</strong> {$pendonor_id}</li>
                            <li><strong>Rumah Sakit:</strong> {$hospital_name}</li>
                            <li><strong>Waktu yang Direkomendasikan:</strong> {$preferred_time} WIB</li>
                            <li><strong>Tanggal Panggilan:</strong> " . date('d-m-Y') . "</li>
                        </ul>
                        <p>Mohon datang ke lokasi pada waktu yang disebutkan atau sesuaikan dengan konfirmasi dari pihak rumah sakit. Pastikan Anda telah memenuhi semua kriteria donor.</p>
                        <p>Bawa identitas diri (KTP/SIM) dan pastikan kondisi tubuh Anda fit.</p>
                        <p>Apabila ada pertanyaan lebih lanjut, silakan hubungi pihak rumah sakit yang bersangkutan.</p>
                        <p>Terima kasih atas kepedulian Anda. Setetes darah Anda sangat berarti bagi mereka yang membutuhkan.</p>
                        <p style="text-align: center; margin-top: 20px;"><a href="https://donoryuk.xyz" class="button">Kunjungi Donor Yuk!</a></p>
                    </div>
                    <div class='footer'>
                        <p>&copy; " . date('Y') . " Donor Yuk!. Semua hak dilindungi.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        $mail->AltBody = "Yth. Bapak/Ibu {$fullname},\n\nTerima kasih atas kesediaan Anda untuk menjadi pendonor darah. Kami memanggil Anda untuk melakukan donor darah di {$hospital_name} pada waktu yang direkomendasikan {$preferred_time} WIB. Nomor antrean Anda: {$pendonor_id}. Pastikan Anda telah memenuhi semua kriteria donor dan membawa identitas diri. Terima kasih atas kepedulian Anda. Setetes darah Anda sangat berarti bagi mereka yang membutuhkan.";

        $mail->send();
        http_response_code(200);
        echo json_encode(array("success" => true, "message" => "Email panggilan berhasil dikirim!"));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("success" => false, "message" => "Gagal mengirim email. Mailer Error: {$mail->ErrorInfo}"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Data tidak lengkap untuk mengirim email panggilan."));
}
?>