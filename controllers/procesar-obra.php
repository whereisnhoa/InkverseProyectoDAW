<?php
session_start();
include '../includes/conexion.php';

// Validación básica de sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../public/login.php");
    exit();
}

// Recoger datos del formulario
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$tipo = $_POST['tipo'] ?? '';
$genero = $_POST['genero'] ?? '';
$es_adulto = isset($_POST['es_adulto']) ? 1 : 0;
$autor = $_SESSION['usuario'];

// Validar campos obligatorios
if ($titulo === '' || $tipo === '' || ($tipo === 'libro' && $genero === '')) {
    echo "Faltan datos obligatorios.";
    exit();
}

// Procesar portada si se subió
$portada_relativa = null;
if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
    $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $tipo_img = mime_content_type($_FILES['portada']['tmp_name']);
    $tam = $_FILES['portada']['size'];

    if (!in_array($tipo_img, $permitidos)) {
        echo "La portada debe ser JPG, PNG o GIF.";
        exit();
    }
    if ($tam > $max_size) {
        echo "La portada excede el tamaño máximo de 5MB.";
        exit();
    }

    $dest_dir = __DIR__ . '/../uploads/portadas/';
    if (!is_dir($dest_dir)) {
        mkdir($dest_dir, 0775, true);
    }

    $ext = pathinfo($_FILES['portada']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid('portada_') . '.' . strtolower($ext);
    $ruta_relativa = '/uploads/portadas/' . $nombre_archivo;
    $ruta_absoluta = $dest_dir . $nombre_archivo;

    if (!move_uploaded_file($_FILES['portada']['tmp_name'], $ruta_absoluta)) {
        echo "Error al guardar la imagen de portada.";
        exit();
    }
    $portada_relativa = $ruta_relativa;
}

// Insertar en la tabla correspondiente
if ($tipo === 'libro') 
{
    // Obtener la categoría basada en el género
    $sql_categoria = "SELECT id FROM categorias WHERE nombre = ? LIMIT 1";
    $stmt_categoria = $conexion->prepare($sql_categoria);
    $stmt_categoria->bind_param("s", $genero);
    $stmt_categoria->execute();
    $result_categoria = $stmt_categoria->get_result();
    
    if ($result_categoria->num_rows > 0) {
        $categoria = $result_categoria->fetch_assoc();
        $categoria_id = $categoria['id'];
    } else {
        // Si no existe la categoría, usar NULL
        $categoria_id = NULL;
    }
    
    $sql = "INSERT INTO libros (titulo, autor, descripcion, categoria_id, portada, publico_adulto)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssisi", $titulo, $autor, $descripcion, $categoria_id, $portada_relativa, $es_adulto);

} else {
    // Es un cómic, puedes agregar portada si la tabla comics también tiene ese campo
    $sql = "INSERT INTO comics (titulo, autor, descripcion, portada, publico_adulto)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssi", $titulo, $autor, $descripcion, $portada_relativa, $es_adulto);
}

if ($stmt->execute()) {
    header("Location: ../usuario/mis_obras.php?exito=1");
    exit();
} else {
    echo "Error al guardar la obra: " . $stmt->error;
}
?>