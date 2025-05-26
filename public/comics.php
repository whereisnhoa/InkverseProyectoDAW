<?php
/**
 * Página de Cómics Ultra Mejorada
 * Diseño moderno con filtros avanzados, búsqueda inteligente y mejor UX
 */

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Parámetros de filtrado avanzados
$mostrar_destacados = isset($_GET['destacados']) && $_GET['destacados'] == 1;
$mostrar_recientes = isset($_GET['recientes']) && $_GET['recientes'] == 1;
$mostrar_populares = isset($_GET['populares']) && $_GET['populares'] == 1;
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$tipo_comic = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$demografia = isset($_GET['demografia']) ? $_GET['demografia'] : '';
$genero = isset($_GET['genero']) ? $_GET['genero'] : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'recientes';

// Paginación
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina = 16;
$offset = ($pagina - 1) * $por_pagina;

// Título dinámico de la página
$titulo_pagina = "Cómics y Manga";
if ($mostrar_destacados) $titulo_pagina .= " - Destacados";
elseif ($mostrar_recientes) $titulo_pagina .= " - Recientes";
elseif ($mostrar_populares) $titulo_pagina .= " - Populares";

// Obtener categorías organizadas
$sql_categorias = "SELECT id, nombre, descripcion FROM categorias WHERE tipo = 'comic' ORDER BY 
                   CASE 
                       WHEN nombre IN ('Cómic', 'Manga', 'Manhwa', 'Manhua') THEN 1
                       WHEN nombre IN ('Shonen', 'Shojo', 'Seinen', 'Josei') THEN 2
                       ELSE 3
                   END, nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = [];
$tipos_principales = [];
$demografias = [];
$generos = [];

if ($result_categorias) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
        
        // Clasificar categorías
        if (in_array($row['nombre'], ['Cómic', 'Manga', 'Manhwa', 'Manhua'])) {
            $tipos_principales[] = $row;
        } elseif (in_array($row['nombre'], ['Shonen', 'Shojo', 'Seinen', 'Josei'])) {
            $demografias[] = $row;
        } else {
            $generos[] = $row;
        }
    }
}

// Construir consulta principal con filtros avanzados
$where_conditions = ["1=1"];
$params = [];
$types = "";

if ($categoria_id > 0) {
    $where_conditions[] = "c.categoria_id = ?";
    $params[] = $categoria_id;
    $types .= "i";
}

if (!empty($busqueda)) {
    $where_conditions[] = "(c.titulo LIKE ? OR c.autor LIKE ? OR c.descripcion LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $types .= "sss";
}

if ($mostrar_destacados) {
    $where_conditions[] = "c.destacado = 1";
}

// Filtros adicionales por tipo de cómic
if (!empty($tipo_comic)) {
    $where_conditions[] = "cat.nombre = ?";
    $params[] = $tipo_comic;
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Determinar orden
switch ($orden) {
    case 'populares':
        $orden_sql = "c.visitas DESC, c.favoritos DESC";
        break;
    case 'valoracion':
        $orden_sql = "c.valoracion DESC, c.visitas DESC";
        break;
    case 'alfabetico':
        $orden_sql = "c.titulo ASC";
        break;
    case 'autor':
        $orden_sql = "c.autor ASC, c.titulo ASC";
        break;
    case 'antiguos':
        $orden_sql = "c.fecha_subida ASC";
        break;
    default:
        $orden_sql = "c.fecha_subida DESC";
}

// Consulta principal con paginación
$sql_principal = "SELECT c.id, c.titulo, c.autor, c.descripcion, c.portada, c.publico_adulto, 
                  cat.nombre as categoria, cat.descripcion as categoria_desc, c.fecha_subida, c.visitas, c.valoracion,
                  (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = c.id AND f.tipo_contenido = 'comic') as total_favoritos,
                  (SELECT COUNT(*) FROM comentarios co WHERE co.contenido_id = c.id AND co.tipo_contenido = 'comic') as total_comentarios
                  FROM comics c
                  LEFT JOIN categorias cat ON c.categoria_id = cat.id
                  WHERE $where_clause
                  ORDER BY $orden_sql
                  LIMIT ? OFFSET ?";

$stmt = $conexion->prepare($sql_principal);
if (!empty($params)) {
    $params[] = $por_pagina;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $por_pagina, $offset);
}
$stmt->execute();
$result_principal = $stmt->get_result();

