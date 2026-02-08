// Afficher/masquer le mot de passe
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');
if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', () => {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        togglePassword.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
}

// Validation basique du formulaire
const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', function(e) {
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        let isValid = true;
        
        // Réinitialiser les messages d'erreur
        document.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
        
        // Valider username
        if (!username.value.trim()) {
            username.classList.add('is-invalid');
            username.nextElementSibling.style.display = 'block';
            isValid = false;
        }
        
        // Valider password
        if (!password.value.trim()) {
            password.classList.add('is-invalid');
            password.parentElement.nextElementSibling.style.display = 'block';
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

// Focus automatique sur le champ username
document.addEventListener('DOMContentLoaded', function() {
    const usernameField = document.getElementById('username');
    if (usernameField && !usernameField.value) {
        usernameField.focus();
    }
    
    // Empêcher le retour en arrière après soumission
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    
    // Désactiver le bouton après soumission pour éviter les doubles clics
    const submitButton = document.querySelector('button[name="login"]');
    if (submitButton) {
        submitButton.addEventListener('click', function() {
            setTimeout(() => {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري المعالجة...';
            }, 100);
        });
    }
});
