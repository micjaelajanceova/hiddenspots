<?php
require_once __DIR__ . '/db.php';

// Load ALL footer settings (description, rules, contact, color)
$stmt = $pdo->query("SELECT site_description, rules, contact_info, primary_color 
                     FROM site_settings 
                     WHERE id = 1 LIMIT 1");

$settings = $stmt->fetch(PDO::FETCH_ASSOC);

$siteDescription = $settings['site_description'] ?? '';
$siteRules = $settings['rules'] ?? '';
$siteContact = $settings['contact_info'] ?? '';
?>
<footer class="text-center py-6 text-gray-500 text-sm space-y-2">
    
     
    <div class="max-w-5xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-8 text-left">

<?php if (!empty($siteDescription)): ?>
<div>
    <h3 class="font-semibold text-gray-700 mb-2 text-sm tracking-wide">About</h3>
    <p class="leading-relaxed text-gray-500"><?= nl2br(htmlspecialchars($siteDescription)) ?></p>
</div>
<?php endif; ?>

<?php if (!empty($siteRules)): ?>
<div>
    <h3 class="font-semibold text-gray-700 mb-2 text-sm tracking-wide">Rules</h3>
    <p class="leading-relaxed text-gray-500"><?= nl2br(htmlspecialchars($siteRules)) ?></p>
</div>
<?php endif; ?>

<?php if (!empty($siteContact)): ?>
<div>
    <h3 class="font-semibold text-gray-700 mb-2 text-sm tracking-wide">Contact</h3>
    <p class="leading-relaxed text-gray-500"><?= nl2br(htmlspecialchars($siteContact)) ?></p>
</div>
<?php endif; ?>

</div>

<p class="border-t pt-4 mt-4 text-xs text-gray-400">
© <?= date('Y') ?> Hidden Spots — All rights reserved.
</p>

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