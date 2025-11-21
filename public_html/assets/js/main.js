// Initialize Macy.js for Masonry layout
window.addEventListener('load', () => {
  const masonry = Macy({
    container: '#masonry',
    columns: 4,
    margin: 12,
    breakAt: { 1024: 3, 640: 2, 0: 1 },
    trueOrder: false,
    waitForImages: true
  });

  masonry.recalculate(true);
  document.getElementById('masonry').style.display = 'block';
});

// Form submit used in header and index.php
uploadForm.addEventListener('submit', e => {
    const lat = latitudeInput.value.trim();
    const lng = longitudeInput.value.trim();
    const address = addressInput.value.trim();

    if ((!lat || !lng) && !address) {
        e.preventDefault();
        alert('Please select a location either by entering an address or clicking on the map.');
    }
});