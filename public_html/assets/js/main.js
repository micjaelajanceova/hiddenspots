document.getElementById('year')?.textContent = new Date().getFullYear();
const uploadBtn = document.getElementById('uploadBtn');
const uploadModal = document.getElementById('uploadModal');
const closeModal = document.getElementById('closeModal');

uploadBtn?.addEventListener('click',()=>uploadModal.classList.add('active'));
closeModal?.addEventListener('click',()=>uploadModal.classList.remove('active'));

// toggle profile menu
const profileBtn = document.getElementById('profileBtn');
const profileMenu = document.getElementById('profileMenu');

profileBtn?.addEventListener('click', () => {
  profileMenu.classList.toggle('hidden');
});

// klik mimo dropdown zatvori menu
document.addEventListener('click', (e) => {
  if(profileMenu && !profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
    profileMenu.classList.add('hidden');
  }
});
