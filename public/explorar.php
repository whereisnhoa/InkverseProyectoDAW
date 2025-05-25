<?php
// Incluir configuración y archivos necesarios
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/conexion.php');

// Configurar filtros de búsqueda
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';
$filtro_categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'recientes';

// Obtener libros según filtros
$sql_libros = "SELECT l.*, c.nombre as categoria_nombre, 'libro' as tipo_obra,
               (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = l.id AND f.tipo_contenido = 'libro') as total_favoritos
               FROM libros l 
               LEFT JOIN categorias c ON l.categoria_id = c.id 
               WHERE 1=1";

$params_libros = [];
$param_types_libros = "";

// Aplicar filtros para libros
if ($filtro_tipo === 'libro' || $filtro_tipo === 'todos') {
    if ($filtro_categoria > 0) {
        $sql_libros .= " AND l.categoria_id = ?";
        $params_libros[] = $filtro_categoria;
        $param_types_libros .= "i";
    }
    
    // Filtrar contenido adulto si es necesario
    if (!isset($_SESSION['usuario_id'])) {
        $sql_libros .= " AND l.publico_adulto = 0";
    }
    
    // Aplicar orden
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

// Obtener cómics según filtros
$sql_comics = "SELECT c.*, 'comic' as tipo_obra,
               (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = c.id AND f.tipo_contenido = 'comic') as total_favoritos
               FROM comics c 
               WHERE 1=1";

$params_comics = [];
$param_types_comics = "";

// Aplicar filtros para cómics
if ($filtro_tipo === 'comic' || $filtro_tipo === 'todos') {
    // Filtrar contenido adulto si es necesario
    if (!isset($_SESSION['usuario_id'])) {
        $sql_comics .= " AND c.publico_adulto = 0";
    }
    
    // Aplicar orden
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

// Obtener categorías populares (solo de libros)
$sql_categorias = "SELECT c.id, c.nombre, c.tipo, COUNT(l.id) as total_obras,
                   (SELECT COUNT(*) FROM favoritos f JOIN libros l2 ON f.contenido_id = l2.id WHERE l2.categoria_id = c.id AND f.tipo_contenido = 'libro') as total_favoritos
                   FROM categorias c
                   LEFT JOIN libros l ON c.id = l.categoria_id
                   GROUP BY c.id
                   HAVING total_obras > 0
                   ORDER BY total_obras DESC, total_favoritos DESC
                   LIMIT 8";

$result_categorias = $conexion->query($sql_categorias);

// Obtener estadísticas generales
$sql_stats = "SELECT 
              (SELECT COUNT(*) FROM libros) + (SELECT COUNT(*) FROM comics) as total_obras,
              (SELECT COUNT(DISTINCT autor) FROM libros) + (SELECT COUNT(DISTINCT autor) FROM comics) as total_autores,
              (SELECT COUNT(*) FROM categorias WHERE id IN (SELECT DISTINCT categoria_id FROM libros)) as total_categorias";

$stats = $conexion->query($sql_stats)->fetch_assoc();

// Contar total de obras encontradas
$total_obras_encontradas = 0;
if ($result_libros) $total_obras_encontradas += $result_libros->num_rows;
if ($result_comics) $total_obras_encontradas += $result_comics->num_rows;

// Incluir header
include_once(__DIR__ . '/../includes/header.php');
?>

<div class="container">
    <div class="explorar-container">
        <!-- Hero Section -->
        <div class="explorar-hero">
            <div class="hero-content">
                <h1><i class="fas fa-compass"></i> Explorar InkVerse</h1>
                <p>Descubre nuevas obras, autores talentosos y géneros fascinantes. Tu próxima lectura favorita te está esperando.</p>
                
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="stat-number"><?php echo number_format($stats['total_obras']); ?></span>
                        <span class="stat-label">Obras</span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-number"><?php echo number_format($stats['total_autores']); ?></span>
                        <span class="stat-label">Autores</span>
                    </div>
                    <div class="hero-stat">
                        <span class="stat-number"><?php echo number_format($stats['total_categorias']); ?></span>
                        <span class="stat-label">Categorías</span>
                    </div>
                </div>
            </div>
            
            <div class="hero-actions">
                <a href="<?php echo BASE_URL; ?>/public/buscar.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Búsqueda avanzada
                </a>
                <a href="<?php echo BASE_URL; ?>/public/aleatorio.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-random"></i> Obra aleatoria
                </a>
            </div>
        </div>

        <!-- Filtros de exploración -->
        <div class="filtros-explorar">
            <form method="GET" class="filtros-form">
                <div class="filtros-principales">
                    <div class="filtro-grupo">
                        <label for="tipo">Tipo de obra:</label>
                        <select name="tipo" id="tipo">
                            <option value="todos" <?php echo $filtro_tipo === 'todos' ? 'selected' : ''; ?>>Todos los tipos</option>
                            <option value="libro" <?php echo $filtro_tipo === 'libro' ? 'selected' : ''; ?>>Libros</option>
                            <option value="comic" <?php echo $filtro_tipo === 'comic' ? 'selected' : ''; ?>>Cómics/Manga</option>
                        </select>
                    </div>

                    <div class="filtro-grupo">
                        <label for="categoria">Categoría:</label>
                        <select name="categoria" id="categoria">
                            <option value="0">Todas las categorías</option>
                            <?php 
                            $result_categorias->data_seek(0);
                            while ($cat = $result_categorias->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?> (<?php echo $cat['total_obras']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filtro-grupo">
                        <label for="orden">Ordenar por:</label>
                        <select name="orden" id="orden">
                            <option value="recientes" <?php echo $orden === 'recientes' ? 'selected' : ''; ?>>Más recientes</option>
                            <option value="populares" <?php echo $orden === 'populares' ? 'selected' : ''; ?>>Más populares</option>
                            <option value="titulo" <?php echo $orden === 'titulo' ? 'selected' : ''; ?>>Título A-Z</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Aplicar filtros
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
                        echo "Obras filtradas";
                    } else {
                        switch ($orden) {
                            case 'populares': echo "Obras más populares"; break;
                            default: echo "Obras recientes";
                        }
                    }
                    ?>
                </h2>
                <span class="resultados-count"><?php echo $total_obras_encontradas; ?> obras encontradas</span>
            </div>

            <?php if ($total_obras_encontradas > 0): ?>
                <div class="obras-grid">
                    
                    <!-- Mostrar libros -->
                    <?php if ($result_libros && $result_libros->num_rows > 0): ?>
                        <?php while ($libro = $result_libros->fetch_assoc()): ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php if (!empty($libro['portada'])): ?>
                                        <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($libro['portada']); ?>" 
                                             alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="portada-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Overlay con acciones rápidas -->
                                    <div class="obra-overlay">
                                        <div class="overlay-actions">
                                            <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $libro['id']; ?>&tipo=libro" 
                                               class="btn-overlay" title="Ver obra">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (isset($_SESSION['usuario_id'])): ?>
                                                <button class="btn-overlay btn-favorito" 
                                                        data-obra-id="<?php echo $libro['id']; ?>" 
                                                        data-tipo="libro"
                                                        title="Añadir a favoritos">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="obra-info">
                                    <div class="obra-header">
                                        <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                                        <div class="obra-badges">
                                            <span class="badge badge-tipo">Libro</span>
                                            <?php if (!empty($libro['categoria_nombre'])): ?>
                                                <span class="badge badge-categoria"><?php echo htmlspecialchars($libro['categoria_nombre']); ?></span>
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

                                    <?php if (!empty($libro['descripcion'])): ?>
                                        <p class="obra-descripcion">
                                            <?php echo substr(htmlspecialchars($libro['descripcion']), 0, 100) . '...'; ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="obra-stats">
                                        <span><i class="fas fa-heart"></i> <?php echo $libro['total_favoritos']; ?></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($libro['fecha_subida'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <!-- Mostrar cómics -->
                    <?php if ($result_comics && $result_comics->num_rows > 0): ?>
                        <?php while ($comic = $result_comics->fetch_assoc()): ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php if (!empty($comic['portada'])): ?>
                                        <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($comic['portada']); ?>" 
                                             alt="Portada de <?php echo htmlspecialchars($comic['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="portada-placeholder">
                                            <i class="fas fa-book-open"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Overlay con acciones rápidas -->
                                    <div class="obra-overlay">
                                        <div class="overlay-actions">
                                            <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $comic['id']; ?>&tipo=comic" 
                                               class="btn-overlay" title="Ver obra">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (isset($_SESSION['usuario_id'])): ?>
                                                <button class="btn-overlay btn-favorito" 
                                                        data-obra-id="<?php echo $comic['id']; ?>" 
                                                        data-tipo="comic"
                                                        title="Añadir a favoritos">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="obra-info">
                                    <div class="obra-header">
                                        <h3><?php echo htmlspecialchars($comic['titulo']); ?></h3>
                                        <div class="obra-badges">
                                            <span class="badge badge-tipo">Cómic</span>
                                            <?php if ($comic['publico_adulto']): ?>
                                                <span class="badge badge-adulto">+18</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <p class="obra-autor">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($comic['autor']); ?>
                                    </p>

                                    <?php if (!empty($comic['descripcion'])): ?>
                                        <p class="obra-descripcion">
                                            <?php echo substr(htmlspecialchars($comic['descripcion']), 0, 100) . '...'; ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="obra-stats">
                                        <span><i class="fas fa-heart"></i> <?php echo $comic['total_favoritos']; ?></span>
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
                        <i class="fas fa-plus"></i> Ver más obras
                    </a>
                </div>
            <?php else: ?>
                <div class="no-obras">
                    <div class="no-obras-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No se encontraron obras</h3>
                    <p>Intenta ajustar los filtros o explora otras categorías.</p>
                    <div class="no-obras-acciones">
                        <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-primary">
                            <i class="fas fa-refresh"></i> Limpiar filtros
                        </a>
                        <a href="<?php echo BASE_URL; ?>/public/aleatorio.php" class="btn btn-outline">
                            <i class="fas fa-random"></i> Obra aleatoria
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Categorías populares -->
        <section class="categorias-section">
            <div class="section-header">
                <h2><i class="fas fa-tags"></i> Categorías Populares</h2>
                <a href="<?php echo BASE_URL; ?>/public/categorias.php" class="btn btn-outline">Ver todas</a>
            </div>

            <div class="categorias-grid">
                <?php 
                $result_categorias->data_seek(0);
                $count = 0;
                while ($categoria = $result_categorias->fetch_assoc() && $count < 6): 
                    $count++;
                ?>
                    <div class="categoria-card">
                        <div class="categoria-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="categoria-content">
                            <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                            <p><?php echo $categoria['total_obras']; ?> obras</p>
                            <span class="categoria-tipo">Literatura</span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>" 
                           class="btn btn-sm btn-primary">Explorar</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- Sección de comunidad -->
        <section class="comunidad-section">
            <div class="section-header">
                <h2><i class="fas fa-heart"></i> Únete a la Comunidad</h2>
                <p>Conecta con otros lectores y escritores</p>
            </div>

            <div class="comunidad-grid">
                <div class="comunidad-card">
                    <div class="comunidad-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="comunidad-content">
                        <h3>Foros de Discusión</h3>
                        <p>Participa en conversaciones sobre tus obras favoritas, comparte opiniones y conecta con otros lectores apasionados.</p>
                        <a href="<?php echo BASE_URL; ?>/comunidad/foros.php" class="btn btn-primary">Visitar foros</a>
                    </div>
                </div>

                <div class="comunidad-card">
                    <div class="comunidad-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="comunidad-content">
                        <h3>Galería de Arte</h3>
                        <p>Explora ilustraciones, portadas y arte conceptual creado por nuestra talentosa comunidad de artistas.</p>
                        <a href="<?php echo BASE_URL; ?>/comunidad/arte.php" class="btn btn-primary">Ver galería</a>
                    </div>
                </div>

                <div class="comunidad-card">
                    <div class="comunidad-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="comunidad-content">
                        <h3>Grupos de Lectura</h3>
                        <p>Únete a grupos de lectura, participa en desafíos literarios y descubre nuevas obras junto a otros lectores.</p>
                        <a href="<?php echo BASE_URL; ?>/comunidad/grupos.php" class="btn btn-primary">Explorar grupos</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Estilos CSS (mismos que antes) -->
<style>
.explorar-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.explorar-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 60px 40px;
    text-align: center;
    color: white;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
}

.hero-content h1 {
    font-size: 48px;
    margin: 0 0 20px 0;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.hero-content p {
    font-size: 20px;
    margin: 0 0 40px 0;
    opacity: 0.9;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.hero-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin: 40px 0;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.hero-stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.hero-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 30px;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 16px;
    font-weight: 600;
}

.filtros-explorar {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    padding: 30px;
    margin-bottom: 40px;
}

.filtros-principales {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    align-items: end;
}

.filtro-grupo {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filtro-grupo label {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.filtro-grupo select {
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    transition: all 0.2s;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.section-header h2 {
    margin: 0;
    color: #333;
    font-size: 28px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.resultados-count {
    color: #666;
    font-size: 14px;
    background: #f8f9fa;
    padding: 6px 12px;
    border-radius: 20px;
}

.obras-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.obra-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.obra-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.obra-portada {
    height: 200px;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.obra-portada img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.portada-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.8);
    font-size: 48px;
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

.overlay-actions {
    display: flex;
    gap: 15px;
}

.btn-overlay {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 18px;
}

.btn-overlay:hover {
    background: white;
    transform: scale(1.1);
}

.btn-favorito:hover {
    background: #e74c3c;
    color: white;
}

.obra-info {
    padding: 20px;
}

.obra-header h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #333;
    line-height: 1.3;
    font-weight: 600;
}

.obra-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-tipo {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-categoria {
    background: #f3e5f5;
    color: #7b1fa2;
}

.badge-adulto {
    background: #ffebee;
    color: #d32f2f;
}

.obra-autor {
    color: #666;
    margin: 12px 0;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
}

.obra-descripcion {
    color: #555;
    line-height: 1.5;
    margin: 12px 0;
    font-size: 14px;
}

.obra-stats {
    display: flex;
    gap: 15px;
    margin: 15px 0 0 0;
    font-size: 13px;
    color: #777;
    flex-wrap: wrap;
}

.obra-stats span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.categorias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.categoria-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 25px;
    text-align: center;
    transition: all 0.3s ease;
}

.categoria-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.categoria-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4361ee, #3a56d4);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 24px;
}

.categoria-content h3 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 18px;
}

.categoria-content p {
    margin: 0 0 5px 0;
    color: #666;
    font-size: 14px;
}

.categoria-tipo {
    display: inline-block;
    background: #f0f0f0;
    color: #666;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    text-transform: uppercase;
    margin-bottom: 15px;
}

.comunidad-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.comunidad-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 30px;
    text-align: center;
    transition: all 0.3s ease;
}

.comunidad-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.comunidad-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4361ee, #3a56d4);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 28px;
}

.comunidad-content h3 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 20px;
}

.comunidad-content p {
    color: #666;
    line-height: 1.6;
    margin: 0 0 20px 0;
}

.ver-mas-container {
    text-align: center;
    margin: 40px 0;
}

.no-obras {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.no-obras-icon {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.no-obras h3 {
    color: #333;
    margin-bottom: 10px;
}

.no-obras p {
    color: #666;
    margin-bottom: 30px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.no-obras-acciones {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #4361ee;
    color: white;
}

.btn-primary:hover {
    background: #3a56d4;
    transform: translateY(-1px);
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

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

@media (max-width: 768px) {
    .explorar-hero {
        padding: 40px 20px;
    }
    
    .hero-content h1 {
        font-size: 32px;
        flex-direction: column;
        gap: 10px;
    }
    
    .hero-content p {
        font-size: 16px;
    }
    
    .hero-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .hero-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .hero-actions .btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    
    .filtros-principales {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .obras-grid {
        grid-template-columns: 1fr;
    }
    
    .categorias-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .comunidad-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- JavaScript para funcionalidad -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar botones de favoritos
    document.querySelectorAll('.btn-favorito').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const obraId = this.dataset.obraId;
            const tipo = this.dataset.tipo;
            toggleFavorito(obraId, tipo, this);
        });
    });
    
    // Función para toggle favorito
    function toggleFavorito(obraId, tipo, button) {
        fetch('<?php echo BASE_URL; ?>/controllers/procesar-favoritos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `accion=toggle&obra_id=${obraId}&tipo=${tipo}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.agregado) {
                    button.style.background = '#e74c3c';
                    button.style.color = 'white';
                    mostrarMensaje('Obra añadida a favoritos', 'success');
                } else {
                    button.style.background = 'rgba(255,255,255,0.9)';
                    button.style.color = '#333';
                    mostrarMensaje('Obra quitada de favoritos', 'info');
                }
            } else {
                mostrarMensaje(data.mensaje || 'Error al procesar favorito', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error de conexión', 'error');
        });
    }
    
    // Función para mostrar mensajes
    function mostrarMensaje(mensaje, tipo) {
        const alerta = document.createElement('div');
        alerta.className = `alerta alerta-${tipo}`;
        alerta.textContent = mensaje;
        alerta.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 12px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        `;
        
        switch(tipo) {
            case 'success':
                alerta.style.background = '#27ae60';
                break;
            case 'error':
                alerta.style.background = '#e74c3c';
                break;
            case 'info':
                alerta.style.background = '#3498db';
                break;
        }
        
        document.body.appendChild(alerta);
        
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
});

// Animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(style);
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>