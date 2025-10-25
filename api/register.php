<?php
header('Content-Type: application/json; charset=utf-8');

// Don't let PHP print HTML errors (use logging instead)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Use output buffering to prevent accidental output breaking JSON
ob_start();

// Convert PHP errors to exceptions so we can return JSON consistently
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
set_exception_handler(function($e) {
    // Clean any buffered output and return a JSON error
    if (ob_get_length()) ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
});

session_start();

require 'db_connect.php';

$fullname = trim($_POST['fullname'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
  echo json_encode(["success" => false, "message" => "All fields are required."]);
  exit;
}

// Check if username or email exists
$stmt = $conn->prepare("SELECT userid FROM users WHERE username=? OR email=?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  echo json_encode(["success" => false, "message" => "Username or email already exists."]);
  exit;
}

// Hash password securely
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $conn->prepare("INSERT INTO users (fullname, username, email, passwordhash) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die(json_encode([
        "success" => false,
        "message" => "Prepare failed: " . $conn->error
    ]));
}
$stmt->bind_param("ssss", $fullname, $username, $email, $password_hash);

if ($stmt->execute()) {
  echo json_encode(["success" => true, "message" => "Registration successful."]);
} else {
  echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
}
?>
