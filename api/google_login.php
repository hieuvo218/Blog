<?php
require __DIR__ . '/../vendor/autoload.php';
session_start();
header("Content-Type: application/json");
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['credential'] ?? '';

if (!$token) {
  echo json_encode(["success" => false, "message" => "Missing Google token"]);
  exit;
}

$client = new Google_Client(['client_id' => '1067391613199-j9maommrof6ehm54hf6i72ksb7150dau.apps.googleusercontent.com']);
$client->setClientSecret('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');
$payload = $client->verifyIdToken($token);

if ($payload) {
  $email = $payload['email'];
  $name = $payload['name'];

  $stmt = $conn->prepare("SELECT userid, username, email FROM users WHERE email=?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($user = $result->fetch_assoc()) {
    $_SESSION['user_id'] = $user['userid'];
    $_SESSION['username'] = $user['username'];
  } else {
    $stmt = $conn->prepare("INSERT INTO users (username, email, provider) VALUES (?, ?, 'google')");
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();
    $_SESSION['user_id'] = $conn->insert_id;
    $_SESSION['username'] = $name;
  }

  echo json_encode(["success" => true]);
} else {
  echo json_encode(["success" => false, "message" => "Invalid Google token"]);
}
?>
