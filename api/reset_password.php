<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db_connect.php';

$input = json_decode(file_get_contents("php://input"), true);
$token = trim($input['token'] ?? '');
$new_password = $input['password'] ?? '';

if (!$token || !$new_password) {
    echo json_encode(["success" => false, "message" => "Missing token or password"]);
    exit;
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

// ✅ Verify token exists
$stmt = $conn->prepare("SELECT userid FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Invalid or expired token."]);
    exit;
}

$user = $result->fetch_assoc();

// 🔄 Update password
$new_hash = password_hash($new_password, PASSWORD_BCRYPT);
$update = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL WHERE userid = ?");
$update->bind_param("si", $new_hash, $user['userid']);
$update->execute();

if ($update->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Password has been reset successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Something went wrong, please try again."]);
}

$conn->close();
