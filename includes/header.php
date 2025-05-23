
<?php
/**
 * Header del sitio
 */

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos de configuración y funciones
// Asegurarse de que config.php esté definido y cargado correctamente
if (!defined('BASE_URL')) { // Usamos BASE_URL para verificar si config.php fue incluido
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

// Lógica para el idioma actual
$idioma = isset($_GET['lang']) ? $_GET['lang'] : 'es';

// Si no tienes un sistema de traducción completo, puedes definir $TEXTOS aquí para evitar errores:
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
        'descubre_nueva_obra' => 'Descubre una Nueva Obra',
        'por' => 'Por',
        'ver_obra' => 'Ver Obra',
        'no_obras_disponibles' => 'No hay obras disponibles para mostrar.',
        'otra_obra_aleatoria' => 'Otra Obra Aleatoria',
        'literatura_azar' => 'Literatura al azar',
        'comic_manga_azar' => 'Cómic/Manga al azar',
        'no_sabes_que_leer' => '¿No sabes qué leer a continuación?',
        'elige_aventura' => 'Deja que la fortuna elija tu próxima aventura literaria o gráfica.',
        'haz_clic_para_descubrir' => 'Haz clic en un botón para descubrir tu próxima lectura.'
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

// Lógica para verificar la edad del usuario para contenido adulto
$usuario_es_mayor_edad = false;
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_fecha_nacimiento'])) {
    $usuario_es_mayor_edad = esMayorEdad($_SESSION['usuario_fecha_nacimiento']);
}

