<?php
session_start();
include '../includes/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || !isset($_POST['contenido_id']) || !isset($_POST['tipo_contenido']) || !isset($_POST['accion'])) {
    echo json_encode([
        "success" => false,
        "mensaje" => "Parámetros incompletos o sesión caducada"
    ]);
    exit();
}

$usuario_id = intval($_SESSION['usuario_id']);
$contenido_id = intval($_POST['contenido_id']);
$tipo_contenido = $_POST['tipo_contenido']; // 'libro' o 'comic'
$accion = $_POST['accion'];

if ($accion === "agregar") {
    $sql = "INSERT IGNORE INTO favoritos (usuario_id, tipo_contenido, contenido_id) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("isi", $usuario_id, $tipo_contenido, $contenido_id);
    $success = $stmt->execute();
    echo json_encode([
        "success" => $success,
        "agregado" => $success,
        "mensaje" => $success ? "Obra añadida a favoritos" : "No se pudo añadir a favoritos"
    ]);
    exit();
} elseif ($accion === "quitar") {
    $sql = "DELETE FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("isi", $usuario_id, $tipo_contenido, $contenido_id);
    $success = $stmt->execute();
    echo json_encode([
        "success" => $success,
        "agregado" => false,
        "mensaje" => $success ? "Obra quitada de favoritos" : "No se pudo quitar de favoritos"
    ]);
    exit();
} elseif ($accion === "toggle") {
    $sql_check = "SELECT 1 FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?";
    $stmt = $conexion->prepare($sql_check);
    $stmt->bind_param("isi", $usuario_id, $tipo_contenido, $contenido_id);
    $stmt->execute();
    $stmt->store_result();
    $yaEsFavorito = ($stmt->num_rows > 0);
    $stmt->close();

    if ($yaEsFavorito) {
        $sql = "DELETE FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isi", $usuario_id, $tipo_contenido, $contenido_id);
        $success = $stmt->execute();
        echo json_encode([
            "success" => $success,
            "agregado" => false,
            "mensaje" => $success ? "Obra quitada de favoritos" : "No se pudo quitar de favoritos"
        ]);
        exit();
    } else {
        $sql = "INSERT IGNORE INTO favoritos (usuario_id, tipo_contenido, contenido_id) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isi", $usuario_id, $tipo_contenido, $contenido_id);
        $success = $stmt->execute();
        echo json_encode([
            "success" => $success,
            "agregado" => $success,
            "mensaje" => $success ? "Obra añadida a favoritos" : "No se pudo añadir a favoritos"
        ]);
        exit();
    }
} else {
    echo json_encode([
        "success" => false,
        "mensaje" => "Acción no válida"
    ]);
    exit();
}
?>