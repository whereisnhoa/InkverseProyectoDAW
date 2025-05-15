
<?php
session_start();
include '../includes/conexion.php';
include_once('../includes/funciones.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para crear un tema.', 'error');
    exit();
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
    redirigirConMensaje(BASE_URL . '/comunidad/foros.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
    exit();
}

// Verificar que se hayan aceptado las reglas
if (!isset($_POST['acepta_reglas'])) {
    redirigirConMensaje(BASE_URL . '/comunidad/nuevo_tema.php', 'Debes aceptar las reglas del foro para crear un tema.', 'error');
    exit();
}

// Obtener datos del formulario
$titulo = limpiarDato($_POST['titulo']);
$contenido = limpiarDato($_POST['contenido']);
$categoria_id = intval($_POST['categoria_id']);
$usuario_id = $_SESSION['usuario_id'];

// Validar datos
if (empty($titulo) || empty($contenido) || empty($categoria_id)) {
    redirigirConMensaje(BASE_URL . '/comunidad/nuevo_tema.php', 'Todos los campos son obligatorios.', 'error');
    exit();
}

// Verificar que la categoría exista
$sql_check = "SELECT id FROM categorias_foro WHERE id = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("i", $categoria_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    redirigirConMensaje(BASE_URL . '/comunidad/nuevo_tema.php', 'La categoría seleccionada no es válida.', 'error');
    exit();
}

// Insertar en la base de datos
$sql = "INSERT INTO temas_foro (categoria_id, usuario_id, titulo, contenido) VALUES (?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iiss", $categoria_id, $usuario_id, $titulo, $contenido);

if ($stmt->execute()) {
    $tema_id = $stmt->insert_id;
    redirigirConMensaje(BASE_URL . '/comunidad/tema.php?id=' . $tema_id, 'Tu tema ha sido creado correctamente.', 'exito');
} else {
    redirigirConMensaje(BASE_URL . '/comunidad/nuevo_tema.php', 'Error al crear el tema: ' . $stmt->error, 'error');
}
?>
