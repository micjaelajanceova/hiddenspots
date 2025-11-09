<?php
// geocode.php
header('Content-Type: application/json');

if (!isset($_GET['q']) || empty($_GET['q'])) {
    echo json_encode([]);
    exit;
}

$search = urlencode($_GET['q']);

// Nominatim URL
$url = "https://nominatim.openstreetmap.org/search?format=json&q={$search}&limit=1";

// Inicializácia cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'HiddenSpots/1.0'); // Nominatim vyžaduje User-Agent
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([]);
    curl_close($ch);
    exit;
}

curl_close($ch);
echo $response;
