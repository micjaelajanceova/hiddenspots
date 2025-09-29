document.getElementById('year')?.textContent = new Date().getFullYear();
const uploadBtn = document.getElementById('uploadBtn');
const uploadModal = document.getElementById('uploadModal');
const closeModal = document.getElementById('closeModal');

uploadBtn?.addEventListener('click',()=>uploadModal.classList.add('active'));
closeModal?.addEventListener('click',()=>uploadModal.classList.remove('active'));
