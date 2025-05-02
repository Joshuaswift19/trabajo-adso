// Mostrar los requisitos de la contraseña
function showRequirements() {
    document.getElementById('password-requirements').style.display = 'block';
}

// Ocultar los requisitos de la contraseña
function hideRequirements() {
    document.getElementById('password-requirements').style.display = 'none';
}

// Validar la contraseña
function validatePassword(passwordInput) {
    const password = passwordInput.value;

    // Validar longitud mínima
    const lengthRequirement = document.getElementById('length-requirement');
    if (password.length >= 10) {
        lengthRequirement.classList.remove('password-invalid');
        lengthRequirement.classList.add('password-valid');
        lengthRequirement.querySelector('i').className = 'bi bi-check icon';
    } else {
        lengthRequirement.classList.remove('password-valid');
        lengthRequirement.classList.add('password-invalid');
        lengthRequirement.querySelector('i').className = 'bi bi-x icon';
    }

    // Validar mayúscula
    const upperRequirement = document.getElementById('uppercase-requirement');
    if (/[A-Z]/.test(password)) {
        upperRequirement.classList.remove('password-invalid');
        upperRequirement.classList.add('password-valid');
        upperRequirement.querySelector('i').className = 'bi bi-check icon';
    } else {
        upperRequirement.classList.remove('password-valid');
        upperRequirement.classList.add('password-invalid');
        upperRequirement.querySelector('i').className = 'bi bi-x icon';
    }

    // Validar número
    const numberRequirement = document.getElementById('number-requirement');
    if (/[0-9]/.test(password)) {
        numberRequirement.classList.remove('password-invalid');
        numberRequirement.classList.add('password-valid');
        numberRequirement.querySelector('i').className = 'bi bi-check icon';
    } else {
        numberRequirement.classList.remove('password-valid');
        numberRequirement.classList.add('password-invalid');
        numberRequirement.querySelector('i').className = 'bi bi-x icon';
    }

    // Validar carácter especial
    const specialRequirement = document.getElementById('special-char-requirement');
    if (/[@$!%*?&]/.test(password)) {
        specialRequirement.classList.remove('password-invalid');
        specialRequirement.classList.add('password-valid');
        specialRequirement.querySelector('i').className = 'bi bi-check icon';
    } else {
        specialRequirement.classList.remove('password-valid');
        specialRequirement.classList.add('password-invalid');
        specialRequirement.querySelector('i').className = 'bi bi-x icon';
    }
}

// Añadir eventos al campo de contraseña
document.addEventListener('DOMContentLoaded', function () {
    // Detectar el campo de contraseña dinámicamente
    const passwordInput = document.querySelector('#password, #new-password'); // Detecta ambos IDs

    if (passwordInput) {
        passwordInput.addEventListener('focus', showRequirements);
        passwordInput.addEventListener('blur', hideRequirements);
        passwordInput.addEventListener('keyup', function () {
            validatePassword(passwordInput);
        });
    }
});