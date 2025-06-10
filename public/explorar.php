<?php
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/conexion.php');
require_once(__DIR__ . '/../includes/idioma.php');

// Configurar filtros de búsqueda
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';
$filtro_categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'recientes';

// Libros (usando relación N:M para categorías)
$sql_libros = "SELECT l.*, GROUP_CONCAT(c.nombre SEPARATOR ', ') as categorias_nombres, 'libro' as tipo_obra,
               (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = l.id AND f.tipo_contenido = 'libro') as total_favoritos
               FROM libros l
               LEFT JOIN libro_categorias lc ON l.id = lc.libro_id
               LEFT JOIN categorias c ON lc.categoria_id = c.id
               WHERE 1=1";
$params_libros = [];
$param_types_libros = "";

if ($filtro_tipo === 'libro' || $filtro_tipo === 'todos') {
    if ($filtro_categoria > 0) {
        $sql_libros .= " AND lc.categoria_id = ?";
        $params_libros[] = $filtro_categoria;
        $param_types_libros .= "i";
    }
    if (!isset($_SESSION['usuario_id'])) {
        $sql_libros .= " AND l.publico_adulto = 0";
    }
    $sql_libros .= " GROUP BY l.id";
    switch ($orden) {
        case 'populares':
            $sql_libros .= " ORDER BY total_favoritos DESC, l.fecha_subida DESC";
            break;
        case 'titulo':
            $sql_libros .= " ORDER BY l.titulo ASC";
            break;
        default:
            $sql_libros .= " ORDER BY l.fecha_subida DESC";
    }
    $sql_libros .= " LIMIT 12";
}

// Cómics (usando relación N:M para categorías)
$sql_comics = "SELECT c.*, 'comic' as tipo_obra,
               (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = c.id AND f.tipo_contenido = 'comic') as total_favoritos
               FROM comics c
               LEFT JOIN comic_categorias cc ON c.id = cc.comic_id
               WHERE 1=1";
$params_comics = [];
$param_types_comics = "";

if ($filtro_tipo === 'comic' || $filtro_tipo === 'todos') {
    if ($filtro_categoria > 0) {
        $sql_comics .= " AND cc.categoria_id = ?";
        $params_comics[] = $filtro_categoria;
        $param_types_comics .= "i";
    }
    if (!isset($_SESSION['usuario_id'])) {
        $sql_comics .= " AND c.publico_adulto = 0";
    }
    $sql_comics .= " GROUP BY c.id";
    switch ($orden) {
        case 'populares':
            $sql_comics .= " ORDER BY total_favoritos DESC, c.fecha_subida DESC";
            break;
        case 'titulo':
            $sql_comics .= " ORDER BY c.titulo ASC";
            break;
        default:
            $sql_comics .= " ORDER BY c.fecha_subida DESC";
    }
    $sql_comics .= " LIMIT 12";
}

// Ejecutar consultas según el filtro de tipo
$result_libros = null;
$result_comics = null;

if ($filtro_tipo === 'todos' || $filtro_tipo === 'libro') {
    if (!empty($params_libros)) {
        $stmt_libros = $conexion->prepare($sql_libros);
        $stmt_libros->bind_param($param_types_libros, ...$params_libros);
        $stmt_libros->execute();
        $result_libros = $stmt_libros->get_result();
    } else {
        $result_libros = $conexion->query($sql_libros);
    }
}
if ($filtro_tipo === 'todos' || $filtro_tipo === 'comic') {
    if (!empty($params_comics)) {
        $stmt_comics = $conexion->prepare($sql_comics);
        $stmt_comics->bind_param($param_types_comics, ...$params_comics);
        $stmt_comics->execute();
        $result_comics = $stmt_comics->get_result();
    } else {
        $result_comics = $conexion->query($sql_comics);
    }
}

// Categorías populares (libros y cómics, N:M)
$sql_categorias = "SELECT c.id, c.nombre, c.tipo,
                   COALESCE(SUM(CASE WHEN c.tipo = 'libro' THEN (SELECT COUNT(*) FROM libro_categorias lc WHERE lc.categoria_id = c.id) ELSE (SELECT COUNT(*) FROM comic_categorias cc WHERE cc.categoria_id = c.id) END), 0) AS total_obras
                   FROM categorias c
                   GROUP BY c.id
                   HAVING total_obras > 0
                   ORDER BY total_obras DESC
                   LIMIT 8";
