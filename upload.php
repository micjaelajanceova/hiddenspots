<?php include 'header.php'; ?>
<section class="card">
  <h2>Upload a hidden spot</h2>
  <form id="uploadForm" action="upload_process.php" method="post" enctype="multipart/form-data">
    <div class="upload-dropzone" id="dropzone">Drop your photo here or click</div>
    <input type="file" id="photo" name="photo" accept="image/*" hidden>
    
    <label>Name</label>
    <input type="text" name="name" required>
    <label>City</label>
    <input type="text" name="city" required>
    <label>Description</label>
    <textarea name="description" required></textarea>
    
    <button type="submit" class="btn">Upload</button>
  </form>
</section>

<script>
  const dropzone = document.getElementById('dropzone');
  const photoInput = document.getElementById('photo');
  dropzone.onclick = () => photoInput.click();
  dropzone.ondrop = e => {
    e.preventDefault();
    photoInput.files = e.dataTransfer.files;
    dropzone.textContent = photoInput.files[0].name;
  };
  dropzone.ondragover = e => e.preventDefault();
</script>
<?php include 'footer.php'; ?>
