<?php
require_once __DIR__ . '/db.php';

// Start session to check logged-in status
require_once __DIR__ . '/../classes/session.php';
$session = new SessionHandle();

// Load SiteSettings class
require_once __DIR__ . '/../classes/sitesettings.php';
$siteSettingsObj = new SiteSettings($pdo);
$siteSettings = $siteSettingsObj->getAll();

$siteDescription = $siteSettings['site_description'] ?? '';
$siteRules       = $siteSettings['rules'] ?? '';
$siteContact     = $siteSettings['contact_info'] ?? '';
$siteColor       = $siteSettings['primary_color'] ?? '';
$siteFont        = $siteSettings['font_family'] ?? 'Arial';
?>

<!----------------------- Footer HTML section ------------------------------>
<footer class="py-10 text-gray-500 text-sm border-t">

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
</body>
<!----------------------- Scripts ------------------------------>
<!-- Images Loaded script -->
<script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>

<!-- Mansonry script -->
<script src="https://cdn.jsdelivr.net/npm/macy@2"></script>

<!-- Logged-in status -->
<script>const isLoggedIn = <?= $session->logged_in() ? 'true' : 'false'; ?>;</script>

<!-- Global script -->
<script src="/assets/js/main.js"></script>


<!-- Map script -->
<script src="/assets/js/map.js" defer></script>
</html>