$result_categorias = $conexion->query($sql_categorias);

// Estadísticas generales
$sql_stats = "SELECT 
              (SELECT COUNT(*) FROM libros) + (SELECT COUNT(*) FROM comics) as total_obras,
              (SELECT COUNT(DISTINCT usuario_id) FROM libros) + (SELECT COUNT(DISTINCT usuario_id) FROM comics) as total_autores,
              (SELECT COUNT(*) FROM categorias) as total_categorias";
$stats = $conexion->query($sql_stats)->fetch_assoc();

// Contar total de obras encontradas
$total_obras_encontradas = 0;
if ($result_libros) $total_obras_encontradas += $result_libros->num_rows;
if ($result_comics) $total_obras_encontradas += $result_comics->num_rows;

// Sección de usuarios que han subido obras
$sql_usuarios = "
    SELECT u.id, u.usuario, u.avatar, u.nombre,
        COUNT(DISTINCT l.id) AS total_libros,
        COUNT(DISTINCT co.id) AS total_comics
    FROM usuarios u
    LEFT JOIN libros l ON l.usuario_id = u.id
    LEFT JOIN comics co ON co.usuario_id = u.id
    WHERE l.id IS NOT NULL OR co.id IS NOT NULL
    GROUP BY u.id
    ORDER BY (COUNT(DISTINCT l.id) + COUNT(DISTINCT co.id)) DESC, u.usuario ASC
    LIMIT 8
";
$result_usuarios = $conexion->query($sql_usuarios);

include_once(__DIR__ . '/../includes/header.php');
?>

