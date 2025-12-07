<?php
// Geocode address using Nominatim API and return JSON response
header('Content-Type: application/json');

// Check for 'q' parameter 
if (!isset($_GET['q']) || empty($_GET['q'])) {
    echo json_encode([]);
    exit;
}

// Encode search query to make it safe for URLs (handles spaces, special characters)
$search = urlencode($_GET['q']);

// Prepare Nominatim API URL
$url = "https://nominatim.openstreetmap.org/search?format=json&q={$search}&limit=1";

// Initialize cURL session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'HiddenSpots/1.0'); 
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo json_encode([]);
    curl_close($ch);
    exit;
}

// Close cURL session
curl_close($ch);
echo $response;
