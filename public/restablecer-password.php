<?php
session_start();
$document_root = $_SERVER['DOCUMENT_ROOT'];
$current_file = str_replace('\\', '/', __FILE__);
$document_root = str_replace('\\', '/', $document_root);

$public_pos = strrpos($current_file, '/public/');
if ($public_pos !== false) {
    $project_path = substr($current_file, 0, $public_pos);
} else {
    $project_path = dirname(dirname(__FILE__));
}
$relative_path = str_replace($document_root, '', $project_path);
if (!defined('BASE_URL')) {
    define('BASE_URL', $relative_path);
}

$header_file = $project_path . '/includes/header.php';
if (file_exists($header_file)) include_once($header_file);

require_once($project_path . '/includes/conexion.php');

$mensaje_exito = null;
$mensaje_error = null;

// Obtener token y email de la URL
$token = isset($_GET['token']) ? $_GET['token'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';

// Verificar que los parámetros existan
if (!$token || !$email) {
    $mensaje_error = "Enlace de recuperación inválido o incompleto.";
    $show_form = false;
} else {
    // Verificar que el token y email existan en la base de datos
    $sql = "SELECT id FROM usuarios WHERE email = ? AND token_recuperacion = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows !== 1) {
        $mensaje_error = "El enlace de recuperación no es válido o ya fue usado.";
        $show_form = false;
    } else {
        $show_form = true;
    }
    $stmt->close();
}

// Procesar el formulario de nueva contraseña
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['password']) && isset($_POST['confirm_password']) && isset($_POST['email']) && isset($_POST['token'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    $token = $_POST['token'];

    // Validar que el token y el email siguen siendo válidos
    $sql = "SELECT id FROM usuarios WHERE email = ? AND token_recuperacion = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows !== 1) {
        $mensaje_error = "El enlace de recuperación no es válido o ya fue usado.";
        $show_form = false;
    } else {
        // Validaciones de contraseña
        $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=\[\]{};:\'",.<>\/?\\|`~¡¿])[A-Za-z\d!@#$%^&*()_\-+=\[\]{};:\'",.<>\/?\\|`~¡¿]{8,}$/';
        if ($password !== $confirm_password) {
            $mensaje_error = "Las contraseñas no coinciden.";
            $show_form = true;
        } elseif (!preg_match($password_pattern, $password)) {
            $mensaje_error = "La contraseña no cumple los requisitos de seguridad.";
            $show_form = true;
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            // Actualizar la contraseña y borrar el token
            $sql = "UPDATE usuarios SET password = ?, token_recuperacion = NULL WHERE email = ? AND token_recuperacion = ?";
            $stmt2 = $conexion->prepare($sql);
            $stmt2->bind_param("sss", $passwordHash, $email, $token);
            if ($stmt2->execute()) {
                $mensaje_exito = "¡Contraseña restablecida exitosamente! Ya puedes iniciar sesión.";
                $show_form = false;
            } else {
                $mensaje_error = "Ocurrió un error al actualizar la contraseña.";
                $show_form = true;
            }
            $stmt2->close();
        }
    }
    $stmt->close();
}
$conexion->close();
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/auth.css">
<body class="auth-bg">
<div class="auth-container">
  <h2>Restablecer contraseña</h2>
  <?php if ($mensaje_exito): ?>
    <div class="alert alert-success"><?php echo $mensaje_exito; ?></div>
    <div class="mt-3">
      <a href="<?php echo BASE_URL; ?>/public/login.php">Ir a iniciar sesión</a>
    </div>
  <?php endif; ?>
  <?php if ($mensaje_error): ?>
    <div class="alert alert-danger"><?php echo $mensaje_error; ?></div>
  <?php endif; ?>

  <?php if (!isset($show_form) || $show_form): ?>
  <form action="<?php echo BASE_URL; ?>/public/restablecer-password.php?token=<?php echo urlencode($token); ?>&email=<?php echo urlencode($email); ?>" method="POST" autocomplete="off" id="resetForm">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <div class="form-group password-input-container" style="position: relative;">
      <label for="password">Nueva contraseña</label>
      <input type="password" name="password" id="password" required>
      <button type="button" class="password-toggle" id="togglePassword" tabindex="-1">
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
    <div class="form-group password-input-container" style="position: relative;">
      <label for="confirm_password">Confirmar nueva contraseña</label>
      <input type="password" name="confirm_password" id="confirm_password" required>
      <button type="button" class="password-toggle" id="toggleConfirmPassword" tabindex="-1">
        <i class="fas fa-eye"></i>
      </button>
      <div class="invalid-feedback" id="password-match-error" style="display:none;">Las contraseñas no coinciden</div>
    </div>
    <button type="submit" class="btn">Restablecer contraseña</button>
  </form>
  <div class="mt-3">
    <a href="<?php echo BASE_URL; ?>/public/login.php">Volver a iniciar sesión</a>
  </div>
  <?php endif; ?>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
  // Mostrar/ocultar contraseñas
  const passwordInput = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
  if(passwordInput && togglePassword) {
    togglePassword.addEventListener('click', function() {
      const icon = togglePassword.querySelector('i');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        togglePassword.setAttribute('aria-label', 'Ocultar contraseña');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        togglePassword.setAttribute('aria-label', 'Mostrar contraseña');
      }
    });
  }
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
  const submitBtn = document.querySelector('.btn');
  const form = document.getElementById('resetForm');

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
<?php
$footer_file = $project_path . '/includes/footer.php';
if (file_exists($footer_file)) include_once($footer_file); else echo '</body></html>';
?>