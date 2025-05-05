<?php
/**
 * Procesamiento del formulario de inicio de sesión
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
        redirigirConMensaje(BASE_URL . '/public/login.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    }
    
    // Obtener y limpiar datos
    $usuario = limpiarDato($_POST['usuario']);
    $password = $_POST['password']; // No limpiar contraseñas
    
    // Validar datos
    if (empty($usuario) || empty($password)) {
        redirigirConMensaje(BASE_URL . '/public/login.php', 'Todos los campos son obligatorios.', 'error');
    }
    
    // Buscar usuario por nombre de usuario o email
    $sql = "SELECT * FROM usuarios WHERE usuario = ? OR email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $usuario, $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $fila = $result->fetch_assoc();
        
        // Verificar la contraseña
        if (password_verify($password, $fila['password'])) {
            // Guardar información en sesión
            $_SESSION['usuario_id'] = $fila['id'];
            $_SESSION['usuario'] = $fila['usuario'];
            $_SESSION['edad'] = date_diff(date_create($fila['fecha_nacimiento']), date_create('today'))->y;
            
            // Redirigir al perfil con mensaje de éxito
            redirigirConMensaje(BASE_URL . '/usuario/perfil.php', 'Bienvenido/a, ' . $fila['usuario'] . '.', 'exito');
        } else {
            // Contraseña incorrecta
            redirigirConMensaje(BASE_URL . '/public/login.php', 'Contraseña incorrecta.', 'error');
        }
    } else {
        // Usuario no encontrado
        redirigirConMensaje(BASE_URL . '/public/login.php', 'Usuario no encontrado.', 'error');
    }
} else {
    // Si no se envió por POST, redirigir
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}
?>
