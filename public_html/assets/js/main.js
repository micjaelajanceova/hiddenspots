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


// Upload Modal Functionality
const uploadModal = document.getElementById('uploadModal');
const closeBtn = document.getElementById('closeUploadModal');
const mobileUploadBtn = document.getElementById('mobileUploadBtn');
const uploadForm = document.getElementById('uploadForm');
const photoInput = document.getElementById('photoInput');
const previewImage = document.getElementById('previewImage');
const finalImage = document.getElementById('finalImage');
const photoDataInput = document.getElementById('photoData');


// Sidebar Toggle Functionality
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');

toggleBtn.addEventListener('click', () => {
  const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');

    const header = document.getElementById('sidebarHeader');
    if (isCollapsed) {
      header.classList.add('flex-col', 'items-center', 'gap-3');
      header.classList.remove('flex-row', 'justify-between');
    } else {
      header.classList.remove('flex-col', 'items-center', 'gap-3');
      header.classList.add('flex-row', 'justify-between');
    }

    const icon = toggleBtn.querySelector('i');
    if (isCollapsed) {
      icon.classList.remove('ph-caret-left');
      icon.classList.add('ph-caret-right');
    } else {
      icon.classList.remove('ph-caret-right');
      icon.classList.add('ph-caret-left');
    }

    if (isCollapsed) {
      sidebar.classList.remove('w-64', 'p-4');
      sidebar.classList.add('w-16', 'p-2');
      document.querySelectorAll('.sidebar-text').forEach(el => el.classList.add('hidden'));
      document.querySelectorAll('#sidebar nav a').forEach(link => {
        link.classList.remove('justify-start', 'gap-4');
        link.classList.add('justify-center', 'gap-0');
      });
    } else {
      sidebar.classList.remove('w-16', 'p-2');
      sidebar.classList.add('w-64', 'p-4');
      document.querySelectorAll('.sidebar-text').forEach(el => el.classList.remove('hidden'));
      document.querySelectorAll('#sidebar nav a').forEach(link => {
        link.classList.remove('justify-center', 'gap-0');
        link.classList.add('justify-start', 'gap-4');
      });
  }

  document.querySelector('.sidebar-logo-full').classList.toggle('hidden');
  document.querySelector('.sidebar-logo-collapsed').classList.toggle('hidden');
  document.querySelector('.sidebar-upload-text').classList.toggle('hidden');
  document.querySelector('.sidebar-upload-collapsed').classList.toggle('hidden');

 sidebar.addEventListener('transitionend', (e) => {
  if (e.propertyName === 'width' || e.propertyName === 'padding-left') {
    if (typeof initMasonry === 'function') {
      initMasonry();
    } else if (window.masonry) {
      window.masonry.recalculate(true);
    }
  }
});
});


window.addEventListener('DOMContentLoaded', () => {
    // Globálna premenná isLoggedIn z footer.php
    if (typeof isLoggedIn !== 'undefined') {
        console.log('Login status:', isLoggedIn ? 'YES' : 'NO');
    }

    const desktopUploadBtn = document.getElementById('desktopUploadBtn');
    const mobileUploadBtn = document.getElementById('mobileUploadBtn');
    const uploadModal = document.getElementById('uploadModal');

    desktopUploadBtn?.addEventListener('click', e => {
        e.preventDefault(); // zabráni posunu na #
        if (!isLoggedIn) return window.location.href = '/auth/login.php';
        uploadModal.classList.remove('hidden');
    });

    mobileUploadBtn?.addEventListener('click', e => {
        e.preventDefault();
        if (!isLoggedIn) return window.location.href = '/auth/login.php';
        uploadModal.classList.remove('hidden');
    });

    // Close button
    document.getElementById('closeUploadModal')?.addEventListener('click', () => {
        uploadModal.classList.add('hidden');
    });
});
