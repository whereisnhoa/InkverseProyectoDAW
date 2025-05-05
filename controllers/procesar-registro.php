<?php
/**
 * Procesamiento del formulario de registro
 */

// Iniciar sesión
session_start();

// Incluir archivos necesarios
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/conexion.php');
require_once(__DIR__ . '/../includes/funciones.php');

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
        redirigirConMensaje(BASE_URL . '/public/register.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    }
    
    // Obtener y limpiar datos
    $nombre = limpiarDato($_POST['nombre']);
    $usuario = limpiarDato($_POST['usuario']);
    $email = limpiarDato($_POST['email']);
    $fecha_nacimiento = limpiarDato($_POST['fecha_nacimiento']);
    $password = $_POST['password']; // No limpiar contraseñas
    
    // Validar datos
    if (empty($nombre) || empty($usuario) || empty($email) || empty($fecha_nacimiento) || empty($password)) {
        redirigirConMensaje(BASE_URL . '/public/register.php', 'Todos los campos son obligatorios.', 'error');
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirigirConMensaje(BASE_URL . '/public/register.php', 'El correo electrónico no es válido.', 'error');
    }
    
    // Validar fecha de nacimiento
    $fecha_actual = new DateTime();
    $fecha_nac = new DateTime($fecha_nacimiento);
    if ($fecha_nac > $fecha_actual) {
        redirigirConMensaje(BASE_URL . '/public/register.php', 'La fecha de nacimiento no puede ser futura.', 'error');
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
        redirigirConMensaje(BASE_URL . '/public/register.php', 'El correo o el nombre de usuario ya están en uso.', 'error');
    } else {
        // Insertar nuevo usuario
        $sql = "INSERT INTO usuarios (nombre, usuario, email, fecha_nacimiento, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssss", $nombre, $usuario, $email, $fecha_nacimiento, $passwordHash);
        
        if ($stmt->execute()) {
            redirigirConMensaje(BASE_URL . '/public/login.php', 'Registro exitoso. Ahora puedes iniciar sesión.', 'exito');
        } else {
            redirigirConMensaje(BASE_URL . '/public/register.php', 'Error al registrar: ' . $stmt->error, 'error');
        }
    }
} else {
    // Si no se envió por POST, redirigir
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}
?>