<?php
/**
 * Página de Cómics Mejorada
 * Muestra todas las obras de tipo cómic/manga con diseño moderno y funcionalidades avanzadas
 */

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Parámetros de filtrado
$mostrar_destacados = isset($_GET['destacados']) && $_GET['destacados'] == 1;
$mostrar_recientes = isset($_GET['recientes']) && $_GET['recientes'] == 1;
$mostrar_populares = isset($_GET['populares']) && $_GET['populares'] == 1;
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Paginación
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina = 12;
$offset = ($pagina - 1) * $por_pagina;

// Título de la página
$titulo_pagina = "Cómics y Manga";
if ($mostrar_destacados) $titulo_pagina .= " - Destacados";
elseif ($mostrar_recientes) $titulo_pagina .= " - Recientes";
elseif ($mostrar_populares) $titulo_pagina .= " - Populares";

// Obtener categorías
$sql_categorias = "SELECT id, nombre FROM categorias WHERE tipo = 'comic' ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = [];
if ($result_categorias) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Construir consulta principal
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

$where_clause = implode(" AND ", $where_conditions);

// Determinar orden
if ($mostrar_destacados) {
    $orden = "c.fecha_destacado DESC, c.fecha_subida DESC";
} elseif ($mostrar_recientes) {
    $orden = "c.fecha_subida DESC";
} elseif ($mostrar_populares) {
    $orden = "c.visitas DESC, c.favoritos DESC";
} else {
    $orden = "c.fecha_subida DESC";
}

// Consulta principal con paginación
$sql_principal = "SELECT c.id, c.titulo, c.autor, c.descripcion, c.portada, c.publico_adulto, 
                  cat.nombre as categoria, c.fecha_subida, c.visitas,
                  (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = c.id AND f.tipo_contenido = 'comic') as total_favoritos
                  FROM comics c
                  LEFT JOIN categorias cat ON c.categoria_id = cat.id
                  WHERE $where_clause
                  ORDER BY $orden
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
$sql_count = "SELECT COUNT(*) as total FROM comics c WHERE $where_clause";
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
                 LIMIT 3";
    $result_hero = $conexion->query($sql_hero);
    if ($result_hero) {
        while ($row = $result_hero->fetch_assoc()) {
            $obras_hero[] = $row;
        }
    }
}
?>

