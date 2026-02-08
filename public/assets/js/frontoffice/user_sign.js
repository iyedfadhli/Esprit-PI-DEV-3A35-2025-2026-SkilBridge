const loginTab = document.getElementById('loginTab');
const registerTab = document.getElementById('registerTab');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');

loginTab.onclick = () => {
    loginTab.classList.add('active');
    registerTab.classList.remove('active');
    loginForm.classList.add('active');
    registerForm.classList.remove('active');
};

registerTab.onclick = () => {
    registerTab.classList.add('active');
    loginTab.classList.remove('active');
    registerForm.classList.add('active');
    loginForm.classList.remove('active');
};


const userType = document.getElementById('userType');
const prenomDiv = document.getElementById('prenomDiv');
const dobDiv = document.getElementById('dobDiv');
const domaineDiv = document.getElementById('domaineDiv');
domaineDiv.style.display = 'none';
prenomDiv.style.display = 'none';
dobDiv.style.display = 'none';

userType.addEventListener('change', () => {
    if (userType.value === 'entreprise') {
        prenomDiv.style.display = 'none';
        dobDiv.style.display = 'none';
        domaineDiv.style.display = 'block';

    } else {
        prenomDiv.style.display = 'block';
        dobDiv.style.display = 'block';
        domaineDiv.style.display = 'none';

    }
});