<?php
/**
 * Página de Literatura Mejorada
 * Muestra todas las obras de tipo libro con diseño moderno y funcionalidades avanzadas
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
$titulo_pagina = "Literatura";
if ($mostrar_destacados) $titulo_pagina .= " - Destacados";
elseif ($mostrar_recientes) $titulo_pagina .= " - Recientes";
elseif ($mostrar_populares) $titulo_pagina .= " - Populares";

// Obtener categorías
$sql_categorias = "SELECT id, nombre FROM categorias WHERE tipo = 'libro' ORDER BY nombre";
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
    $where_conditions[] = "l.categoria_id = ?";
    $params[] = $categoria_id;
    $types .= "i";
}

if (!empty($busqueda)) {
    $where_conditions[] = "(l.titulo LIKE ? OR l.autor LIKE ? OR l.descripcion LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $types .= "sss";
}

if ($mostrar_destacados) {
    $where_conditions[] = "l.destacado = 1";
}

$where_clause = implode(" AND ", $where_conditions);

// Determinar orden
if ($mostrar_destacados) {
    $orden = "l.fecha_destacado DESC, l.fecha_subida DESC";
} elseif ($mostrar_recientes) {
    $orden = "l.fecha_subida DESC";
} elseif ($mostrar_populares) {
    $orden = "l.visitas DESC, l.favoritos DESC";
} else {
    $orden = "l.fecha_subida DESC";
}

// Consulta principal con paginación
$sql_principal = "SELECT l.id, l.titulo, l.usuario_id, l.descripcion, l.portada, l.publico_adulto, 
                  c.nombre as categoria, l.fecha_subida, l.visitas,
                  (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = l.id AND f.tipo_contenido = 'libro') as total_favoritos
                  FROM libros l
                  LEFT JOIN categorias c ON l.categoria_id = c.id
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
$sql_count = "SELECT COUNT(*) as total FROM libros l WHERE $where_clause";
$stmt_count = $conexion->prepare($sql_count);
if (!empty($params) && count($params) > 2) {
    $params_count = array_slice($params, 0, -2); // Remover LIMIT y OFFSET
    $types_count = substr($types, 0, -2);
    if (!empty($params_count)) {
        $stmt_count->bind_param($types_count, ...$params_count);
    }
}
$stmt_count->execute();
$total_obras = $stmt_count->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_obras / $por_pagina);

// Obtener obras destacadas para el hero (solo si no hay filtros)
$obras_hero = [];
if (!$mostrar_destacados && !$mostrar_recientes && !$mostrar_populares && empty($busqueda) && $categoria_id == 0) {
    $sql_hero = "SELECT l.id, l.titulo, l.usuario_id, l.portada, c.nombre as categoria
                 FROM libros l
                 LEFT JOIN categorias c ON l.categoria_id = c.id
                 WHERE l.destacado = 1
                 ORDER BY l.fecha_destacado DESC
                 LIMIT 3";
    $result_hero = $conexion->query($sql_hero);
    if ($result_hero) {
        while ($row = $result_hero->fetch_assoc()) {
            $obras_hero[] = $row;
        }
    }
}
?>

<!-- Hero Section (solo en página principal) -->
<?php if (empty($obras_hero) === false): ?>
<div class="literatura-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1><i class="fas fa-book"></i> Literatura</h1>
                <p class="hero-description">Explora novelas, poesía, ensayos y todo tipo de obras literarias</p>
                <div class="hero-stats">
                    <span><i class="fas fa-book"></i> <?php echo number_format($total_obras); ?> obras disponibles</span>
                </div>
            </div>
            
            <div class="hero-books">
                <?php foreach ($obras_hero as $index => $obra): ?>
                    <div class="hero-book" style="--delay: <?php echo $index * 0.2; ?>s">
                        <div class="book-cover">
                            <?php if (!empty($obra['portada'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" 
                                     alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
                            <?php else: ?>
                                <div class="book-placeholder">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
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
<!-- Header simple para páginas filtradas -->
<div class="page-header">
    <div class="container">
        <div class="header-content">
            <h1><i class="fas fa-book"></i> <?php echo $titulo_pagina; ?></h1>
            <p>Explora nuestra colección de literatura</p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container">
    <!-- Navegación y filtros -->
    <div class="literatura-nav">
        <div class="nav-sections">
            <a href="<?php echo BASE_URL; ?>/public/literatura.php" 
               class="nav-item <?php echo (!$mostrar_destacados && !$mostrar_recientes && !$mostrar_populares) ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i> Todas
            </a>
            <a href="<?php echo BASE_URL; ?>/public/literatura.php?destacados=1" 
               class="nav-item <?php echo $mostrar_destacados ? 'active' : ''; ?>">
                <i class="fas fa-star"></i> Destacados
            </a>
            <a href="<?php echo BASE_URL; ?>/public/literatura.php?recientes=1" 
               class="nav-item <?php echo $mostrar_recientes ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Recientes
            </a>
            <a href="<?php echo BASE_URL; ?>/public/literatura.php?populares=1" 
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
            <!-- Mantener parámetros de sección -->
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
                <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="btn btn-outline">
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
                <?php while ($libro = $result_principal->fetch_assoc()): ?>
                    <div class="obra-card" data-aos="fade-up">
                        <div class="obra-image">
                            <?php if (!empty($libro['portada'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($libro['portada']); ?>" 
                                     alt="<?php echo htmlspecialchars($libro['titulo']); ?>">
                            <?php else: ?>
                                <div class="obra-placeholder">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Overlay con acciones -->
                            <div class="obra-overlay">
                                <div class="obra-actions">
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $libro['id']; ?>&tipo=libro" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <?php if (isset($_SESSION['usuario_id'])): ?>
                                        <button class="btn btn-outline btn-sm btn-favorito" 
                                                data-obra-id="<?php echo $libro['id']; ?>" 
                                                data-tipo="libro">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="obra-content">
                            <div class="obra-header">
                                <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                                <div class="obra-badges">
                                    <?php if (!empty($libro['categoria'])): ?>
                                        <span class="badge badge-categoria">
                                            <?php echo htmlspecialchars($libro['categoria']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($libro['publico_adulto']): ?>
                                        <span class="badge badge-adulto">+18</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <p class="obra-autor">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($libro['autor']); ?>
                            </p>
                            
                            <p class="obra-descripcion">
                                <?php echo substr(htmlspecialchars($libro['descripcion']), 0, 120) . '...'; ?>
                            </p>
                            
                            <div class="obra-stats">
                                <span class="stat-item">
                                    <i class="fas fa-eye"></i>
                                    <?php echo number_format($libro['visitas']); ?>
                                </span>
                                <span class="stat-item">
                                    <i class="fas fa-heart"></i>
                                    <?php echo $libro['total_favoritos']; ?>
                                </span>
                                <span class="stat-item">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($libro['fecha_subida'])); ?>
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
                    <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="btn btn-primary">
                        <i class="fas fa-book"></i> Ver todas las obras
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

<!-- Estilos CSS -->
<style>
/* Hero Section */
.literatura-hero {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}

