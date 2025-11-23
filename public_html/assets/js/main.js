// Initialize Macy.js for Masonry layout
function initMasonry() {
  window.masonry = Macy({
    container: '#masonry',
    columns: 4,
    margin: 12,
    breakAt: { 1024: 3, 640: 2, 0: 2 },
    trueOrder: false,
    waitForImages: true
  });

  document.getElementById('masonry').style.display = 'block';
}

window.addEventListener('load', () => {
  initMasonry();
});