<div class="container">
    <div class="explorar-container">
        <!-- Hero Section -->
        <div class="explorar-hero">
            <div class="hero-content">
                <h1><i class="fas fa-compass"></i> <?php echo $TEXTOS['explorar_titulo'] ?? 'Explorar InkVerse'; ?></h1>
                <p><?php echo $TEXTOS['explorar_subtitulo'] ?? 'Descubre nuevas obras, autores talentosos y géneros fascinantes. Tu próxima lectura favorita te está esperando.'; ?></p>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="stat-number"><?php echo number_format($stats['total_obras']); ?></span>
                        <span class="stat-label"><?php echo $TEXTOS['explorar_obras'] ?? 'Obras'; ?></span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-number"><?php echo number_format($stats['total_autores']); ?></span>
                        <span class="stat-label"><?php echo $TEXTOS['explorar_autores'] ?? 'Autores'; ?></span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-number"><?php echo number_format($stats['total_categorias']); ?></span>
                        <span class="stat-label"><?php echo $TEXTOS['explorar_categorias'] ?? 'Categorías'; ?></span>
                    </div>
                </div>
            </div>
            <div class="hero-actions">
                <a href="<?php echo BASE_URL; ?>/public/buscar.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> <?php echo $TEXTOS['explorar_busqueda'] ?? 'Búsqueda avanzada'; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/aleatorio.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-random"></i> <?php echo $TEXTOS['explorar_aleatorio'] ?? 'Obra aleatoria'; ?>
                </a>
            </div>
        </div>

        <!-- Filtros de exploración -->
        <div class="filtros-explorar">
            <form method="GET" class="filtros-form">
                <div class="filtros-principales">
                    <div class="filtro-grupo">
                        <label for="tipo"><?php echo $TEXTOS['explorar_filtro_tipo'] ?? 'Tipo de obra:'; ?></label>
                        <select name="tipo" id="tipo">
                            <option value="todos" <?php echo $filtro_tipo === 'todos' ? 'selected' : ''; ?>><?php echo $TEXTOS['explorar_filtro_todos'] ?? 'Todos los tipos'; ?></option>
                            <option value="libro" <?php echo $filtro_tipo === 'libro' ? 'selected' : ''; ?>><?php echo $TEXTOS['explorar_filtro_libros'] ?? 'Libros'; ?></option>
                            <option value="comic" <?php echo $filtro_tipo === 'comic' ? 'selected' : ''; ?>><?php echo $TEXTOS['explorar_filtro_comics'] ?? 'Cómics/Manga'; ?></option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <label for="categoria"><?php echo $TEXTOS['explorar_filtro_categoria'] ?? 'Categoría:'; ?></label>
                        <select name="categoria" id="categoria">
                            <option value="0"><?php echo $TEXTOS['explorar_filtro_todas_categorias'] ?? 'Todas las categorías'; ?></option>
                            <?php 
                            $result_categorias->data_seek(0);
                            while ($cat = $result_categorias->fetch_assoc()):
                                if (!is_array($cat)) continue;
                            ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars((string)($cat['nombre'] ?? '')); ?> (<?php echo $cat['total_obras']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <label for="orden"><?php echo $TEXTOS['explorar_filtro_ordenar'] ?? 'Ordenar por:'; ?></label>
                        <select name="orden" id="orden">
                            <option value="recientes" <?php echo $orden === 'recientes' ? 'selected' : ''; ?>><?php echo $TEXTOS['explorar_orden_recientes'] ?? 'Más recientes'; ?></option>
                            <option value="populares" <?php echo $orden === 'populares' ? 'selected' : ''; ?>><?php echo $TEXTOS['explorar_orden_populares'] ?? 'Más populares'; ?></option>
                            <option value="titulo" <?php echo $orden === 'titulo' ? 'selected' : ''; ?>><?php echo $TEXTOS['explorar_orden_titulo'] ?? 'Título A-Z'; ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> <?php echo $TEXTOS['explorar_aplicar'] ?? 'Aplicar filtros'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Obras encontradas -->
        <section class="obras-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-book-open"></i> 
                    <?php 
                    if ($filtro_tipo !== 'todos' || $filtro_categoria > 0) {
                        echo $TEXTOS['explorar_obras_filtradas'] ?? "Obras filtradas";
                    } else {
                        switch ($orden) {
                            case 'populares': echo $TEXTOS['explorar_obras_populares'] ?? "Obras más populares"; break;
                            default: echo $TEXTOS['explorar_obras_recientes'] ?? "Obras recientes";
                        }
                    }
                    ?>
                </h2>
                <span class="resultados-count"><?php echo $total_obras_encontradas; ?> <?php echo $TEXTOS['explorar_resultados'] ?? 'obras encontradas'; ?></span>
            </div>
            <?php if ($total_obras_encontradas > 0): ?>
                <div class="obras-grid">
                    <?php if ($result_libros && $result_libros->num_rows > 0): ?>
                        <?php while ($libro = $result_libros->fetch_assoc()): ?>
                            <?php if (!is_array($libro)) continue; ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php if (!empty($libro['portada'])): ?>
                                        <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars((string)$libro['portada']); ?>" 
                                             alt="Portada de <?php echo htmlspecialchars((string)$libro['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="portada-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="obra-overlay">
                                        <div class="overlay-actions">
                                            <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo (int)$libro['id']; ?>&tipo=libro" 
                                               class="btn-overlay" title="<?php echo $TEXTOS['explorar_ver_obra'] ?? 'Ver obra'; ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (isset($_SESSION['usuario_id'])): ?>
                                                <button class="btn-overlay btn-favorito" 
                                                        data-obra-id="<?php echo (int)$libro['id']; ?>" 
                                                        data-tipo="libro"
                                                        title="<?php echo $TEXTOS['explorar_favorito'] ?? 'Añadir a favoritos'; ?>">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="obra-info">
                                    <div class="obra-header">
                                        <h3><?php echo htmlspecialchars((string)$libro['titulo']); ?></h3>
                                        <div class="obra-badges">
                                            <span class="badge badge-tipo"><?php echo $TEXTOS['explorar_badge_libro'] ?? 'Libro'; ?></span>
                                            <?php if (!empty($libro['categorias_nombres'])): ?>
                                                <span class="badge badge-categoria"><?php echo htmlspecialchars((string)$libro['categorias_nombres']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($libro['publico_adulto'])): ?>
                                                <span class="badge badge-adulto">+18</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="obra-autor">
                                        <i class="fas fa-user"></i>
                                        <?php
                                        $autor_id = $libro['usuario_id'];
                                        $autor_nombre = '';
                                        if ($autor_id) {
                                            $q = $conexion->query("SELECT usuario FROM usuarios WHERE id = " . intval($autor_id) . " LIMIT 1");
                                            if ($q && ($row = $q->fetch_assoc())) {
                                                $autor_nombre = $row['usuario'];
                                            }
                                        }
                                        echo htmlspecialchars((string)$autor_nombre);
                                        ?>
                                    </p>
                                    <?php if (!empty($libro['descripcion'])): ?>
                                        <p class="obra-descripcion">
                                            <?php echo substr(htmlspecialchars((string)$libro['descripcion']), 0, 100) . '...'; ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="obra-stats">
                                        <span><i class="fas fa-heart"></i> <?php echo (int)$libro['total_favoritos']; ?></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($libro['fecha_subida'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    <?php if ($result_comics && $result_comics->num_rows > 0): ?>
                        <?php while ($comic = $result_comics->fetch_assoc()): ?>
                            <?php if (!is_array($comic)) continue; ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php if (!empty($comic['portada'])): ?>
                                        <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars((string)$comic['portada']); ?>" 
                                             alt="Portada de <?php echo htmlspecialchars((string)$comic['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="portada-placeholder">
                                            <i class="fas fa-book-open"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="obra-overlay">
                                        <div class="overlay-actions">
                                            <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo (int)$comic['id']; ?>&tipo=comic" 
                                               class="btn-overlay" title="<?php echo $TEXTOS['explorar_ver_obra'] ?? 'Ver obra'; ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (isset($_SESSION['usuario_id'])): ?>
                                                <button class="btn-overlay btn-favorito" 
                                                        data-obra-id="<?php echo (int)$comic['id']; ?>" 
                                                        data-tipo="comic"
                                                        title="<?php echo $TEXTOS['explorar_favorito'] ?? 'Añadir a favoritos'; ?>">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="obra-info">
                                    <div class="obra-header">
                                        <h3><?php echo htmlspecialchars((string)$comic['titulo']); ?></h3>
                                        <div class="obra-badges">
                                            <span class="badge badge-tipo"><?php echo $TEXTOS['explorar_badge_comic'] ?? 'Cómic'; ?></span>
                                            <?php if (!empty($comic['publico_adulto'])): ?>
                                                <span class="badge badge-adulto">+18</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="obra-autor">
                                        <i class="fas fa-user"></i>
                                        <?php
                                        $autor_id = $comic['usuario_id'];
                                        $autor_nombre = '';
                                        if ($autor_id) {
                                            $q = $conexion->query("SELECT usuario FROM usuarios WHERE id = " . intval($autor_id) . " LIMIT 1");
                                            if ($q && ($row = $q->fetch_assoc())) {
                                                $autor_nombre = $row['usuario'];
                                            }
                                        }
                                        echo htmlspecialchars((string)$autor_nombre);
                                        ?>
                                    </p>
                                    <?php if (!empty($comic['descripcion'])): ?>
                                        <p class="obra-descripcion">
                                            <?php echo substr(htmlspecialchars((string)$comic['descripcion']), 0, 100) . '...'; ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="obra-stats">
                                        <span><i class="fas fa-heart"></i> <?php echo (int)$comic['total_favoritos']; ?></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($comic['fecha_subida'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
                <div class="ver-mas-container">
                    <a href="<?php echo BASE_URL; ?>/public/buscar.php?tipo=<?php echo $filtro_tipo; ?>&categoria=<?php echo $filtro_categoria; ?>&orden=<?php echo $orden; ?>" 
                       class="btn btn-outline btn-lg">
                        <i class="fas fa-plus"></i> <?php echo $TEXTOS['explorar_ver_mas_obras'] ?? 'Ver más obras'; ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="no-obras">
                    <div class="no-obras-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3><?php echo $TEXTOS['explorar_no_obras'] ?? 'No se encontraron obras'; ?></h3>
                    <p><?php echo $TEXTOS['explorar_no_obras_desc'] ?? 'Intenta ajustar los filtros o explora otras categorías.'; ?></p>
                    <div class="no-obras-acciones">
                        <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-primary">
                            <i class="fas fa-refresh"></i> <?php echo $TEXTOS['explorar_limpiar'] ?? 'Limpiar filtros'; ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/public/aleatorio.php" class="btn btn-outline">
                            <i class="fas fa-random"></i> <?php echo $TEXTOS['explorar_aleatorio'] ?? 'Obra aleatoria'; ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Categorías populares -->
        <section class="categorias-section">
            <div class="section-header">
                <h2><i class="fas fa-tags"></i> <?php echo $TEXTOS['explorar_categorias_populares'] ?? 'Categorías Populares'; ?></h2>
                <a href="<?php echo BASE_URL; ?>/public/categorias.php" class="btn btn-outline"><?php echo $TEXTOS['explorar_ver_todas_cat'] ?? 'Ver todas'; ?></a>
            </div>
            <div class="categorias-grid">
                <?php 
                $result_categorias->data_seek(0);
                $count = 0;
                while (($categoria = $result_categorias->fetch_assoc()) && $count < 6):
                    if (!is_array($categoria)) continue; $count++;
                ?>
                    <div class="categoria-card">
                        <div class="categoria-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="categoria-content">
                            <h3><?php echo htmlspecialchars((string)($categoria['nombre'] ?? '')); ?></h3>
                            <p><?php echo $categoria['total_obras']; ?> <?php echo $TEXTOS['explorar_obras'] ?? 'obras'; ?></p>
                            <span class="categoria-tipo"><?php echo $categoria['tipo'] === 'comic' ? ($TEXTOS['explorar_badge_comic'] ?? 'Cómic/Manga') : ($TEXTOS['explorar_badge_libro'] ?? 'Literatura'); ?></span>
                        </div>
                    
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?categoria_id=<?php echo (int)$categoria['id']; ?>&tipo=<?php echo urlencode($categoria['tipo']); ?>" 
                        class="btn btn-sm btn-primary"><?php echo $TEXTOS['explorar_explorar'] ?? 'Explorar'; ?></a>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- Sección: Descubre usuarios con obras -->
        <section class="usuarios-section">
            <div class="section-header">
                <h2><i class="fas fa-users"></i> <?php echo $TEXTOS['explorar_usuarios_titulo'] ?? 'Descubre autores y creadoras/es'; ?></h2>
                <p><?php echo $TEXTOS['explorar_usuarios_subtitulo'] ?? 'Conoce a quienes están compartiendo historias y cómics en InkVerse'; ?></p>
            </div>
            <div class="usuarios-grid">
                <?php if ($result_usuarios && $result_usuarios->num_rows > 0): ?>
                    <?php while ($usuario = $result_usuarios->fetch_assoc()): ?>
                        <?php if (!is_array($usuario)) continue; ?>
                        <div class="usuario-card">
                            <div class="usuario-avatar">
                                <?php if (!empty($usuario['avatar'])): ?>
                                    <img src="<?php echo BASE_URL . '/uploads/avatars/' . htmlspecialchars((string)$usuario['avatar']); ?>" alt="<?php echo htmlspecialchars((string)$usuario['usuario']); ?>">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="usuario-info">
                                <h4><?php echo htmlspecialchars((string)($usuario['usuario'] ?? '')); ?></h4>
                                <?php if (!empty($usuario['nombre'])): ?>
                                    <p class="usuario-nombre"><?php echo htmlspecialchars((string)$usuario['nombre']); ?></p>
                                <?php endif; ?>
                                <div class="usuario-obras">
                                    <?php if (!empty($usuario['total_libros'])): ?>
                                        <span class="badge badge-tipo"><?php echo $TEXTOS['explorar_badge_libro'] ?? 'Libros'; ?>: <?php echo (int)$usuario['total_libros']; ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($usuario['total_comics'])): ?>
                                        <span class="badge badge-tipo"><?php echo $TEXTOS['explorar_badge_comic'] ?? 'Cómics'; ?>: <?php echo (int)$usuario['total_comics']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo BASE_URL; ?>/public/perfil.php?id=<?php echo (int)$usuario['id']; ?>" class="btn btn-sm btn-outline"><?php echo $TEXTOS['explorar_ver_perfil'] ?? 'Ver perfil'; ?></a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-usuarios"><?php echo $TEXTOS['explorar_no_usuarios'] ?? 'Todavía no hay autores con obras públicas. ¡Sé el primero en publicar!'; ?></p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Sección de comunidad -->
        <section class="comunidad-section">
            <div class="section-header">
                <h2><i class="fas fa-heart"></i> <?php echo $TEXTOS['explorar_comunidad'] ?? 'Únete a la Comunidad'; ?></h2>
                <p><?php echo $TEXTOS['explorar_comunidad_sub'] ?? 'Conecta con otros lectores y escritores'; ?></p>
            </div>
            <div class="comunidad-grid">
                <div class="comunidad-card">
                    <div class="comunidad-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="comunidad-content">
                        <h3><?php echo $TEXTOS['explorar_foros'] ?? 'Foros de Discusión'; ?></h3>
                        <p><?php echo $TEXTOS['explorar_foros_desc'] ?? 'Participa en conversaciones sobre tus obras favoritas, comparte opiniones y conecta con otros lectores apasionados.'; ?></p>
                        <a href="<?php echo BASE_URL; ?>/comunidad/foros.php" class="btn btn-primary"><?php echo $TEXTOS['explorar_ir_foros'] ?? 'Visitar foros'; ?></a>
                    </div>
                </div>
                <div class="comunidad-card">
                    <div class="comunidad-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="comunidad-content">
                        <h3><?php echo $TEXTOS['explorar_galeria'] ?? 'Galería de Arte'; ?></h3>
                        <p><?php echo $TEXTOS['explorar_galeria_desc'] ?? 'Explora ilustraciones, portadas y arte conceptual creado por nuestra talentosa comunidad de artistas.'; ?></p>
                        <a href="<?php echo BASE_URL; ?>/comunidad/arte.php" class="btn btn-primary"><?php echo $TEXTOS['explorar_ir_galeria'] ?? 'Ver galería'; ?></a>
                    </div>
                </div>
                <div class="comunidad-card">
                    <div class="comunidad-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="comunidad-content">
                        <h3><?php echo $TEXTOS['explorar_grupos'] ?? 'Grupos de Lectura'; ?></h3>
                        <p><?php echo $TEXTOS['explorar_grupos_desc'] ?? 'Únete a grupos de lectura, participa en desafíos literarios y descubre nuevas obras junto a otros lectores.'; ?></p>
                        <a href="<?php echo BASE_URL; ?>/comunidad/grupos.php" class="btn btn-primary"><?php echo $TEXTOS['explorar_ir_grupos'] ?? 'Explorar grupos'; ?></a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<style>

.body {
    background: linear-gradient(120deg, #ece9f7 0%, #f6f7fb 100%);
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
    color: #2d2d50;
}
.explorar-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 32px 12px 48px 12px;
}
.explorar-hero {
    background: linear-gradient(135deg, #7f53ff 0%, #5ad6ff 100%);
    border-radius: 22px;
    padding: 64px 36px;
    text-align: center;
    color: white;
    margin-bottom: 48px;
    box-shadow: 0 6px 32px 0 rgb(130 90 255 / 14%);
    overflow: hidden;
    position: relative;
}
.explorar-hero::before {
    content: "";
    position: absolute;
    left: -80px; top: -80px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: rgba(255,255,255,0.13);
    z-index: 0;
}
.hero-content, .hero-actions { position: relative; z-index: 1; }
.hero-content h1 {
    font-size: 54px;
    margin: 0 0 25px 0;
    font-weight: 800;
    letter-spacing: -2px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 18px;
    text-shadow: 0 2px 24px rgba(90, 100, 255, 0.10);
}
.hero-content p {
    font-size: 24px;
    margin: 0 0 44px 0;
    opacity: 0.95;
    max-width: 650px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.4;
}
.hero-stats {
    display: flex;
    justify-content: center;
    gap: 36px;
    margin: 40px 0;
}
.hero-stat {
    text-align: center;
    padding: 14px 32px;
    background: rgba(255,255,255,0.16);
    border-radius: 18px;
    box-shadow: 0 2px 12px 0 rgba(255,255,255,0.10);
}
.stat-number {
    display: block;
    font-size: 36px;
    font-weight: bold;
    margin-bottom: 6px;
    letter-spacing: -1px;
}
.stat-label {
    font-size: 15px;
    opacity: 0.84;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.hero-actions {
    display: flex;
    gap: 22px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 35px;
}
.btn-lg {
    padding: 18px 34px;
    font-size: 18px;
    font-weight: 700;
    border-radius: 12px;
    box-shadow: 0 2px 10px 0 rgba(130, 90, 255,0.08);
    letter-spacing: 0.5px;
}
.btn-primary {
    background: linear-gradient(90deg, #7f53ff 0%, #5ad6ff 100%);
    color: #fff;
    border: none;
}
.btn-primary:hover {
    background: linear-gradient(90deg, #5ad6ff 0%, #7f53ff 100%);
    color: #fff;
    transform: translateY(-2px) scale(1.04);
}
.btn-outline {
    background: transparent;
    border: 2px solid #5ad6ff;
    color: #5ad6ff;
}
.btn-outline:hover {
    background: #5ad6ff;
    color: #fff;
}
.btn-sm {
    padding: 7px 14px;
    font-size: 13px;
    border-radius: 8px;
}

.filtros-explorar {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 32px rgba(0,0,0,0.06);
    padding: 32px;
    margin-bottom: 48px;
}
.filtros-principales {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 22px;
    align-items: end;
}
.filtro-grupo {
    display: flex;
    flex-direction: column;
    gap: 11px;
}
.filtro-grupo label {
    font-weight: 700;
    color: #555;
    font-size: 15px;
    letter-spacing: 0.4px;
}
.filtro-grupo select {
    padding: 13px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 15px;
    background: white;
    transition: all 0.2s;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    padding-bottom: 14px;
    border-bottom: 2px solid #f1f2f8;
}
.section-header h2 {
    margin: 0;
    color: #2d2d50;
    font-size: 28px;
    display: flex;
    align-items: center;
    gap: 14px;
    font-weight: 700;
    letter-spacing: 0.2px;
}
.resultados-count {
    color: #8585ad;
    font-size: 15px;
    background: #f8f9fa;
    padding: 6px 18px;
    border-radius: 20px;
    font-weight: 600;
}

.obras-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(295px, 1fr));
    gap: 30px;
    margin-bottom: 45px;
}
.obra-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(127,83,255,0.10);
    overflow: hidden;
    transition: all 0.27s cubic-bezier(.4,2.2,.6,1);
    position: relative;
    border: 2px solid transparent;
}
.obra-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 12px 36px rgba(127,83,255,0.16);
    border: 2px solid #ded6ff;
}
.obra-portada {
    height: 220px;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #7f53ff 0%, #5ad6ff 100%);
}
.obra-portada img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.27s cubic-bezier(.4,2.2,.6,1);
}
.portada-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.8);
    font-size: 54px;
}

.obra-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(45, 45, 80, 0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.25s;
}
.obra-card:hover .obra-overlay {
    opacity: 1;
}
.overlay-actions {
    display: flex;
    gap: 18px;
}
.btn-overlay {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    color: #363657;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 22px;
    box-shadow: 0 2px 8px 0 rgba(45,45,80,0.10);
}
.btn-overlay:hover {
    background: #fff;
    color: #7f53ff;
    transform: scale(1.12);
}
.btn-favorito:hover {
    background: #f64c72;
    color: #fff;
}
.obra-info {
    padding: 26px 22px 22px 22px;
}
.obra-header h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
    color: #363657;
    line-height: 1.25;
    font-weight: 700;
}
.obra-badges {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
    margin-bottom: 13px;
}
.badge {
    padding: 4px 10px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.7px;
}
.badge-tipo {
    background: #eaf1ff;
    color: #7f53ff;
}
.badge-categoria {
    background: #f3e5f5;
    color: #a13fff;
}
.badge-adulto {
    background: #ffebee;
    color: #d32f2f;
}
.obra-autor {
    color: #8b89b5;
    margin: 13px 0;
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 15px;
}
.obra-descripcion {
    color: #555;
    line-height: 1.5;
    margin: 12px 0;
    font-size: 15px;
}
.obra-stats {
    display: flex;
    gap: 18px;
    margin: 17px 0 0 0;
    font-size: 13px;
    color: #a09fc0;
    flex-wrap: wrap;
}
.obra-stats span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.categorias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 22px;
    margin-bottom: 45px;
}
.categoria-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(127,83,255,0.08);
    padding: 28px;
    text-align: center;
    transition: all 0.18s;
    border: 2px solid transparent;
}
.categoria-card:hover {
    transform: translateY(-7px) scale(1.03);
    box-shadow: 0 10px 28px rgba(127,83,255,0.14);
    border: 2px solid #e1e1fa;
}
.categoria-icon {
    width: 66px;
    height: 66px;
    border-radius: 50%;
    background: linear-gradient(135deg, #5ad6ff, #7f53ff);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    color: white;
    font-size: 26px;
}
.categoria-content h3 {
    margin: 0 0 9px 0;
    color: #363657;
    font-size: 19px;
    font-weight: 700;
}
.categoria-content p {
    margin: 0 0 6px 0;
    color: #7f53ff;
    font-size: 15px;
    font-weight: 600;
}
.categoria-tipo {
    display: inline-block;
    background: #f0f0f8;
    color: #7f53ff;
    padding: 4px 11px;
    border-radius: 13px;
    font-size: 12px;
    text-transform: uppercase;
    margin-bottom: 16px;
    font-weight: 700;
}

.usuarios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}
.usuario-card {
    background: #fff;
    border-radius: 13px;
    box-shadow: 0 2px 10px rgba(127,83,255,0.10);
    padding: 25px 20px;
    text-align: center;
    transition: all 0.18s;
    border: 2px solid transparent;
}
.usuario-card:hover {
    transform: translateY(-4px) scale(1.03);
    box-shadow: 0 8px 24px rgba(127,83,255,0.12);
    border: 2px solid #efdaff;
}
.usuario-avatar {
    width: 74px;
    height: 74px;
    border-radius: 50%;
    margin: 0 auto 16px;
    overflow: hidden;
    background: #f1f0fa;
    display: flex;
    align-items: center;
    justify-content: center;
}
.usuario-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}
.avatar-placeholder {
    font-size: 40px;
    color: #a09fc0;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}
.usuario-info h4 {
    margin: 0 0 5px 0;
    font-size: 19px;
    color: #363657;
    font-weight: 700;
}
.usuario-nombre {
    color: #8585ad;
    font-size: 14px;
    margin-bottom: 8px;
}
.usuario-obras {
    margin-bottom: 10px;
}
.usuario-obras .badge-tipo {
    margin-right: 5px;
    margin-left: 5px;
    background: #eaf1ff;
    color: #7f53ff;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.no-usuarios {
    color: #a09fc0;
    font-style: italic;
    margin: 32px 0;
    text-align: center;
}

.comunidad-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 28px;
    margin-bottom: 48px;
}
.comunidad-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 16px rgba(127,83,255,0.08);
    padding: 34px;
    text-align: center;
    transition: all 0.18s;
    border: 2px solid transparent;
}
.comunidad-card:hover {
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 10px 32px rgba(127,83,255,0.11);
    border: 2px solid #e0e0fa;
}
.comunidad-icon {
    width: 78px;
    height: 78px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7f53ff, #5ad6ff);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 22px;
    color: white;
    font-size: 31px;
}
.comunidad-content h3 {
    margin: 0 0 17px 0;
    color: #363657;
    font-size: 22px;
    font-weight: 700;
}
.comunidad-content p {
    color: #7d7ca1;
    line-height: 1.6;
    margin: 0 0 20px 0;
    font-size: 15px;
}

