<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$servername = "localhost";
$username = "root";
$password = ""; // change if you have one
$dbname = "bookstore";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$min = isset($_GET['min']) ? (float)$_GET['min'] : 0;
$max = isset($_GET['max']) ? (float)$_GET['max'] : 99999999; // default big number

$like = "%$search%";

// If user slider is at the max end (e.g., 500000), allow higher prices
if ($max >= 500000) {
    $sql = "SELECT * FROM Books WHERE price >= ? AND title LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ds", $min, $like);
} else {
    $sql = "SELECT * FROM Books WHERE price >= ? AND price <= ? AND title LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dds", $min, $max, $like);
}

$stmt->execute();
$result = $stmt->get_result();

$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

echo json_encode($books);
$conn->close();
?>
