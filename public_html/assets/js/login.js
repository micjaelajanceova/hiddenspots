// ------------------ Toggle between Login and Register forms ------------------
const showRegister = document.getElementById('showRegister');
const showLogin = document.getElementById('showLogin');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');

// When "Register" button clicked, hide login form and show register form
showRegister?.addEventListener('click', () => {
    loginForm.classList.add('hidden');
    registerForm.classList.remove('hidden');
});

// When "Login" button clicked, hide register form and show login form
showLogin?.addEventListener('click', () => {
    registerForm.classList.add('hidden');
    loginForm.classList.remove('hidden');
});

// ------------------ Decide which form to show on page load ------------------

// Container holding data attributes for messages and success flag
const loginContainer = document.getElementById('loginContainer');
const msg = loginContainer.getAttribute('data-msg');
const success = loginContainer.getAttribute('data-success') === 'true';

// Logic to decide which form to show
if (success) {
    // If last action succeeded, show login form
    loginForm.classList.remove('hidden');
    registerForm.classList.add('hidden');
} else if (
    // If there are validation errors from registration
    msg.includes('Email already exists') ||
    msg.includes('Passwords do not match') ||
    msg.includes('Password') ||   
    msg.includes('Username cannot') ||       
    msg.includes('Username already exists') 
) {
    // Show registration form with error message
    loginForm.classList.add('hidden');
    registerForm.classList.remove('hidden');
} else {
    // Check URL query for action
    const params = new URLSearchParams(window.location.search);
    const action = params.get('action');

    if (action === 'register') {
        loginForm.classList.add('hidden');
        registerForm.classList.remove('hidden');
    } else {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
    }
}

// ------------------ Background slideshow for login page ------------------
const slides = document.querySelectorAll('.bg-slide');
let current = 0;
slides[current].style.opacity = 1;

setInterval(() => {
    slides[current].style.opacity = 0;
    current = (current + 1) % slides.length;
    slides[current].style.opacity = 1;
}, 5000); 