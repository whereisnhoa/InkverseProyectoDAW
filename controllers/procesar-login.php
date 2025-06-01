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

// Obtener datos del formulario
$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validar que los campos no estén vacíos
if (empty($usuario) || empty($password)) {
    $_SESSION['mensaje'] = "Por favor, completa todos los campos.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

// Buscar usuario por nombre de usuario o email
$sql = "SELECT * FROM usuarios WHERE usuario = ? OR email = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $fila = $result->fetch_assoc();

    // NUEVO: comprobar si el email está verificado
    if (!$fila['email_verificado']) {
        $_SESSION['mensaje'] = "Debes verificar tu correo electrónico antes de iniciar sesión.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: " . BASE_URL . "/public/login.php");
        exit();
    }

    // Verificar la contraseña
    if (password_verify($password, $fila['password'])) {
        // Guardar información en sesión
        $_SESSION['usuario_id'] = $fila['id'];
        $_SESSION['usuario'] = $fila['usuario'];
        $_SESSION['email'] = $fila['email'];
        $_SESSION['fecha_registro'] = $fila['fecha_registro'];
        $_SESSION['edad'] = date_diff(date_create($fila['fecha_nacimiento']), date_create('today'))->y;

        $_SESSION['mensaje'] = "¡Bienvenido, " . $fila['usuario'] . "!";
        $_SESSION['tipo_mensaje'] = "exito";

        // Redirigir al perfil o página principal
        header("Location: " . BASE_URL . "/usuario/perfil.php");
        exit();
    } else {
        $_SESSION['mensaje'] = "Contraseña incorrecta.";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: " . BASE_URL . "/public/login.php");
        exit();
    }
} else {
    $_SESSION['mensaje'] = "Usuario no encontrado.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}
?>
