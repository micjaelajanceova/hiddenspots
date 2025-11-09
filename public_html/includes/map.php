<?php
function getCoordinates($address) {
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($address);

    $opts = [
        "http" => [
            "header" => "User-Agent: HiddenSpotsApp/1.0\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        return null;
    }

    $data = json_decode($response, true);

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
