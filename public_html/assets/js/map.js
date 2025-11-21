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

// ---------- DEBOUNCE ----------
function debounce(fn, delay) {
  let timeout;
  return function(...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), delay);
  };
}

// ---------- MAP ----------
document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('uploadMap');
    if (!mapEl) return;

    uploadMap = L.map(mapEl).setView([55.6761, 12.5683], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(uploadMap);

    uploadMap.on('click', (e) => {
        const { lat, lng } = e.latlng;
        if (uploadMarker) uploadMap.removeLayer(uploadMarker);
        uploadMarker = L.marker([lat, lng]).addTo(uploadMap)
            .bindPopup('Selected location').openPopup();
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    });
});

// ---------- MODAL OPEN/CLOSE ----------
openBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (!isLoggedIn) {
        window.location.href = '/auth/login.php';
        return;
        }

        uploadModal.classList.remove('hidden');
        hideFeedMap();

        // reset
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

// ---------- PHOTO PREVIEW ----------
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

// ---------- NEXT/BACK ----------
document.getElementById('nextBtn').addEventListener('click', () => {
    document.getElementById('stepPreview').classList.add('hidden');
    document.getElementById('stepForm').classList.remove('hidden');
    setTimeout(() => { if (uploadMap) uploadMap.invalidateSize(); }, 100);
});

document.getElementById('backBtn').addEventListener('click', () => {
    document.getElementById('stepForm').classList.add('hidden');
    document.getElementById('stepPreview').classList.remove('hidden');
});

// ---------- GEOCODE ----------
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