// Contar total para paginación
$sql_count = "SELECT COUNT(*) as total FROM comics c LEFT JOIN categorias cat ON c.categoria_id = cat.id WHERE $where_clause";
$stmt_count = $conexion->prepare($sql_count);
if (!empty($params) && count($params) > 2) {
    $params_count = array_slice($params, 0, -2);
    $types_count = substr($types, 0, -2);
    if (!empty($params_count)) {
        $stmt_count->bind_param($types_count, ...$params_count);
    }
}
$stmt_count->execute();
$total_obras = $stmt_count->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_obras / $por_pagina);

// Obtener obras destacadas para el hero
$obras_hero = [];
if (!$mostrar_destacados && !$mostrar_recientes && !$mostrar_populares && empty($busqueda) && $categoria_id == 0) {
    $sql_hero = "SELECT c.id, c.titulo, c.autor, c.portada, cat.nombre as categoria
                 FROM comics c
                 LEFT JOIN categorias cat ON c.categoria_id = cat.id
                 WHERE c.destacado = 1
                 ORDER BY c.fecha_destacado DESC
                 LIMIT 4";
    $result_hero = $conexion->query($sql_hero);
    if ($result_hero) {
        while ($row = $result_hero->fetch_assoc()) {
            $obras_hero[] = $row;
        }
    }
}

// Obtener estadísticas
$sql_stats = "SELECT 
              COUNT(*) as total_comics,
              COUNT(CASE WHEN cat.nombre = 'Manga' THEN 1 END) as total_manga,
              COUNT(CASE WHEN cat.nombre = 'Manhwa' THEN 1 END) as total_manhwa,
              COUNT(CASE WHEN cat.nombre = 'Cómic' THEN 1 END) as total_comic
              FROM comics c
              LEFT JOIN categorias cat ON c.categoria_id = cat.id";
$result_stats = $conexion->query($sql_stats);
$stats = $result_stats ? $result_stats->fetch_assoc() : ['total_comics' => 0, 'total_manga' => 0, 'total_manhwa' => 0, 'total_comic' => 0];
?>

