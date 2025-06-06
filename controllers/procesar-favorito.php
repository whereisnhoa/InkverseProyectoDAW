<?php
session_start();
require_once(__DIR__ . '/../includes/conexion.php');
header('Content-Type: application/json');

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['success' => false, 'mensaje' => 'No autenticado']);
    exit;
}

$accion = $_POST['accion'] ?? '';
$contenido_id = isset($_POST['contenido_id']) ? intval($_POST['contenido_id']) : 0;
$tipo_contenido = strtolower(trim($_POST['tipo_contenido'] ?? ''));

if ($tipo_contenido === 'libros') $tipo_contenido = 'libro';
if ($tipo_contenido === 'comics') $tipo_contenido = 'comic';

if (($accion === 'quitar' || $accion === 'toggle') && $contenido_id && ($tipo_contenido === 'libro' || $tipo_contenido === 'comic')) {
    $stmt = $conexion->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?");
    $stmt->bind_param("isi", $usuario_id, $tipo_contenido, $contenido_id);
    $stmt->execute();
    $stmt->store_result();
    $existe = $stmt->num_rows > 0;
    $stmt->close();

    if ($accion === 'toggle') {
        if ($existe) {
            $stmt = $conexion->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?");
            $stmt->bind_param("isi", $usuario_id, $tipo_contenido, $contenido_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['agregado' => false]);
            exit;
        } else {
            $stmt = $conexion->prepare("INSERT INTO favoritos (usuario_id, tipo_contenido, contenido_id) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $usuario_id, $tipo_contenido, $contenido_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['agregado' => true]);
            exit;
        }
    } elseif ($accion === 'quitar') {
        $stmt = $conexion->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?");
        $stmt->bind_param("isi", $usuario_id, $tipo_contenido, $contenido_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'mensaje' => 'Datos de obra inválidos']);
exit;