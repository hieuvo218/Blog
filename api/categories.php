<?php
require 'db_connect.php';
header("Content-Type: application/json");

$result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");
$categories = [];

while ($row = $result->fetch_assoc()) {
  $categories[] = $row['category'];
}

echo json_encode($categories);
?>
