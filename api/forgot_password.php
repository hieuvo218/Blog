<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require '../vendor/autoload.php'; // adjust path if needed
require_once __DIR__ . '/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = new mysqli("localhost", "root", "", "blogsite");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$email = $_POST['email'] ?? '';

if (!$email) {
    echo json_encode(["success" => false, "message" => "Email is required"]);
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT userid FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Email not found"]);
    exit;
}

// Generate reset token
$token = bin2hex(random_bytes(16));
$stmt = $conn->prepare("UPDATE users SET reset_token=? WHERE email=?");
$stmt->bind_param("ss", $token, $email);
$stmt->execute();

// Create reset link
$resetLink = "http://localhost/mywebsite/reset_password.html?token=$token";

// Send email via Gmail SMTP
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;  // 🟡 change this
    $mail->Password = SMTP_PASS;     // 🟡 use Gmail app password
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;

    $mail->setFrom('hvotranminh2003@gmail.com', 'MyWebsite');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "Password Reset Request";
    $mail->Body = "Click <a href='$resetLink'>here</a> to reset your password.";

    $mail->send();
    echo json_encode(["success" => true, "message" => "Reset link sent"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Mailer error: " . $mail->ErrorInfo]);
}
?>
