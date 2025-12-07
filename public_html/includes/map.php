<?php
function getCoordinates($address) {
    // Build the API URL with the encoded address
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($address);

    // HTTP context options (set User-Agent as required by Nominatim)
    $opts = [
        "http" => [
            "header" => "User-Agent: HiddenSpotsApp/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);

    // Send GET request to the API
    $response = file_get_contents($url, false, $context);

     // Return null if the request failed
    if ($response === FALSE) {
        return null;
    }

    // Decode the JSON response

    $data = json_decode($response, true);
    // Check if data exists and contains lat/lon
    if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
        return [
            'lat' => $data[0]['lat'],
            'lng' => $data[0]['lon']
        ];
    } else {
        return null;
    }
}
?>
