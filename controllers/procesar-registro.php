
<?php
session_start();

// Determinar la ruta base del proyecto de forma más robusta
$document_root = $_SERVER['DOCUMENT_ROOT'];
$current_file = str_replace('\\', '/', __FILE__);
$document_root = str_replace('\\', '/', $document_root);

// Encontrar la posición de 'controllers' en la ruta actual
$controllers_pos = strrpos($current_file, '/controllers/');
if ($controllers_pos !== false) {
    $project_path = substr($current_file, 0, $controllers_pos);
} else {
    // Fallback: asumir que estamos en la carpeta controllers
    $project_path = dirname(dirname(__FILE__));
}

// Calcular la ruta relativa para BASE_URL
$relative_path = str_replace($document_root, '', $project_path);

// Definir BASE_URL si no está definido
if (!defined('BASE_URL')) {
    define('BASE_URL', $relative_path);
}

// Incluir archivos necesarios con rutas absolutas
$conexion_file = $project_path . '/includes/conexion.php';

// Verificar que el archivo de conexión existe antes de incluirlo
if (!file_exists($conexion_file)) {
    die("Error: No se pudo encontrar el archivo de conexión en: " . $conexion_file);
}

require_once($conexion_file);

// Función para limpiar datos
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

// Verificar que todos los campos requeridos estén presentes
if (empty($nombre) || empty($usuario) || empty($email) || empty($fecha_nacimiento) || empty($password) || empty($confirm_password)) {
    $_SESSION['mensaje'] = "Todos los campos son obligatorios.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Verificar que las contraseñas coincidan
if ($password !== $confirm_password) {
    $_SESSION['mensaje'] = "Las contraseñas no coinciden.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['mensaje'] = "El formato del correo electrónico no es válido.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Validar requisitos de contraseña
$password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/';
if (!preg_match($password_pattern, $password)) {
    $_SESSION['mensaje'] = "La contraseña no cumple con los requisitos de seguridad.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Verificar que se hayan aceptado los términos y condiciones
if (!isset($_POST['acepta_terminos'])) {
    $_SESSION['mensaje'] = "Debes aceptar los términos y condiciones para registrarte.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Encriptar contraseña
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
} else {
    // Insertar nuevo usuario
    $sql = "INSERT INTO usuarios (usuario, email, fecha_nacimiento, password) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssss", $usuario, $email, $fecha_nacimiento, $passwordHash);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Registro exitoso. Ahora puedes iniciar sesión.";
        $_SESSION['tipo_mensaje'] = "exito";
        header("Location: " . BASE_URL . "/public/login.php");
        exit();
    } else {
        $_SESSION['mensaje'] = "Error al registrar: " . $stmt->error;
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: " . BASE_URL . "/public/register.php");
        exit();
    }
}
?>
