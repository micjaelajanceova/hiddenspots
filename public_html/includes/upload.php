<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/classes/session.php';
$session = new SessionHandle();

if (!$session->getUserId()) {
    header("Location: /auth/login.php");
    exit();
}


$error = null;

function getCoordinates($address) {
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($address);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'HiddenSpotsApp/1.0'); 
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) return null;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  //  CSRF TOKEN CHECK
  if (!isset($_POST['csrf_token']) 
  || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
  die("Invalid CSRF token");
    }


//  RATE LIMIT – max 5 uploads in 1 minute
    $stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM hidden_spots 
    WHERE user_id = ? 
    AND created_at >= (NOW() - INTERVAL 1 MINUTE)
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $uploadsLastMinute = $stmt->fetchColumn();

    if ($uploadsLastMinute > 5) {
        die("Too many uploads – slow down.");
    }


    $user_id = (int) $_SESSION['user_id'];
    $name = trim($_POST['name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $photoData = $_POST['photoData'] ?? '';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    if (!$name || !$city || !$category || !$photoData) {
        $error = "Please fill all required fields and select a photo.";
    } else {
        if ($address && (!$latitude || !$longitude)) {
            $coords = getCoordinates($address);
            if ($coords) {
                $latitude = $coords['lat'];
                $longitude = $coords['lng'];
            }
        }

        if (!$latitude || !$longitude) {
            $error = "Please provide a location either by address or by clicking on the map.";
        } else {
            if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
                $data = substr($photoData, strpos($photoData, ',') + 1);
                $data = base64_decode($data);
                $ext = strtolower($type[1]);
                $allowed = ['jpg','jpeg','png','webp'];

                if (!in_array($ext, $allowed)) {
                    $error = "Invalid image type.";
                } else {
                    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $filePath = __DIR__ . '/../uploads/'  . $fileName;

                    if (!file_put_contents($filePath, $data)) {
                        $error = "Failed to save image.";
                    } else {
                        try {
                            $sql = "INSERT INTO hidden_spots 
                                    (user_id, name, description, city, address, type, file_path, latitude, longitude, created_at) 
                                    VALUES (:user_id, :name, :description, :city, :address, :type, :file_path, :latitude, :longitude, NOW())";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([
                                ':user_id' => $user_id,
                                ':name' => $name,
                                ':description' => $description,
                                ':city' => $city,
                                ':address' => $address,
                                ':type' => $category,
                                ':file_path' => 'uploads/' . $fileName,
                                ':latitude' => $latitude,
                                ':longitude' => $longitude
                            ]);

                            header("Location: ../index.php?upload=success");
                            exit();
                        } catch (PDOException $e) {
                            if (file_exists($filePath)) unlink($filePath);
                            $error = "Database error: " . $e->getMessage();
                        }
                    }
                }
            } else {
                $error = "Invalid image data.";
            }
        }
    }

    // --- ZOBRAZENIE CHYBY MIMO VŠETKYCH BLOCOK ---
    if ($error) {
        echo "<p style='color:red;'>$error</p>";
    }
}
?>


<script>
// ---------- FORM SUBMIT ----------
uploadForm.addEventListener('submit', e => {
    const lat = latitudeInput.value.trim();
    const lng = longitudeInput.value.trim();
    const address = addressInput.value.trim();

    if ((!lat || !lng) && !address) {
        e.preventDefault();
        alert('Please select a location either by entering an address or clicking on the map.');
    }
});
</script>