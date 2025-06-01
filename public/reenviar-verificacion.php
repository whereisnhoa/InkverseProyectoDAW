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

$mensaje_exito = null;
$mensaje_error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['email'])) {
    require_once($project_path . '/includes/conexion.php');
    require_once($project_path . '/includes/email.php');
    $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $sql = "SELECT nombre, email_verificado, token_verificacion FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($nombre, $email_verificado, $token_verificacion);
        $stmt->fetch();
        if ($email_verificado) {
            $mensaje_exito = "Tu correo ya está verificado. Puedes iniciar sesión.";
        } else {
            $link_verificacion = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
                "://" . $_SERVER['HTTP_HOST'] . BASE_URL . "/public/verificar-email.php?token=" . $token_verificacion . "&email=" . urlencode($email);
            if (enviarCorreoVerificacion($email, $nombre, $link_verificacion)) {
                $mensaje_exito = "Te hemos reenviado el correo de verificación. Revisa tu bandeja de entrada.";
            } else {
                $mensaje_error = "Hubo un error al reenviar el correo. Intenta más tarde.";
            }
        }
    } else {
        $mensaje_error = "No existe ninguna cuenta con ese correo.";
    }
    $stmt->close();
    $conexion->close();
}
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/auth.css">
<body class="auth-bg">
<div class="auth-container">
  <h2>Reenviar correo de verificación</h2>
  <?php if ($mensaje_exito): ?>
    <div class="alert alert-success"><?php echo $mensaje_exito; ?></div>
  <?php endif; ?>
  <?php if ($mensaje_error): ?>
    <div class="alert alert-danger"><?php echo $mensaje_error; ?></div>
  <?php endif; ?>
  <form action="<?php echo BASE_URL; ?>/public/reenviar-verificacion.php" method="POST">
    <div class="form-group">
      <label for="email">Correo electrónico</label>
      <input type="email" name="email" id="email" required>
    </div>
    <button type="submit" class="btn">Reenviar correo</button>
  </form>
  <div class="mt-3">
    <a href="<?php echo BASE_URL; ?>/public/login.php">Volver a iniciar sesión</a>
  </div>
</div>
<?php
$footer_file = $project_path . '/includes/footer.php';
if (file_exists($footer_file)) include_once($footer_file); else echo '</body></html>';
?>