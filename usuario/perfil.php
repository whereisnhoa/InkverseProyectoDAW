<?php
// Iniciar sesión
session_start();

// Determinar la ruta base del proyecto de forma más robusta
$document_root = $_SERVER['DOCUMENT_ROOT'];
$current_file = str_replace('\\', '/', __FILE__);
$document_root = str_replace('\\', '/', $document_root);

// Encontrar la posición de 'usuario' en la ruta actual
$usuario_pos = strrpos($current_file, '/usuario/');
if ($usuario_pos !== false) {
    $project_path = substr($current_file, 0, $usuario_pos);
} else {
    // Fallback: asumir que estamos en la carpeta usuario
    $project_path = dirname(dirname(__FILE__));
}

// Calcular la ruta relativa para BASE_URL
$relative_path = str_replace($document_root, '', $project_path);

// Definir BASE_URL si no está definido
if (!defined('BASE_URL')) {
    define('BASE_URL', $relative_path);
}

// Incluir archivos necesarios con rutas absolutas
$funciones_file = $project_path . '/includes/funciones.php';
$header_file = $project_path . '/includes/header.php';
$footer_file = $project_path . '/includes/footer.php';

// Verificar que los archivos existan antes de incluirlos
if (file_exists($funciones_file)) {
    require_once($funciones_file);
} else {
    die("Error: No se pudo encontrar el archivo de funciones en: " . $funciones_file);
}

// Verificar que el usuario esté logueado
verificarLogin();

// Incluir header
if (file_exists($header_file)) {
    include_once($header_file);
} else {
    // Header básico si no se encuentra el archivo
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mi Perfil - Proyecto</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1, h2, h3 { color: #333; }
            .profile-section { margin-bottom: 30px; }
            .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
            .btn:hover { background: #0056b3; }
        </style>
    </head>
    <body>
    <div class="container">';
}
?>

<div class="profile-section">
    <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></h1>
    
    <div class="profile-info">
        <h2>Información de tu cuenta</h2>
        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
        <?php if (isset($_SESSION['email'])): ?>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
        <?php endif; ?>
        <p><strong>Fecha de registro:</strong> <?php echo isset($_SESSION['fecha_registro']) ? htmlspecialchars($_SESSION['fecha_registro']) : 'No disponible'; ?></p>
    </div>
</div>

<div class="profile-section">
    <h2>Mis Acciones</h2>
    <ul>
        <li><a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php">Mis Obras</a></li>
        <li><a href="<?php echo BASE_URL; ?>/usuario/favoritos.php">Mis Favoritos</a></li>
        <li><a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php">Publicar Nueva Obra</a></li>
    </ul>
</div>

<div class="profile-section">
    <h2>Configuración de Cuenta</h2>
    <p><a href="<?php echo BASE_URL; ?>/usuario/editar_perfil.php" class="btn">Editar Perfil</a></p>
    <p><a href="<?php echo BASE_URL; ?>/usuario/cambiar_password.php" class="btn">Cambiar Contraseña</a></p>
</div>

<div class="profile-section">
    <a href="<?php echo BASE_URL; ?>/public/logout.php" class="btn">Cerrar sesión</a>
</div>

<?php 
if (file_exists($footer_file)) {
    include_once($footer_file);
} else {
    echo '</div></body></html>';
}
?>
