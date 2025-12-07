// ---------- Upload Modal & Map Logic ----------
const uploadModal = document.getElementById('uploadModal');
const closeBtn = document.getElementById('closeUploadModal');
const openBtns = document.querySelectorAll('a[onclick*="uploadModal"], button.ph-plus');
const uploadForm = document.getElementById('uploadForm');
const photoInput = document.getElementById('photoInput');
const previewImage = document.getElementById('previewImage');
const finalImage = document.getElementById('finalImage');
const photoDataInput = document.getElementById('photoData');
const cityInput = document.querySelector('input[name="city"]');
const addressInput = document.querySelector('input[name="address"]');

let uploadMap, uploadMarker;

// ---------- Debounce ----------
function debounce(fn, delay) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), delay);
  };
}

// ---------- Map ----------
document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('uploadMap');
    if (!mapEl) return;

    uploadMap = L.map(mapEl).setView([55.6761, 12.5683], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(uploadMap);

    // Add marker on map click
    uploadMap.on('click', (e) => {
        const { lat, lng } = e.latlng;
        if (uploadMarker) uploadMap.removeLayer(uploadMarker);
        uploadMarker = L.marker([lat, lng]).addTo(uploadMap)
            .bindPopup('Selected location').openPopup();
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    });
});

// ---------- Modal Open/Close ----------
openBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (!isLoggedIn) {
        window.location.href = '/auth/login.php';
        return;
        }

        uploadModal.classList.remove('hidden');
        hideFeedMap();

        // reset form steps and images
        document.getElementById('stepSelect').classList.remove('hidden');
        document.getElementById('stepPreview').classList.add('hidden');
        document.getElementById('stepForm').classList.add('hidden');
        photoInput.value = '';
        previewImage.src = '';
        finalImage.src = '';
        photoDataInput.value = '';

        setTimeout(() => { if (uploadMap) uploadMap.invalidateSize(); }, 200);
    });
});

closeBtn.addEventListener('click', () => {
    uploadModal.classList.add('hidden');
    showFeedMap();
});

// ---------- Photo Preview ----------
photoInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
        previewImage.src = event.target.result;
        finalImage.src = event.target.result;
        photoDataInput.value = event.target.result;

        document.getElementById('stepSelect').classList.add('hidden');
        document.getElementById('stepPreview').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
});

// ---------- Next/Back Navigation ----------
document.getElementById('nextBtn').addEventListener('click', () => {
    document.getElementById('stepPreview').classList.add('hidden');
    document.getElementById('stepForm').classList.remove('hidden');
    setTimeout(() => { if (uploadMap) uploadMap.invalidateSize(); }, 100);
});

document.getElementById('backBtn').addEventListener('click', () => {
    document.getElementById('stepForm').classList.add('hidden');
    document.getElementById('stepPreview').classList.remove('hidden');
});

// ---------- Geocode City Input ----------
if (cityInput) {
    cityInput.addEventListener('input', debounce(async () => {
        const city = cityInput.value.trim();
        if (!city || !uploadMap) return;
        try {
            const res = await fetch(`includes/geocode.php?q=${encodeURIComponent(city)}`);
            const data = await res.json();
            if (data.length) {
                const { lat, lon } = data[0];
                uploadMap.setView([lat, lon], 13);
            }
        } catch(e) { console.error(e); }
    }, 500));
}

// ---------- Geocode Address Input ----------
if (addressInput) {
    addressInput.addEventListener('input', debounce(async () => {
        const addr = addressInput.value.trim();
        if (!addr || !uploadMap) return;
        try {
            const res = await fetch(`includes/geocode.php?q=${encodeURIComponent(addr)}`);
            const data = await res.json();
            if (data.length) {
                const { lat, lon } = data[0];
                uploadMap.setView([lat, lon], 14);
                if (uploadMarker) uploadMap.removeLayer(uploadMarker);
                uploadMarker = L.marker([lat, lon]).addTo(uploadMap)
                    .bindPopup('Selected location').openPopup();
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lon;
            }
        } catch(e) { console.error(e); }
    }, 500));
}


// PROFILE MENU TOGGLE
const profileBtn = document.getElementById('profileBtn');
const profileMenu = document.getElementById('profileMenu');

if (profileBtn && profileMenu) {
  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    profileMenu.classList.toggle('hidden');
  });

  document.addEventListener('click', (e) => {
    if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
      profileMenu.classList.add('hidden');
    }
  });
}
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