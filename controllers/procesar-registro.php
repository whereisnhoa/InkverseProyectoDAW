<?php
session_start();

$document_root = $_SERVER['DOCUMENT_ROOT'];
$current_file = str_replace('\\', '/', __FILE__);
$document_root = str_replace('\\', '/', $document_root);

$controllers_pos = strrpos($current_file, '/controllers/');
if ($controllers_pos !== false) {
    $project_path = substr($current_file, 0, $controllers_pos);
} else {
    $project_path = dirname(dirname(__FILE__));
}
$relative_path = str_replace($document_root, '', $project_path);
if (!defined('BASE_URL')) {
    define('BASE_URL', $relative_path);
}
$conexion_file = $project_path . '/includes/conexion.php';
if (!file_exists($conexion_file)) {
    die("Error: No se pudo encontrar el archivo de conexión en: " . $conexion_file);
}
require_once($conexion_file);

function limpiarDato($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// Obtener y limpiar datos del formulario
$nombre = isset($_POST['nombre']) ? limpiarDato($_POST['nombre']) : '';
$usuario = isset($_POST['usuario']) ? limpiarDato($_POST['usuario']) : '';
$email = isset($_POST['email']) ? limpiarDato($_POST['email']) : '';
$fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? limpiarDato($_POST['fecha_nacimiento']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validaciones igual que antes ...
if (empty($nombre) || empty($usuario) || empty($email) || empty($fecha_nacimiento) || empty($password) || empty($confirm_password)) {
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}
if ($password !== $confirm_password) {
    $_SESSION['mensaje'] = "Las contraseñas no coinciden.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['mensaje'] = "El formato del correo electrónico no es válido.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}
$password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/';
if (!preg_match($password_pattern, $password)) {
    $_SESSION['mensaje'] = "La contraseña no cumple con los requisitos de seguridad.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}
if (!isset($_POST['acepta_terminos'])) {
    $_SESSION['mensaje'] = "Debes aceptar los términos y condiciones para registrarte.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Comprobar si el usuario o email ya existen
$sql_check = "SELECT * FROM usuarios WHERE email = ? OR usuario = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("ss", $email, $usuario);
$stmt_check->execute();
$result = $stmt_check->get_result();
if ($result->num_rows > 0) {
    $_SESSION['mensaje'] = "El correo o el nombre de usuario ya están en uso.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Generar token de verificación único
$token_verificacion = bin2hex(random_bytes(32));

// Insertar nuevo usuario (incluye nombre, fecha_nacimiento, email_verificado, token_verificacion)
$sql = "INSERT INTO usuarios (nombre, usuario, email, fecha_nacimiento, password, email_verificado, token_verificacion) VALUES (?, ?, ?, ?, ?, 0, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssssss", $nombre, $usuario, $email, $fecha_nacimiento, $passwordHash, $token_verificacion);

if ($stmt->execute()) {
    // Enviar correo de verificación usando PHPMailer y Gmail
    require_once($project_path . '/includes/email.php');

    $link_verificacion = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
        "://" . $_SERVER['HTTP_HOST'] . BASE_URL . "/public/verificar-email.php?token=" . $token_verificacion . "&email=" . urlencode($email);

    $correo_enviado = enviarCorreoVerificacion($email, $nombre, $link_verificacion);

    if ($correo_enviado) {
        $_SESSION['mensaje'] = "Registro exitoso. Revisa tu correo para verificar tu cuenta antes de iniciar sesión.";
        $_SESSION['tipo_mensaje'] = "exito";
        header("Location: " . BASE_URL . "/public/login.php");
        exit();
    } else {
        $_SESSION['mensaje'] = "Hubo un error enviando tu correo de verificación. Contacta al soporte.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: " . BASE_URL . "/public/register.php");
        exit();
    }
} else {
    $_SESSION['mensaje'] = "Error al registrar: " . $stmt->error;
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}