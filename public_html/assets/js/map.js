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
if (openBtns && openBtns.length > 0 && uploadModal) {
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
}

if (closeBtn && uploadModal) {
    closeBtn.addEventListener('click', () => {
        uploadModal.classList.add('hidden');
        showFeedMap();
    });
}


// ---------- Photo Preview ----------
if (photoInput) {
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
}
// ---------- Next/Back Navigation ----------
const nextBtn = document.getElementById('nextBtn');
const backBtn = document.getElementById('backBtn');
if (nextBtn) {
document.getElementById('nextBtn').addEventListener('click', () => {
    document.getElementById('stepPreview').classList.add('hidden');
    document.getElementById('stepForm').classList.remove('hidden');
    setTimeout(() => { if (uploadMap) uploadMap.invalidateSize(); }, 100);
});
}
if (backBtn) {
document.getElementById('backBtn').addEventListener('click', () => {
    document.getElementById('stepForm').classList.add('hidden');
    document.getElementById('stepPreview').classList.remove('hidden');
});
}
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

// ---------- Feed Map Toggle ----------
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('filterBtn');
  const dropdown = document.getElementById('filterDropdown');
  const mapBtn = document.getElementById('showMap');
  const mapDiv = document.getElementById('feedMap');

  if (btn && dropdown && mapDiv) {
    btn.addEventListener('click', e => {
      e.preventDefault();
      e.stopPropagation();
      dropdown.classList.toggle('hidden');


      if (!dropdown.classList.contains('hidden') && mapDiv.style.display === 'block') {
        mapDiv.style.display = 'none';
      }
    });


    document.addEventListener('click', () => {
      dropdown.classList.add('hidden');
    });
  }
});


// MAP TOGGLE
const mapBtn = document.getElementById('showMap');
const mapDiv = document.getElementById('feedMap');
let feedMap; 

if (mapBtn && mapDiv) {
mapBtn.addEventListener('click', () => {
   
    // toggle map
    const isHidden = mapDiv.style.display === 'none';
    mapDiv.style.display = isHidden ? 'block' : 'none';

    // rotate arrow ▼
    const arrow = document.getElementById('feedMapArrow');
    if (arrow) {
        arrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    if (mapDiv.style.display === 'block') {
        setTimeout(() => {
            if (!feedMap) initFeedMap(); 
            else feedMap.invalidateSize();
        }, 100);
    }
});
}

    // --- CLOSE MAP WHEN CLICKING PROFILE HEADER OR MENU ---
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');

    if(profileBtn && mapDiv){
        profileBtn.addEventListener('click', () => {
            if(mapDiv.style.display === 'block'){
                mapDiv.style.display = 'none';
            }
        });
    }
    if(profileMenu && mapDiv){
        profileMenu.addEventListener('click', () => {
            if(mapDiv.style.display === 'block'){
                mapDiv.style.display = 'none';
            }
        });
    }


function initFeedMap() {
    
    let mapCenter = [55.6761, 12.5683]; // default Copenhagen
    const firstSpot = spots.find(s => s.latitude && s.longitude);
    if(firstSpot) mapCenter = [parseFloat(firstSpot.latitude), parseFloat(firstSpot.longitude)];

    feedMap = L.map('feedMap').setView(mapCenter, 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(feedMap);

 
    spots.forEach(spot => {
    const lat = parseFloat(spot.latitude);
    const lng = parseFloat(spot.longitude);
    if(!isNaN(lat) && !isNaN(lng)) {
        const marker = L.marker([lat, lng]).addTo(feedMap);
        const displayAddress = spot.address ? spot.address : `${lat.toFixed(5)},  ${lng.toFixed(5)}`;

        const popupContent = `
            <div style="text-align:center; max-width:200px;">
                <img src="${spot.file_path}" 
                    alt="${spot.name}" 
                    style="width:100%; height:120px; object-fit:cover; border-radius:6px; margin-bottom:5px;" />
                <b><a href="spot-view.php?id=${spot.id}"
                      style="color:#1d4ed8; text-decoration:none;">
                    ${spot.name}
                </a></b><br>
                ${displayAddress}<br>
                <small>@${spot.user_name}</small>
            </div>`;

        marker.bindPopup(popupContent);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const cityMapBtn = document.getElementById('showCityMapBtn');
    const cityMapDiv = document.getElementById('cityMap');
    let cityMap;

    cityMapBtn.addEventListener('click', () => {
        const mapArrow = document.getElementById('mapArrow');
        const isHidden = cityMapDiv.style.display === 'none';

        cityMapDiv.style.display = isHidden ? 'block' : 'none';

        if (isHidden) {
            mapArrow.style.transform = 'rotate(180deg)';
            setTimeout(() => {
                if (!cityMap) initCityMap();
                else cityMap.invalidateSize();
            }, 100);
        } else {
            mapArrow.style.transform = 'rotate(0deg)';
        }
    });

    function initCityMap() {
        const lat = spotData.lat ?? 0;
        const lng = spotData.lng ?? 0;

        if (lat === 0 && lng === 0) {
            alert('Coordinates not available for this spot.');
            return;
        }

        cityMap = L.map('cityMap').setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(cityMap);

        L.marker([lat, lng]).addTo(cityMap)
          .bindPopup(`<b>${spotData.name}</b><br>${spotData.address || (lat + ', ' + lng)}`)
          .openPopup();
    }
});
