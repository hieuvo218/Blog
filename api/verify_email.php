<?php
require_once 'db_connect.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    exit('Invalid verification link.');
}

$stmt = $conn->prepare("SELECT userid FROM users WHERE verification_token=? AND verified=0");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $update = $conn->prepare("UPDATE users SET verified=1, verification_token=NULL WHERE verification_token=?");
    $update->bind_param("s", $token);
    $update->execute();
    echo "<h3 style='text-align:center;color:green;'>✅ Your account has been verified. You can now <a href='../login.html'>login</a>.</h3>";
} else {
    echo "<h3 style='text-align:center;color:red;'>❌ Invalid or expired verification link.</h3>";
}
