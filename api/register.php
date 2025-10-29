<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  exit(json_encode(["success" => false, "message" => "Invalid email"]));
}
// 🔒 Password validation rules
if (strlen($new_password) < 8 || 
    !preg_match('/[A-Z]/', $new_password) || 
    !preg_match('/[0-9]/', $new_password)) {
    echo json_encode([
        "success" => false, 
        "message" => "Password must be at least 8 characters, include a number and an uppercase letter."
    ]);
    exit;
}

// check if email already registered
$check = $conn->prepare("SELECT userid FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
  exit(json_encode(["success" => false, "message" => "Email already registered"]));
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$token = bin2hex(random_bytes(16)); // random verification token

$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, verification_token) VALUES (?,?,?,?)");
$stmt->bind_param("ssss", $username, $email, $hash, $token);
$stmt->execute();

// ✅ Define the verification link (you forgot this line)
$verify_link = "http://localhost/mywebsite/api/verify_email.php?token=" . urlencode($token);

// ✅ Send verification email
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
    $mail->Subject = 'Account Verification - MyWebsite';
    $mail->Body = "
        <p>Hello <b>$username</b>,</p>
        <p>Thanks for registering on MyWebsite! Please click the link below to verify your account:</p>
        <p><a href='$verify_link'>$verify_link</a></p>
        <p>If you didn’t create an account, you can safely ignore this email.</p>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Check your email for a verification link']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
}
