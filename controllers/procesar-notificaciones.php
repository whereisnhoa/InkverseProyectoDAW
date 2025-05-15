
<?php
session_start();
include '../includes/conexion.php';
include_once('../includes/funciones.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para acceder a esta función.', 'error');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$notificacion_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Determinar la URL de retorno
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . '/usuario/notificaciones.php';

switch ($accion) {
    case 'marcar_leida':
        // Verificar que la notificación exista y pertenezca al usuario
        if ($notificacion_id > 0) {
            if (marcarNotificacionLeida($notificacion_id, $usuario_id)) {
                // Si hay una URL en la notificación, redirigir a ella
                $sql = "SELECT url FROM notificaciones WHERE id = ? AND usuario_id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("ii", $notificacion_id, $usuario_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $notificacion = $result->fetch_assoc();
                    if (!empty($notificacion['url'])) {
                        header("Location: " . $notificacion['url']);
                        exit();
                    }
                }
                
                redirigirConMensaje($referer, 'Notificación marcada como leída.', 'exito');
            } else {
                redirigirConMensaje($referer, 'Error al marcar la notificación como leída.', 'error');
            }
        } else {
            redirigirConMensaje($referer, 'Notificación no especificada correctamente.', 'error');
        }
        break;
        
    case 'marcar_todas_leidas':
        if (marcarTodasNotificacionesLeidas($usuario_id)) {
            redirigirConMensaje($referer, 'Todas las notificaciones han sido marcadas como leídas.', 'exito');
        } else {
            redirigirConMensaje($referer, 'Error al marcar las notificaciones como leídas.', 'error');
        }
        break;
        
    default:
        redirigirConMensaje($referer, 'Acción no válida.', 'error');
        break;
}
?>
