
// Enable/Disable post button based on textarea input
const textarea = document.getElementById('commentText');
const postButton = document.getElementById('postText');

textarea.addEventListener('input', () => {
    if(textarea.value.trim().length > 0){
        postButton.disabled = false;
        postButton.classList.remove('text-gray-400', 'cursor-not-allowed');
        postButton.classList.add('text-black', 'cursor-pointer');
    } else {
        postButton.disabled = true;
        postButton.classList.add('text-gray-400', 'cursor-not-allowed');
        postButton.classList.remove('text-black', 'cursor-pointer');
    }
});


// SPOT VIEW INTERACTIONS
const favBtn = document.getElementById('favBtn');
const favIcon = document.getElementById('favIcon');
const favToast = document.getElementById('favToast');

const likeBtn = document.getElementById('likeBtn');
const likeIcon = document.getElementById('likeIcon');
const likeCount = document.getElementById('likeCount');

const descDiv = document.getElementById("spotDescription");
const saveBtn = document.getElementById("saveDescBtn");
const editMenuBtn = document.getElementById("editDescMenuBtn");
const charCountDiv = document.getElementById("descCharCount");

const MAX_CHARS = 1000;


// FAVOURITE BUTTON
favBtn.addEventListener('click', () => {
  fetch('actions/favourite.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'spot_id=' + spotId
  })
  .then(r => r.text())
  .then(res => {
    if (res === 'not_logged_in') return alert('You must be logged in to favorite!');
    if (res === 'added') {
      favIcon.classList.remove('text-gray-400');
      favIcon.classList.add('text-yellow-500');
      showFavToast("Saved to favourites");
    }
    if (res === 'removed') {
      favIcon.classList.remove('text-yellow-500');
      favIcon.classList.add('text-gray-400');
      showFavToast("Removed from favourites");
    }
  });
});

function showFavToast(message) {
  favToast.querySelector('span').textContent = message;
  favToast.classList.remove('opacity-0');
  favToast.classList.add('opacity-100');
  favToast.classList.add('bg-opacity-20');
  setTimeout(() => {
    favToast.classList.remove('bg-opacity-20');
    favToast.classList.add('bg-opacity-0');
  }, 500);
  setTimeout(() => {
    favToast.classList.remove('opacity-100');
    favToast.classList.add('opacity-0');
  }, 3000);
}


// LIKE BUTTON
likeBtn.addEventListener('click', () => {
  fetch('actions/like.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'spot_id=' + spotId
  })
  .then(res => res.text())
  .then(data => {
    if (data === 'not_logged_in') return alert('You must be logged in to like!');
    if (data === 'liked') {
      likeIcon.classList.remove('text-gray-400');
      likeIcon.classList.add('text-red-600');
    } else if (data === 'unliked') {
      likeIcon.classList.remove('text-red-600');
      likeIcon.classList.add('text-gray-400');
    }

    fetch('actions/like.php?count=' + spotId)
      .then(r => r.text())
      .then(count => { likeCount.textContent = count; });
  });
});



// SPOT DESCRIPTION EDITING
document.addEventListener("DOMContentLoaded", () => {
    if (!editMenuBtn || !descDiv || !saveBtn) return;

    const menu = document.getElementById("spotMenu");

    // Toggle menu
    const menuBtn = document.getElementById("spotMenuBtn");
    if (menuBtn && menu) {
        menuBtn.addEventListener("click", e => {
            e.stopPropagation();
            menu.classList.toggle("hidden");
        });
        document.addEventListener("click", () => menu.classList.add("hidden"));
    }

    // Place caret at the end
    function placeCaretAtEnd(el) {
        el.focus();
        if (typeof window.getSelection !== "undefined" && typeof document.createRange !== "undefined") {
            const range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }

    // Count characters + special Enter = 100
    function getEffectiveCharCount(el) {
        let count = 0;
        el.childNodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE) {
                count += node.textContent.length;
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                const tag = node.tagName.toLowerCase();
                if (tag === 'div' || tag === 'br') count += 99;
                count += node.innerText.length;
            }
        });
        return count;
    }

    // Update character count display
    function updateCharCount() {
        charCountDiv.textContent = `${getEffectiveCharCount(descDiv)} / ${MAX_CHARS} characters`;
    }

    // Activate edit mode
    editMenuBtn.addEventListener("click", () => {
        menu.classList.add("hidden");
        descDiv.contentEditable = "true";
        descDiv.classList.add("bg-gray-100");
        saveBtn.classList.remove("hidden");
        charCountDiv.classList.remove("hidden");
        updateCharCount();
        placeCaretAtEnd(descDiv);
    });

    // Limit input characters
