
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
    $sql = "SELECT nombre, email_verificado FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($nombre, $email_verificado);
        $stmt->fetch();
        if (!$email_verificado) {
            $mensaje_error = "Primero debes verificar tu correo para poder recuperarlo.";
        } else {
            // Generar token de recuperación
            $token_recuperacion = bin2hex(random_bytes(32));
            $update = $conexion->prepare("UPDATE usuarios SET token_recuperacion = ? WHERE email = ?");
            $update->bind_param("ss", $token_recuperacion, $email);
            $update->execute();
            $update->close();

            $link_recuperacion = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
                "://" . $_SERVER['HTTP_HOST'] . BASE_URL . "/public/restablecer-password.php?token=" . $token_recuperacion . "&email=" . urlencode($email);

            // Envía correo
            $asunto = "Recupera tu contraseña - InkVerse";
            $cuerpo = "
                <h2>Hola $nombre</h2>
                <p>Has solicitado recuperar tu contraseña en <b>InkVerse</b>.</p>
                <p>Para restablecerla, haz clic en el siguiente enlace:</p>
                <a href='$link_recuperacion' style='background:#8c52ff;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;font-weight:bold;'>Restablecer contraseña</a>
                <p style='font-size:0.95em;color:#888;margin-top:18px;'>Si no solicitaste este cambio, ignora este mensaje.</p>
            ";
            // Puedes adaptar enviarCorreoVerificacion para recibir asunto y cuerpo, o duplicar la función para recuperación
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'sandbox.smtp.mailtrap.io';
                $mail->SMTPAuth = true;
                $mail->Username = '9f0e43dffe2385'; // Username de Mailtrap
                $mail->Password = '8fff4764141894';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 2525;
                $mail->setFrom('no-reply@inkverse.com', 'InkVerse');
                $mail->addAddress($email, $nombre);
                $mail->Subject = $asunto;
                $mail->isHTML(true);
                $mail->Body = $cuerpo;
                $mail->AltBody = "Hola $nombre,\n\nPara restablecer tu contraseña, visita: $link_recuperacion";
                $mail->send();
                $mensaje_exito = "Te hemos enviado un correo para restablecer tu contraseña. Revisa tu bandeja de entrada.";
            } catch (Exception $e) {
                $mensaje_error = "Hubo un error al enviar el correo de recuperación. Intenta más tarde.";
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
  <h2>Recuperar contraseña</h2>
  <?php if ($mensaje_exito): ?>
    <div class="alert alert-success"><?php echo $mensaje_exito; ?></div>
  <?php endif; ?>
  <?php if ($mensaje_error): ?>
    <div class="alert alert-danger"><?php echo $mensaje_error; ?></div>
  <?php endif; ?>
  <form action="<?php echo BASE_URL; ?>/public/recuperar-password.php" method="POST">
    <div class="form-group">
      <label for="email">Correo electrónico</label>
      <input type="email" name="email" id="email" required>
    </div>
    <button type="submit" class="btn">Enviar enlace de recuperación</button>
  </form>
  <div class="mt-3">
    <a href="<?php echo BASE_URL; ?>/public/login.php">Volver a iniciar sesión</a>
  </div>
</div>
<?php
$footer_file = $project_path . '/includes/footer.php';
if (file_exists($footer_file)) include_once($footer_file); else echo '</body></html>';
?>