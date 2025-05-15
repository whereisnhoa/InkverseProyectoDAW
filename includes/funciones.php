
<?php
/**
 * Este archivo contiene funciones utilizadas en todo el proyecto
 */

// iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

// mostrar mensajes almacenados en sesión (éxito o error)
function mostrarMensaje() 
{
    if (isset($_SESSION['mensaje'])) {
        $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
        echo "<div class='alerta alerta-{$tipo}'>" . $_SESSION['mensaje'] . "</div>";
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
    }
}

// redirigir con mensaje
function redirigirConMensaje($url, $mensaje, $tipo = 'info') 
{
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
    header("Location: $url");
    exit;
}

// validar edad mínima
function esMayorEdad($fechaNacimiento) 
{
    $hoy = new DateTime();
    $fecha = new DateTime($fechaNacimiento);
    $edad = $hoy->diff($fecha)->y;
    return $edad >= 18;
}

// comprobar si el usuario está logueado
function estaLogueado() 
{
    return isset($_SESSION['usuario_id']);
}

// proteger páginas privadas
function requerirLogin() 
{
    if (!estaLogueado()) 
    {
        redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para acceder.', 'error');
    }
}

// limpiar datos de entrada
function limpiarDato($dato) 
{
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// generar token CSRF
function generarTokenCSRF() 
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// verificar token CSRF
function verificarTokenCSRF($token) 
{
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        redirigirConMensaje(BASE_URL . '/public/index.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    }
}

// Función para crear una notificación
function crearNotificacion($usuario_id, $tipo, $mensaje, $url = '', $contenido_id = null, $tipo_contenido = null) {
    global $conexion;
    
    $sql = "INSERT INTO notificaciones (usuario_id, tipo, mensaje, url, contenido_id, tipo_contenido) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("isssss", $usuario_id, $tipo, $mensaje, $url, $contenido_id, $tipo_contenido);
    return $stmt->execute();
}

// Función para obtener notificaciones de un usuario
function obtenerNotificaciones($usuario_id, $limit = 10, $solo_no_leidas = false) {
    global $conexion;
    
    $sql = "SELECT * FROM notificaciones WHERE usuario_id = ?";
    
    if ($solo_no_leidas) {
        $sql .= " AND leida = 0";
    }
    
    $sql .= " ORDER BY fecha_creacion DESC LIMIT ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $usuario_id, $limit);
    $stmt->execute();
    
    return $stmt->get_result();
}

// Función para marcar notificación como leída
function marcarNotificacionLeida($notificacion_id, $usuario_id) {
    global $conexion;
    
    $sql = "UPDATE notificaciones SET leida = 1 
            WHERE id = ? AND usuario_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $notificacion_id, $usuario_id);
    return $stmt->execute();
}

// Función para marcar todas las notificaciones como leídas
function marcarTodasNotificacionesLeidas($usuario_id) {
    global $conexion;
    
    $sql = "UPDATE notificaciones SET leida = 1 
            WHERE usuario_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    return $stmt->execute();
}

// Función para contar notificaciones no leídas
function contarNotificacionesNoLeidas($usuario_id) {
    global $conexion;
    
    $sql = "SELECT COUNT(*) as total FROM notificaciones 
            WHERE usuario_id = ? AND leida = 0";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'];
}

// Función para mostrar tiempo transcurrido de forma amigable
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'año',
        'm' => 'mes',
        'w' => 'semana',
        'd' => 'día',
        'h' => 'hora',
        'i' => 'minuto',
        's' => 'segundo',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'hace ' . implode(', ', $string) : 'justo ahora';
}