// Variables para notificaciones
$notificaciones_no_leidas = 0;
if (isset($_SESSION['usuario_id']) && function_exists('contarNotificacionesNoLeidas')) {
    $notificaciones_no_leidas = contarNotificacionesNoLeidas($_SESSION['usuario_id']);
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVerse</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Variables JS
        const IS_USER_MINOR = <?php echo ($usuario_es_mayor_edad ? 'false' : 'true'); ?>;
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const TEXTOS_JS = <?php echo json_encode($TEXTOS); ?>;
    </script>
</head>
<body>
    <header>
        <div class="container">
            <div class="navbar">
                <div class="logo">
                    <a href="<?php echo BASE_URL; ?>/public/index.php">
                        <img src="<?php echo BASE_URL; ?>/includes/img/inkverse3.jpg" alt="Logo InkVerse">
                        <span>InkVerse</span>
                    </a>
                </div>
                
                <nav class="nav-links">
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/public/index.php"><i class="fas fa-home"></i> <?= htmlspecialchars($TEXTOS['inicio']) ?></a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/explorar.php"><i class="fas fa-compass"></i> <?= htmlspecialchars($TEXTOS['explorar']) ?></a></li>
                        
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <i class="fas fa-tags"></i> <?= htmlspecialchars($TEXTOS['categorias']) ?> <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="dropdown-content">
                                <div class="dropdown-grid">
                                    <div class="dropdown-section">
                                        <h4><i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['literatura']) ?></h4>
                                        <div class="dropdown-links">
                                            <?php foreach ($categorias_libros as $categoria): ?>
                                                <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=libro">
                                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                            <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="ver-todas">
                                                <i class="fas fa-th-large"></i> <?= htmlspecialchars($TEXTOS['ver_todas_obras']) ?>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/public/categorias.php?tipo=libro" class="ver-todas">
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
                                            <a href="<?php echo BASE_URL; ?>/public/comics.php" class="ver-todas">
                                                <i class="fas fa-th-large"></i> <?= htmlspecialchars($TEXTOS['ver_todas_obras']) ?>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/public/categorias.php?tipo=comic" class="ver-todas">
                                                <i class="fas fa-list"></i> <?= htmlspecialchars($TEXTOS['ver_todas_categorias']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <i class="fas fa-users"></i> <?= htmlspecialchars($TEXTOS['comunidad']) ?> <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="dropdown-content">
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
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>
                
                <div class="header-right">
                    <div class="search-bar">
                        <form action="<?php echo BASE_URL; ?>/public/buscar.php" method="GET">
                            <input type="text" name="q" placeholder="<?= htmlspecialchars($TEXTOS['buscar_obras']) ?>" aria-label="Buscar">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    
                    <div class="user-actions">
                        <div class="dropdown language-dropdown">
                            <button class="dropdown-toggle icon-btn" aria-label="Seleccionar idioma">
                                <img src="<?php echo BASE_URL; ?>/includes/img/<?php echo htmlspecialchars($idioma); ?>.png" alt="<?php echo htmlspecialchars($idioma); ?>" class="flag-icon">
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
                            <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php" title="<?= htmlspecialchars($TEXTOS['mis_favoritos']) ?>" class="icon-btn">
                                <i class="fas fa-heart"></i>
                            </a>
                            
                            <div class="dropdown notification-dropdown">
                                <button class="dropdown-toggle icon-btn" title="<?= htmlspecialchars($TEXTOS['notificaciones']) ?>">
                                    <i class="fas fa-bell"></i>
                                    <?php if ($notificaciones_no_leidas > 0): ?>
                                        <span class="notification-badge"><?php echo $notificaciones_no_leidas; ?></span>
                                    <?php endif; ?>
                                </button>
                                <div class="dropdown-content notification-dropdown-content">
                                    <div class="notification-header">
                                        <h4><?= htmlspecialchars($TEXTOS['notificaciones']) ?></h4>
                                        <?php if ($notificaciones_no_leidas > 0): ?>
                                            <a href="<?php echo BASE_URL; ?>/controllers/procesar-notificaciones.php?accion=marcar_todas_leidas" class="notification-mark-all">
                                                <?= htmlspecialchars($TEXTOS['marcar_todas_leidas']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="notification-list">
                                        <?php
                                        $notificaciones = function_exists('obtenerNotificaciones') ? obtenerNotificaciones($_SESSION['usuario_id'], 5) : null;
                                        if ($notificaciones && $notificaciones->num_rows > 0):
                                            while ($notificacion = $notificaciones->fetch_assoc()):
                                        ?>
                                            <a href="<?php echo htmlspecialchars($notificacion['url']); ?>" class="notification-item <?php echo $notificacion['leida'] ? '' : 'unread'; ?>">
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
                                                        <?php
                                                        echo function_exists('time_elapsed_string') ? time_elapsed_string($notificacion['fecha_creacion']) : $notificacion['fecha_creacion'];
                                                        ?>
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
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="notification-footer">
                                        <a href="<?php echo BASE_URL; ?>/usuario/notificaciones.php"><?= htmlspecialchars($TEXTOS['ver_todas']) ?></a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <button class="dark-mode-toggle icon-btn" title="<?= htmlspecialchars($TEXTOS['cambiar_modo']) ?>">
                            <i class="fas fa-moon"></i>
                        </button>
                        
                        <div class="dropdown user-dropdown">
                            <button class="dropdown-toggle icon-btn">
                                <i class="fas fa-user-circle"></i>
                            </button>
                            <div class="dropdown-content user-dropdown-content">
                                <?php if (isset($_SESSION['usuario_id'])): ?>
                                    <div class="user-info">
                                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                                        <?php if (isset($_SESSION['usuario_fecha_nacimiento'])): ?>
                                            <span class="user-age">
                                                <?php 
                                                $edad = (new DateTime())->diff(new DateTime($_SESSION['usuario_fecha_nacimiento']))->y;
                                                echo htmlspecialchars($edad) . ' ' . ($TEXTOS['años'] ?? 'años'); 
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>/usuario/perfil.php">
                                        <i class="fas fa-user"></i> <?= htmlspecialchars($TEXTOS['mi_perfil']) ?>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php">
                                        <i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['mis_obras']) ?>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php">
                                        <i class="fas fa-heart"></i> <?= htmlspecialchars($TEXTOS['favoritos']) ?>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/usuario/notificaciones.php">
                                        <i class="fas fa-bell"></i> <?= htmlspecialchars($TEXTOS['notificaciones']) ?>
                                        <?php if ($notificaciones_no_leidas > 0): ?>
                                            <span class="badge"><?php echo $notificaciones_no_leidas; ?></span>
                                        <?php endif; ?>
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
                        
                        <button class="mobile-menu-toggle icon-btn">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Menú móvil -->
    <div class="mobile-nav-overlay"></div>
    <div class="mobile-nav-menu">
        <button class="close-btn"><i class="fas fa-times"></i></button>
        <ul>
            <li><a href="<?php echo BASE_URL; ?>/public/index.php"><i class="fas fa-home"></i> <?= htmlspecialchars($TEXTOS['inicio']) ?></a></li>
            <li><a href="<?php echo BASE_URL; ?>/public/explorar.php"><i class="fas fa-compass"></i> <?= htmlspecialchars($TEXTOS['explorar']) ?></a></li>
            
            <li class="dropdown-mobile">
                <button class="dropdown-toggle"><i class="fas fa-tags"></i> <?= htmlspecialchars($TEXTOS['categorias']) ?> <i class="fas fa-chevron-down"></i></button>
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
                <button class="dropdown-toggle"><i class="fas fa-users"></i> <?= htmlspecialchars($TEXTOS['comunidad']) ?> <i class="fas fa-chevron-down"></i></button>
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