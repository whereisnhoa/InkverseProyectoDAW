
<?php
/**
 * Header del sitio
 * Este archivo contiene la cabecera HTML y la navegación principal
 */

// iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

// incluir archivos de configuración y funciones
if (!defined('BASE_PATH')) 
{
    // ruta al archivo config.php
    require_once(__DIR__ . '/config.php');
}

// intentar incluir conexión con manejo de errores
if (file_exists(__DIR__ . '/conexion.php')) 
{
    require_once(__DIR__ . '/conexion.php');
} else {
    // si no se puede cargar conexion.php, crear una conexión básica
    $conexion = new mysqli("localhost", "root", "", "proyecto_db");
    if ($conexion->connect_error) 
    {
        die("Error de conexión: " . $conexion->connect_error);
    }
}

// cargar funciones si existe el archivo
if (file_exists(__DIR__ . '/funciones.php'))
 {
    require_once(__DIR__ . '/funciones.php');
}

?>

<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilo.css">
    
</head>

<body>

    <header>

        <nav>
            <a href="<?php echo BASE_URL; ?>/public/index.php">Inicio</a>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="<?php echo BASE_URL; ?>/public/explorar.php">Explorar</a>
                <a href="<?php echo BASE_URL; ?>/public/categorias.php">Categorías</a>
                <a href="<?php echo BASE_URL; ?>/usuario/perfil.php">Mi Perfil</a>
                <a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php">Mis Obras</a>
                <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php">Favoritos</a>
                <a href="<?php echo BASE_URL; ?>/public/logout.php">Cerrar Sesión</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/public/login.php">Login</a>
                <a href="<?php echo BASE_URL; ?>/public/register.php">Registro</a>
            <?php endif; ?>
        </nav>

    </header>
    
    <main>

        <?php 
        // mostrar mensajes de éxito o error si existen
        if (isset($_SESSION['mensaje'])) {
            $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
            echo '<div class="alerta alerta-' . $tipo . '">' . $_SESSION['mensaje'] . '</div>';
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
        }

        ?>
