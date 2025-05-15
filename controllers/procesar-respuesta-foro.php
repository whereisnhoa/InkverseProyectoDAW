
<?php
session_start();
include '../includes/conexion.php';
include_once('../includes/funciones.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para responder.', 'error');
    exit();
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
    redirigirConMensaje(BASE_URL . '/comunidad/foros.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    exit();
}

// Obtener datos del formulario
$contenido = limpiarDato($_POST['contenido']);
$tema_id = intval($_POST['tema_id']);
$usuario_id = $_SESSION['usuario_id'];

// Validar datos
if (empty($contenido) || empty($tema_id)) {
    redirigirConMensaje(BASE_URL . '/comunidad/tema.php?id=' . $tema_id, 'El contenido de la respuesta no puede estar vacío.', 'error');
    exit();
}

// Verificar que el tema exista y no esté cerrado
$sql_check = "SELECT tf.id, tf.estado, tf.titulo, tf.usuario_id, u.usuario as autor_nombre 
              FROM temas_foro tf
              JOIN usuarios u ON tf.usuario_id = u.id
              WHERE tf.id = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $tema_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$tema = $result_check->fetch_assoc();

if (!$tema) {
    redirigirConMensaje(BASE_URL . '/comunidad/foros.php', 'El tema no existe.', 'error');
    exit();
}

if ($tema['estado'] == 'cerrado') {
    redirigirConMensaje(BASE_URL . '/comunidad/tema.php?id=' . $tema_id, 'No puedes responder a un tema cerrado.', 'error');
    exit();
}

// Insertar respuesta en la base de datos
$sql = "INSERT INTO respuestas_foro (tema_id, usuario_id, contenido) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iis", $tema_id, $usuario_id, $contenido);

if ($stmt->execute()) {
    // Actualizar contador de respuestas y fecha de última actividad del tema
    $sql_update = "UPDATE temas_foro SET respuestas = respuestas + 1, ultima_actividad = NOW() WHERE id = ?";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("i", $tema_id);
    $stmt_update->execute();
    
    // Enviar notificación al autor del tema (si no es el mismo usuario)
    if ($tema['usuario_id'] != $usuario_id) {
        $mensaje = $_SESSION['usuario'] . " ha respondido a tu tema: " . $tema['titulo'];
        $url = BASE_URL . '/comunidad/tema.php?id=' . $tema_id;
        
        crearNotificacion($tema['usuario_id'], 'respuesta_foro', $mensaje, $url, $tema_id, 'tema_foro');
    }
    
    // También notificar a otros participantes únicos del tema (excepto el autor y el usuario actual)
    $sql_participantes = "SELECT DISTINCT usuario_id FROM respuestas_foro 
                         WHERE tema_id = ? AND usuario_id != ? AND usuario_id != ?";
    $stmt_participantes = $conexion->prepare($sql_participantes);
    $stmt_participantes->bind_param("iii", $tema_id, $usuario_id, $tema['usuario_id']);
    $stmt_participantes->execute();
    $result_participantes = $stmt_participantes->get_result();
    
    while ($participante = $result_participantes->fetch_assoc()) {
        $mensaje = "Hay una nueva respuesta en el tema: " . $tema['titulo'];
        $url = BASE_URL . '/comunidad/tema.php?id=' . $tema_id;
        
        crearNotificacion($participante['usuario_id'], 'respuesta_foro', $mensaje, $url, $tema_id, 'tema_foro');
    }
    
    redirigirConMensaje(BASE_URL . '/comunidad/tema.php?id=' . $tema_id, 'Tu respuesta ha sido publicada correctamente.', 'exito');
} else {
    redirigirConMensaje(BASE_URL . '/comunidad/tema.php?id=' . $tema_id, 'Error al publicar la respuesta: ' . $stmt->error, 'error');
}
?>
