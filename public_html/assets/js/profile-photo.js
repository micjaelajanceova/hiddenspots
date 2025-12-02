// JS for profile photo upload/remove
document.addEventListener('DOMContentLoaded', () => {
  const photoInput = document.getElementById('photoInput');
  const changePhotoBtn = document.getElementById('changePhotoBtn');
  const removePhotoBtn = document.getElementById('removePhotoBtn');
  const photoPreview = document.getElementById('photoPreview');
  const photoLetter = document.getElementById('photoLetter');
  const uploadStatus = document.getElementById('uploadStatus');

  // Open file picker
  changePhotoBtn.addEventListener('click', (e) => {
    e.preventDefault();
    photoInput.click();
  });

  // Upload photo
  photoInput.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (ev) => {
      photoPreview.src = ev.target.result;
      photoPreview.classList.remove('hidden');
      if(photoLetter) photoLetter.style.display = 'none';
    };
    reader.readAsDataURL(file);

    uploadStatus.classList.remove('hidden');
    uploadStatus.textContent = 'Uploading...';

    const fd = new FormData();
    fd.append('profile_photo', file);

    try {
      const res = await fetch(window.location.href, { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
          photoPreview.src = '../' + data.path;
          uploadStatus.textContent = 'Photo saved!';
          uploadStatus.classList.remove('hidden');
          uploadStatus.classList.remove('text-red-600'); 
          uploadStatus.classList.add('text-green-600');  
          setTimeout(() => uploadStatus.classList.add('hidden'), 2000);
      } else {
          uploadStatus.textContent = 'Error: ' + (data.message || 'Upload failed');
          uploadStatus.classList.remove('text-green-600');
          uploadStatus.classList.add('text-red-600');
          setTimeout(() => uploadStatus.classList.add('hidden'), 2000);
      }
      } catch (err) {
        uploadStatus.textContent = 'Upload failed.';
        setTimeout(() => uploadStatus.classList.add('hidden'), 2000);
    }
  });

  // Remove photo
  removePhotoBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!confirm('Remove profile photo?')) return;

    try {
      const res = await fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'remove_photo=1'
      });
      const data = await res.json();
      if (data.success) {
        photoPreview.src = '';
        photoPreview.classList.add('hidden');
        if(photoLetter) photoLetter.style.display = 'block';
      } else {
        alert('Could not remove photo.');
      }
    } catch (err) {
      alert('Could not remove photo.');
    }
  });
});