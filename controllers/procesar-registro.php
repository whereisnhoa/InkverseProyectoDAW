<?php
/**
 * Procesamiento del formulario de registro
 */

$ruta_base = $_SERVER['DOCUMENT_ROOT'] . '/proyecto';
include_once($ruta_base . '/includes/conexion.php');
include_once($ruta_base . '/includes/funciones.php');

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    redirigirConMensaje(BASE_URL . '/public/register.php', 'Acceso no válido.', 'error');
    exit();
}

// variables de datos
$nombre = limpiarDato($_POST['nombre']);
$usuario = limpiarDato($_POST['usuario']);
$email = limpiarDato($_POST['email']);
$fecha_nacimiento = limpiarDato($_POST['fecha_nacimiento']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validar datos
if (empty($nombre) || empty($usuario) || empty($email) || empty($fecha_nacimiento) || empty($password)) {
    redirigirConMensaje(BASE_URL . '/public/register.php', 'Todos los campos son obligatorios.', 'error');
    exit();
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirigirConMensaje(BASE_URL . '/public/register.php', 'El correo electrónico no es válido.', 'error');
    exit();
}

// Validar fecha de nacimiento
$fecha_actual = new DateTime();
$fecha_nac = new DateTime($fecha_nacimiento);
$edad = $fecha_actual->diff($fecha_nac)->y;

if ($edad < 13) {
    redirigirConMensaje(BASE_URL . '/public/register.php', 'Debes tener al menos 13 años para registrarte.', 'error');
    exit();
}

// Validar que las contraseñas coincidan
if ($password !== $confirm_password) {
    redirigirConMensaje(BASE_URL . '/public/register.php', 'Las contraseñas no coinciden.', 'error');
    exit();
}

// Validar requisitos de la contraseña
$errores_password = [];

// Verificar longitud mínima
if (strlen($password) < 8) {
    $errores_password[] = 'La contraseña debe tener al menos 8 caracteres.';
}

// Verificar mayúsculas
if (!preg_match('/[A-Z]/', $password)) {
    $errores_password[] = 'La contraseña debe incluir al menos una letra mayúscula.';
}

// Verificar minúsculas
if (!preg_match('/[a-z]/', $password)) {
    $errores_password[] = 'La contraseña debe incluir al menos una letra minúscula.';
}

// Verificar números
if (!preg_match('/[0-9]/', $password)) {
    $errores_password[] = 'La contraseña debe incluir al menos un número.';
}

// Verificar caracteres especiales
if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
    $errores_password[] = 'La contraseña debe incluir al menos un carácter especial (!@#$%^&*).';
}

// Si hay errores en la contraseña, redirigir con mensaje
if (!empty($errores_password)) {
    $mensaje_error = 'La contraseña no cumple con los requisitos de seguridad:<ul>';
    foreach ($errores_password as $error) {
        $mensaje_error .= '<li>' . $error . '</li>';
    }
    $mensaje_error .= '</ul>';
    
    $_SESSION['mensaje'] = $mensaje_error;
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Verificar que se hayan aceptado los términos y condiciones
if (!isset($_POST['acepta_terminos'])) {
    redirigirConMensaje(BASE_URL . '/public/register.php', 'Debes aceptar los términos y condiciones para registrarte.', 'error');
    exit();
}

// para encriptar las contraseñas y que no se garde tal cual en la base de datos
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// comprobar si el usuario o email ya existen
$sql_check = "SELECT * FROM usuarios WHERE email = ? OR usuario = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("ss", $email, $usuario);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    $usuario_existente = $result->fetch_assoc();
    if ($usuario_existente['email'] === $email) {
        redirigirConMensaje(BASE_URL . '/public/register.php', 'El correo electrónico ya está en uso.', 'error');
    } else {
        redirigirConMensaje(BASE_URL . '/public/register.php', 'El nombre de usuario ya está en uso.', 'error');
    }
    exit();
} else {
    // Insertar nuevo usuario
    $sql = "INSERT INTO usuarios (nombre, usuario, email, fecha_nacimiento, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $usuario, $email, $fecha_nacimiento, $passwordHash);

    if ($stmt->execute()) {
        redirigirConMensaje(BASE_URL . '/public/login.php', 'Registro completado con éxito. Ahora puedes iniciar sesión.', 'exito');
    } else {
        redirigirConMensaje(BASE_URL . '/public/register.php', 'Error al registrar: ' . $stmt->error, 'error');
    }
}
?>
