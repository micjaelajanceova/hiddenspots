<?php
require_once __DIR__ . '/db.php';

$settingsStmt = $pdo->query("SELECT rules, contact_info FROM site_settings WHERE id = 1 LIMIT 1");
$footerInfo = $settingsStmt->fetch(PDO::FETCH_ASSOC);
?>

<footer class="text-center py-6 text-gray-500 text-sm">
    © <?= date('Y') ?> Hidden Spots — All rights reserved.
    
    <?php if (!empty($footerInfo['rules'])): ?>
        <div class="mt-2 text-gray-400 text-xs">
            <strong>Rules:</strong>
            <?= nl2br(htmlspecialchars($footerInfo['rules'])) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($footerInfo['contact_info'])): ?>
        <div class="mt-1 text-gray-400 text-xs">
            <strong>Contact:</strong>
            <?= nl2br(htmlspecialchars($footerInfo['contact_info'])) ?>
        </div>
    <?php endif; ?>
    
</footer>
</div>

<!-- Images Loaded script -->
<script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>

<!-- Mansonry script -->
<script src="https://cdn.jsdelivr.net/npm/macy@2"></script>

<!-- Global script -->
<script src="/assets/js/main.js"></script>

<script src="/assets/js/profile-photo.js" defer></script>


<!-- Map and Upload scripts -->
<script>
  const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<script type="module" src="/assets/js/map.js"></script>
<script type="module">
import { initUploadMap, setupGeocode } from '/assets/js/map.js';
initUploadMap('uploadMap', 'latitude', 'longitude');
setupGeocode('city', 'address');
</script>


</body>
</html>