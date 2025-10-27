<?php
session_start();
header("Content-Type: application/json");
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$name = $data['name'] ?? '';

if (!$email) {
  echo json_encode(["success" => false, "message" => "No email returned from Facebook"]);
  exit;
}

$stmt = $conn->prepare("SELECT userid, username, email FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
  $_SESSION['user_id'] = $user['userid'];
  $_SESSION['username'] = $user['username'];
} else {
  $stmt = $conn->prepare("INSERT INTO users (username, email) VALUES (?, ?)");
  $stmt->bind_param("ss", $name, $email);
  $stmt->execute();
  $_SESSION['user_id'] = $conn->insert_id;
  $_SESSION['username'] = $name;
}

echo json_encode(["success" => true]);
?>
