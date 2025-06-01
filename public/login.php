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
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/auth.css">
<body class="auth-bg">
<div class="auth-container">
  <h2>Iniciar sesión</h2>
  <?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert <?php echo $_SESSION['tipo_mensaje']=="exito" ? "alert-success" : "alert-danger"; ?>">
      <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
    </div>
  <?php endif; ?>
  <form action="<?php echo BASE_URL; ?>/controllers/procesar-login.php" method="POST">
    <div class="form-group">
      <label for="usuario">Nombre de usuario o email</label>
      <input type="text" name="usuario" id="usuario" required>
    </div>
    <div class="form-group password-input-container">
      <label for="password">Contraseña</label>
      <input type="password" name="password" id="password" required>
      <button type="button" class="password-toggle" id="togglePassword" tabindex="-1">
        <i class="fas fa-eye"></i>
      </button>
    </div>
    <button type="submit" class="btn">Entrar</button>
  </form>
  <div class="mt-3 text-center">
    <p>¿No tienes una cuenta? <a href="<?php echo BASE_URL; ?>/public/register.php">Registrarse</a></p>
    <p><a href="<?php echo BASE_URL; ?>/public/recuperar-password.php">¿Olvidaste tu contraseña?</a></p>
    <p>¿No recibiste el correo de verificación? <a href="<?php echo BASE_URL; ?>/public/reenviar-verificacion.php">Reenviar verificación</a></p>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const passwordInput = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');
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
});
</script>
<?php
$footer_file = $project_path . '/includes/footer.php';
if (file_exists($footer_file)) include_once($footer_file); else echo '</body></html>';
?>