descDiv.addEventListener("beforeinput", (e) => {
    if (e.inputType === "deleteContentBackward" || e.inputType === "deleteContentForward") return;

    let currentCount = getEffectiveCharCount(descDiv);
    let addition = 0;

    if (e.inputType === "insertLineBreak") {
        addition = 100; 
    } else if (e.data) {
        addition = e.data.length;
    }
    

    // BLOCK input if it would meet or exceed MAX_CHARS
    if (currentCount + addition > MAX_CHARS || currentCount + addition === MAX_CHARS) {
        e.preventDefault();
    }
});


descDiv.addEventListener("paste", (e) => {
    e.preventDefault(); // prevent the default paste

    const pasteData = (e.clipboardData || window.clipboardData).getData('text');
    const currentCount = getEffectiveCharCount(descDiv);

    // How many characters can still be added
    const allowed = Math.max(0, MAX_CHARS - currentCount);

    // If no characters are allowed, do nothing
    if (allowed <= 0) return;

    // Insert only the allowed portion
    const textToInsert = pasteData.substring(0, allowed);
    document.execCommand('insertText', false, textToInsert);
});



    // Update char count on every input
    descDiv.addEventListener("input", updateCharCount);

    // Save description
    saveBtn.addEventListener("click", () => {
        const newDesc = descDiv.innerText.trim();
        const spotId = descDiv.dataset.spotId;

        fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `edit_spot_id=${encodeURIComponent(spotId)}&edit_description=${encodeURIComponent(newDesc)}`
        })
        .then(() => {
            descDiv.contentEditable = "false";
            descDiv.classList.remove("bg-gray-100");
            saveBtn.classList.add("hidden");
            charCountDiv.classList.add("hidden");

            const toast = document.createElement("div");
            toast.textContent = "Description updated successfully ✅";
            toast.className = "fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow";
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2500);
        });
    });
});



// MAP TOGGLE
const mapBtn = document.getElementById('showMap');
const mapDiv = document.getElementById('feedMap');
let feedMap; 

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


    // --- CLOSE MAP WHEN CLICKING PROFILE HEADER OR MENU ---
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


const spotAddress = "<?= $spot['address'] ?? '' ?>";

// MAP
const cityMapBtn = document.getElementById('showCityMapBtn');
const cityMapDiv = document.getElementById('cityMap');
let cityMap; 

cityMapBtn.addEventListener('click', () => {
    const mapArrow = document.getElementById('mapArrow');
    const isHidden = cityMapDiv.style.display === 'none';
    
    cityMapDiv.style.display = isHidden ? 'block' : 'none';
    
    if (isHidden) {
        mapArrow.style.transform = 'rotate(180deg)'; // šípka hore
        setTimeout(() => {
            if (!cityMap) initCityMap();
            else cityMap.invalidateSize();
        }, 100);
    } else {
        mapArrow.style.transform = 'rotate(0deg)'; // šípka dole
    }
});

function initCityMap() {

    if(lat === 0 && lng === 0) {
        alert('Coordinates not available for this spot.');
        return;
    }

    cityMap = L.map('cityMap').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(cityMap);

    L.marker([lat, lng]).addTo(cityMap)
    .bindPopup(`
    <b><?= addslashes($spot['name']) ?></b><br>
    <?php if (!empty($spot['address'])): ?>
        <?= addslashes($spot['address']) ?>
    <?php else: ?>
        <?= $spot['latitude'] . ', ' . $spot['longitude'] ?>
    <?php endif; ?>
`)
.openPopup();
}