.literatura-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="books" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M10,10 L40,10 L40,40 L10,40 Z" fill="rgba(255,255,255,0.1)" stroke="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23books)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
    position: relative;
    z-index: 2;
}

.hero-text h1 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.hero-description {
    font-size: 20px;
    margin-bottom: 30px;
    opacity: 0.9;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    gap: 30px;
    font-size: 16px;
    font-weight: 500;
}

.hero-stats span {
    display: flex;
    align-items: center;
    gap: 8px;
}

.hero-books {
    display: flex;
    gap: 20px;
    justify-content: center;
    align-items: flex-end;
}

.hero-book {
    animation: float 3s ease-in-out infinite;
    animation-delay: var(--delay);
}

.book-cover {
    width: 120px;
    height: 160px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    margin-bottom: 15px;
    position: relative;
}

.book-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-placeholder {
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
}

.book-info {
    text-align: center;
}

.book-info h4 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    line-height: 1.3;
}

.book-info p {
    font-size: 12px;
    opacity: 0.8;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

/* Header simple */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
    text-align: center;
}

.header-content h1 {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

/* Navegación */
.literatura-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 40px 0;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.nav-sections {
    display: flex;
    gap: 20px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 10px;
    text-decoration: none;
    color: #666;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nav-item:hover,
.nav-item.active {
    background: #4361ee;
    color: white;
    transform: translateY(-2px);
}

/* Filtros */
.filtros-panel {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.3s ease;
}

.filtros-panel.active {
    max-height: 200px;
}

.filtros-form {
    padding: 30px;
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 20px;
    align-items: end;
}

.filtro-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.filtro-group input,
.filtro-group select {
    width: 100%;
    padding: 12px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.filtro-group input:focus,
.filtro-group select:focus {
    outline: none;
    border-color: #4361ee;
}

.filtro-actions {
    display: flex;
    gap: 10px;
}

/* Resultados */
.resultados-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.resultados-info h2 {
    font-size: 28px;
    color: #333;
    margin-bottom: 5px;
}

.resultados-info p {
    color: #666;
    font-size: 16px;
}

.vista-toggle {
    display: flex;
    gap: 5px;
    background: #f8f9fa;
    padding: 5px;
    border-radius: 10px;
}

.vista-btn {
    padding: 10px 15px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #666;
}

.vista-btn.active,
.vista-btn:hover {
    background: #4361ee;
    color: white;
}

/* Grid de obras */
.obras-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

.obra-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
}

.obra-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.obra-image {
    height: 200px;
    position: relative;
    overflow: hidden;
}

.obra-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.obra-card:hover .obra-image img {
    transform: scale(1.1);
}

.obra-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 60px;
}

.obra-overlay {
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

.obra-card:hover .obra-overlay {
    opacity: 1;
}

.obra-actions {
    display: flex;
    gap: 10px;
}

.obra-content {
    padding: 25px;
}

.obra-header {
    margin-bottom: 15px;
}

.obra-header h3 {
    font-size: 18px;
    color: #333;
    margin-bottom: 8px;
    font-weight: 700;
    line-height: 1.3;
}

.obra-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-categoria {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-adulto {
    background: #ffebee;
    color: #d32f2f;
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

.obra-stats {
    display: flex;
    justify-content: space-between;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
    font-size: 12px;
}

.stat-item i {
    color: #4361ee;
}

/* Botones */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
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

.btn-primary {
    background: #4361ee;
    color: white;
}

.btn-primary:hover {
    background: #3a56d4;
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    border: 2px solid #4361ee;
    color: #4361ee;
}

.btn-outline:hover {
    background: #4361ee;
    color: white;
}

/* Sin resultados */
.no-results {
    text-align: center;
    padding: 80px 20px;
    color: #666;
}

.no-results-icon {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 30px;
}

.no-results h3 {
    font-size: 24px;
    margin-bottom: 15px;
    color: #333;
}

.no-results p {
    font-size: 16px;
    margin-bottom: 30px;
}

.no-results-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

/* Paginación */
.paginacion {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin: 60px 0;
}

.paginas {
    display: flex;
    gap: 5px;
}

.pagina-btn {
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    color: #666;
    font-weight: 500;
    transition: all 0.3s ease;
}

.pagina-btn:hover,
.pagina-btn.active {
    background: #4361ee;
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .literatura-hero {
        padding: 60px 0;
    }
    
    .hero-content {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }
    
    .hero-text h1 {
        font-size: 36px;
        justify-content: center;
    }
    
    .literatura-nav {
        flex-direction: column;
        gap: 20px;
    }
    
    .nav-sections {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .filtros-form {
        grid-template-columns: 1fr;
    }
    
    .resultados-header {
        flex-direction: column;
        gap: 20px;
        align-items: flex-start;
    }
    
    .obras-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .no-results-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .paginacion {
        flex-wrap: wrap;
    }
}

@media (max-width: 480px) {
    .hero-books {
        gap: 10px;
    }
    
    .book-cover {
        width: 80px;
        height: 110px;
    }
    
    .nav-sections {
        flex-direction: column;
        width: 100%;
    }
    
    .nav-item {
        justify-content: center;
    }
    
    .obras-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- AOS Animation Library -->
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
    
    // Toggle filtros
    window.toggleFilters = function() {
        const panel = document.getElementById('filtros-panel');
        panel.classList.toggle('active');
    };
    
    // Vista toggle
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
    
    // Botones de favorito
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
