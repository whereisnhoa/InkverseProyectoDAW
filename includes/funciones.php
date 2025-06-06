<?php
/**
 * Funciones de utilidad para el proyecto
 */

// Incluir configuración si no está incluida
if (!defined('BASE_URL')) {
    require_once(__DIR__ . '/config.php');
}

/**
 * Verificar si el usuario está logueado
 */
function verificarLogin() {
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        header("Location: " . BASE_URL . "/public/login.php");
        exit();
    }
}

/**
 * Verificar si el usuario está logueado (sin redirección)
 */
function estaLogueado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verificar si un usuario sigue a otro
 */
function verificarSeguimiento($conexion, $seguidor_id, $seguido_id) {
    $sql = "SELECT COUNT(*) as count FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $seguidor_id, $seguido_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] > 0;
}

/**
 * Limpiar y validar entrada de datos
 */
function limpiarDatos($datos) {
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos);
    return $datos;
}

/**
 * Generar token CSRF
 */
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redireccionar con mensaje
 */
function redirigirConMensaje($url, $mensaje, $tipo = 'info') {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
    header("Location: " . $url);
    exit();
}

/**
 * Mostrar mensaje de sesión
 */
function mostrarMensaje() {
    if (isset($_SESSION['mensaje'])) {
        $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
        echo '<div class="alerta alerta-' . $tipo . '">' . $_SESSION['mensaje'] . '</div>';
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
    }
}

/**
 * Validar email
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validar contraseña
 */
function validarPassword($password) {
    // Mínimo 8 caracteres, al menos una letra y un número
    return strlen($password) >= 8 && preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password);
}

/**
 * Formatear fecha
 */
function formatearFecha($fecha, $formato = 'd/m/Y') {
    return date($formato, strtotime($fecha));
}

/**
 * Calcular tiempo transcurrido
 */
function tiempoTranscurrido($fecha) {
    $tiempo = time() - strtotime($fecha);
    
    if ($tiempo < 60) {
        return 'hace ' . $tiempo . ' segundos';
    } elseif ($tiempo < 3600) {
        return 'hace ' . round($tiempo / 60) . ' minutos';
    } elseif ($tiempo < 86400) {
        return 'hace ' . round($tiempo / 3600) . ' horas';
    } elseif ($tiempo < 2592000) {
        return 'hace ' . round($tiempo / 86400) . ' días';
    } else {
        return formatearFecha($fecha);
    }
}

/**
 * Subir archivo
 */
function subirArchivo($archivo, $directorio, $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'], $tamaño_maximo = 2097152) {
    if (!isset($archivo['error']) || is_array($archivo['error'])) {
        throw new RuntimeException('Parámetros de archivo inválidos.');
    }

    switch ($archivo['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No se envió ningún archivo.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('El archivo excede el tamaño máximo permitido.');
        default:
            throw new RuntimeException('Error desconocido.');
    }

    if ($archivo['size'] > $tamaño_maximo) {
        throw new RuntimeException('El archivo excede el tamaño máximo permitido.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);
    $extensiones_mime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif'
    ];

    if (!array_key_exists($mime, $extensiones_mime)) {
        throw new RuntimeException('Formato de archivo no válido.');
    }

    $extension = $extensiones_mime[$mime];
    if (!in_array($extension, $tipos_permitidos)) {
        throw new RuntimeException('Tipo de archivo no permitido.');
    }

    // Crear directorio si no existe
    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true);
    }

    // Generar nombre único
    $nombre_archivo = uniqid() . '.' . $extension;
    $ruta_completa = $directorio . '/' . $nombre_archivo;

    if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        throw new RuntimeException('Error al mover el archivo.');
    }

    return $nombre_archivo;
}

/**
 * Crear notificaciones
 */
function crearNotificacion($usuario_id, $tipo, $mensaje, $url = null) {
    global $conexion;
    $sql = "INSERT INTO notificaciones (usuario_id, tipo, mensaje, url, fecha_creacion, leida) VALUES (?, ?, ?, ?, NOW(), 0)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("isss", $usuario_id, $tipo, $mensaje, $url);
    return $stmt->execute();
}

