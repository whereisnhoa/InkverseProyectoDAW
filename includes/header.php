
<?php
/**
 * Header del sitio
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

// Obtener categorías para los menús desplegables
$sql_categorias_libros = "SELECT id, nombre FROM categorias WHERE tipo = 'libro' ORDER BY nombre LIMIT 6";
$result_categorias_libros = $conexion->query($sql_categorias_libros);
$categorias_libros = [];
if ($result_categorias_libros && $result_categorias_libros->num_rows > 0) {
    while ($row = $result_categorias_libros->fetch_assoc()) {
        $categorias_libros[] = $row;
    }
}

$sql_categorias_comics = "SELECT id, nombre FROM categorias WHERE tipo = 'comic' ORDER BY nombre LIMIT 6";
$result_categorias_comics = $conexion->query($sql_categorias_comics);
$categorias_comics = [];
if ($result_categorias_comics && $result_categorias_comics->num_rows > 0) {
    while ($row = $result_categorias_comics->fetch_assoc()) {
        $categorias_comics[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVerse - <?= htmlspecialchars($TEXTOS['titulo_sitio'] ?? 'Tu Portal de Literatura y Cómics') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilo.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>/img/favicon.ico" type="image/x-icon">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="nav-left">
                    <a href="<?php echo BASE_URL; ?>/public/index.php" class="logo">
                        <img src="<?php echo BASE_URL; ?>/includes/img/inkverse3.jpg" alt="Logo">
                        <span>InkVerse</span>
                    </a>
                    <nav>
                        <ul>
                            <li><a href="<?php echo BASE_URL; ?>/public/index.php"><?= htmlspecialchars($TEXTOS['inicio'] ?? 'Inicio') ?></a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle"><?= htmlspecialchars($TEXTOS['literatura'] ?? 'Literatura') ?> <i class="fas fa-chevron-down"></i></a>
                                <div class="dropdown-content">
                                    <div class="dropdown-inner">
                                        <div class="dropdown-section">
                                            <h3><?= htmlspecialchars($TEXTOS['categorias'] ?? 'Categorías') ?></h3>
                                            <ul>
                                                <?php foreach ($categorias_libros as $cat): ?>
                                                    <li><a href="<?php echo BASE_URL; ?>/public/categorias.php?tipo=libro&id=<?= htmlspecialchars($cat['id']); ?>"><?= htmlspecialchars($cat['nombre']); ?></a></li>
                                                <?php endforeach; ?>
                                                <li><a href="<?php echo BASE_URL; ?>/public/categorias.php?tipo=libro"><?= htmlspecialchars($TEXTOS['ver_todas'] ?? 'Ver Todas') ?></a></li>
                                            </ul>
                                        </div>
                                        <div class="dropdown-section">
                                            <h3><?= htmlspecialchars($TEXTOS['explorar'] ?? 'Explorar') ?></h3>
                                            <ul>
                                                <li><a href="#"><?= htmlspecialchars($TEXTOS['autores'] ?? 'Autores') ?></a></li>
                                                <li><a href="#"><?= htmlspecialchars($TEXTOS['series'] ?? 'Series') ?></a></li>
                                                <li><a href="#"><?= htmlspecialchars($TEXTOS['reseñas'] ?? 'Reseñas') ?></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle"><?= htmlspecialchars($TEXTOS['comics'] ?? 'Cómics') ?> <i class="fas fa-chevron-down"></i></a>
                                <div class="dropdown-content">
                                    <div class="dropdown-inner">
                                        <div class="dropdown-section">
                                            <h3><?= htmlspecialchars($TEXTOS['categorias'] ?? 'Categorías') ?></h3>
                                            <ul>
                                                <?php foreach ($categorias_comics as $cat): ?>
                                                    <li><a href="<?php echo BASE_URL; ?>/public/categorias.php?tipo=comic&id=<?= htmlspecialchars($cat['id']); ?>"><?= htmlspecialchars($cat['nombre']); ?></a></li>
                                                <?php endforeach; ?>
                                                <li><a href="<?php echo BASE_URL; ?>/public/categorias.php?tipo=comic"><?= htmlspecialchars($TEXTOS['ver_todas'] ?? 'Ver Todas') ?></a></li>
                                            </ul>
                                        </div>
                                        <div class="dropdown-section">
                                            <h3><?= htmlspecialchars($TEXTOS['explorar'] ?? 'Explorar') ?></h3>
                                            <ul>
                                                <li><a href="#"><?= htmlspecialchars($TEXTOS['artistas'] ?? 'Artistas') ?></a></li>
                                                <li><a href="#"><?= htmlspecialchars($TEXTOS['editoriales'] ?? 'Editoriales') ?></a></li>
                                                <li><a href="#"><?= htmlspecialchars($TEXTOS['colecciones'] ?? 'Colecciones') ?></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li><a href="<?php echo BASE_URL; ?>/public/foro.php"><?= htmlspecialchars($TEXTOS['foro'] ?? 'Foro') ?></a></li>
                            <li><a href="<?php echo BASE_URL; ?>/public/blog.php"><?= htmlspecialchars($TEXTOS['blog'] ?? 'Blog') ?></a></li>
                            <li><a href="<?php echo BASE_URL; ?>/public/contacto.php"><?= htmlspecialchars($TEXTOS['contacto'] ?? 'Contacto') ?></a></li>
                        </ul>
                    </nav>
                </div>
                <div class="nav-right">
                    <div class="action-icons">
                        <button class="search-toggle icon-btn">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="dark-mode-toggle icon-btn" aria-label="<?= htmlspecialchars($TEXTOS['toggle_dark_mode'] ?? 'Toggle Dark Mode') ?>">
                            <i class="fas fa-moon"></i>
                        </button>
                        
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                        <div class="dropdown notification-dropdown">
                            <button class="dropdown-toggle icon-btn" aria-label="<?= htmlspecialchars($TEXTOS['notificaciones'] ?? 'Notificaciones') ?>">
                                <i class="fas fa-bell"></i>
                                </button>
                            <div class="dropdown-content notification-dropdown-content">
                                <p class="dropdown-title"><?= htmlspecialchars($TEXTOS['notificaciones'] ?? 'Notificaciones') ?></p>
                                <ul class="notification-list">
                                    <li><a href="#">Nueva respuesta en tu hilo.</a></li>
                                    <li><a href="#">Has ganado una insignia.</a></li>
                                    <li><a href="#">Actualización de una novela que sigues.</a></li>
                                </ul>
                                <a href="#" class="view-all"><?= htmlspecialchars($TEXTOS['ver_todas_notificaciones'] ?? 'Ver todas') ?></a>
                            </div>
                        </div>

                        <div class="dropdown user-dropdown">
                            <button class="dropdown-toggle icon-btn" aria-label="<?= htmlspecialchars($TEXTOS['menu_usuario'] ?? 'Menú de Usuario') ?>">
                                <i class="fas fa-user-circle"></i>
                            </button>
                            <div class="dropdown-content user-dropdown-content">
                                <p class="dropdown-title"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></p>
                                <ul class="user-menu-list">
                                    <li><a href="<?php echo BASE_URL; ?>/public/perfil.php"><i class="fas fa-user"></i> <?= htmlspecialchars($TEXTOS['mi_perfil'] ?? 'Mi Perfil') ?></a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/public/configuracion.php"><i class="fas fa-cog"></i> <?= htmlspecialchars($TEXTOS['configuracion'] ?? 'Configuración') ?></a></li>
                                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
                                        <li><a href="<?php echo BASE_URL; ?>/admin/index.php"><i class="fas fa-tachometer-alt"></i> <?= htmlspecialchars($TEXTOS['panel_admin'] ?? 'Panel de Administración') ?></a></li>
                                    <?php endif; ?>
                                    <li><a href="<?php echo BASE_URL; ?>/public/logout.php"><i class="fas fa-sign-out-alt"></i> <?= htmlspecialchars($TEXTOS['cerrar_sesion'] ?? 'Cerrar Sesión') ?></a></li>
                                </ul>
                            </div>
                        </div>
                        <?php else: ?>
                            <div class="user-actions">
                                <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-sign-in-alt"></i> <?= htmlspecialchars($TEXTOS['iniciar_sesion'] ?? 'Iniciar Sesión') ?>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-user-plus"></i> <?= htmlspecialchars($TEXTOS['registrarse'] ?? 'Registrarse') ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="dropdown language-dropdown">
                            <button class="dropdown-toggle icon-btn" aria-label="<?= htmlspecialchars($TEXTOS['select_language'] ?? 'Select Language') ?>">
                                <img src="<?php echo BASE_URL; ?>/img/<?php echo $idioma; ?>.png" alt="<?= htmlspecialchars($idioma); ?> Flag" class="flag-icon-main">
                            </button>
                            <div class="dropdown-content language-dropdown-content">
                                <p class="dropdown-title"><?= htmlspecialchars($TEXTOS['select_language'] ?? 'Select Language') ?>:</p>
                                <div class="flags-dropdown-list">
                                    <a href="?lang=es<?php echo isset($_GET['order']) ? '&order='.htmlspecialchars($_GET['order']) : ''; ?><?php echo isset($_GET['direction']) ? '&direction='.htmlspecialchars($_GET['direction']) : ''; ?>" class="flag-item <?php echo ($idioma == 'es') ? 'active' : ''; ?>">
                                        <img src="<?php echo BASE_URL; ?>/img/es.png" alt="Español">
                                        <span>Español</span>
                                    </a>
                                    <a href="?lang=en<?php echo isset($_GET['order']) ? '&order='.htmlspecialchars($_GET['order']) : ''; ?><?php echo isset($_GET['direction']) ? '&direction='.htmlspecialchars($_GET['direction']) : ''; ?>" class="flag-item <?php echo ($idioma == 'en') ? 'active' : ''; ?>">
                                        <img src="<?php echo BASE_URL; ?>/img/en.png" alt="English">
                                        <span>English</span>
                                    </a>
                                    <a href="?lang=ca<?php echo isset($_GET['order']) ? '&order='.htmlspecialchars($_GET['order']) : ''; ?><?php echo isset($_GET['direction']) ? '&direction='.htmlspecialchars($_GET['direction']) : ''; ?>" class="flag-item <?php echo ($idioma == 'ca') ? 'active' : ''; ?>">
                                        <img src="<?php echo BASE_URL; ?>/img/ca.png" alt="Català">
                                        <span>Català</span>
                                    </a>
                                    <a href="?lang=fr<?php echo isset($_GET['order']) ? '&order='.htmlspecialchars($_GET['order']) : ''; ?><?php echo isset($_GET['direction']) ? '&direction='.htmlspecialchars($_GET['direction']) : ''; ?>" class="flag-item <?php echo ($idioma == 'fr') ? 'active' : ''; ?>">
                                        <img src="<?php echo BASE_URL; ?>/img/fr.png" alt="Français">
                                        <span>Français</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <button class="mobile-menu-toggle icon-btn">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div> </div>
            </div> </div> </header>
    
    <main>
        
        <?php
        // Mostrar mensajes de éxito o error si existen
        if (isset($_SESSION['mensaje'])) {
            $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
            // clave de traducción
            $mensaje_clave = $_SESSION['mensaje'];
            // $TEXTOS para obtener la traducción
            $mensaje_traducido = $TEXTOS[$mensaje_clave] ?? $mensaje_clave;
            echo '<div class="alerta alerta-' . $tipo . '">' . htmlspecialchars($mensaje_traducido) . '</div>';
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
        }
        ?>