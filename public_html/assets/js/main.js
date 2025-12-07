// Initialize Macy.js for Masonry layout
function initMasonry() {
  window.masonry = Macy({
    container: '#masonry',
    columns: 4,
    margin: 12,
    breakAt: { 1024: 3, 640: 2, 0: 1 },
    trueOrder: false,
    waitForImages: true
  });

  // Show the masonry container after initialization
  document.getElementById('masonry').style.display = 'block';
}

// Run the masonry layout when page loads
window.addEventListener('load', () => {
  initMasonry();
});



// Handle mutual exclusivity of profile menu and city map
(function () {

  function isVisible(el) {
    return !!el && window.getComputedStyle(el).display !== 'none' && el.offsetParent !== null;
  }


  document.addEventListener('click', function (e) {
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    const cityMap = document.getElementById('cityMap');
    const showCityMapBtn = document.getElementById('showCityMapBtn');

    const clickedProfileBtn = profileBtn && profileBtn.contains(e.target);
    const clickedProfileMenu = profileMenu && profileMenu.contains(e.target);

    const clickedMap = cityMap && cityMap.contains(e.target);
    const clickedMapBtn = showCityMapBtn && showCityMapBtn.contains(e.target);

    // --- BEHAVIOUR:
    // 1) If user clicked profileBtn or profileMenu -> close the map (if open)
    if (clickedProfileBtn || clickedProfileMenu) {
      if (cityMap && isVisible(cityMap)) {
        cityMap.style.display = 'none';
      }
      // Let existing profile toggle logic run (do not stop propagation)
      return;
    }

    // 2) If user clicked map or mapBtn -> close profile menu (if open)
    if (clickedMap || clickedMapBtn) {
      if (profileMenu && !profileMenu.classList.contains('hidden')) {
        profileMenu.classList.add('hidden');
      }
      // If click was on mapBtn itself, also toggle the map (existing map button handler may do that)
      // We don't stopPropagation so existing handlers work.
      return;
    }

  }, true); // use capture phase to react early
})();
