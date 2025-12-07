<?php
require_once __DIR__ . '/db.php';

require_once __DIR__ . '/session.php';
$session = new SessionHandle();

// Load ALL footer settings (description, rules, contact, color)
$stmt = $pdo->query("SELECT site_description, rules, contact_info, primary_color 
                     FROM site_settings 
                     WHERE id = 1 LIMIT 1");

$settings = $stmt->fetch(PDO::FETCH_ASSOC);

$siteDescription = $settings['site_description'] ?? '';
$siteRules = $settings['rules'] ?? '';
$siteContact = $settings['contact_info'] ?? '';
?>


<footer class="py-10 text-gray-500 text-sm">

<div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10 text-left px-4 sm:px-6 md:px-0">

  <?php if (!empty($siteDescription)): ?>
    <div>
      <h5 class="font-semibold mb-3 tracking-wide text-xs">About HiddenSpots</h5>
      <h6 class="leading-relaxed"><?= nl2br(htmlspecialchars($siteDescription)) ?></h6>
    </div>
  <?php endif; ?>


  <?php if (!empty($siteRules)): ?>
    <div>
      <h5 class="font-semibold mb-3 tracking-wide text-xs">Rules & Regulations
</h5>
      <h6 class="leading-relaxed"><?= nl2br(htmlspecialchars($siteRules)) ?></h6>
    </div>
  <?php endif; ?>


  <?php if (!empty($siteContact)): ?>
    <div>
      <h5 class="font-semibold mb-3 tracking-wide text-xs">Get in Touch</h5>
      <h6 class="leading-relaxed"><?= nl2br(htmlspecialchars($siteContact)) ?></h6>
    </div>
  <?php endif; ?>

</div>

<h6 class="text-center mt-10 border-t pt-4 text-xs">
    © <?= date('Y') ?> Hidden Spots — All rights reserved.
  </h6>

</footer>

</div>

<!-- Images Loaded script -->
<script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>

<!-- Mansonry script -->
<script src="https://cdn.jsdelivr.net/npm/macy@2"></script>

<!-- Global script -->
<script src="/assets/js/main.js"></script>

<!-- Map and Upload scripts -->
<script>const isLoggedIn = <?= $session->logged_in() ? 'true' : 'false'; ?>;</script>

<!-- Map and Upload scripts -->
<script type="module" src="/assets/js/map.js"></script>
<script type="module">
import { initUploadMap, setupGeocode } from '/assets/js/map.js';
initUploadMap('uploadMap', 'latitude', 'longitude');
setupGeocode('city', 'address');
</script>


</body>
</html>