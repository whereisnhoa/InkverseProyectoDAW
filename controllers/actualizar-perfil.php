<?php
// Iniciar sesión
session_start();

// Incluir configuración primero
require_once(__DIR__ . '/../includes/config.php');

// Incluir archivos necesarios
require_once(__DIR__ . '/../includes/funciones.php');
require_once(__DIR__ . '/../includes/conexion.php');

// Verificar que el usuario esté logueado
verificarLogin();

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/usuario/editar_perfil.php");
    exit();
}

// Verificar token CSRF
if (!verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
    $_SESSION['mensaje'] = "Error de validación. Por favor, intenta nuevamente.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/usuario/editar_perfil.php");
    exit();
}

// Obtener y limpiar datos del formulario
$usuario_id = $_SESSION['usuario_id'];
$usuario = limpiarDatos($_POST['usuario'] ?? '');
$email = limpiarDatos($_POST['email'] ?? '');
$nombre = limpiarDatos($_POST['nombre'] ?? '');
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
$biografia = limpiarDatos($_POST['biografia'] ?? '');
$sitio_web = limpiarDatos($_POST['sitio_web'] ?? '');
$ubicacion = limpiarDatos($_POST['ubicacion'] ?? '');
$privacidad = $_POST['privacidad'] ?? 'publico';

// Validaciones
$errores = [];

// Validar campos requeridos
if (empty($usuario)) {
    $errores[] = "El nombre de usuario es requerido.";
}

if (empty($email)) {
    $errores[] = "El correo electrónico es requerido.";
} elseif (!validarEmail($email)) {
    $errores[] = "El formato del correo electrónico no es válido.";
}

// Validar longitud de biografía
if (strlen($biografia) > 500) {
    $errores[] = "La biografía no puede exceder los 500 caracteres.";
}

// Validar sitio web si se proporciona
if (!empty($sitio_web) && !filter_var($sitio_web, FILTER_VALIDATE_URL)) {
    $errores[] = "El formato del sitio web no es válido.";
}

// Validar fecha de nacimiento
if (!empty($fecha_nacimiento)) {
    $fecha = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    if (!$fecha) {
        $errores[] = "El formato de la fecha de nacimiento no es válido.";
    } else {
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha)->y;
        if ($edad < 13) {
            $errores[] = "Debes tener al menos 13 años para usar esta plataforma.";
        }
    }
}

// Verificar si el nombre de usuario ya existe (excepto el actual)
$sql_check = "SELECT id FROM usuarios WHERE usuario = ? AND id != ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("si", $usuario, $usuario_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $errores[] = "El nombre de usuario ya está en uso.";
}

// Verificar si el email ya existe (excepto el actual)
$sql_check_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
$stmt_check_email = $conexion->prepare($sql_check_email);
$stmt_check_email->bind_param("si", $email, $usuario_id);
$stmt_check_email->execute();
if ($stmt_check_email->get_result()->num_rows > 0) {
    $errores[] = "El correo electrónico ya está en uso.";
}

// Si hay errores, redirigir con mensaje
if (!empty($errores)) {
    $_SESSION['mensaje'] = implode('<br>', $errores);
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/usuario/editar_perfil.php");
    exit();
}

// Actualizar datos en la base de datos
$sql = "UPDATE usuarios SET 
        usuario = ?, 
        email = ?, 
        nombre = ?, 
        fecha_nacimiento = ?, 
        biografia = ?, 
        sitio_web = ?, 
        ubicacion = ?, 
        privacidad = ?,
        fecha_actualizacion = NOW()
        WHERE id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssssssssi", 
    $usuario, 
    $email, 
    $nombre, 
    $fecha_nacimiento, 
    $biografia, 
    $sitio_web, 
    $ubicacion, 
    $privacidad, 
    $usuario_id
);

if ($stmt->execute()) {
    // Actualizar datos de sesión
    $_SESSION['usuario'] = $usuario;
    $_SESSION['email'] = $email;
    
    $_SESSION['mensaje'] = "Perfil actualizado correctamente.";
    $_SESSION['tipo_mensaje'] = "exito";
} else {
    $_SESSION['mensaje'] = "Error al actualizar el perfil. Por favor, inténtalo de nuevo.";
    $_SESSION['tipo_mensaje'] = "error";
}

// Redirigir de vuelta al perfil
header("Location: " . BASE_URL . "/usuario/perfil.php");
exit();
?>
