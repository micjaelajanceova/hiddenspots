<footer class="text-center py-6 text-gray-500 text-sm">
    © <?= date('Y') ?> Hidden Spots — All rights reserved.
</footer>
</div>

<!-- Mansonry script -->
<script src="https://cdn.jsdelivr.net/npm/macy@2"></script>

<!-- Global script -->
<script src="/assets/js/main.js"></script>

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