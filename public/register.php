
<?php
// Iniciar sesión
session_start();

// Determinar la ruta base del proyecto de forma más robusta
$document_root = $_SERVER['DOCUMENT_ROOT'];
$current_file = str_replace('\\', '/', __FILE__);
$document_root = str_replace('\\', '/', $document_root);

// Encontrar la posición de 'public' en la ruta actual
$public_pos = strrpos($current_file, '/public/');
if ($public_pos !== false) {
    $project_path = substr($current_file, 0, $public_pos);
} else {
    // Fallback: asumir que estamos en la carpeta public
    $project_path = dirname(dirname(__FILE__));
}

// Calcular la ruta relativa para BASE_URL
$relative_path = str_replace($document_root, '', $project_path);

// Definir BASE_URL si no está definido
if (!defined('BASE_URL')) {
    define('BASE_URL', $relative_path);
}

// Verificar que los archivos existan antes de incluirlos
$header_file = $project_path . '/includes/header.php';
$footer_file = $project_path . '/includes/footer.php';

if (file_exists($header_file)) {
    include_once($header_file);
} 

?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<body class="auth-bg">
<div class="auth-container">
    <h2>Registrar nuevo usuario</h2>
    <form action="<?php echo BASE_URL; ?>/controllers/procesar-registro.php" method="POST" id="registroForm">
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
                <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar contraseña">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="password-strength-container" id="passwordStrengthContainer" style="display: none;">
                <div class="password-strength" id="passwordStrength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <div class="password-strength-text" id="passwordStrengthText"></div>
            </div>
            <div class="password-requirements">
                <p>La contraseña debe cumplir los siguientes requisitos:</p>
                <ul>
                    <li id="req-length" class="requirement unmet">
                        <i class="fas fa-times-circle"></i> Al menos 8 caracteres
                    </li>
                    <li id="req-uppercase" class="requirement unmet">
                        <i class="fas fa-times-circle"></i> Al menos una letra mayúscula
                    </li>
                    <li id="req-lowercase" class="requirement unmet">
                        <i class="fas fa-times-circle"></i> Al menos una letra minúscula
                    </li>
                    <li id="req-number" class="requirement unmet">
                        <i class="fas fa-times-circle"></i> Al menos un número
                    </li>
                    <li id="req-special" class="requirement unmet">
                        <i class="fas fa-times-circle"></i> Al menos un carácter especial (!@#$%^&*)
                    </li>
                </ul>
            </div>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirmar contraseña:</label>
            <div class="password-input-container">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Mostrar contraseña de confirmación">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <div class="invalid-feedback" id="password-match-error">Las contraseñas no coinciden</div>
        </div>
        <div class="form-group">
            <label class="checkbox-inline">
                <input type="checkbox" name="acepta_terminos" id="acepta_terminos" required>
                Acepto los <a href="<?php echo BASE_URL; ?>/public/terminos.php" target="_blank">Términos y Condiciones</a> y la <a href="<?php echo BASE_URL; ?>/public/privacidad.php" target="_blank">Política de Privacidad</a>
            </label>
        </div>
        <button type="submit" class="btn" id="submitBtn">Registrarse</button>
    </form>
    <div class="mt-3 text-center">
        <p>¿Ya tienes una cuenta? <a href="<?php echo BASE_URL; ?>/public/login.php">Iniciar sesión</a></p>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrengthContainer = document.getElementById('passwordStrengthContainer');
    const passwordStrength = document.getElementById('passwordStrength');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const passwordStrengthText = document.getElementById('passwordStrengthText');
    const passwordMatchError = document.getElementById('password-match-error');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('registroForm');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');

    function togglePasswordVisibility(inputField, toggleButton) {
        const icon = toggleButton.querySelector('i');
        if (inputField.type === 'password') {
            inputField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            toggleButton.setAttribute('aria-label', 'Ocultar contraseña');
        } else {
            inputField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            toggleButton.setAttribute('aria-label', 'Mostrar contraseña');
        }
    }
    togglePassword.addEventListener('click', function() {
        togglePasswordVisibility(passwordInput, togglePassword);
    });
    toggleConfirmPassword.addEventListener('click', function() {
        togglePasswordVisibility(confirmPasswordInput, toggleConfirmPassword);
    });

    function updateRequirement(element, isMet) {
        if (isMet) {
            element.classList.remove('unmet');
            element.classList.add('met');
            element.querySelector('i').className = 'fas fa-check-circle';
        } else {
            element.classList.remove('met');
            element.classList.add('unmet');
            element.querySelector('i').className = 'fas fa-times-circle';
        }
    }
    function checkPasswordStrength(password) {
        if (password === '') {
            passwordStrengthContainer.style.display = 'none';
            return false;
        }
        passwordStrengthContainer.style.display = 'block';
        let strength = 0;
        let hasLength = password.length >= 8;
        let hasUppercase = /[A-Z]/.test(password);
        let hasLowercase = /[a-z]/.test(password);
        let hasNumber = /[0-9]/.test(password);
        let hasSpecial = /[!@#$%^&*]/.test(password);
        updateRequirement(reqLength, hasLength);
        updateRequirement(reqUppercase, hasUppercase);
        updateRequirement(reqLowercase, hasLowercase);
        updateRequirement(reqNumber, hasNumber);
        updateRequirement(reqSpecial, hasSpecial);
        if (hasLength) strength += 1;
        if (hasUppercase) strength += 1;
        if (hasLowercase) strength += 1;
        if (hasNumber) strength += 1;
        if (hasSpecial) strength += 1;
        passwordStrength.className = 'password-strength';
        if (strength < 3) {
            passwordStrength.classList.add('strength-weak');
            passwordStrengthText.textContent = 'Contraseña débil';
        } else if (strength < 5) {
            passwordStrength.classList.add('strength-medium');
            passwordStrengthText.textContent = 'Contraseña moderada';
        } else {
            passwordStrength.classList.add('strength-strong');
            passwordStrengthText.textContent = 'Contraseña fuerte';
        }
        return hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
    }
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        if (confirmPassword === '') {
            passwordMatchError.style.display = 'none';
            return true;
        }
        if (password === confirmPassword) {
            passwordMatchError.style.display = 'none';
            return true;
        } else {
            passwordMatchError.style.display = 'block';
            return false;
        }
    }
    form.addEventListener('submit', function(e) {
        const isPasswordValid = checkPasswordStrength(passwordInput.value);
        const doPasswordsMatch = checkPasswordMatch();
        if (!isPasswordValid) {
            e.preventDefault();
            alert('La contraseña debe cumplir todos los requisitos de seguridad.');
            return;
        }
        if (!doPasswordsMatch) {
            e.preventDefault();
            alert('Las contraseñas no coinciden.');
            return;
        }
    });
    passwordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
        if (confirmPasswordInput.value !== '') {
            checkPasswordMatch();
        }
    });
    confirmPasswordInput.addEventListener('input', function() {
        checkPasswordMatch();
    });
});
</script>

<?php 
if (file_exists($footer_file)) {
    include_once($footer_file);
} else {
    echo '</body></html>';
}
?>
