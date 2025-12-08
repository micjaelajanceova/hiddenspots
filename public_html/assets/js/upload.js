 // Upload Modal Functionality
const uploadModal = document.getElementById('uploadModal');
const closeBtn = document.getElementById('closeUploadModal');
const mobileUploadBtn = document.getElementById('mobileUploadBtn');
const uploadForm = document.getElementById('uploadForm');
const photoInput = document.getElementById('photoInput');
const previewImage = document.getElementById('previewImage');
const finalImage = document.getElementById('finalImage');
const photoDataInput = document.getElementById('photoData');


// Wait until DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {


    // isLoggedIn comes from footer.php (global variable)
    if (typeof isLoggedIn !== "undefined") {
        console.log("Login status:", isLoggedIn ? "YES" : "NO");
    }

    // Main index button
    const mainBtn = document.getElementById('openUploadModal');
    mainBtn?.addEventListener('click', e => {
        e.preventDefault();
        if (!isLoggedIn) return window.location.href = '/auth/login.php';
        uploadModal.classList.remove('hidden');
    });

    // Desktop upload button
    document.getElementById('desktopUploadBtn').addEventListener('click', e => {
    e.preventDefault();
    if (!isLoggedIn) {
        window.location.href = '/auth/login.php';
        return;
    }
    document.getElementById('uploadModal').classList.remove('hidden');
    });

    // Mobile upload button
    document.getElementById('mobileUploadBtn')?.addEventListener('click', e => {
    e.preventDefault();
    if (!isLoggedIn) {
        window.location.href = '/auth/login.php';
        return;
    }
    document.getElementById('uploadModal').classList.remove('hidden');
});
});

