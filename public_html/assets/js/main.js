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


// Tab navigation for admin panel
  function showTab(tabId){

  document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));

  document.getElementById(tabId).classList.remove('hidden');

  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('bg-black', 'text-white');
    btn.classList.add('bg-gray-200', 'text-gray-800');
  });

  const activeBtn = document.getElementById('tab-' + tabId);
  activeBtn.classList.remove('bg-gray-200', 'text-gray-800');
  activeBtn.classList.add('bg-black', 'text-white');
}

if (document.getElementById('site')) {
  showTab('site');
}


