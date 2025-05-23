
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
} else {
    // Header básico si no se encuentra el archivo
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registro - Proyecto</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="text"], input[type="email"], input[type="password"], input[type="date"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            .password-input-container { position: relative; display: flex; align-items: center; }
            .password-input-container input { padding-right: 45px; }
            .password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; font-size: 16px; padding: 5px; border-radius: 3px; transition: color 0.2s, background-color 0.2s; }
            .password-toggle:hover { color: #333; background-color: #f0f0f0; }
            .password-toggle:focus { outline: 2px solid #007bff; outline-offset: 2px; }
            .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
            .btn:hover { background: #0056b3; }
            .checkbox-inline { display: flex; align-items: flex-start; gap: 8px; }
            .checkbox-inline input[type="checkbox"] { width: auto; margin-top: 4px; }
            .mt-3 { margin-top: 20px; }
            a { color: #007bff; text-decoration: none; }
            a:hover { text-decoration: underline; }
            .password-requirements { font-size: 0.85rem; color: #666; margin-top: 5px; }
            .password-requirements ul { margin: 5px 0; padding-left: 20px; }
            .password-strength-container { margin-top: 8px; }
            .password-strength { height: 5px; border-radius: 3px; transition: all 0.3s; background-color: #e9ecef; }
            .password-strength-bar { height: 100%; border-radius: 3px; transition: all 0.3s; width: 0%; }
            .strength-weak .password-strength-bar { background-color: #dc3545; width: 30%; }
            .strength-medium .password-strength-bar { background-color: #ffc107; width: 60%; }
            .strength-strong .password-strength-bar { background-color: #28a745; width: 100%; }
            .password-strength-text { font-size: 0.85rem; margin-top: 5px; font-weight: 500; }
            .strength-weak .password-strength-text { color: #dc3545; }
            .strength-medium .password-strength-text { color: #856404; }
            .strength-strong .password-strength-text { color: #28a745; }
            .requirement { display: flex; align-items: center; gap: 5px; }
            .requirement.met { color: #28a745; }
            .requirement.unmet { color: #dc3545; }
            .requirement i { font-size: 14px; }
            .invalid-feedback { color: #dc3545; font-size: 0.85rem; margin-top: 5px; display: none; }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>';
}
?>

<div class="container">
    <h2>Registrar nuevo usuario</h2>

    <!-- Formulario de registro de usuario -->
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

    <div class="mt-3">
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
    
    // Botones para mostrar/ocultar contraseña
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    
    // Requisitos
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    
    // Función para alternar visibilidad de contraseña
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
    
    // Event listeners para los botones de mostrar/ocultar contraseña
    togglePassword.addEventListener('click', function() {
        togglePasswordVisibility(passwordInput, togglePassword);
    });
    
    toggleConfirmPassword.addEventListener('click', function() {
        togglePasswordVisibility(confirmPasswordInput, toggleConfirmPassword);
    });
    
    // Función para actualizar el estado de un requisito
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
    
    // Función para verificar la fortaleza de la contraseña
    function checkPasswordStrength(password) {
        // Si no hay contraseña, ocultar el indicador
        if (password === '') {
            passwordStrengthContainer.style.display = 'none';
            return false;
        }
        
        // Mostrar el indicador
        passwordStrengthContainer.style.display = 'block';
        
        let strength = 0;
        let hasLength = password.length >= 8;
        let hasUppercase = /[A-Z]/.test(password);
        let hasLowercase = /[a-z]/.test(password);
        let hasNumber = /[0-9]/.test(password);
        let hasSpecial = /[!@#$%^&*]/.test(password);
        
        // Actualizar requisitos visuales
        updateRequirement(reqLength, hasLength);
        updateRequirement(reqUppercase, hasUppercase);
        updateRequirement(reqLowercase, hasLowercase);
        updateRequirement(reqNumber, hasNumber);
        updateRequirement(reqSpecial, hasSpecial);
        
        // Calcular fortaleza
        if (hasLength) strength += 1;
        if (hasUppercase) strength += 1;
        if (hasLowercase) strength += 1;
        if (hasNumber) strength += 1;
        if (hasSpecial) strength += 1;
        
        // Actualizar barra de fortaleza y texto
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
        
        // Devolver si la contraseña es válida
        return hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
    }
    
    // Verificar coincidencia de contraseñas
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
    
    // Validar formulario antes de enviar
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
    
    // Eventos para validación en tiempo real
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
