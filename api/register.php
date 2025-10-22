<?php
header("Content-Type: application/json");
require 'db_connect.php';

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '') {
  echo json_encode(["success" => false, "message" => "All fields are required."]);
  exit;
}

// Check if username or email exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
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
$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $password_hash);

if ($stmt->execute()) {
  echo json_encode(["success" => true, "message" => "Registration successful."]);
} else {
  echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
}
?>
