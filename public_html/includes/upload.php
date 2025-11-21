<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
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

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center hidden" style="z-index:9999;">
  <div id="uploadContainer" 
       class="bg-white rounded-2xl shadow-lg w-full h-full md:max-w-3xl md:h-[80vh] flex flex-col overflow-hidden relative animate-[fadeIn_0.3s_ease]">

    <!-- Header -->
    <div class="flex justify-between items-center p-4 border-b border-gray-200">
      <div class="text-center py-3 font-semibold text-lg">Create new post</div>
      <button id="closeUploadModal" class="text-black text-2xl hover:opacity-80">&times;</button>
    </div>

    <!-- STEP 1: Select Photo -->
    <div id="stepSelect" class="flex flex-col items-center justify-center flex-1 text-center p-4">
      <label for="photoInput" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg cursor-pointer transition">
        Select photo
      </label>
      <input type="file" id="photoInput" name="photo" accept="image/*" class="hidden">
    </div>

    <!-- STEP 2: Preview -->
    <div id="stepPreview" class="hidden flex items-center justify-center flex-1 bg-white relative overflow-hidden p-4">
      <img 
        id="previewImage" 
        class="max-w-[90%] max-h-[90%] object-contain rounded-lg transition-transform duration-300" 
      />
      <button 
        id="nextBtn" 
        class="absolute top-4 right-4 bg-blue-500 hover:bg-blue-600 text-white px-5 py-1.5 rounded-lg font-semibold transition">
        Next
      </button>
    </div>

    <!-- STEP 3: Form -->
    <div id="stepForm" class="hidden flex flex-1 h-full overflow-hidden">
      <!-- Image preview -->
      <div class="w-1/2 h-full bg-black flex justify-center items-center overflow-hidden">
        <img id="finalImage" class="w-full h-full object-cover" />
      </div>

      <!-- Form -->
      <div class="w-1/2 p-6 overflow-y-auto">
        <h2 class="text-lg font-semibold mb-4">New Hidden Spot</h2>
        <form id="uploadForm" action="includes/upload.php" method="post" enctype="multipart/form-data" class="space-y-4">
          <input type="hidden" name="photoData" id="photoData">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          <input type="text" name="name" placeholder="Name" required class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none" />
          <input type="text" name="city" placeholder="City" required class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none" />
          <input type="text" name="address" placeholder="Address (optional)" class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none" />

          
          <input type="hidden" name="latitude" id="latitude">
          <input type="hidden" name="longitude" id="longitude">

          <!-- MAP -->
          <div id="uploadMap" class="w-full h-56 rounded-lg shadow-md border border-gray-200 my-2"></div>

          <select name="category" required class="w-full border rounded p-2 bg-white focus:ring-2 focus:ring-blue-400 outline-none">
            <option value="">Select a category</option>
            <option>Nature</option>
            <option>Cafés</option>
            <option>Urban</option>
            <option>Architecture</option>
            <option>Viewpoint</option>
            <option>Restaurant</option>
            <option>Other</option>
          </select>

          <textarea name="description" rows="3" placeholder="Description or tip" class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none"></textarea>

          <div class="flex justify-end gap-3">
            <button type="button" id="backBtn" class="text-gray-600 hover:underline">Back</button>
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition">Share</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

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