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

if (file_exists($header_file)) include_once($header_file);
?>

<!-- estilo -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="auth-container">
  <h2>Registrar nuevo usuario</h2>
  <form action="<?php echo BASE_URL; ?>/controllers/procesar-registro.php" method="POST" id="registroForm" novalidate>
    <div class="form-group">
      <label for="nombre">Nombre completo</label>
      <input type="text" name="nombre" id="nombre" required>
    </div>
    <div class="form-group">
      <label for="usuario">Nombre de usuario</label>
      <input type="text" name="usuario" id="usuario" required>
    </div>
    <div class="form-group">
      <label for="email">Correo electrónico</label>
      <input type="email" name="email" id="email" required>
    </div>
    <div class="form-group">
      <label for="fecha_nacimiento">Fecha de nacimiento</label>
      <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required>
    </div>

      <div class="form-group password-input-container" style="position: relative;">
      <label for="password">Contraseña</label>
      <input type="password" name="password" id="password" required>
      <button type="button" class="password-toggle" id="togglePassword" tabindex="0" style="position: absolute; right: 16px; top: 47%; transform: translateY(-50%); z-index: 10; background: none; border: none; cursor: pointer;">
        <i class="fas fa-eye"></i>
      </button>
    </div>

    <div class="password-strength-container" id="passwordStrengthContainer" style="display: none;">
      <div class="password-strength" id="passwordStrength">
        <div class="password-strength-bar" id="passwordStrengthBar"></div>
      </div>
      <div class="password-strength-text" id="passwordStrengthText"></div>
    </div>
    <div class="password-requirements mt-2">
      <p class="mb-1">La contraseña debe cumplir los siguientes requisitos:</p>
      <ul class="mb-0 small">
        <li id="req-length" class="requirement unmet"><i class="fas fa-times-circle"></i> Al menos 8 caracteres</li>
        <li id="req-uppercase" class="requirement unmet"><i class="fas fa-times-circle"></i> Al menos una letra mayúscula</li>
        <li id="req-lowercase" class="requirement unmet"><i class="fas fa-times-circle"></i> Al menos una letra minúscula</li>
        <li id="req-number" class="requirement unmet"><i class="fas fa-times-circle"></i> Al menos un número</li>
        <li id="req-special" class="requirement unmet"><i class="fas fa-times-circle"></i> Al menos un carácter especial (!@#$%^&*)</li>
      </ul>
    </div>
    <div class="form-group password-input-container">
      <label for="confirm_password">Confirmar contraseña</label>
      <input type="password" name="confirm_password" id="confirm_password" required>
      <button type="button" class="password-toggle" id="toggleConfirmPassword" tabindex="-1">
        <i class="fas fa-eye"></i>
      </button>
      <div class="invalid-feedback" id="password-match-error" style="display:none;">Las contraseñas no coinciden</div>
    </div>

    <!-- terminos -->
   <div class="form-group terms-group">
  <label class="custom-checkbox" for="acepto" style="width: 100%;">
    <input type="checkbox" name="acepto" id="acepto" required>
    <span class="checkmark"></span>
    <span>
      Acepto los
      <a href="terminos.php" target="_blank"><b>Términos y Condiciones</b></a>
      y la
      <a href="privacidad.php" target="_blank"><b>Política de Privacidad</b></a>
    </span>
  </label>
</div>

    <button type="submit" class="btn" id="submitBtn">Registrarse</button>

  </form>
  <div class="mt-3 text-center">
    <p>¿Ya tienes una cuenta? <a href="<?php echo BASE_URL; ?>/public/login.php">Iniciar sesión</a></p>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

  const passwordInput = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
  const passwordStrengthContainer = document.getElementById('passwordStrengthContainer');
  const passwordStrength = document.getElementById('passwordStrength');
  const passwordStrengthBar = document.getElementById('passwordStrengthBar');
  const passwordStrengthText = document.getElementById('passwordStrengthText');
  const passwordMatchError = document.getElementById('password-match-error');
  const reqLength = document.getElementById('req-length');
  const reqUppercase = document.getElementById('req-uppercase');
  const reqLowercase = document.getElementById('req-lowercase');
  const reqNumber = document.getElementById('req-number');
  const reqSpecial = document.getElementById('req-special');
  const submitBtn = document.getElementById('submitBtn');
  const form = document.getElementById('registroForm');

  if(confirmPasswordInput && toggleConfirmPassword) {
    toggleConfirmPassword.addEventListener('click', function() {
      const icon = toggleConfirmPassword.querySelector('i');
      if (confirmPasswordInput.type === 'password') {
        confirmPasswordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        toggleConfirmPassword.setAttribute('aria-label', 'Ocultar contraseña');
      } else {
        confirmPasswordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        toggleConfirmPassword.setAttribute('aria-label', 'Mostrar contraseña');
      }
    });
  }

  // Validación y requisitos de contraseña
  function updateRequirement(element, isMet) {
    if (!element) return;
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
    if (!passwordStrengthContainer || !passwordStrength || !passwordStrengthBar || !passwordStrengthText) return true;
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
    passwordStrengthBar.style.width = strength * 20 + '%';
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
    if (!passwordMatchError) return true;
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

  if(passwordInput) {
    passwordInput.addEventListener('input', function() {
      checkPasswordStrength(this.value);
      if (confirmPasswordInput && confirmPasswordInput.value !== '') {
        checkPasswordMatch();
      }
    });
  }
  if(confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', function() {
      checkPasswordMatch();
    });
  }

  if(form) {
  form.addEventListener('submit', function(e) {
    const terminos = document.getElementById('acepto');
    if (terminos && !terminos.checked) {
      e.preventDefault();
      alert('Debes aceptar los términos y condiciones para registrarte.');
      return;
    }
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
}
});
</script>

<?php if (file_exists($footer_file)) include_once($footer_file); else echo '</body></html>'; ?>