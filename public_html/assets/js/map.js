
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