.ver-mas-container {
    text-align: center;
    margin: 48px 0 0 0;
}

.no-obras {
    text-align: center;
    padding: 70px 20px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 2px 14px rgba(127,83,255,0.07);
    margin-bottom: 30px;
}

.no-obras-icon {
    font-size: 70px;
    color: #ddd;
    margin-bottom: 28px;
}

.no-obras h3 {
    color: #363657;
    margin-bottom: 14px;
    font-weight: 700;
}

.no-obras p {
    color: #8585ad;
    margin-bottom: 36px;
    max-width: 440px;
    margin-left: auto;
    margin-right: auto;
    font-size: 16px;
}

.no-obras-acciones {
    display: flex;
    gap: 18px;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 900px) {
    .explorar-hero { padding: 30px 7vw; }
    .hero-content h1 { font-size: 30px; }
    .hero-content p { font-size: 16px; }
    .hero-stats { flex-direction: column; gap: 16px; }
    .filtros-principales { grid-template-columns: 1fr; }
    .section-header { flex-direction: column; align-items: flex-start; gap: 10px; }
    .obras-grid { grid-template-columns: 1fr; }
    .categorias-grid { grid-template-columns: 1fr; }
    .comunidad-grid { grid-template-columns: 1fr; }
    .usuarios-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .explorar-container { padding: 6px 0; }
    .explorar-hero { padding: 16px 2vw; }
    .filtros-explorar { padding: 12px; }
    .obra-info, .categoria-card, .comunidad-card, .usuario-card { padding-left: 10px; padding-right: 10px; }
    .hero-stat { padding: 8px 5vw; }
    .categoria-card, .comunidad-card, .usuario-card { padding: 18px 8px; }
}

</style>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>