
<?php

session_start();
$document_root = $_SERVER['DOCUMENT_ROOT'];
$current_file = str_replace('\\', '/', __FILE__);
$document_root = str_replace('\\', '/', $document_root);
$public_pos = strrpos($current_file, '/public/');
$project_path = ($public_pos !== false) ? substr($current_file, 0, $public_pos) : dirname(dirname(__FILE__));
$relative_path = str_replace($document_root, '', $project_path);

if (!defined('BASE_URL')) {
    define('BASE_URL', $relative_path);
}

require_once($project_path . '/includes/conexion.php');
$token = isset($_GET['token']) ? $_GET['token'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';

if (!$token || !$email) {
    $_SESSION['mensaje'] = "Enlace no válido.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}
// Buscar usuario por token y email
$sql = "SELECT * FROM usuarios WHERE email = ? AND token_verificacion = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    // Marcar email como verificado
    $sql_upd = "UPDATE usuarios SET email_verificado = 1, token_verificacion = NULL WHERE email = ?";
    $stmt_upd = $conexion->prepare($sql_upd);
    $stmt_upd->bind_param("s", $email);
    $stmt_upd->execute();
    $_SESSION['mensaje'] = "¡Correo verificado! Ya puedes iniciar sesión.";
    $_SESSION['tipo_mensaje'] = "exito";
} else {
    $_SESSION['mensaje'] = "El enlace de verificación no es válido o ya fue utilizado.";
    $_SESSION['tipo_mensaje'] = "error";
}

header("Location: " . BASE_URL . "/public/login.php");

exit();

?>