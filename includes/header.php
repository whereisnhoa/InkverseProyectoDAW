
<?php
/**
 * Header del sitio
 * Este archivo contiene la cabecera HTML y la navegación principal
 */

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos de configuración y funciones
if (!defined('BASE_PATH')) {
    // Ruta al archivo config.php
    require_once(__DIR__ . '/config.php');
}

// Intentar incluir conexión con manejo de errores
if (file_exists(__DIR__ . '/conexion.php')) {
    require_once(__DIR__ . '/conexion.php');
} else {
    // Si no se puede cargar conexion.php, crear una conexión básica
    $conexion = new mysqli("localhost", "root", "", "proyecto_db");
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
}

// Cargar funciones si existe el archivo
if (file_exists(__DIR__ . '/funciones.php')) {
    require_once(__DIR__ . '/funciones.php');
}

// Obtener categorías para el menú desplegable
$sql_categorias = "SELECT id, nombre FROM categorias ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = [];
if ($result_categorias && $result_categorias->num_rows > 0) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyecto</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilo.css">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <!-- Logo y navegación principal -->
            <div class="nav-left">
                <a href="<?php echo BASE_URL; ?>/public/index.php" class="logo">
                    <img src="<?php echo BASE_URL; ?>/img/logo.png" alt="Logo">
                    <span>Proyecto</span>
                </a>
                
                <nav>
                    <a href="<?php echo BASE_URL; ?>/public/index.php">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                    
                    <a href="<?php echo BASE_URL; ?>/public/explorar.php">
                        <i class="fas fa-compass"></i> Explorar
                    </a>
                    
                    <!-- Menú desplegable de categorías -->
                    <div class="dropdown">
                        <button class="dropdown-toggle">
                            <i class="fas fa-tags"></i> Categorías <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <div class="dropdown-inner">
                                <div class="dropdown-section">
                                    <h4>Libros</h4>
                                    <div class="dropdown-links">
                                        <?php foreach (array_slice($categorias, 0, 5) as $categoria): ?>
                                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=libro">
                                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?tipo=libro" class="ver-todas">
                                            <i class="fas fa-ellipsis-h"></i> Ver todas
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown-section">
                                    <h4>Cómics</h4>
                                    <div class="dropdown-links">
                                        <?php foreach (array_slice($categorias, 0, 5) as $categoria): ?>
                                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=comic">
                                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?tipo=comic" class="ver-todas">
                                            <i class="fas fa-ellipsis-h"></i> Ver todas
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
            
            <!-- Barra de búsqueda y acciones de usuario -->
            <div class="nav-right">
                <!-- Barra de búsqueda -->
                <div class="search-bar">
                    <form action="<?php echo BASE_URL; ?>/public/buscar.php" method="GET">
                        <input type="text" name="q" placeholder="Buscar obras..." aria-label="Buscar">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <!-- Iconos de acción -->
                <div class="action-icons">
                    <!-- Favoritos (solo para usuarios logueados) -->
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php" title="Mis favoritos" class="icon-btn">
                            <i class="fas fa-heart"></i>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Toggle de modo oscuro -->
                    <button class="dark-mode-toggle icon-btn" title="Cambiar modo oscuro/claro">
                        <i class="fas fa-moon"></i>
                    </button>
                    
                    <!-- Menú de usuario -->
                    <div class="dropdown user-dropdown">
                        <button class="dropdown-toggle icon-btn">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <div class="dropdown-content user-dropdown-content">
                            <?php if (isset($_SESSION['usuario_id'])): ?>
                                <div class="user-info">
                                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                                </div>
                                <a href="<?php echo BASE_URL; ?>/usuario/perfil.php">
                                    <i class="fas fa-user"></i> Mi Perfil
                                </a>
                                <a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php">
                                    <i class="fas fa-book"></i> Mis Obras
                                </a>
                                <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php">
                                    <i class="fas fa-heart"></i> Favoritos
                                </a>
                                <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php">
                                    <i class="fas fa-plus-circle"></i> Nueva Obra
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo BASE_URL; ?>/public/logout.php" class="logout-link">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/public/login.php">
                                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                                </a>
                                <a href="<?php echo BASE_URL; ?>/public/register.php">
                                    <i class="fas fa-user-plus"></i> Registrarse
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Botón de menú móvil -->
                    <button class="mobile-menu-toggle icon-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <main>
        <?php 
        // Mostrar mensajes de éxito o error si existen
        if (isset($_SESSION['mensaje'])) {
            $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
            echo '<div class="alerta alerta-' . $tipo . '">' . $_SESSION['mensaje'] . '</div>';
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
        }
        ?>
