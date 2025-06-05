<?php
session_start();
include '../includes/conexion.php';
include_once('../includes/funciones.php');

if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje('../public/login.php', 'Debes iniciar sesión para dar me gusta.', 'error');
    exit();
}
if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
    redirigirConMensaje('../comunidad/arte.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    exit();
}
$arte_id = intval($_POST['arte_id']);
$accion = $_POST['accion'];
$usuario_id = $_SESSION['usuario_id'];

if (empty($arte_id) || !in_array($accion, ['dar', 'quitar'])) {
    redirigirConMensaje('../comunidad/ver_arte.php?id=' . $arte_id, 'Datos inválidos.', 'error');
    exit();
}
$sql_check = "SELECT ga.id, ga.titulo, u.id as autor_id, u.usuario as autor_nombre FROM galeria_arte ga JOIN usuarios u ON ga.usuario_id = u.id WHERE ga.id = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $arte_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows == 0) {
    redirigirConMensaje('../comunidad/arte.php', 'La obra no existe.', 'error');
    exit();
}
$arte = $result_check->fetch_assoc();
$conexion->begin_transaction();

try {
    if ($accion == 'dar') {
        $sql_check_like = "SELECT id FROM me_gusta_arte WHERE arte_id = ? AND usuario_id = ?";
        $stmt_check_like = $conexion->prepare($sql_check_like);
        $stmt_check_like->bind_param("ii", $arte_id, $usuario_id);
        $stmt_check_like->execute();
        $result_check_like = $stmt_check_like->get_result();
        if ($result_check_like->num_rows == 0) {
            $sql_insert = "INSERT INTO me_gusta_arte (arte_id, usuario_id) VALUES (?, ?)";
            $stmt_insert = $conexion->prepare($sql_insert);
            $stmt_insert->bind_param("ii", $arte_id, $usuario_id);
            $stmt_insert->execute();
            $sql_update = "UPDATE galeria_arte SET me_gusta = me_gusta + 1 WHERE id = ?";
            $stmt_update = $conexion->prepare($sql_update);
            $stmt_update->bind_param("i", $arte_id);
            $stmt_update->execute();
        }
    } else {
        $sql_delete = "DELETE FROM me_gusta_arte WHERE arte_id = ? AND usuario_id = ?";
        $stmt_delete = $conexion->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $arte_id, $usuario_id);
        $stmt_delete->execute();
        if ($stmt_delete->affected_rows > 0) {
            $sql_update = "UPDATE galeria_arte SET me_gusta = GREATEST(me_gusta - 1, 0) WHERE id = ?";
            $stmt_update = $conexion->prepare($sql_update);
            $stmt_update->bind_param("i", $arte_id);
            $stmt_update->execute();
        }
    }
    $conexion->commit();
    redirigirConMensaje('../comunidad/ver_arte.php?id=' . $arte_id, 'Acción realizada correctamente.', 'exito');
} catch (Exception $e) {
    $conexion->rollback();
    redirigirConMensaje('../comunidad/ver_arte.php?id=' . $arte_id, 'Error al procesar la acción: ' . $e->getMessage(), 'error');
}
?>