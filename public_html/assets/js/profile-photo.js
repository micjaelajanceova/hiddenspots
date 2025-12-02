// JS for profile photo upload/remove
document.addEventListener('DOMContentLoaded', () => {
  const photoInput = document.getElementById('photoInput');
  const changePhotoBtn = document.getElementById('changePhotoBtn');
  const removePhotoBtn = document.getElementById('removePhotoBtn');
  const photoPreview = document.getElementById('photoPreview');
  const photoLetter = document.getElementById('photoLetter');
  const uploadStatus = document.getElementById('uploadStatus');

  // Open file picker when "Change Photo" clicked
  changePhotoBtn.addEventListener('click', (e) => {
    e.preventDefault();
    photoInput.click();
  });

  // Handle photo selection and upload
  photoInput.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    // Preview image on page
    const reader = new FileReader();
    reader.onload = (ev) => {
      photoPreview.src = ev.target.result;
      photoPreview.classList.remove('hidden');
      if(photoLetter) photoLetter.style.display = 'none';
    };
    reader.readAsDataURL(file);

    // Show uploading message
    uploadStatus.classList.remove('hidden');
    uploadStatus.textContent = 'Uploading...';

     // Prepare file to send via AJAX (FormData)
    const fd = new FormData();
    fd.append('profile_photo', file);

    try {
      // Send file to PHP script
      const res = await fetch('../actions/profile-photo.php', { method: 'POST', body: fd });
      const data = await res.json();
      // Update preview to saved file
      if (data.success) {
          photoPreview.src = '../' + data.path;
          uploadStatus.textContent = 'Photo saved!';
          uploadStatus.classList.remove('hidden');
          uploadStatus.classList.remove('text-red-600'); 
          uploadStatus.classList.add('text-green-600');  
          setTimeout(() => uploadStatus.classList.add('hidden'), 2000);
      } else {
        // Show error message
          uploadStatus.textContent = 'Error: ' + (data.message || 'Upload failed');
          uploadStatus.classList.remove('text-green-600');
          uploadStatus.classList.add('text-red-600');
          setTimeout(() => uploadStatus.classList.add('hidden'), 2000);
      }
      } catch (err) {
        // Handle network or other errors
        uploadStatus.textContent = 'Upload failed.';
        setTimeout(() => uploadStatus.classList.add('hidden'), 2000);
    }
  });

  // Remove profile photo
  removePhotoBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!confirm('Remove profile photo?')) return; // ask for confirmation

    const fd = new FormData();
    fd.append('remove_photo', '1');

    try {
      // Send AJAX request to remove photo
      const res = await fetch('../actions/profile-photo.php', {
      method: 'POST',
      body: fd
    });
      const data = await res.json();
      if (data.success) {
        // hide photo and show initials
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