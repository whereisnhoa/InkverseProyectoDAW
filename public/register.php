<?php
// Incluir configuración para obtener la ruta base
require_once(__DIR__ . '/../includes/config.php');

// Incluir header con ruta absoluta
include_once(BASE_PATH . '/includes/header.php');
?>

<div class="container">
    <h2>Registrar nuevo usuario</h2>

    <!-- formulario de registro de usuario -->
    <form action="<?php echo BASE_URL; ?>/controllers/procesar-registro.php" method="POST" id="registro-form">

        <div class="form-group">
            <label for="nombre">Nombre completo:</label>
            <input type="text" name="nombre" id="nombre" required>
        </div>

        <div class="form-group">
            <label for="usuario">Nombre de usuario:</label>
            <input type="text" name="usuario" id="usuario" required>
        </div>

        <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="fecha_nacimiento">Fecha de nacimiento:</label>
            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required>
        </div>

        <div class="form-group">
            <label for="password">Contraseña:</label>
            <div class="password-input-container">
                <input type="password" name="password" id="password" required>
                <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <!-- Indicador de seguridad de la contraseña -->
            <div class="password-strength-meter">
                <div class="strength-meter-bar">
                    <div class="strength-meter-fill" id="strength-meter-fill"></div>
                </div>
                <div class="strength-meter-label" id="strength-meter-label">Nivel de seguridad: No válida</div>
            </div>
            
            <!-- Requisitos de la contraseña -->
            <div class="password-requirements">
                <h4>La contraseña debe cumplir los siguientes requisitos:</h4>
                <ul>
                    <li id="req-length"><i class="fas fa-times-circle"></i> Al menos 8 caracteres</li>
                    <li id="req-uppercase"><i class="fas fa-times-circle"></i> Al menos una letra mayúscula</li>
                    <li id="req-lowercase"><i class="fas fa-times-circle"></i> Al menos una letra minúscula</li>
                    <li id="req-number"><i class="fas fa-times-circle"></i> Al menos un número</li>
                    <li id="req-special"><i class="fas fa-times-circle"></i> Al menos un carácter especial (!@#$%^&*)</li>
                </ul>
            </div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmar contraseña:</label>
            <div class="password-input-container">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <button type="button" class="toggle-password" aria-label="Mostrar contraseña">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div id="password-match-message"></div>
        </div>

        <div class="form-group checkbox-group">
            <label class="checkbox-inline">
                <input type="checkbox" name="acepta_terminos" id="acepta_terminos" required>
                Acepto los <a href="<?php echo BASE_URL; ?>/public/terminos.php" target="_blank">Términos y Condiciones</a> y la <a href="<?php echo BASE_URL; ?>/public/privacidad.php" target="_blank">Política de Privacidad</a>
            </label>
        </div>

        <button type="submit" class="btn" id="submit-btn">Registrarse</button>
    </form>

    <div class="mt-3">
        <p>¿Ya tienes una cuenta? <a href="<?php echo BASE_URL; ?>/public/login.php">Iniciar sesión</a></p>
    </div>
</div>

<!-- Script para validación de contraseña -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthMeterFill = document.getElementById('strength-meter-fill');
    const strengthMeterLabel = document.getElementById('strength-meter-label');
    const passwordMatchMessage = document.getElementById('password-match-message');
    const submitBtn = document.getElementById('submit-btn');
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    const form = document.getElementById('registro-form');
    
    // Requisitos de la contraseña
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    
    // Función para verificar la fortaleza de la contraseña
    function checkPasswordStrength(password) {
        let strength = 0;
        let feedback = [];
        
        // Verificar longitud
        const hasMinLength = password.length >= 8;
        if (hasMinLength) {
            strength += 20;
            reqLength.innerHTML = '<i class="fas fa-check-circle"></i> Al menos 8 caracteres';
            reqLength.classList.add('requirement-met');
        } else {
            reqLength.innerHTML = '<i class="fas fa-times-circle"></i> Al menos 8 caracteres';
            reqLength.classList.remove('requirement-met');
        }
        
        // Verificar mayúsculas
        const hasUppercase = /[A-Z]/.test(password);
        if (hasUppercase) {
            strength += 20;
            reqUppercase.innerHTML = '<i class="fas fa-check-circle"></i> Al menos una letra mayúscula';
            reqUppercase.classList.add('requirement-met');
        } else {
            reqUppercase.innerHTML = '<i class="fas fa-times-circle"></i> Al menos una letra mayúscula';
            reqUppercase.classList.remove('requirement-met');
        }
        
        // Verificar minúsculas
        const hasLowercase = /[a-z]/.test(password);
        if (hasLowercase) {
            strength += 20;
            reqLowercase.innerHTML = '<i class="fas fa-check-circle"></i> Al menos una letra minúscula';
            reqLowercase.classList.add('requirement-met');
        } else {
            reqLowercase.innerHTML = '<i class="fas fa-times-circle"></i> Al menos una letra minúscula';
            reqLowercase.classList.remove('requirement-met');
        }
        
        // Verificar números
        const hasNumber = /[0-9]/.test(password);
        if (hasNumber) {
            strength += 20;
            reqNumber.innerHTML = '<i class="fas fa-check-circle"></i> Al menos un número';
            reqNumber.classList.add('requirement-met');
        } else {
            reqNumber.innerHTML = '<i class="fas fa-times-circle"></i> Al menos un número';
            reqNumber.classList.remove('requirement-met');
        }
        
        // Verificar caracteres especiales
        const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
        if (hasSpecial) {
            strength += 20;
            reqSpecial.innerHTML = '<i class="fas fa-check-circle"></i> Al menos un carácter especial (!@#$%^&*)';
            reqSpecial.classList.add('requirement-met');
        } else {
            reqSpecial.innerHTML = '<i class="fas fa-times-circle"></i> Al menos un carácter especial (!@#$%^&*)';
            reqSpecial.classList.remove('requirement-met');
        }
        
        // Actualizar el medidor de fortaleza
        strengthMeterFill.style.width = strength + '%';
        
        // Determinar el nivel de seguridad
        let level = '';
        let color = '';
        
        if (strength < 40) {
            level = 'Débil';
            color = '#ff4d4d'; // Rojo
        } else if (strength < 80) {
            level = 'Moderada';
            color = '#ffa64d'; // Naranja
        } else {
            level = 'Fuerte';
            color = '#4CAF50'; // Verde
        }
        
        strengthMeterFill.style.backgroundColor = color;
        strengthMeterLabel.textContent = 'Nivel de seguridad: ' + level;
        
        // Verificar si cumple todos los requisitos
        return hasMinLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
    }
    
    // Verificar si las contraseñas coinciden
    function checkPasswordsMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword === '') {
            passwordMatchMessage.textContent = '';
            passwordMatchMessage.className = '';
            return false;
        }
        
        if (password === confirmPassword) {
            passwordMatchMessage.textContent = 'Las contraseñas coinciden';
            passwordMatchMessage.className = 'password-match-success';
            return true;
        } else {
            passwordMatchMessage.textContent = 'Las contraseñas no coinciden';
            passwordMatchMessage.className = 'password-match-error';
            return false;
        }
    }
    
    // Validar el formulario antes de enviar
    function validateForm() {
        const passwordValid = checkPasswordStrength(passwordInput.value);
        const passwordsMatch = checkPasswordsMatch();
        
        return passwordValid && passwordsMatch;
    }
    
    // Eventos para validar la contraseña en tiempo real
    passwordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
        if (confirmPasswordInput.value !== '') {
            checkPasswordsMatch();
        }
    });
    
    confirmPasswordInput.addEventListener('input', checkPasswordsMatch);
    
    // Mostrar/ocultar contraseña
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Validar el formulario antes de enviar
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            alert('Por favor, asegúrate de que la contraseña cumple con todos los requisitos y que ambas contraseñas coinciden.');
        }
    });
});
</script>

<?php include_once(BASE_PATH . '/includes/footer.php'); ?>