<!-- Hero Section Mejorado -->
<?php if (empty($obras_hero) === false): ?>
<div class="comics-hero-advanced">
    <div class="hero-background">
        <div class="hero-particles"></div>
        <div class="hero-gradient"></div>
    </div>
    
    <div class="container">
        <div class="hero-content-advanced">
            <div class="hero-text-section">
                <div class="hero-badge">
                    <i class="fas fa-book-open"></i>
                    <span>Mundo Visual</span>
                </div>
                <h1 class="hero-title">Cómics & Manga</h1>
                <p class="hero-description">Explora universos visuales únicos donde cada viñeta cuenta una historia</p>
                
                <div class="hero-stats-grid">
                    <div class="hero-stat">
                        <div class="stat-number"><?php echo number_format($stats['total_comics']); ?></div>
                        <div class="stat-label">Total Obras</div>
                    </div>
                    <div class="hero-stat">
                        <div class="stat-number"><?php echo number_format($stats['total_manga']); ?></div>
                        <div class="stat-label">Manga</div>
                    </div>
                    <div class="hero-stat">
                        <div class="stat-number"><?php echo number_format($stats['total_manhwa']); ?></div>
                        <div class="stat-label">Manhwa</div>
                    </div>
                    <div class="hero-stat">
                        <div class="stat-number"><?php echo number_format($stats['total_comic']); ?></div>
                        <div class="stat-label">Cómics</div>
                    </div>
                </div>
                
                <div class="hero-actions">
                    <a href="#explorar" class="btn btn-primary btn-lg">
                        <i class="fas fa-compass"></i> Explorar Ahora
                    </a>
                    <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn btn-outline-white btn-lg">
                        <i class="fas fa-plus"></i> Publicar Obra
                    </a>
                </div>
            </div>
            
            <div class="hero-showcase">
                <div class="showcase-grid">
                    <?php foreach ($obras_hero as $index => $obra): ?>
                        <div class="showcase-item" style="--delay: <?php echo $index * 0.15; ?>s">
                            <div class="showcase-cover">
                                <?php if (!empty($obra['portada'])): ?>
                                    <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" 
                                         alt="<?php echo htmlspecialchars($obra['titulo']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="showcase-placeholder">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="showcase-overlay">
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra['id']; ?>&tipo=comic" 
                                       class="showcase-link">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="showcase-info">
                                <h4><?php echo htmlspecialchars($obra['titulo']); ?></h4>
                                <p><?php echo htmlspecialchars($obra['autor']); ?></p>
                                <?php if (!empty($obra['categoria'])): ?>
                                    <span class="showcase-category"><?php echo htmlspecialchars($obra['categoria']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="page-header-simple">
    <div class="container">
        <div class="header-content">
            <h1><i class="fas fa-book-open"></i> <?php echo $titulo_pagina; ?></h1>
            <p>Explora nuestra colección de cómics y manga</p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container" id="explorar">
    <!-- Navegación y filtros avanzados -->
    <div class="comics-nav-advanced">
        <div class="nav-sections">
            <a href="<?php echo BASE_URL; ?>/public/comics.php" 
               class="nav-item <?php echo (!$mostrar_destacados && !$mostrar_recientes && !$mostrar_populares) ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i> 
                <span>Todos</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/public/comics.php?destacados=1" 
               class="nav-item <?php echo $mostrar_destacados ? 'active' : ''; ?>">
                <i class="fas fa-star"></i> 
                <span>Destacados</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/public/comics.php?recientes=1" 
               class="nav-item <?php echo $mostrar_recientes ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> 
                <span>Recientes</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/public/comics.php?populares=1" 
               class="nav-item <?php echo $mostrar_populares ? 'active' : ''; ?>">
                <i class="fas fa-fire"></i> 
                <span>Populares</span>
            </a>
        </div>
        
        <div class="nav-actions">
            <button class="btn btn-outline" onclick="toggleAdvancedFilters()">
                <i class="fas fa-sliders-h"></i> Filtros Avanzados
            </button>
            <div class="view-toggle">
                <button class="view-btn active" data-view="grid" title="Vista en cuadrícula">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-btn" data-view="list" title="Vista en lista">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Panel de filtros avanzados -->
    <div class="filtros-panel-advanced" id="filtros-panel">
        <form method="GET" class="filtros-form-advanced">
            <!-- Mantener parámetros de sección -->
            <?php if ($mostrar_destacados): ?>
                <input type="hidden" name="destacados" value="1">
            <?php elseif ($mostrar_recientes): ?>
                <input type="hidden" name="recientes" value="1">
            <?php elseif ($mostrar_populares): ?>
                <input type="hidden" name="populares" value="1">
            <?php endif; ?>
            
            <div class="filtros-grid">
                <div class="filtro-group">
                    <label for="buscar">
                        <i class="fas fa-search"></i> Buscar
                    </label>
                    <input type="text" id="buscar" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>" 
                           placeholder="Título, autor o descripción...">
                </div>
                
                <div class="filtro-group">
                    <label for="tipo">
                        <i class="fas fa-bookmark"></i> Tipo
                    </label>
                    <select id="tipo" name="tipo">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tipos_principales as $tipo): ?>
                            <option value="<?php echo htmlspecialchars($tipo['nombre']); ?>" 
                                    <?php echo $tipo_comic == $tipo['nombre'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tipo['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-group">
                    <label for="categoria">
                        <i class="fas fa-tags"></i> Género
                    </label>
                    <select id="categoria" name="categoria">
                        <option value="0">Todos los géneros</option>
                        <?php foreach ($generos as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $categoria_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filtro-group">
                    <label for="orden">
                        <i class="fas fa-sort"></i> Ordenar por
                    </label>
                    <select id="orden" name="orden">
                        <option value="recientes" <?php echo $orden == 'recientes' ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="populares" <?php echo $orden == 'populares' ? 'selected' : ''; ?>>Más populares</option>
                        <option value="valoracion" <?php echo $orden == 'valoracion' ? 'selected' : ''; ?>>Mejor valorados</option>
                        <option value="alfabetico" <?php echo $orden == 'alfabetico' ? 'selected' : ''; ?>>A-Z</option>
                        <option value="autor" <?php echo $orden == 'autor' ? 'selected' : ''; ?>>Por autor</option>
                        <option value="antiguos" <?php echo $orden == 'antiguos' ? 'selected' : ''; ?>>Más antiguos</option>
                    </select>
                </div>
            </div>
            
            <div class="filtro-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Aplicar Filtros
                </button>
                <a href="<?php echo BASE_URL; ?>/public/comics.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Filtros rápidos por demografía -->
    <?php if (!empty($demografias)): ?>
    <div class="quick-filters">
        <div class="quick-filters-label">
            <i class="fas fa-users"></i> Demografía:
        </div>
        <div class="quick-filters-items">
            <?php foreach ($demografias as $demo): ?>
                <a href="?categoria=<?php echo $demo['id']; ?>" 
                   class="quick-filter-item <?php echo $categoria_id == $demo['id'] ? 'active' : ''; ?>"
                   title="<?php echo htmlspecialchars($demo['descripcion']); ?>">
                    <?php echo htmlspecialchars($demo['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resultados -->
    <div class="resultados-header-advanced">
        <div class="resultados-info">
            <h2>
                <?php 
                if ($mostrar_destacados) echo "Obras Destacadas";
                elseif ($mostrar_recientes) echo "Obras Recientes";
                elseif ($mostrar_populares) echo "Obras Populares";
                else echo "Todas las Obras";
                ?>
            </h2>
            <p>
                <span class="results-count"><?php echo number_format($total_obras); ?></span> 
                obras encontradas
                <?php if (!empty($busqueda)): ?>
                    para "<strong><?php echo htmlspecialchars($busqueda); ?></strong>"
                <?php endif; ?>
            </p>
        </div>
        
        <div class="resultados-actions">
            <div class="items-per-page">
                <label>Mostrar:</label>
                <select onchange="changeItemsPerPage(this.value)">
                    <option value="16" <?php echo $por_pagina == 16 ? 'selected' : ''; ?>>16</option>
                    <option value="24" <?php echo $por_pagina == 24 ? 'selected' : ''; ?>>24</option>
                    <option value="32" <?php echo $por_pagina == 32 ? 'selected' : ''; ?>>32</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Grid de obras mejorado -->
    <div class="obras-container-advanced" id="obras-container">
        <?php if ($result_principal && $result_principal->num_rows > 0): ?>
            <div class="obras-grid-advanced" id="obras-grid">
                <?php while ($comic = $result_principal->fetch_assoc()): ?>
                    <article class="obra-card-advanced" data-aos="fade-up">
                        <div class="obra-image-container">
                            <?php if (!empty($comic['portada'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($comic['portada']); ?>" 
                                     alt="<?php echo htmlspecialchars($comic['titulo']); ?>"
                                     class="obra-image"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="obra-placeholder-advanced">
                                    <i class="fas fa-book-open"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Overlay con acciones -->
                            <div class="obra-overlay-advanced">
                                <div class="obra-actions-advanced">
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $comic['id']; ?>&tipo=comic" 
                                       class="action-btn primary" title="Ver obra">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (isset($_SESSION['usuario_id'])): ?>
                                        <button class="action-btn secondary btn-favorito" 
                                                data-obra-id="<?php echo $comic['id']; ?>" 
                                                data-tipo="comic"
                                                title="Añadir a favoritos">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="action-btn secondary" title="Compartir">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                </div>
                                
                                <!-- Rating -->
                                <?php if ($comic['valoracion'] > 0): ?>
                                <div class="obra-rating">
                                    <div class="rating-stars">
                                        <?php
                                        $rating = round($comic['valoracion']);
                                        for ($i = 1; $i <= 5; $i++):
                                        ?>
                                            <i class="fas fa-star <?php echo $i <= $rating ? 'active' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-value"><?php echo number_format($comic['valoracion'], 1); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Badges -->
                            <div class="obra-badges">
                                <?php if (!empty($comic['categoria'])): ?>
                                    <span class="badge badge-categoria" title="<?php echo htmlspecialchars($comic['categoria_desc']); ?>">
                                        <?php echo htmlspecialchars($comic['categoria']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($comic['publico_adulto']): ?>
                                    <span class="badge badge-adulto">+18</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="obra-content-advanced">
                            <div class="obra-header-advanced">
                                <h3 class="obra-title">
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $comic['id']; ?>&tipo=comic">
                                        <?php echo htmlspecialchars($comic['titulo']); ?>
                                    </a>
                                </h3>
                            </div>
                            
                            <p class="obra-autor">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($comic['autor']); ?>
                            </p>
                            
                            <p class="obra-descripcion">
                                <?php echo substr(htmlspecialchars($comic['descripcion']), 0, 100) . '...'; ?>
                            </p>
                            
                            <div class="obra-stats-advanced">
                                <div class="stat-item">
                                    <i class="fas fa-eye"></i>
                                    <span><?php echo number_format($comic['visitas']); ?></span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-heart"></i>
                                    <span><?php echo $comic['total_favoritos']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-comments"></i>
                                    <span><?php echo $comic['total_comentarios']; ?></span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d/m/Y', strtotime($comic['fecha_subida'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results-advanced">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>No se encontraron obras</h3>
                <p>No hay obras que coincidan con los criterios de búsqueda.</p>
                <div class="no-results-actions">
                    <a href="<?php echo BASE_URL; ?>/public/comics.php" class="btn btn-primary">
                        <i class="fas fa-book-open"></i> Ver todas las obras
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-outline">
                        <i class="fas fa-compass"></i> Explorar más contenido
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Paginación mejorada -->
    <?php if ($total_paginas > 1): ?>
        <div class="paginacion-advanced">
            <div class="pagination-info">
                Página <?php echo $pagina; ?> de <?php echo $total_paginas; ?>
                (<?php echo number_format($total_obras); ?> obras total)
            </div>
            
            <div class="pagination-controls">
                <?php if ($pagina > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>" 
                       class="btn btn-outline btn-sm" title="Primera página">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>" 
                       class="btn btn-outline btn-sm" title="Página anterior">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>
                
                <div class="pagination-numbers">
                    <?php
                    $inicio = max(1, $pagina - 2);
                    $fin = min($total_paginas, $pagina + 2);
                    
                    for ($i = $inicio; $i <= $fin; $i++):
                    ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>" 
                           class="pagination-number <?php echo $i == $pagina ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                
                <?php if ($pagina < $total_paginas): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>" 
                       class="btn btn-outline btn-sm" title="Página siguiente">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $total_paginas])); ?>" 
                       class="btn btn-outline btn-sm" title="Última página">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Estilos CSS avanzados -->
<style>
/* Hero Section Avanzado */
.comics-hero-advanced {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
}

.hero-particles {
    position: absolute;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="particles" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23particles)"/></svg>');
    animation: float 20s ease-in-out infinite;
}

.hero-gradient {
    position: absolute;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(0,0,0,0.1) 0%, transparent 50%, rgba(255,255,255,0.1) 100%);
}

.hero-content-advanced {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;
    color: white;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
    border: 1px solid rgba(255,255,255,0.3);
}

.hero-title {
    font-size: 4rem;
    font-weight: 800;
    margin-bottom: 20px;
    line-height: 1.1;
    background: linear-gradient(135deg, #fff 0%, #f0f0f0 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-description {
    font-size: 1.25rem;
    margin-bottom: 40px;
    opacity: 0.9;
    line-height: 1.6;
}

.hero-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.hero-stat {
    text-align: center;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    padding: 20px 15px;
    border-radius: 15px;
    border: 1px solid rgba(255,255,255,0.2);
}

.hero-stat .stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.hero-stat .stat-label {
    font-size: 0.875rem;
    opacity: 0.8;
}

.hero-actions {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.btn-outline-white {
    background: transparent;
    border: 2px solid rgba(255,255,255,0.8);
    color: white;
}

.btn-outline-white:hover {
    background: white;
    color: #667eea;
}

/* Showcase */
.hero-showcase {
    position: relative;
}

.showcase-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.showcase-item {
    animation: fadeInUp 0.8s ease-out;
    animation-delay: var(--delay);
    animation-fill-mode: both;
}

.showcase-cover {
    position: relative;
    aspect-ratio: 3/4;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    transition: transform 0.3s ease;
}

.showcase-cover:hover {
    transform: translateY(-10px) scale(1.05);
}

.showcase-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.showcase-placeholder {
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: rgba(255,255,255,0.8);
}

.showcase-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.showcase-cover:hover .showcase-overlay {
    opacity: 1;
}

.showcase-link {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
    font-size: 1.5rem;
    transition: all 0.3s ease;
}

.showcase-link:hover {
    background: white;
    transform: scale(1.1);
}

.showcase-info {
    padding: 15px 0;
    text-align: center;
}

.showcase-info h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.showcase-info p {
    font-size: 0.875rem;
    opacity: 0.8;
    margin-bottom: 8px;
}

.showcase-category {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Header simple para páginas filtradas */
.page-header-simple {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
}

.header-content h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

/* Navegación avanzada */
.comics-nav-advanced {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 60px 0 40px;
    padding: 25px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    border: 1px solid #f0f0f0;
}

.nav-sections {
    display: flex;
    gap: 25px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 25px;
    border-radius: 12px;
    text-decoration: none;
    color: #666;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
}

.nav-item:hover,
.nav-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.view-toggle {
    display: flex;
    background: #f8f9fa;
    padding: 5px;
    border-radius: 10px;
    gap: 5px;
}

.view-btn {
    padding: 10px 15px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #666;
}

.view-btn.active,
.view-btn:hover {
    background: #667eea;
    color: white;
}

/* Filtros avanzados */
.filtros-panel-advanced {
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    margin-bottom: 40px;
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.4s ease;
    border: 1px solid #f0f0f0;
}

.filtros-panel-advanced.active {
    max-height: 400px;
}

.filtros-form-advanced {
    padding: 40px;
}

.filtros-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.filtro-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filtro-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.filtro-group input,
.filtro-group select {
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
}

.filtro-group input:focus,
.filtro-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filtro-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

/* Filtros rápidos */
.quick-filters {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 15px;
    flex-wrap: wrap;
}

.quick-filters-label {
    font-weight: 600;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.quick-filters-items {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.quick-filter-item {
    padding: 8px 16px;
    background: white;
    border: 2px solid #e1e5e9;
    border-radius: 25px;
    text-decoration: none;
    color: #666;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
}

.quick-filter-item:hover,
.quick-filter-item.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
    transform: translateY(-2px);
}

/* Resultados header */
.resultados-header-advanced {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding: 25px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.resultados-info h2 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 8px;
}

.resultados-info p {
    color: #666;
    font-size: 16px;
}

.results-count {
    font-weight: 700;
    color: #667eea;
}

.resultados-actions {
    display: flex;
    align-items: center;
    gap: 20px;
}

.items-per-page {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #666;
}

.items-per-page select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

/* Grid de obras avanzado */
.obras-grid-advanced {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

.obra-card-advanced {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    transition: all 0.4s ease;
    border: 1px solid #f0f0f0;
    position: relative;
}

.obra-card-advanced:hover {
    transform: translateY(-15px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.obra-image-container {
    position: relative;
    aspect-ratio: 3/4;
    overflow: hidden;
}

.obra-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.obra-card-advanced:hover .obra-image {
    transform: scale(1.1);
}

.obra-placeholder-advanced {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 4rem;
}

.obra-overlay-advanced {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.8);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 20px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.obra-card-advanced:hover .obra-overlay-advanced {
    opacity: 1;
}

.obra-actions-advanced {
    display: flex;
    gap: 15px;
}

.action-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.action-btn.primary {
    background: #667eea;
    color: white;
}

.action-btn.secondary {
    background: rgba(255,255,255,0.9);
    color: #333;
}

.action-btn:hover {
    transform: scale(1.1);
}

.obra-rating {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.9);
    padding: 8px 15px;
    border-radius: 25px;
}

.rating-stars {
    display: flex;
    gap: 2px;
}

.rating-stars .fa-star {
    color: #ddd;
    font-size: 14px;
}

.rating-stars .fa-star.active {
    color: #ffc107;
}

.rating-value {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.obra-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-categoria {
    background: rgba(102, 126, 234, 0.9);
    color: white;
}

.badge-adulto {
    background: rgba(220, 53, 69, 0.9);
    color: white;
}

.obra-content-advanced {
    padding: 25px;
}

.obra-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 10px;
    line-height: 1.3;
}

.obra-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.obra-title a:hover {
    color: #667eea;
}

.obra-autor {
    color: #666;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
}

.obra-descripcion {
    color: #555;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 14px;
}

.obra-stats-advanced {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #666;
    font-size: 12px;
}

.stat-item i {
    color: #667eea;
    width: 14px;
}

/* Sin resultados */
.no-results-advanced {
    text-align: center;
    padding: 100px 20px;
    color: #666;
}

.no-results-icon {
    font-size: 5rem;
    color: #ddd;
    margin-bottom: 30px;
}

.no-results-advanced h3 {
    font-size: 2rem;
    margin-bottom: 15px;
    color: #333;
}

.no-results-advanced p {
    font-size: 1.125rem;
    margin-bottom: 40px;
}

.no-results-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Paginación avanzada */
.paginacion-advanced {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 60px 0;
    padding: 25px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    flex-wrap: wrap;
    gap: 20px;
}

.pagination-info {
    color: #666;
    font-size: 14px;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pagination-numbers {
    display: flex;
    gap: 5px;
}

.pagination-number {
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    color: #666;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid #e1e5e9;
}

.pagination-number:hover,
.pagination-number.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

/* Botones */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-align: center;
    justify-content: center;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 12px;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 16px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.btn-outline {
    background: transparent;
    border: 2px solid #667eea;
    color: #667eea;
}

.btn-outline:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
}

/* Animaciones */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

/* Responsive */
@media (max-width: 768px) {
    .hero-content-advanced {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .showcase-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .comics-nav-advanced {
        flex-direction: column;
        gap: 20px;
    }
    
    .nav-sections {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .filtros-grid {
        grid-template-columns: 1fr;
    }
    
    .resultados-header-advanced {
        flex-direction: column;
        gap: 20px;
        align-items: flex-start;
    }
    
    .obras-grid-advanced {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .paginacion-advanced {
        flex-direction: column;
        text-align: center;
    }
    
    .quick-filters {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-stats-grid {
        grid-template-
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-stats-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .showcase-grid {
        grid-template-columns: 1fr;
    }
    
    .nav-sections {
        flex-direction: column;
        width: 100%;
    }
    
    .nav-item {
        justify-content: center;
    }
    
    .obras-grid-advanced {
        grid-template-columns: 1fr;
    }
    
    .obra-stats-advanced {
        grid-template-columns: 1fr;
    }
    
    .no-results-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .no-results-actions .btn {
        width: 100%;
        max-width: 300px;
    }
}
</style>

<!-- Scripts mejorados -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar AOS
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 100
    });
    
    // Toggle filtros avanzados
    window.toggleAdvancedFilters = function() {
        const panel = document.getElementById('filtros-panel');
        panel.classList.toggle('active');
    };
    
    // Vista toggle mejorado
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const view = this.dataset.view;
            const container = document.getElementById('obras-grid');
            
            if (view === 'list') {
                container.classList.add('vista-lista');
            } else {
                container.classList.remove('vista-lista');
            }
        });
    });
    
    // Botones de favorito mejorados
    document.querySelectorAll('.btn-favorito').forEach(btn => {
        btn.addEventListener('click', function() {
            const obraId = this.dataset.obraId;
            const tipo = this.dataset.tipo;
            window.toggleFavorito(obraId, tipo, this);
        });
    });
    
    // Cambiar items por página
    window.changeItemsPerPage = function(value) {
        const url = new URL(window.location);
        url.searchParams.set('por_pagina', value);
        url.searchParams.delete('pagina'); // Reset a página 1
        window.location.href = url.toString();
    };
    
    // Smooth scroll para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Lazy loading mejorado para imágenes
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Tooltips para badges de categoría
    document.querySelectorAll('.badge-categoria[title]').forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            // Crear tooltip personalizado si es necesario
        });
    });
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
