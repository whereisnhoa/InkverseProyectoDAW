<?php
session_start();
include '../includes/conexion.php';

// 1. Seguridad: solo usuarios logueados
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// 2. Recoge datos del formulario
$usuario_id = $_SESSION['usuario_id'];
$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$tipo = $_POST['tipo'];
$categoria_id = intval($_POST['categoria_id']);
$modo_publicacion = $_POST['modo_publicacion'];
$es_adulto = isset($_POST['es_adulto']) ? 1 : 0;

$archivo = '';
$portada = '';
// 3. Si es subida completa, maneja PDF e imagen
if ($modo_publicacion === 'completa') {
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
        $archivo = uniqid() . '_' . basename($_FILES['archivo']['name']);
        move_uploaded_file($_FILES['archivo']['tmp_name'], "../uploads/obras/" . $archivo);
    }
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] == 0) {
        $portada = uniqid() . '_' . basename($_FILES['portada']['name']);
        move_uploaded_file($_FILES['portada']['tmp_name'], "../uploads/portadas/" . $portada);
    }
}
// 4. Inserta la obra en la tabla correspondiente
if ($tipo === 'libro') {
    $sql = "INSERT INTO libros (titulo, descripcion, categoria_id, usuario_id, archivo, portada, publico_adulto, fecha_subida) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssiissi", $titulo, $descripcion, $categoria_id, $usuario_id, $archivo, $portada, $es_adulto);
    $stmt->execute();
    $obra_id = $stmt->insert_id;
} else {
    $sql = "INSERT INTO comics (titulo, descripcion, categoria_id, usuario_id, portada, publico_adulto, fecha_subida) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssiisi", $titulo, $descripcion, $categoria_id, $usuario_id, $portada, $es_adulto);
    $stmt->execute();
    $obra_id = $stmt->insert_id;
}

// 5. Redirige según el modo seleccionado
if ($modo_publicacion === 'capitulos') {
    // Redirige a la pantalla de subir capítulos
    header("Location: ../usuario/subir_capitulo.php?tipo=$tipo&obra_id=$obra_id");
    exit;
} else {
    // Redirige a mis obras
    header("Location: ../usuario/mis_obras.php");
    exit;
}
?>