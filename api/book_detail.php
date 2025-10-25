<?php
require 'db_connect.php';
header("Content-Type: application/json");

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(["error" => "Invalid book ID"]);
  exit;
}

$stmt = $conn->prepare("SELECT id, title, author, price, image_url, description 
                        FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

echo json_encode($book ?: []);
?>
