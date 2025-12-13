// Sidebar Toggle Functionality
console.log('TOGGLE JS RUNNING');
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');
if (!sidebar || !toggleBtn); 
toggleBtn.addEventListener('click', () => {
    // Toggle collapsed class
  const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');

    // Adjust header layout
    const header = document.getElementById('sidebarHeader');
    if (header) {
    if (isCollapsed) {
      header.classList.add('flex-col', 'items-center', 'gap-3');
      header.classList.remove('flex-row', 'justify-between');
    } else {
      header.classList.remove('flex-col', 'items-center', 'gap-3');
      header.classList.add('flex-row', 'justify-between');
    } 
  }
  
    // Change toggle icon
    const icon = toggleBtn.querySelector('i');
    if (isCollapsed) {
      icon.classList.remove('ph-caret-left');
      icon.classList.add('ph-caret-right');
    } else {
      icon.classList.remove('ph-caret-right');
      icon.classList.add('ph-caret-left');
    }

    // Adjust sidebar width and padding
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

  // Toggle logos and upload text
  document.querySelector('.sidebar-logo-full')?.classList.toggle('hidden');
  document.querySelector('.sidebar-logo-collapsed')?.classList.toggle('hidden');
  document.querySelector('.sidebar-upload-text')?.classList.toggle('hidden');
  document.querySelector('.sidebar-upload-collapsed')?.classList.toggle('hidden');

  // Recalculate Masonry layout after transition
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
