
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
<html lang="<?= $idioma ?>">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inkverse</title>

    <!-- css -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilo.css">
    <!-- Font Awesome para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>

     <header>

        <div class="header-container">

            <div class="nav-left">

                <a href="<?php echo BASE_URL; ?>/public/index.php" class="logo">
                    <img src="<?php echo BASE_URL; ?>/includes/img/inkverse3.jpg" alt="Logo">
                    <span>InkVerse</span>
                </a>

                <nav>

                    <a href="<?php echo BASE_URL; ?>/public/index.php">
                        <i class="fas fa-home"></i> <?= htmlspecialchars($TEXTOS['inicio'] ?? 'Inicio') ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/explorar.php">
                        <i class="fas fa-compass"></i> <?= htmlspecialchars($TEXTOS['explorar'] ?? 'Explorar') ?>
                    </a>
                    
                    <div class="dropdown">

                        <button class="dropdown-toggle">
                            <i class="fas fa-tags"></i> <?= htmlspecialchars($TEXTOS['categorias'] ?? 'Categorías') ?> <i class="fas fa-chevron-down"></i>
                        </button>

                        <div class="dropdown-content">
                            <div class="dropdown-inner categorias-dropdown">
                                <div class="dropdown-section">
                                    <h4><i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['literatura'] ?? 'Literatura') ?></h4>
                                    <div class="dropdown-links">
                                        <?php foreach ($categorias_libros as $categoria): ?>
                                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=libro">
                                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                        <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="ver-todas">
                                            <i class="fas fa-th-large"></i> <?= htmlspecialchars($TEXTOS['ver_todas_obras'] ?? 'Ver todas las obras') ?>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/public/categorias.php?tipo=libro" class="ver-todas">
                                            <i class="fas fa-list"></i> <?= htmlspecialchars($TEXTOS['ver_todas_categorias'] ?? 'Ver todas las categorías') ?>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="dropdown-section">
                                    <h4><i class="fas fa-book-open"></i> <?= htmlspecialchars($TEXTOS['comic_manga'] ?? 'Cómic/Manga') ?></h4>
                                    <div class="dropdown-links">
                                        <?php foreach ($categorias_comics as $categoria): ?>
                                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=comic">
                                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                        <a href="<?php echo BASE_URL; ?>/public/comics.php" class="ver-todas">
                                            <i class="fas fa-th-large"></i> <?= htmlspecialchars($TEXTOS['ver_todas_obras'] ?? 'Ver todas las obras') ?>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/public/categorias.php?tipo=comic" class="ver-todas">
                                            <i class="fas fa-list"></i> <?= htmlspecialchars($TEXTOS['ver_todas_categorias'] ?? 'Ver todas las categorías') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dropdown">
                        <button class="dropdown-toggle">
                            <i class="fas fa-users"></i> <?= htmlspecialchars($TEXTOS['comunidad'] ?? 'Comunidad') ?> <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <div class="dropdown-inner comunidad-dropdown">
                                <div class="dropdown-section">
                                    <h4><?= htmlspecialchars($TEXTOS['participar'] ?? 'Participar') ?></h4>
                                    <div class="dropdown-links">
                                        <a href="<?php echo BASE_URL; ?>/comunidad/foros.php">
                                            <i class="fas fa-comments"></i> <?= htmlspecialchars($TEXTOS['foros_discusion'] ?? 'Foros de discusión') ?>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/comunidad/grupos.php">
                                            <i class="fas fa-user-friends"></i> <?= htmlspecialchars($TEXTOS['grupos_lectura'] ?? 'Grupos de lectura') ?>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/comunidad/eventos.php">
                                            <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($TEXTOS['eventos'] ?? 'Eventos') ?>
                                        </a>
                                    </div>
                                </div>

                                <div class="dropdown-section">
                                    <h4><?= htmlspecialchars($TEXTOS['contenido'] ?? 'Contenido') ?></h4>
                                    <div class="dropdown-links">
                                        <a href="<?php echo BASE_URL; ?>/comunidad/arte.php">
                                            <i class="fas fa-palette"></i> <?= htmlspecialchars($TEXTOS['galeria_arte'] ?? 'Galería de arte') ?>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/comunidad/recomendaciones.php">
                                            <i class="fas fa-thumbs-up"></i> <?= htmlspecialchars($TEXTOS['recomendaciones'] ?? 'Recomendaciones') ?>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/comunidad/noticias.php">
                                            <i class="fas fa-newspaper"></i> <?= htmlspecialchars($TEXTOS['noticias_novedades'] ?? 'Noticias y novedades') ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
            
            <div class="nav-right">
                <div class="search-bar">
                    <form action="<?php echo BASE_URL; ?>/public/buscar.php" method="GET">
                        <input type="text" name="q" placeholder="<?= htmlspecialchars($TEXTOS['buscar_obras'] ?? 'Buscar obras...') ?>" aria-label="Buscar">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                
                <iv class="action-icons">
                    
                <!-- Selector de idioma -->
                <div class="dropdown flag-dropdown">

                    <button class="dropdown-toggle" aria-label="Seleccionar idioma">

                        <img src="<?php echo BASE_URL; ?>/img/<?php echo htmlspecialchars($idioma); ?>.png" alt="<?php echo htmlspecialchars($idioma); ?>">
                        <i class="fas fa-chevron-down"></i>
                    
                    </button>

                    <div class="dropdown-content flag-dropdown-content">

                        <a href="?lang=es<?php echo isset($_GET['order']) ? '&order='.htmlspecialchars($_GET['order']) : ''; ?><?php echo isset($_GET['direction']) ? '&direction='.htmlspecialchars($_GET['direction']) : ''; ?>" class="flag-item <?php echo ($idioma == 'es') ? 'active' : ''; ?>">
                            <img src="<?php echo BASE_URL; ?>/includes/img/es.png" alt="Español">
                            <span>Español</span>
                        </a>

                        <a href="?lang=en<?php echo isset($_GET['order']) ? '&order='.htmlspecialchars($_GET['order']) : ''; ?><?php echo isset($_GET['direction']) ? '&direction='.htmlspecialchars($_GET['direction']) : ''; ?>" class="flag-item <?php echo ($idioma == 'en') ? 'active' : ''; ?>">
                            <img src="<?php echo BASE_URL; ?>/includes/img/en.png" alt="English">
                            <span>English</span>
                        </a>

                        <a href="?lang=fr<?php echo isset($_GET['order']) ? '&order='.htmlspecialchars($_GET['order']) : ''; ?><?php echo isset($_GET['direction']) ? '&direction='.htmlspecialchars($_GET['direction']) : ''; ?>" class="flag-item <?php echo ($idioma == 'fr') ? 'active' : ''; ?>">
                            <img src="<?php echo BASE_URL; ?>/includes/img/fr.png" alt="Français">
                            <span>Français</span>
                        </a>

                        <a href="?lang=fr<?php echo isset($_GET['order']) ? '&order='.htmlspecialchars($_GET['order']) : ''; ?><?php echo isset($_GET['direction']) ? '&direction='.htmlspecialchars($_GET['direction']) : ''; ?>" class="flag-item <?php echo ($idioma == 'fr') ? 'active' : ''; ?>">
                            <img src="<?php echo BASE_URL; ?>/includes/img/fr.png" alt="Français">
                            <span>Français</span>
                        </a>

                        <a href="?lang=fr<?php echo isset($_GET['order']) ? '&order='.htmlspecialchars($_GET['order']) : ''; ?><?php echo isset($_GET['direction']) ? '&direction='.htmlspecialchars($_GET['direction']) : ''; ?>" class="flag-item <?php echo ($idioma == 'ca') ? 'active' : ''; ?>">
                            <img src="<?php echo BASE_URL; ?>/includes/img/ca.png" alt="Catalá">
                            <span>Catalá</span>
                        </a>

                    </div>
                </div>
            
                <!-- favoritos -->
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php" title="<?= htmlspecialchars($TEXTOS['mis_favoritos'] ?? 'Mis favoritos') ?>" class="icon-btn">
                            <i class="fas fa-heart"></i>
                        </a>
                        
                        <!-- notificaciones -->
                        <div class="dropdown notification-dropdown">
                            <button class="dropdown-toggle icon-btn" title="<?= htmlspecialchars($TEXTOS['notificaciones'] ?? 'Notificaciones') ?>">
                                <i class="fas fa-bell"></i>
                                <?php 
                                $notificaciones_no_leidas = contarNotificacionesNoLeidas($_SESSION['usuario_id']);
                                if ($notificaciones_no_leidas > 0): 
                                ?>
                                <span class="notification-badge"><?php echo $notificaciones_no_leidas; ?></span>
                                <?php endif; ?>
                            </button>

                            <div class="dropdown-content notification-dropdown-content">
                                <div class="notification-header">
                                    <h4><?= htmlspecialchars($TEXTOS['notificaciones'] ?? 'Notificaciones') ?></h4>
                                    <?php if ($notificaciones_no_leidas > 0): ?>
                                        <a href="<?php echo BASE_URL; ?>/controllers/procesar-notificaciones.php?accion=marcar_todas_leidas" class="notification-mark-all">
                                            <?= htmlspecialchars($TEXTOS['marcar_todas_leidas'] ?? 'Marcar todas como leídas') ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="notification-list">
                                    <?php
                                    $notificaciones = obtenerNotificaciones($_SESSION['usuario_id'], 5);
                                    if ($notificaciones && $notificaciones->num_rows > 0):
                                        while ($notificacion = $notificaciones->fetch_assoc()):
                                    ?>
                                        <a href="<?php echo $notificacion['url']; ?>" class="notification-item <?php echo $notificacion['leida'] ? '' : 'unread'; ?>">
                                            <div class="notification-icon">
                                                <?php
                                                $icon = 'bell';
                                                switch ($notificacion['tipo']) {
                                                    case 'comentario': $icon = 'comment'; break;
                                                    case 'like': $icon = 'heart'; break;
                                                    case 'respuesta_foro': $icon = 'reply'; break;
                                                    case 'nueva_obra': $icon = 'book'; break;
                                                    case 'sistema': $icon = 'info-circle'; break;
                                                }
                                                ?>
                                                <i class="fas fa-<?php echo $icon; ?>"></i>
                                            </div>
                                            <div class="notification-content">
                                                <p><?= htmlspecialchars($notificacion['mensaje']); ?></p> 
                                                <span class="notification-time">
                                                    <?php // Si time_elapsed_string() produce texto como "hace x minutos", también necesita traducir eso
                                                    echo time_elapsed_string($notificacion['fecha_creacion']); 
                                                    ?>
                                                </span>
                                            </div>
                                        </a>
                                    <?php
                                        endwhile;
                                    else:
                                    ?>
                                        <div class="notification-empty">
                                            <p><?= htmlspecialchars($TEXTOS['no_notificaciones'] ?? 'No tienes notificaciones') ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="notification-footer">
                                    <a href="<?php echo BASE_URL; ?>/usuario/notificaciones.php"><?= htmlspecialchars($TEXTOS['ver_todas'] ?? 'Ver todas') ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- modo oscuro -->
                    <button class="dark-mode-toggle icon-btn" title="<?= htmlspecialchars($TEXTOS['cambiar_modo'] ?? 'Cambiar modo oscuro/claro') ?>">
                        <i class="fas fa-moon"></i>
                    </button>
                    
                    <!-- usuario -->
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
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($TEXTOS['mi_perfil'] ?? 'Mi Perfil') ?>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php">
                                    <i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['mis_obras'] ?? 'Mis Obras') ?>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php">
                                    <i class="fas fa-heart"></i> <?= htmlspecialchars($TEXTOS['favoritos'] ?? 'Favoritos') ?>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/usuario/notificaciones.php">
                                    <i class="fas fa-bell"></i> <?= htmlspecialchars($TEXTOS['notificaciones'] ?? 'Notificaciones') ?>
                                    <?php if ($notificaciones_no_leidas > 0): ?>
                                        <span class="badge"><?php echo $notificaciones_no_leidas; ?></span>
                                    <?php endif; ?>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php">
                                    <i class="fas fa-plus-circle"></i> <?= htmlspecialchars($TEXTOS['nueva_obra'] ?? 'Nueva Obra') ?>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="<?php echo BASE_URL; ?>/public/logout.php" class="logout-link">
                                    <i class="fas fa-sign-out-alt"></i> <?= htmlspecialchars($TEXTOS['cerrar_sesion'] ?? 'Cerrar Sesión') ?>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/public/login.php">
                                    <i class="fas fa-sign-in-alt"></i> <?= htmlspecialchars($TEXTOS['iniciar_sesion'] ?? 'Iniciar Sesión') ?>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/public/register.php">
                                    <i class="fas fa-user-plus"></i> <?= htmlspecialchars($TEXTOS['registrarse'] ?? 'Registrarse') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- menú desplegable para móviles -->
                    <button class="mobile-menu-toggle icon-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
        
    </header>
    
    <main>

        <?php 
        // Mostrar mensajes de éxito o error si existen

        if (isset($_SESSION['mensaje'])) 
        {
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