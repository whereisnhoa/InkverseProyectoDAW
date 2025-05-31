<?php
/**
 * Header del sitio - Versión completamente funcional
 */

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos de configuración y funciones
if (!defined('BASE_URL')) {
    require_once(__DIR__ . '/config.php');
}

// Intentar incluir conexión con manejo de errores
if (file_exists(__DIR__ . '/conexion.php')) {
    require_once(__DIR__ . '/conexion.php');
} else {
    $conexion = new mysqli("localhost", "root", "", "proyecto_db");
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
}

// Cargar funciones si existe el archivo
if (file_exists(__DIR__ . '/funciones.php')) {
    require_once(__DIR__ . '/funciones.php');
}

// Lógica para el idioma actual
$idioma = isset($_GET['lang']) ? $_GET['lang'] : 'es';

// Textos por defecto
if (!isset($TEXTOS)) {
    $TEXTOS = [
        'inicio' => 'Inicio',
        'explorar' => 'Explorar',
        'categorias' => 'Categorías',
        'literatura' => 'Literatura',
        'comic_manga' => 'Cómic/Manga',
        'ver_todas_obras' => 'Ver todas las obras',
        'ver_todas_categorias' => 'Ver todas las categorías',
        'comunidad' => 'Comunidad',
        'participar' => 'Participar',
        'foros_discusion' => 'Foros de discusión',
        'grupos_lectura' => 'Grupos de lectura',
        'eventos' => 'Eventos',
        'contenido' => 'Contenido',
        'galeria_arte' => 'Galería de arte',
        'recomendaciones' => 'Recomendaciones',
        'noticias_novedades' => 'Noticias y novedades',
        'buscar_obras' => 'Buscar obras...',
        'mis_favoritos' => 'Mis favoritos',
        'notificaciones' => 'Notificaciones',
        'marcar_todas_leidas' => 'Marcar todas como leídas',
        'no_notificaciones' => 'No tienes notificaciones',
        'ver_todas' => 'Ver todas',
        'cambiar_modo' => 'Cambiar modo oscuro/claro',
        'mi_perfil' => 'Mi Perfil',
        'mis_obras' => 'Mis Obras',
        'favoritos' => 'Favoritos',
        'nueva_obra' => 'Nueva Obra',
        'cerrar_sesion' => 'Cerrar Sesión',
        'iniciar_sesion' => 'Iniciar Sesión',
        'registrarse' => 'Registrarse',
        'años' => 'años',
        'acerca_de' => 'Acerca de',
        'contacto' => 'Contacto',
        'aleatorio' => 'Aleatorio'
    ];
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

// Verificar edad del usuario
$usuario_es_mayor_edad = false;
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_fecha_nacimiento'])) {
    $fecha_nacimiento = new DateTime($_SESSION['usuario_fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nacimiento)->y;
    $usuario_es_mayor_edad = $edad >= 18;
}

// Variables para notificaciones
$notificaciones_no_leidas = 0;
if (isset($_SESSION['usuario_id'])) {
    $sql_notif = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ? AND leida = 0";
    $stmt_notif = $conexion->prepare($sql_notif);
    if ($stmt_notif) {
        $stmt_notif->bind_param("i", $_SESSION['usuario_id']);
        $stmt_notif->execute();
        $result_notif = $stmt_notif->get_result();
        if ($result_notif) {
            $notificaciones_no_leidas = $result_notif->fetch_assoc()['total'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVerse - Plataforma de Literatura y Cómics</title>
    <meta name="description" content="Descubre, lee y comparte literatura y cómics en InkVerse. La plataforma definitiva para amantes de la lectura.">
    
    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/public.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos adicionales para mejorar la funcionalidad -->
    <style>
        /* Estilos para dropdowns */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 8px;
            padding: 8px 0;
            top: 100%;
            left: 0;
        }
        
        .dropdown.active .dropdown-content {
            display: block;
        }
        
        .mega-dropdown {
            min-width: 600px;
            padding: 20px;
        }
        
        .dropdown-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .dropdown-section h4 {
            margin-bottom: 10px;
            color: #333;
            font-size: 14px;
            font-weight: 600;
        }
        
        .dropdown-links a {
            display: block;
            padding: 8px 12px;
            color: #666;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .dropdown-links a:hover {
            background-color: #f8f9fa;
            color: #333;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #eee;
            margin: 8px 0;
        }
        
        /* Estilos para menú móvil */
        .mobile-nav-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .mobile-nav-overlay.active {
            display: block;
        }
        
        .mobile-nav-menu {
            position: fixed;
            top: 0;
            left: -100%;
            width: 300px;
            height: 100%;
            background-color: white;
            z-index: 1000;
            transition: left 0.3s ease;
            overflow-y: auto;
        }
        
        .mobile-nav-menu.active {
            left: 0;
        }
        
        .mobile-nav-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .mobile-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .mobile-logo img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }
        
        .mobile-nav-content {
            padding: 20px;
        }
        
        .mobile-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .mobile-nav-list li {
            margin-bottom: 10px;
        }
        
        .mobile-nav-list a {
            display: block;
            padding: 12px;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        
        .mobile-nav-list a:hover {
            background-color: #f8f9fa;
        }
        
        .dropdown-mobile-content {
            display: none;
            padding-left: 20px;
            margin-top: 10px;
        }
        
        .dropdown-mobile.active .dropdown-mobile-content {
            display: block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-menu-toggle {
                display: none;
            }
        }
        
        /* Tema oscuro */
        [data-theme="dark"] {
            background-color: #1a1a1a;
            color: #ffffff;
        }
        
        [data-theme="dark"] .dropdown-content {
            background-color: #2d2d2d;
            color: #ffffff;
        }
        
        [data-theme="dark"] .dropdown-links a {
            color: #cccccc;
        }
        
        [data-theme="dark"] .dropdown-links a:hover {
            background-color: #3d3d3d;
            color: #ffffff;
        }
    </style>
    
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="navbar">
                <!-- Logo -->
                <div class="logo">
                    <a href="<?php echo BASE_URL; ?>/public/index.php">
                        <img src="<?php echo BASE_URL; ?>/includes/img/inkverse3.jpg" alt="Logo InkVerse">
                        <span>InkVerse</span>
                    </a>
                </div>
                
                <!-- Navegación principal -->
                <nav class="nav-links">
                    <ul>
                        <li>
                            <a href="<?php echo BASE_URL; ?>/public/index.php" class="nav-link">
                                <i class="fas fa-home"></i> <?= htmlspecialchars($TEXTOS['inicio']) ?>
                            </a>
                        </li>
                        
                        <li>
                            <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="nav-link">
                                <i class="fas fa-compass"></i> <?= htmlspecialchars($TEXTOS['explorar']) ?>
                            </a>
                        </li>
                        
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle nav-link" onclick="return false;">
                                <i class="fas fa-tags"></i> <?= htmlspecialchars($TEXTOS['categorias']) ?> 
                                <i class="fas fa-chevron-down dropdown-arrow"></i>
                            </a>
                            <div class="dropdown-content mega-dropdown">
                                <div class="dropdown-grid">
                                    <div class="dropdown-section">
                                        <h4><i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['literatura']) ?></h4>
                                        <div class="dropdown-links">
                                            <?php foreach ($categorias_libros as $categoria): ?>
                                                <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=libro">
                                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                            <div class="dropdown-divider"></div>
                                            <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="ver-todas">
                                                <i class="fas fa-th-large"></i> <?= htmlspecialchars($TEXTOS['ver_todas_obras']) ?>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/public/categorias.php" class="ver-todas">
                                                <i class="fas fa-list"></i> <?= htmlspecialchars($TEXTOS['ver_todas_categorias']) ?>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="dropdown-section">
                                        <h4><i class="fas fa-book-open"></i> <?= htmlspecialchars($TEXTOS['comic_manga']) ?></h4>
                                        <div class="dropdown-links">
                                            <?php foreach ($categorias_comics as $categoria): ?>
                                                <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=comic">
                                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                            <div class="dropdown-divider"></div>
                                            <a href="<?php echo BASE_URL; ?>/public/comics.php" class="ver-todas">
                                                <i class="fas fa-th-large"></i> <?= htmlspecialchars($TEXTOS['ver_todas_obras']) ?>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/public/categorias.php" class="ver-todas">
                                                <i class="fas fa-list"></i> <?= htmlspecialchars($TEXTOS['ver_todas_categorias']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle nav-link" onclick="return false;">
                                <i class="fas fa-users"></i> <?= htmlspecialchars($TEXTOS['comunidad']) ?> 
                                <i class="fas fa-chevron-down dropdown-arrow"></i>
                            </a>
                            <div class="dropdown-content mega-dropdown">
                                <div class="dropdown-grid">
                                    <div class="dropdown-section">
                                        <h4><?= htmlspecialchars($TEXTOS['participar']) ?></h4>
                                        <div class="dropdown-links">
                                            <a href="<?php echo BASE_URL; ?>/comunidad/foros.php">
                                                <i class="fas fa-comments"></i> <?= htmlspecialchars($TEXTOS['foros_discusion']) ?>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/comunidad/grupos.php">
                                                <i class="fas fa-user-friends"></i> <?= htmlspecialchars($TEXTOS['grupos_lectura']) ?>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/comunidad/eventos.php">
                                                <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($TEXTOS['eventos']) ?>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="dropdown-section">
                                        <h4><?= htmlspecialchars($TEXTOS['contenido']) ?></h4>
                                        <div class="dropdown-links">
                                            <a href="<?php echo BASE_URL; ?>/comunidad/arte.php">
                                                <i class="fas fa-palette"></i> <?= htmlspecialchars($TEXTOS['galeria_arte']) ?>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/comunidad/recomendaciones.php">
                                                <i class="fas fa-thumbs-up"></i> <?= htmlspecialchars($TEXTOS['recomendaciones']) ?>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/comunidad/noticias.php">
                                                <i class="fas fa-newspaper"></i> <?= htmlspecialchars($TEXTOS['noticias_novedades']) ?>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="dropdown-section">
                                        <h4>Información</h4>
                                        <div class="dropdown-links">
                                            <a href="<?php echo BASE_URL; ?>/public/acerca.php">
                                                <i class="fas fa-info-circle"></i> <?= htmlspecialchars($TEXTOS['acerca_de']) ?>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/public/contacto.php">
                                                <i class="fas fa-envelope"></i> <?= htmlspecialchars($TEXTOS['contacto']) ?>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/public/aleatorio.php">
                                                <i class="fas fa-random"></i> <?= htmlspecialchars($TEXTOS['aleatorio']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>
                
                <!-- Acciones del header -->
                <div class="header-right">
                    <!-- Barra de búsqueda -->
                    <div class="search-bar">
                        <form action="<?php echo BASE_URL; ?>/public/buscar.php" method="GET" class="search-form">
                            <input type="text" name="q" placeholder="<?= htmlspecialchars($TEXTOS['buscar_obras']) ?>" 
                                   aria-label="Buscar" class="search-input">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Acciones de usuario -->
                    <div class="user-actions">
                        <!-- Selector de idioma -->
                        <div class="dropdown language-dropdown">
                            <button class="dropdown-toggle icon-btn" aria-label="Seleccionar idioma" onclick="return false;">
                                <img src="<?php echo BASE_URL; ?>/includes/img/<?php echo htmlspecialchars($idioma); ?>.png" 
                                     alt="<?php echo htmlspecialchars($idioma); ?>" class="flag-icon">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-content language-dropdown-content">
                                <a href="?lang=es" class="flag-item <?php echo ($idioma == 'es') ? 'active' : ''; ?>">
                                    <img src="<?php echo BASE_URL; ?>/includes/img/es.png" alt="Español">
                                    <span>Español</span>
                                </a>
                                <a href="?lang=en" class="flag-item <?php echo ($idioma == 'en') ? 'active' : ''; ?>">
                                    <img src="<?php echo BASE_URL; ?>/includes/img/en.png" alt="English">
                                    <span>English</span>
                                </a>
                                <a href="?lang=fr" class="flag-item <?php echo ($idioma == 'fr') ? 'active' : ''; ?>">
                                    <img src="<?php echo BASE_URL; ?>/includes/img/fr.png" alt="Français">
                                    <span>Français</span>
                                </a>
                                <a href="?lang=ca" class="flag-item <?php echo ($idioma == 'ca') ? 'active' : ''; ?>">
                                    <img src="<?php echo BASE_URL; ?>/includes/img/ca.png" alt="Catalá">
                                    <span>Catalá</span>
                                </a>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <!-- Favoritos -->
                            <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php" 
                               title="<?= htmlspecialchars($TEXTOS['mis_favoritos']) ?>" 
                               class="icon-btn favorites-btn">
                                <i class="fas fa-heart"></i>
                            </a>
                            
                            <!-- Notificaciones -->
                            <div class="dropdown notification-dropdown">
                                <button class="dropdown-toggle icon-btn" 
                                        title="<?= htmlspecialchars($TEXTOS['notificaciones']) ?>"
                                        onclick="return false;">
                                    <i class="fas fa-bell"></i>
                                    <?php if ($notificaciones_no_leidas > 0): ?>
                                        <span class="notification-badge"><?php echo $notificaciones_no_leidas; ?></span>
                                    <?php endif; ?>
                                </button>
                                <div class="dropdown-content notification-dropdown-content">
                                    <div class="notification-header">
                                        <h4><?= htmlspecialchars($TEXTOS['notificaciones']) ?></h4>
                                        <?php if ($notificaciones_no_leidas > 0): ?>
                                            <a href="<?php echo BASE_URL; ?>/controllers/procesar-notificaciones.php?accion=marcar_todas_leidas" 
                                               class="notification-mark-all">
                                                <?= htmlspecialchars($TEXTOS['marcar_todas_leidas']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="notification-list">
                                        <?php
                                        if (isset($_SESSION['usuario_id'])) {
                                            $sql_notif_list = "SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha_creacion DESC LIMIT 5";
                                            $stmt_notif_list = $conexion->prepare($sql_notif_list);
                                            if ($stmt_notif_list) {
                                                $stmt_notif_list->bind_param("i", $_SESSION['usuario_id']);
                                                $stmt_notif_list->execute();
                                                $notificaciones = $stmt_notif_list->get_result();
                                                
                                                if ($notificaciones && $notificaciones->num_rows > 0):
                                                    while ($notificacion = $notificaciones->fetch_assoc()):
                                        ?>
                                            <a href="<?php echo htmlspecialchars($notificacion['url'] ?? '#'); ?>" 
                                               class="notification-item <?php echo $notificacion['leida'] ? '' : 'unread'; ?>">
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
                                                        <?php echo date('d/m/Y H:i', strtotime($notificacion['fecha_creacion'])); ?>
                                                    </span>
                                                </div>
                                            </a>
                                        <?php
                                                    endwhile;
                                                else:
                                        ?>
                                            <div class="notification-empty">
                                                <p><?= htmlspecialchars($TEXTOS['no_notificaciones']) ?></p>
                                            </div>
                                        <?php 
                                                endif;
                                            }
                                        }
                                        ?>
                                    </div>
                                    
                                    <div class="notification-footer">
                                        <a href="<?php echo BASE_URL; ?>/usuario/notificaciones.php">
                                            <?= htmlspecialchars($TEXTOS['ver_todas']) ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Toggle modo oscuro -->
                        <button class="dark-mode-toggle icon-btn" 
                                title="<?= htmlspecialchars($TEXTOS['cambiar_modo']) ?>"
                                onclick="toggleTheme()">
                            <i class="fas fa-moon"></i>
                        </button>
                        
                        <!-- Menú de usuario -->
                        <div class="dropdown user-dropdown">
                            <button class="dropdown-toggle icon-btn user-menu-btn" onclick="return false;">
                                <i class="fas fa-user-circle"></i>
                            </button>
                            <div class="dropdown-content user-dropdown-content">
                                <?php if (isset($_SESSION['usuario_id'])): ?>
                                    <div class="user-info">
                                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                                        <?php if (isset($_SESSION['usuario_fecha_nacimiento'])): ?>
                                            <span class="user-age">
                                                <?php 
                                                $fecha_nacimiento = new DateTime($_SESSION['usuario_fecha_nacimiento']);
                                                $hoy = new DateTime();
                                                $edad = $hoy->diff($fecha_nacimiento)->y;
                                                echo htmlspecialchars($edad) . ' ' . $TEXTOS['años']; 
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a href="<?php echo BASE_URL; ?>/usuario/perfil.php">
                                        <i class="fas fa-user"></i> <?= htmlspecialchars($TEXTOS['mi_perfil']) ?>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php">
                                        <i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['mis_obras']) ?>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php">
                                        <i class="fas fa-heart"></i> <?= htmlspecialchars($TEXTOS['favoritos']) ?>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php">
                                        <i class="fas fa-plus-circle"></i> <?= htmlspecialchars($TEXTOS['nueva_obra']) ?>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="<?php echo BASE_URL; ?>/public/logout.php" class="logout-link">
                                        <i class="fas fa-sign-out-alt"></i> <?= htmlspecialchars($TEXTOS['cerrar_sesion']) ?>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>/public/login.php">
                                        <i class="fas fa-sign-in-alt"></i> <?= htmlspecialchars($TEXTOS['iniciar_sesion']) ?>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/public/register.php">
                                        <i class="fas fa-user-plus"></i> <?= htmlspecialchars($TEXTOS['registrarse']) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Botón menú móvil -->
                        <button class="mobile-menu-toggle icon-btn" onclick="toggleMobileMenu()">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Menú móvil -->
    <div class="mobile-nav-overlay" onclick="closeMobileMenu()"></div>
    <div class="mobile-nav-menu">
        <div class="mobile-nav-header">
            <div class="mobile-logo">
                <img src="<?php echo BASE_URL; ?>/includes/img/inkverse3.jpg" alt="InkVerse">
                <span>InkVerse</span>
            </div>
            <button class="close-btn" onclick="closeMobileMenu()"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="mobile-nav-content">
            <ul class="mobile-nav-list">
                <li><a href="<?php echo BASE_URL; ?>/public/index.php"><i class="fas fa-home"></i> <?= htmlspecialchars($TEXTOS['inicio']) ?></a></li>
                <li><a href="<?php echo BASE_URL; ?>/public/explorar.php"><i class="fas fa-compass"></i> <?= htmlspecialchars($TEXTOS['explorar']) ?></a></li>
                
                <li class="dropdown-mobile">
                    <button class="dropdown-toggle" onclick="toggleMobileDropdown(this)">
                        <i class="fas fa-tags"></i> <?= htmlspecialchars($TEXTOS['categorias']) ?> 
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-mobile-content">
                        <h4><i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['literatura']) ?></h4>
                        <?php foreach ($categorias_libros as $categoria): ?>
                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=libro">
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </a>
                        <?php endforeach; ?>
                        <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="ver-todas">
                            <i class="fas fa-th-large"></i> <?= htmlspecialchars($TEXTOS['ver_todas_obras']) ?>
                        </a>
                        
                        <h4><i class="fas fa-book-open"></i> <?= htmlspecialchars($TEXTOS['comic_manga']) ?></h4>
                        <?php foreach ($categorias_comics as $categoria): ?>
                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=comic">
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </a>
                        <?php endforeach; ?>
                        <a href="<?php echo BASE_URL; ?>/public/comics.php" class="ver-todas">
                            <i class="fas fa-th-large"></i> <?= htmlspecialchars($TEXTOS['ver_todas_obras']) ?>
                        </a>
                    </div>
                </li>
                
                <li class="dropdown-mobile">
                    <button class="dropdown-toggle" onclick="toggleMobileDropdown(this)">
                        <i class="fas fa-users"></i> <?= htmlspecialchars($TEXTOS['comunidad']) ?> 
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-mobile-content">
                        <a href="<?php echo BASE_URL; ?>/comunidad/foros.php">
                            <i class="fas fa-comments"></i> <?= htmlspecialchars($TEXTOS['foros_discusion']) ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/comunidad/grupos.php">
                            <i class="fas fa-user-friends"></i> <?= htmlspecialchars($TEXTOS['grupos_lectura']) ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/comunidad/eventos.php">
                            <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($TEXTOS['eventos']) ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/comunidad/arte.php">
                            <i class="fas fa-palette"></i> <?= htmlspecialchars($TEXTOS['galeria_arte']) ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/comunidad/noticias.php">
                            <i class="fas fa-newspaper"></i> <?= htmlspecialchars($TEXTOS['noticias_novedades']) ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/public/acerca-de.php">
                            <i class="fas fa-info-circle"></i> <?= htmlspecialchars($TEXTOS['acerca_de']) ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/public/contacto.php">
                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($TEXTOS['contacto']) ?>
                        </a>
                    </div>
                </li>
            </ul>
            
            <div class="mobile-user-actions">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/usuario/perfil.php" class="btn btn-outline">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($TEXTOS['mi_perfil']) ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php" class="btn btn-outline">
                        <i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['mis_obras']) ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php" class="btn btn-outline">
                        <i class="fas fa-heart"></i> <?= htmlspecialchars($TEXTOS['favoritos']) ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn btn-accent">
                        <i class="fas fa-plus-circle"></i> <?= htmlspecialchars($TEXTOS['nueva_obra']) ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/logout.php" class="btn btn-outline logout-btn">
                        <i class="fas fa-sign-out-alt"></i> <?= htmlspecialchars($TEXTOS['cerrar_sesion']) ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-outline">
                        <i class="fas fa-sign-in-alt"></i> <?= htmlspecialchars($TEXTOS['iniciar_sesion']) ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-accent">
                        <i class="fas fa-user-plus"></i> <?= htmlspecialchars($TEXTOS['registrarse']) ?>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="language-switcher">
                <h4>Idioma</h4>
                <div class="language-options">
                    <a href="?lang=es" class="flag-item <?php echo ($idioma == 'es') ? 'active' : ''; ?>">
                        <img src="<?php echo BASE_URL; ?>/includes/img/es.png" alt="Español">
                        <span>Español</span>
                    </a>
                    <a href="?lang=en" class="flag-item <?php echo ($idioma == 'en') ? 'active' : ''; ?>">
                        <img src="<?php echo BASE_URL; ?>/includes/img/en.png" alt="English">
                        <span>English</span>
                    </a>
                    <a href="?lang=fr" class="flag-item <?php echo ($idioma == 'fr') ? 'active' : ''; ?>">
                        <img src="<?php echo BASE_URL; ?>/includes/img/fr.png" alt="Français">
                        <span>Français</span>
                    </a>
                    <a href="?lang=ca" class="flag-item <?php echo ($idioma == 'ca') ? 'active' : ''; ?>">
                        <img src="<?php echo BASE_URL; ?>/includes/img/ca.png" alt="Catalá">
                        <span>Catalá</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Variables JavaScript -->
    <script>
        const IS_USER_MINOR = <?php echo ($usuario_es_mayor_edad ? 'false' : 'true'); ?>;
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const TEXTOS_JS = <?php echo json_encode($TEXTOS); ?>;
    </script>
    
    <!-- JavaScript funcional -->
    <script>
        // Variables globales
        let currentTheme = localStorage.getItem('theme') || 'light';
        
        // Aplicar tema al cargar
        document.addEventListener('DOMContentLoaded', function() {
            if (currentTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
                const themeToggle = document.querySelector('.dark-mode-toggle i');
                if (themeToggle) {
                    themeToggle.className = 'fas fa-sun';
                }
            }
        });
        
        // Función para toggle de tema
        function toggleTheme() {
            const body = document.body;
            const themeToggle = document.querySelector('.dark-mode-toggle i');
            
            if (currentTheme === 'light') {
                currentTheme = 'dark';
                body.setAttribute('data-theme', 'dark');
                if (themeToggle) themeToggle.className = 'fas fa-sun';
            } else {
                currentTheme = 'light';
                body.removeAttribute('data-theme');
                if (themeToggle) themeToggle.className = 'fas fa-moon';
            }
            
            localStorage.setItem('theme', currentTheme);
        }
        
        // Función para toggle de menú móvil
        function toggleMobileMenu() {
            const mobileMenu = document.querySelector('.mobile-nav-menu');
            const overlay = document.querySelector('.mobile-nav-overlay');
            
            mobileMenu.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileMenu() {
            const mobileMenu = document.querySelector('.mobile-nav-menu');
            const overlay = document.querySelector('.mobile-nav-overlay');
            
            mobileMenu.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Función para dropdowns móviles
        function toggleMobileDropdown(button) {
            const dropdown = button.parentElement;
            dropdown.classList.toggle('active');
        }
        
        // Inicializar dropdowns de escritorio
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                
                if (toggle) {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Cerrar otros dropdowns
                        dropdowns.forEach(otherDropdown => {
                            if (otherDropdown !== dropdown) {
                                otherDropdown.classList.remove('active');
                            }
                        });
                        
                        // Toggle current dropdown
                        dropdown.classList.toggle('active');
                    });
                }
            });
            
            // Cerrar dropdowns al hacer click fuera
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.remove('active');
                    });
                }
            });
            
            // Cerrar dropdowns con Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.remove('active');
                    });
                }
            });
        });
        
        // Función para mostrar alertas
        function showAlert(message, type = 'info') {
            const existingAlerts = document.querySelectorAll('.alerta');
            existingAlerts.forEach(alert => alert.remove());
            
            const alert = document.createElement('div');
            alert.className = `alerta alerta-${type}`;
            alert.textContent = message;
            alert.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                padding: 12px 20px;
                border-radius: 6px;
                color: white;
                font-weight: 500;
                max-width: 300px;
                word-wrap: break-word;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            `;
            
            switch(type) {
                case 'success':
                    alert.style.background = '#27ae60';
                    break;
                case 'error':
                    alert.style.background = '#e74c3c';
                    break;
                case 'info':
                    alert.style.background = '#3498db';
                    break;
                case 'warning':
                    alert.style.background = '#f39c12';
                    break;
                default:
                    alert.style.background = '#6c757d';
            }
            
            document.body.appendChild(alert);
            
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 4000);
        }
    </script>
    
    <main>
        <?php 
        // Mostrar mensajes de éxito o error si existen
        if (isset($_SESSION['mensaje'])) {
            $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
            $mensaje_clave = $_SESSION['mensaje'];
            $mensaje_traducido = $TEXTOS[$mensaje_clave] ?? $mensaje_clave; 
            echo '<div class="alerta alerta-' . $tipo . '">' . htmlspecialchars($mensaje_traducido) . '</div>';
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
        }
        ?>