<!-- Hero Section -->
<?php if (empty($obras_hero) === false): ?>
<div class="comics-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1><i class="fas fa-book-open"></i> Cómics y Manga</h1>
                <p class="hero-description">Sumérgete en el mundo visual de cómics, manga y novelas gráficas</p>
                <div class="hero-stats">
                    <span><i class="fas fa-book-open"></i> <?php echo number_format($total_obras); ?> obras disponibles</span>
                </div>
            </div>
            
            <div class="hero-comics">
                <?php foreach ($obras_hero as $index => $obra): ?>
                    <div class="hero-comic" style="--delay: <?php echo $index * 0.2; ?>s">
                        <div class="comic-cover">
                            <?php if (!empty($obra['portada'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" 
                                     alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
                            <?php else: ?>
                                <div class="comic-placeholder">
                                    <i class="fas fa-book-open"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="comic-info">
                            <h4><?php echo htmlspecialchars($obra['titulo']); ?></h4>
                            <p><?php echo htmlspecialchars($obra['autor']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="page-header">
    <div class="container">
        <div class="header-content">
            <h1><i class="fas fa-book-open"></i> <?php echo $titulo_pagina; ?></h1>
            <p>Explora nuestra colección de cómics y manga</p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container">
    <!-- Navegación y filtros -->
    <div class="comics-nav">
        <div class="nav-sections">
            <a href="<?php echo BASE_URL; ?>/public/comics.php" 
               class="nav-item <?php echo (!$mostrar_destacados && !$mostrar_recientes && !$mostrar_populares) ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i> Todos
            </a>
            <a href="<?php echo BASE_URL; ?>/public/comics.php?destacados=1" 
               class="nav-item <?php echo $mostrar_destacados ? 'active' : ''; ?>">
                <i class="fas fa-star"></i> Destacados
            </a>
            <a href="<?php echo BASE_URL; ?>/public/comics.php?recientes=1" 
               class="nav-item <?php echo $mostrar_recientes ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Recientes
            </a>
            <a href="<?php echo BASE_URL; ?>/public/comics.php?populares=1" 
               class="nav-item <?php echo $mostrar_populares ? 'active' : ''; ?>">
                <i class="fas fa-fire"></i> Populares
            </a>
        </div>
        
        <div class="nav-actions">
            <button class="btn btn-outline" onclick="toggleFilters()">
                <i class="fas fa-filter"></i> Filtros
            </button>
        </div>
    </div>

    <!-- Panel de filtros -->
    <div class="filtros-panel" id="filtros-panel">
        <form method="GET" class="filtros-form">
            <?php if ($mostrar_destacados): ?>
                <input type="hidden" name="destacados" value="1">
            <?php elseif ($mostrar_recientes): ?>
                <input type="hidden" name="recientes" value="1">
            <?php elseif ($mostrar_populares): ?>
                <input type="hidden" name="populares" value="1">
            <?php endif; ?>
            
            <div class="filtro-group">
                <label for="buscar">Buscar:</label>
                <input type="text" id="buscar" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>" 
                       placeholder="Título, autor o descripción...">
            </div>
            
            <div class="filtro-group">
                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria">
                    <option value="0">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo $categoria_id == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filtro-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="<?php echo BASE_URL; ?>/public/comics.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Resultados -->
    <div class="resultados-header">
        <div class="resultados-info">
            <h2>
                <?php 
                if ($mostrar_destacados) echo "Obras Destacadas";
                elseif ($mostrar_recientes) echo "Obras Recientes";
                elseif ($mostrar_populares) echo "Obras Populares";
                else echo "Todas las Obras";
                ?>
            </h2>
            <p><?php echo number_format($total_obras); ?> obras encontradas</p>
        </div>
        
        <div class="vista-toggle">
            <button class="vista-btn active" data-vista="grid" title="Vista en cuadrícula">
                <i class="fas fa-th"></i>
            </button>
            <button class="vista-btn" data-vista="list" title="Vista en lista">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>

    <!-- Grid de obras -->
    <div class="obras-container" id="obras-container">
        <?php if ($result_principal && $result_principal->num_rows > 0): ?>
            <div class="obras-grid" id="obras-grid">
                <?php while ($comic = $result_principal->fetch_assoc()): ?>
                    <div class="obra-card" data-aos="fade-up">
                        <div class="obra-image">
                            <?php if (!empty($comic['portada'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($comic['portada']); ?>" 
                                     alt="<?php echo htmlspecialchars($comic['titulo']); ?>">
                            <?php else: ?>
                                <div class="obra-placeholder">
                                    <i class="fas fa-book-open"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="obra-overlay">
                                <div class="obra-actions">
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $comic['id']; ?>&tipo=comic" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <?php if (isset($_SESSION['usuario_id'])): ?>
                                        <button class="btn btn-outline btn-sm btn-favorito" 
                                                data-obra-id="<?php echo $comic['id']; ?>" 
                                                data-tipo="comic">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="obra-content">
                            <div class="obra-header">
                                <h3><?php echo htmlspecialchars($comic['titulo']); ?></h3>
                                <div class="obra-badges">
                                    <?php if (!empty($comic['categoria'])): ?>
                                        <span class="badge badge-categoria">
                                            <?php echo htmlspecialchars($comic['categoria']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($comic['publico_adulto']): ?>
                                        <span class="badge badge-adulto">+18</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <p class="obra-autor">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($comic['autor']); ?>
                            </p>
                            
                            <p class="obra-descripcion">
                                <?php echo substr(htmlspecialchars($comic['descripcion']), 0, 120) . '...'; ?>
                            </p>
                            
                            <div class="obra-stats">
                                <span class="stat-item">
                                    <i class="fas fa-eye"></i>
                                    <?php echo number_format($comic['visitas']); ?>
                                </span>
                                <span class="stat-item">
                                    <i class="fas fa-heart"></i>
                                    <?php echo $comic['total_favoritos']; ?>
                                </span>
                                <span class="stat-item">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($comic['fecha_subida'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
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

    <!-- Paginación -->
    <?php if ($total_paginas > 1): ?>
        <div class="paginacion">
            <?php if ($pagina > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>" 
                   class="btn btn-outline">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
            <?php endif; ?>
            
            <div class="paginas">
                <?php
                $inicio = max(1, $pagina - 2);
                $fin = min($total_paginas, $pagina + 2);
                
                for ($i = $inicio; $i <= $fin; $i++):
                ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>" 
                       class="pagina-btn <?php echo $i == $pagina ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            
            <?php if ($pagina < $total_paginas): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>" 
                   class="btn btn-outline">
                    Siguiente <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Estilos CSS específicos para cómics -->
<style>
.comics-hero {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}

.comics-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="comics" width="40" height="40" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><path d="M10,10 L30,10 L30,30 L10,30 Z" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23comics)"/></svg>');
    opacity: 0.3;
}

.hero-comics {
    display: flex;
    gap: 20px;
    justify-content: center;
    align-items: flex-end;
}

.hero-comic {
    animation: bounce 2s ease-in-out infinite;
    animation-delay: var(--delay);
}

.comic-cover {
    width: 120px;
    height: 160px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    margin-bottom: 15px;
    position: relative;
    transform: perspective(1000px) rotateY(-15deg);
}

.comic-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.comic-placeholder {
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
}

.comic-info {
    text-align: center;
}

.comic-info h4 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    line-height: 1.3;
}

.comic-info p {
    font-size: 12px;
    opacity: 0.8;
}

@keyframes bounce {
    0%, 100% { transform: perspective(1000px) rotateY(-15deg) translateY(0px); }
    50% { transform: perspective(1000px) rotateY(-15deg) translateY(-10px); }
}

.comics-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 40px 0;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.obra-placeholder {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

/* Resto de estilos heredados de literatura con ajustes de color */
.nav-item:hover,
.nav-item.active {
    background: #fa709a;
    color: white;
    transform: translateY(-2px);
}

.btn-primary {
    background: #fa709a;
    color: white;
}

.btn-primary:hover {
    background: #f85a8a;
    transform: translateY(-2px);
}

.btn-outline {
    border-color: #fa709a;
    color: #fa709a;
}

.btn-outline:hover {
    background: #fa709a;
    color: white;
}

.stat-item i {
    color: #fa709a;
}

.vista-btn.active,
.vista-btn:hover {
    background: #fa709a;
    color: white;
}

.pagina-btn:hover,
.pagina-btn.active {
    background: #fa709a;
    color: white;
}

/* Responsive específico para cómics */
@media (max-width: 768px) {
    .hero-comics {
        gap: 10px;
    }
    
    .comic-cover {
        width: 80px;
        height: 110px;
        transform: perspective(1000px) rotateY(-10deg);
    }
}
</style>

<!-- Scripts heredados -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 100
    });
    
    window.toggleFilters = function() {
        const panel = document.getElementById('filtros-panel');
        panel.classList.toggle('active');
    };
    
    document.querySelectorAll('.vista-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.vista-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const vista = this.dataset.vista;
            const container = document.getElementById('obras-grid');
            
            if (vista === 'list') {
                container.classList.add('vista-lista');
            } else {
                container.classList.remove('vista-lista');
            }
        });
    });
    
    document.querySelectorAll('.btn-favorito').forEach(btn => {
        btn.addEventListener('click', function() {
            const obraId = this.dataset.obraId;
            const tipo = this.dataset.tipo;
            window.toggleFavorito(obraId, tipo, this);
        });
    });
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
