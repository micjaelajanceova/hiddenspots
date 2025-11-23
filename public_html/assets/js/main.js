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


