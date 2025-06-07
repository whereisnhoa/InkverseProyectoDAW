<?php
/**
 * Header del sitio 
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    require_once(__DIR__ . '/config.php');
}
if (file_exists(__DIR__ . '/conexion.php')) {
    require_once(__DIR__ . '/conexion.php');
} else {
    $conexion = new mysqli("localhost", "root", "", "proyecto_db");
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
}
if (file_exists(__DIR__ . '/funciones.php')) {
    require_once(__DIR__ . '/funciones.php');
}

require_once(__DIR__ . '/idioma.php');

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
$usuario_es_mayor_edad = false;
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_fecha_nacimiento'])) {
    $fecha_nacimiento = new DateTime($_SESSION['usuario_fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nacimiento)->y;
    $usuario_es_mayor_edad = $edad >= 18;
}
$notificaciones_no_leidas = 0;
if (isset($_SESSION['usuario_id'])) {
    $sql_notif = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ? AND leido = 0";
    $stmt_notif = $conexion->prepare($sql_notif);
    if ($stmt_notif) {
        $stmt_notif->bind_param("i", $_SESSION['usuario_id']);
        $stmt_notif->execute();
        $result_notif = $stmt_notif->get_result();
        if ($result_notif) {
            $notificaciones_no_leidas = $result_notif->fetch_assoc()['total'];
        }
        $stmt_notif->close();
    }
}

$current_script = basename($_SERVER['SCRIPT_NAME']);
$body_class = '';
if ($current_script === 'login.php' || $current_script === 'register.php') {
    $body_class = 'auth-bg';
}

?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkVerse</title>
    <meta name="description" content="InkVerse - Plataforma de Literatura y Cómics">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL; ?>/css/public.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body<?= $body_class ? ' class="' . $body_class . '"' : ''; ?>>

<header class="main-header">
    <div class="header-bar">

        <!-- LOGO -->
        <div class="logo">
            <a href="<?= BASE_URL; ?>/public/index.php" style="display:flex;align-items:center;gap:13px;text-decoration:none;">
                <img src="<?= BASE_URL; ?>/includes/img/inkverse3.jpg" alt="Logo InkVerse">
                <span>InkVerse</span>
            </a>
        </div>

        <!-- BOTÓN HAMBURGUESA -->
        <button class="menu-toggle icon-btn" aria-label="Abrir menú" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- NAV MENU -->
        <nav class="nav-links">
            <ul>
                <li>
                    <a href="<?= BASE_URL; ?>/public/index.php" class="nav-link">
                        <i class="fas fa-home"></i> <?= htmlspecialchars($TEXTOS['inicio']) ?>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL; ?>/public/explorar.php" class="nav-link">
                        <i class="fas fa-compass"></i> <?= htmlspecialchars($TEXTOS['explorar']) ?>
                    </a>
                </li>
                <!-- CATEGORÍAS -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle nav-link" onclick="return false;">
                        <i class="fas fa-tags"></i> <?= htmlspecialchars($TEXTOS['categorias']) ?>
                    </a>
                    <div class="dropdown-content mega-dropdown">
                        <div class="dropdown-grid">
                            <div class="dropdown-section">
                                <h4><i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['literatura']) ?></h4>
                                <div class="dropdown-links">
                                    <?php foreach ($categorias_libros as $categoria): ?>
                                        <a href="<?= BASE_URL; ?>/public/categoria.php?categoria_id=<?= $categoria['id']; ?>&tipo=libro">
                                            <i class="fas fa-angle-right"></i> <?= htmlspecialchars($categoria['nombre']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                    <div class="dropdown-divider"></div>
                                    <a href="<?= BASE_URL; ?>/public/literatura.php" class="ver-todas">
                                        <i class="fas fa-th-large"></i> <?= htmlspecialchars($TEXTOS['ver_literatura']) ?>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/public/categorias.php" class="ver-todas">
                                        <i class="fas fa-list"></i> <?= htmlspecialchars($TEXTOS['ver_todas']) ?>
                                    </a>
                                </div>
                            </div>
                            <div class="dropdown-section">
                                <h4><i class="fas fa-book-open"></i> <?= htmlspecialchars($TEXTOS['comics_manga']) ?></h4>
                                <div class="dropdown-links">
                                    <?php foreach ($categorias_comics as $categoria): ?>
                                        <a href="<?= BASE_URL; ?>/public/categoria.php?categoria_id=<?= $categoria['id']; ?>&tipo=comic">
                                            <i class="fas fa-angle-right"></i> <?= htmlspecialchars($categoria['nombre']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                    <div class="dropdown-divider"></div>
                                    <a href="<?= BASE_URL; ?>/public/comics.php" class="ver-todas">
                                        <i class="fas fa-th-large"></i> <?= htmlspecialchars($TEXTOS['ver_comics']) ?>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/public/categorias.php" class="ver-todas">
                                        <i class="fas fa-list"></i> <?= htmlspecialchars($TEXTOS['ver_todas']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- COMUNIDAD -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle nav-link" onclick="return false;">
                        <i class="fas fa-users"></i> <?= htmlspecialchars($TEXTOS['comunidad']) ?>
                    </a>
                    <div class="dropdown-content mega-dropdown">
                        <div class="dropdown-grid">
                            <div class="dropdown-section">
                                <h4><i class="fas fa-handshake"></i> <?= htmlspecialchars($TEXTOS['participar'] ?? 'Participar') ?></h4>
                                <div class="dropdown-links">
                                    <a href="<?= BASE_URL; ?>/comunidad/foros.php">
                                        <i class="fas fa-comments"></i> <?= htmlspecialchars($TEXTOS['foros_discusion']) ?>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/comunidad/grupos.php">
                                        <i class="fas fa-user-friends"></i> <?= htmlspecialchars($TEXTOS['grupos_lectura']) ?>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/comunidad/eventos.php">
                                        <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($TEXTOS['eventos']) ?>
                                    </a>
                                </div>
                            </div>
                            <div class="dropdown-section">
                                <h4><i class="fas fa-palette"></i> <?= htmlspecialchars($TEXTOS['galeria_arte']) ?></h4>
                                <div class="dropdown-links">
                                    <a href="<?= BASE_URL; ?>/comunidad/arte.php">
                                        <i class="fas fa-image"></i> <?= htmlspecialchars($TEXTOS['galeria_arte']) ?>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/comunidad/recomendaciones.php">
                                        <i class="fas fa-thumbs-up"></i> <?= htmlspecialchars($TEXTOS['recomendaciones'] ?? 'Recomendaciones') ?>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/comunidad/noticias.php">
                                        <i class="fas fa-newspaper"></i> <?= htmlspecialchars($TEXTOS['noticias'] ?? 'Noticias') ?>
                                    </a>
                                </div>
                            </div>
                            <div class="dropdown-section">
                                <h4><i class="fas fa-info-circle"></i> <?= htmlspecialchars($TEXTOS['footer_acerca'] ?? 'Acerca de') ?></h4>
                                <div class="dropdown-links">
                                    <a href="<?= BASE_URL; ?>/public/acerca.php">
                                        <i class="fas fa-info-circle"></i> <?= htmlspecialchars($TEXTOS['footer_acerca'] ?? 'Acerca de') ?>
                                    </a>
                                    <a href="<?= BASE_URL; ?>/public/contacto.php">
                                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($TEXTOS['footer_contacto'] ?? 'Contacto') ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- BÚSQUEDA -->
        <div class="search-bar">
            <form action="<?= BASE_URL; ?>/public/buscar.php" method="GET" class="search-form">
                <input type="text" name="q" placeholder="<?= htmlspecialchars($TEXTOS['buscar_obras'] ?? '...') ?>"
                       aria-label="Buscar" class="search-input">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <!-- ACCIONES USUARIO -->
        <div class="header-actions">
            <!-- Idioma -->
            <div class="dropdown language-dropdown">
                <button class="lang-btn dropdown-toggle icon-btn" aria-label="Seleccionar idioma" onclick="return false;">
                    <img src="<?= BASE_URL; ?>/includes/img/<?= htmlspecialchars((string)$idioma); ?>.png"
                         alt="<?= htmlspecialchars((string)$idioma); ?>" class="flag-icon">
                </button>
                <div class="dropdown-content language-dropdown-content">
                    <a href="?lang=es" class="flag-item <?= ($idioma == 'es') ? 'active' : ''; ?>">
                        <img src="<?= BASE_URL; ?>/includes/img/es.png" alt="Español"><span>Español</span>
                    </a>
                    <a href="?lang=en" class="flag-item <?= ($idioma == 'en') ? 'active' : ''; ?>">
                        <img src="<?= BASE_URL; ?>/includes/img/en.png" alt="English"><span>English</span>
                    </a>
                    <a href="?lang=fr" class="flag-item <?= ($idioma == 'fr') ? 'active' : ''; ?>">
                        <img src="<?= BASE_URL; ?>/includes/img/fr.png" alt="Français"><span>Français</span>
                    </a>
                    <a href="?lang=ca" class="flag-item <?= ($idioma == 'ca') ? 'active' : ''; ?>">
                        <img src="<?= BASE_URL; ?>/includes/img/ca.png" alt="Catalá"><span>Catalá</span>
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="<?= BASE_URL; ?>/usuario/favoritos.php"
                   title="<?= htmlspecialchars($TEXTOS['favoritos']) ?>"
                   class="icon-btn favorites-btn">
                    <i class="fas fa-heart"></i>
                </a>
                <div class="dropdown notification-dropdown">
                    <button class="dropdown-toggle icon-btn"
                            title="<?= htmlspecialchars($TEXTOS['notificaciones'] ?? 'Notificaciones') ?>"
                            onclick="return false;">
                        <i class="fas fa-bell"></i>
                        <?php if ($notificaciones_no_leidas > 0): ?>
                            <span class="notification-badge"><?= $notificaciones_no_leidas; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-content notification-dropdown-content">
                        <div class="notification-header">
                            <h4><?= htmlspecialchars($TEXTOS['notificaciones'] ?? 'Notificaciones') ?></h4>
                            <?php if ($notificaciones_no_leidas > 0): ?>
                                <a href="<?= BASE_URL; ?>/controllers/procesar-notificaciones.php?accion=marcar_todas_leidas"
                                   class="notification-mark-all">
                                    <?= htmlspecialchars($TEXTOS['marcar_todas_leidas'] ?? 'Marcar todas como leídas') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="notification-list">
                            <?php
                            if (isset($_SESSION['usuario_id'])) {
                                $sql_notif_list = "SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 5";
                                $stmt_notif_list = $conexion->prepare($sql_notif_list);
                                if ($stmt_notif_list) {
                                    $stmt_notif_list->bind_param("i", $_SESSION['usuario_id']);
                                    $stmt_notif_list->execute();
                                    $notificaciones = $stmt_notif_list->get_result();
                                    if ($notificaciones && $notificaciones->num_rows > 0):
                                        while ($notificacion = $notificaciones->fetch_assoc()):
                                            switch ($notificacion['tipo']) {
                                                case 'comentario': $icon = 'comment'; break;
                                                case 'like': $icon = 'heart'; break;
                                                case 'respuesta_foro': $icon = 'reply'; break;
                                                case 'nueva_obra': $icon = 'book'; break;
                                                case 'sistema': default: $icon = 'bell'; break;
                                            }
                                            $url = !empty($notificacion['url']) ? htmlspecialchars($notificacion['url']) : '#';
                            ?>
                                <a href="<?= $url; ?>" class="notification-item <?= $notificacion['leido'] ? '' : 'unread'; ?>">
                                    <div class="notification-icon">
                                        <i class="fas fa-<?= $icon; ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p><?= htmlspecialchars($notificacion['mensaje']); ?></p>
                                        <span class="notification-time">
                                            <?= date('d/m/Y H:i', strtotime($notificacion['fecha'])); ?>
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
                            <?php
                                    endif;
                                    $stmt_notif_list->close();
                                }
                            }
                            ?>
                        </div>
                        <div class="notification-footer">
                            <a href="<?= BASE_URL; ?>/usuario/notificaciones.php">
                                <?= htmlspecialchars($TEXTOS['ver_todas'] ?? 'Ver todas') ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <!-- MODO OSCURO -->
        <button class="dark-mode-toggle icon-btn"
                title="<?= htmlspecialchars($TEXTOS['cambiar_modo'] ?? 'Cambiar modo oscuro/claro') ?>">
            <i class="fas fa-moon"></i>
        </button>
            <div class="dropdown user-dropdown">
                <button class="dropdown-toggle icon-btn user-menu-btn" onclick="return false;">
                    <i class="fas fa-user-circle"></i>
                </button>
                <div class="dropdown-content user-dropdown-content">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($_SESSION['usuario']); ?></span>
                            <?php if (isset($_SESSION['usuario_fecha_nacimiento'])): ?>
                                <span class="user-age">
                                    <?php
                                    $fecha_nacimiento = new DateTime($_SESSION['usuario_fecha_nacimiento']);
                                    $hoy = new DateTime();
                                    $edad = $hoy->diff($fecha_nacimiento)->y;
                                    echo htmlspecialchars($edad) . ' ' . htmlspecialchars($TEXTOS['años'] ?? 'años');
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?= BASE_URL; ?>/usuario/perfil.php">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($TEXTOS['mi_perfil']) ?>
                        </a>
                        <a href="<?= BASE_URL; ?>/usuario/mis_obras.php">
                            <i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['mis_obras']) ?>
                        </a>
                        <a href="<?= BASE_URL; ?>/usuario/favoritos.php">
                            <i class="fas fa-heart"></i> <?= htmlspecialchars($TEXTOS['favoritos']) ?>
                        </a>
                        <a href="<?= BASE_URL; ?>/usuario/nueva_obra.php">
                            <i class="fas fa-plus-circle"></i> <?= htmlspecialchars($TEXTOS['publicar_obra']) ?>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= BASE_URL; ?>/public/logout.php" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i> <?= htmlspecialchars($TEXTOS['cerrar_sesion']) ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL; ?>/public/login.php">
                            <i class="fas fa-sign-in-alt"></i> <?= htmlspecialchars($TEXTOS['iniciar_sesion']) ?>
                        </a>
                        <a href="<?= BASE_URL; ?>/public/register.php">
                            <i class="fas fa-user-plus"></i> <?= htmlspecialchars($TEXTOS['registrarse']) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>

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