<?php
require __DIR__ . '/../vendor/autoload.php';
use GuzzleHttp\Client;

$client = new Client();
try {
    $response = $client->request('GET', 'https://www.googleapis.com/oauth2/v1/certs');
    echo $response->getStatusCode() === 200 ? "✅ HTTPS works!" : "❌ HTTPS failed!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>