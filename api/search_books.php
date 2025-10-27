<?php
header("Content-Type: application/json");
require 'db_connect.php';

$q = trim($_GET['q'] ?? '');

if ($q === '') {
  echo json_encode([]);
  exit;
}

$stmt = $conn->prepare("SELECT id, title, image_url, price FROM books WHERE title LIKE CONCAT('%', ?, '%') LIMIT 8");
$stmt->bind_param("s", $q);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
  $suggestions[] = $row;
}

echo json_encode($suggestions);
?>
