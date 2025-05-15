
<?php
session_start();
include '../includes/conexion.php';
include_once('../includes/funciones.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para subir arte.', 'error');
    exit();
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
    redirigirConMensaje(BASE_URL . '/comunidad/arte.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    exit();
}

// Verificar que se hayan aceptado los términos
if (!isset($_POST['acepta_terminos'])) {
    redirigirConMensaje(BASE_URL . '/comunidad/subir_arte.php', 'Debes aceptar los términos y condiciones para subir arte.', 'error');
    exit();
}

// Obtener datos del formulario
$titulo = limpiarDato($_POST['titulo']);
$descripcion = limpiarDato($_POST['descripcion']);
$categoria_id = intval($_POST['categoria_id']);
$publico_adulto = isset($_POST['publico_adulto']) ? 1 : 0;
$usuario_id = $_SESSION['usuario_id'];

// Validar datos
if (empty($titulo) || empty($categoria_id)) {
    redirigirConMensaje(BASE_URL . '/comunidad/subir_arte.php', 'Todos los campos obligatorios deben ser completados.', 'error');
    exit();
}

// Verificar que la categoría exista
$sql_check = "SELECT id FROM categorias_arte WHERE id = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $categoria_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    redirigirConMensaje(BASE_URL . '/comunidad/subir_arte.php', 'La categoría seleccionada no es válida.', 'error');
    exit();
}

// Procesar la imagen
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] != UPLOAD_ERR_OK) {
    redirigirConMensaje(BASE_URL . '/comunidad/subir_arte.php', 'Error al subir la imagen. Por favor, intenta nuevamente.', 'error');
    exit();
}

// Verificar tipo de archivo
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$file_type = $_FILES['imagen']['type'];

if (!in_array($file_type, $allowed_types)) {
    redirigirConMensaje(BASE_URL . '/comunidad/subir_arte.php', 'Tipo de archivo no permitido. Solo se aceptan imágenes JPG, PNG o GIF.', 'error');
    exit();
}

// Verificar tamaño de archivo (máximo 5MB)
$max_size = 5 * 1024 * 1024; // 5MB en bytes
if ($_FILES['imagen']['size'] > $max_size) {
    redirigirConMensaje(BASE_URL . '/comunidad/subir_arte.php', 'La imagen es demasiado grande. El tamaño máximo permitido es 5MB.', 'error');
    exit();
}

// Crear directorios si no existen
$upload_dir = '../uploads/arte/';
$thumb_dir = '../uploads/arte/thumbnails/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (!file_exists($thumb_dir)) {
    mkdir($thumb_dir, 0755, true);
}

// Generar nombre de archivo único
$file_extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
$file_name = uniqid('arte_') . '.' . $file_extension;
$file_path = $upload_dir . $file_name;
$thumb_path = $thumb_dir . $file_name;

// Mover archivo subido
if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $file_path)) {
    redirigirConMensaje(BASE_URL . '/comunidad/subir_arte.php', 'Error al guardar la imagen. Por favor, intenta nuevamente.', 'error');
    exit();
}

// Crear thumbnail
$thumb_created = false;
if (function_exists('imagecreatetruecolor')) {
    $max_width = 300;
    $max_height = 300;
    
    list($width, $height) = getimagesize($file_path);
    
    // Calcular nuevas dimensiones manteniendo la proporción
    if ($width > $height) {
        $new_width = $max_width;
        $new_height = intval($height * $max_width / $width);
    } else {
        $new_height = $max_height;
        $new_width = intval($width * $max_height / $height);
    }
    
    // Crear imagen
    $thumb = imagecreatetruecolor($new_width, $new_height);
    
    // Cargar imagen original según su tipo
    switch ($file_type) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($file_path);
            break;
        case 'image/png':
            $source = imagecreatefrompng($file_path);
            // Preservar transparencia
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($file_path);
            break;
    }
    
    // Redimensionar
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Guardar thumbnail según tipo de imagen
    switch ($file_type) {
        case 'image/jpeg':
            imagejpeg($thumb, $thumb_path, 80);
            break;
        case 'image/png':
            imagepng($thumb, $thumb_path, 8);
            break;
        case 'image/gif':
            imagegif($thumb, $thumb_path);
            break;
    }
    
    // Liberar memoria
    imagedestroy($source);
    imagedestroy($thumb);
    
    $thumb_created = true;
}

// Rutas relativas para la base de datos
$imagen_db = str_replace('../', BASE_URL . '/', $file_path);
$thumb_db = $thumb_created ? str_replace('../', BASE_URL . '/', $thumb_path) : null;

// Insertar en la base de datos
$sql = "INSERT INTO galeria_arte (usuario_id, categoria_id, titulo, descripcion, imagen, thumbnail, publico_adulto) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iissssi", $usuario_id, $categoria_id, $titulo, $descripcion, $imagen_db, $thumb_db, $publico_adulto);

if ($stmt->execute()) {
    $arte_id = $stmt->insert_id;
    redirigirConMensaje(BASE_URL . '/comunidad/ver_arte.php?id=' . $arte_id, 'Tu obra ha sido subida correctamente.', 'exito');
} else {
    // Si hay error, eliminar la imagen subida
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    if (file_exists($thumb_path)) {
        unlink($thumb_path);
    }
    
    redirigirConMensaje(BASE_URL . '/comunidad/subir_arte.php', 'Error al guardar la obra en la base de datos: ' . $stmt->error, 'error');
}
?>
