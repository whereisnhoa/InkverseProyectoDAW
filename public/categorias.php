<?php
/**
 * Página de Categorías Ultra Mejorada
 * Diseño moderno con mejor organización y funcionalidades avanzadas
 */

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Obtener categorías de libros con conteo de obras y estadísticas
$sql_categorias_libros = "SELECT c.id, c.nombre, c.descripcion, 
                          COUNT(l.id) as total_obras,
                          AVG(l.valoracion) as valoracion_promedio,
                          MAX(l.fecha_subida) as ultima_obra,
                          (SELECT l2.portada FROM libros l2 WHERE l2.categoria_id = c.id AND l2.portada IS NOT NULL ORDER BY l2.fecha_subida DESC LIMIT 1) as portada_ejemplo
                          FROM categorias c 
                          LEFT JOIN libros l ON c.id = l.categoria_id 
                          WHERE c.tipo = 'libro' 
                          GROUP BY c.id, c.nombre, c.descripcion 
                          ORDER BY total_obras DESC, c.nombre";
$result_categorias_libros = $conexion->query($sql_categorias_libros);

// Obtener categorías de cómics organizadas por tipo
$sql_categorias_comics = "SELECT c.id, c.nombre, c.descripcion, 
                          COUNT(co.id) as total_obras,
                          AVG(co.valoracion) as valoracion_promedio,
                          MAX(co.fecha_subida) as ultima_obra,
                          (SELECT co2.portada FROM comics co2 WHERE co2.categoria_id = c.id AND co2.portada IS NOT NULL ORDER BY co2.fecha_subida DESC LIMIT 1) as portada_ejemplo,
                          CASE 
                              WHEN c.nombre IN ('Cómic', 'Manga', 'Manhwa', 'Manhua') THEN 'tipo'
                              WHEN c.nombre IN ('Shonen', 'Shojo', 'Seinen', 'Josei') THEN 'demografia'
                              ELSE 'genero'
                          END as categoria_tipo
                          FROM categorias c 
                          LEFT JOIN comics co ON c.id = co.categoria_id 
                          WHERE c.tipo = 'comic' 
                          GROUP BY c.id, c.nombre, c.descripcion 
                          ORDER BY categoria_tipo, total_obras DESC, c.nombre";
$result_categorias_comics = $conexion->query($sql_categorias_comics);

// Organizar categorías de cómics por tipo
$comics_por_tipo = [
    'tipo' => [],
    'demografia' => [],
    'genero' => []
];

if ($result_categorias_comics) {
    while ($row = $result_categorias_comics->fetch_assoc()) {
        $comics_por_tipo[$row['categoria_tipo']][] = $row;
    }
}

// Obtener estadísticas generales
$sql_stats = "SELECT 
              (SELECT COUNT(*) FROM libros) as total_libros,
              (SELECT COUNT(*) FROM comics) as total_comics,
              (SELECT COUNT(*) FROM categorias WHERE tipo = 'libro') as total_cat_libros,
              (SELECT COUNT(*) FROM categorias WHERE tipo = 'comic') as total_cat_comics,
              (SELECT COUNT(*) FROM usuarios) as total_usuarios,
              (SELECT COUNT(*) FROM favoritos) as total_favoritos";
$result_stats = $conexion->query($sql_stats);
$stats = $result_stats->fetch_assoc();

// Obtener categorías más populares
$sql_populares = "SELECT c.nombre, c.tipo, COUNT(f.id) as total_favoritos
                  FROM categorias c
                  LEFT JOIN libros l ON c.id = l.categoria_id AND c.tipo = 'libro'
                  LEFT JOIN comics co ON c.id = co.categoria_id AND c.tipo = 'comic'
                  LEFT JOIN favoritos f ON (f.contenido_id = l.id AND f.tipo_contenido = 'libro') OR (f.contenido_id = co.id AND f.tipo_contenido = 'comic')
                  GROUP BY c.id, c.nombre, c.tipo
                  HAVING total_favoritos > 0
                  ORDER BY total_favoritos DESC
                  LIMIT 6";
$result_populares = $conexion->query($sql_populares);
$categorias_populares = [];
if ($result_populares) {
    while ($row = $result_populares->fetch_assoc()) {
        $categorias_populares[] = $row;
    }
}
?>

<!-- Hero Section Mejorado -->
<div class="categorias-hero-advanced">
    <div class="hero-background">
        <div class="hero-pattern"></div>
        <div class="hero-gradient"></div>
    </div>
    
    <div class="container">
        <div class="hero-content-grid">
            <div class="hero-text-content">
                <div class="hero-badge">
                    <i class="fas fa-layer-group"></i>
                    <span>Organizado por Géneros</span>
                </div>
                <h1 class="hero-title">Explorar por Categorías</h1>
                <p class="hero-description">Descubre contenido perfectamente organizado por géneros, temáticas y estilos que se adaptan a tus gustos</p>
                
                <!-- Estadísticas mejoradas -->
                <div class="stats-grid-advanced">
                    <div class="stat-card-advanced">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['total_libros']); ?></div>
                            <div class="stat-label">Libros</div>
                        </div>
                    </div>
                    <div class="stat-card-advanced">
                        <div class="stat-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['total_comics']); ?></div>
                            <div class="stat-label">Cómics</div>
                        </div>
                    </div>
                    <div class="stat-card-advanced">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['total_cat_libros'] + $stats['total_cat_comics']; ?></div>
                            <div class="stat-label">Categorías</div>
                        </div>
                    </div>
                    <div class="stat-card-advanced">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['total_usuarios']); ?></div>
                            <div class="stat-label">Usuarios</div>
                        </div>
                    </div>
                </div>
                
                <div class="hero-actions">
                    <a href="#literatura" class="btn btn-primary btn-lg">
                        <i class="fas fa-book"></i> Explorar Literatura
                    </a>
                    <a href="#comics" class="btn btn-outline-white btn-lg">
                        <i class="fas fa-book-open"></i> Ver Cómics
                    </a>
                </div>
            </div>
            
            <div class="hero-visual">
                <div class="floating-categories">
                    <?php if (!empty($categorias_populares)): ?>
                        <?php foreach (array_slice($categorias_populares, 0, 4) as $index => $cat): ?>
                            <div class="floating-cat" style="--delay: <?php echo $index * 0.2; ?>s">
                                <div class="cat-icon">
                                    <i class="fas <?php echo $cat['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                                </div>
                                <span><?php echo htmlspecialchars($cat['nombre']); ?></span>
                                <div class="cat-count"><?php echo $cat['total_favoritos']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Navegación rápida mejorada -->
    <div class="quick-nav-advanced">
        <a href="#literatura" class="quick-nav-item-advanced literatura">
            <div class="nav-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="nav-content">
                <h3>Literatura</h3>
                <p><?php echo $stats['total_cat_libros']; ?> categorías</p>
            </div>
        </a>
        <a href="#comics" class="quick-nav-item-advanced comics">
            <div class="nav-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="nav-content">
                <h3>Cómics & Manga</h3>
                <p><?php echo $stats['total_cat_comics']; ?> categorías</p>
            </div>
        </a>
        <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="quick-nav-item-advanced explorar">
            <div class="nav-icon">
                <i class="fas fa-compass"></i>
            </div>
            <div class="nav-content">
                <h3>Explorar Todo</h3>
                <p>Descubrir obras</p>
            </div>
        </a>
        <a href="<?php echo BASE_URL; ?>/public/aleatorio.php" class="quick-nav-item-advanced aleatorio">
            <div class="nav-icon">
                <i class="fas fa-random"></i>
            </div>
            <div class="nav-content">
                <h3>Sorpréndeme</h3>
                <p>Obra aleatoria</p>
            </div>
        </a>
    </div>

    <!-- Categorías más populares -->
    <?php if (!empty($categorias_populares)): ?>
    <section class="populares-section">
        <div class="section-header-advanced">
            <div class="section-title">
                <h2><i class="fas fa-fire"></i> Categorías Más Populares</h2>
                <p>Las categorías con más obras favoritas de la comunidad</p>
            </div>
        </div>
        
        <div class="populares-grid">
            <?php foreach ($categorias_populares as $cat): ?>
                <div class="popular-item" data-aos="fade-up">
                    <div class="popular-icon">
                        <i class="fas <?php echo $cat['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                    </div>
                    <h4><?php echo htmlspecialchars($cat['nombre']); ?></h4>
                    <p><?php echo $cat['total_favoritos']; ?> favoritos</p>
                    <span class="popular-type"><?php echo $cat['tipo'] == 'libro' ? 'Literatura' : 'Cómic'; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Categorías de Literatura -->
    <section id="literatura" class="categorias-section-advanced">
        <div class="section-header-advanced">
            <div class="section-title">
                <h2><i class="fas fa-book"></i> Literatura</h2>
                <p>Explora géneros literarios desde poesía hasta ciencia ficción</p>
            </div>
            <div class="section-actions">
                <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Ver todas las obras
                </a>
            </div>
        </div>
        
        <div class="categorias-grid-advanced">
            <?php if ($result_categorias_libros && $result_categorias_libros->num_rows > 0): ?>
                <?php 
                $result_categorias_libros->data_seek(0); // Reset pointer
                while ($categoria = $result_categorias_libros->fetch_assoc()): 
                ?>
                    <div class="categoria-card-advanced literatura-card" data-aos="fade-up">
                        <div class="categoria-image-container">
                            <?php if (!empty($categoria['portada_ejemplo'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($categoria['portada_ejemplo']); ?>" 
                                     alt="<?php echo htmlspecialchars($categoria['nombre']); ?>"
                                     class="categoria-image">
                            <?php else: ?>
                                <div class="categoria-placeholder literatura">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                            <div class="categoria-overlay">
                                <div class="categoria-stats-overlay">
                                    <span class="obras-count"><?php echo $categoria['total_obras']; ?> obras</span>
                                    <?php if ($categoria['valoracion_promedio'] > 0): ?>
                                        <div class="rating-overlay">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo number_format($categoria['valoracion_promedio'], 1); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="categoria-content-advanced">
                            <div class="categoria-header">
                                <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                                <?php if ($categoria['ultima_obra']): ?>
                                    <span class="ultima-actualizacion">
                                        Actualizado <?php echo date('d/m/Y', strtotime($categoria['ultima_obra'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="categoria-description">
                                <?php echo !empty($categoria['descripcion']) ? 
                                    htmlspecialchars($categoria['descripcion']) : 
                                    'Explora obras de este fascinante género literario'; ?>
                            </p>
                            
                            <div class="categoria-stats-grid">
                                <div class="stat-mini">
                                    <i class="fas fa-book"></i>
                                    <span><?php echo $categoria['total_obras']; ?> obras</span>
                                </div>
                                <?php if ($categoria['valoracion_promedio'] > 0): ?>
                                <div class="stat-mini">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo number_format($categoria['valoracion_promedio'], 1); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="categoria-actions-advanced">
                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=libro" 
                               class="btn btn-outline btn-block">
                                <i class="fas fa-eye"></i> Explorar Categoría
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-categories-advanced">
                    <div class="no-cat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>No hay categorías de literatura</h3>
                    <p>Aún no se han creado categorías para libros.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Categorías de Cómics -->
    <section id="comics" class="categorias-section-advanced">
        <div class="section-header-advanced">
            <div class="section-title">
                <h2><i class="fas fa-book-open"></i> Cómics y Manga</h2>
                <p>Sumérgete en historias visuales de todo el mundo</p>
            </div>
            <div class="section-actions">
                <a href="<?php echo BASE_URL; ?>/public/comics.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Ver todas las obras
                </a>
            </div>
        </div>
        
        <!-- Tipos principales de cómics -->
        <?php if (!empty($comics_por_tipo['tipo'])): ?>
        <div class="subsection">
            <h3 class="subsection-title">
                <i class="fas fa-bookmark"></i> Tipos de Cómics
            </h3>
            <div class="tipos-grid">
                <?php foreach ($comics_por_tipo['tipo'] as $tipo): ?>
                    <div class="tipo-card" data-aos="fade-up">
                        <div class="tipo-icon">
                            <?php
                            $icon = 'fa-book-open';
                            switch($tipo['nombre']) {
                                case 'Manga': $icon = 'fa-torii-gate'; break;
                                case 'Manhwa': $icon = 'fa-flag'; break;
                                case 'Manhua': $icon = 'fa-dragon'; break;
                            }
                            ?>
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($tipo['nombre']); ?></h4>
                        <p><?php echo htmlspecialchars($tipo['descripcion']); ?></p>
                        <div class="tipo-stats">
                            <span><?php echo $tipo['total_obras']; ?> obras</span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $tipo['id']; ?>&tipo=comic" 
                           class="btn btn-sm btn-outline">Explorar</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Demografías -->
        <?php if (!empty($comics_por_tipo['demografia'])): ?>
        <div class="subsection">
            <h3 class="subsection-title">
                <i class="fas fa-users"></i> Por Demografía
            </h3>
            <div class="demografia-grid">
                <?php foreach ($comics_por_tipo['demografia'] as $demo): ?>
                    <div class="demografia-card" data-aos="fade-up">
                        <div class="demografia-header">
                            <h4><?php echo htmlspecialchars($demo['nombre']); ?></h4>
                            <span class="obras-badge"><?php echo $demo['total_obras']; ?></span>
                        </div>
                        <p><?php echo htmlspecialchars($demo['descripcion']); ?></p>
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $demo['id']; ?>&tipo=comic" 
                           class="demografia-link">
                            <i class="fas fa-arrow-right"></i> Ver obras
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Géneros -->
        <?php if (!empty($comics_por_tipo['genero'])): ?>
        <div class="subsection">
            <h3 class="subsection-title">
                <i class="fas fa-tags"></i> Géneros y Temáticas
            </h3>
            <div class="categorias-grid-advanced">
                <?php foreach ($comics_por_tipo['genero'] as $categoria): ?>
                    <div class="categoria-card-advanced comics-card" data-aos="fade-up">
                        <div class="categoria-image-container">
                            <?php if (!empty($categoria['portada_ejemplo'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($categoria['portada_ejemplo']); ?>" 
                                     alt="<?php echo htmlspecialchars($categoria['nombre']); ?>"
                                     class="categoria-image">
                            <?php else: ?>
                                <div class="categoria-placeholder comics">
                                    <i class="fas fa-book-open"></i>
                                </div>
                            <?php endif; ?>
                            <div class="categoria-overlay">
                                <div class="categoria-stats-overlay">
                                    <span class="obras-count"><?php echo $categoria['total_obras']; ?> obras</span>
                                    <?php if ($categoria['valoracion_promedio'] > 0): ?>
                                        <div class="rating-overlay">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo number_format($categoria['valoracion_promedio'], 1); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="categoria-content-advanced">
                            <div class="categoria-header">
                                <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                                <?php if ($categoria['ultima_obra']): ?>
                                    <span class="ultima-actualizacion">
                                        Actualizado <?php echo date('d/m/Y', strtotime($categoria['ultima_obra'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="categoria-description">
                                <?php echo !empty($categoria['descripcion']) ? 
                                    htmlspecialchars($categoria['descripcion']) : 
                                    'Explora obras de este género visual'; ?>
                            </p>
                            
                            <div class="categoria-stats-grid">
                                <div class="stat-mini">
                                    <i class="fas fa-book-open"></i>
                                    <span><?php echo $categoria['total_obras']; ?> obras</span>
                                </div>
                                <?php if ($categoria['valoracion_promedio'] > 0): ?>
                                <div class="stat-mini">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo number_format($categoria['valoracion_promedio'], 1); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="categoria-actions-advanced">
                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=comic" 
                               class="btn btn-outline btn-block">
                                <i class="fas fa-eye"></i> Explorar Categoría
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <!-- Sección de acciones adicionales -->
    <section class="additional-actions-advanced">
        <div class="actions-container">
            <div class="action-card-advanced" data-aos="fade-up">
                <div class="action-icon-advanced">
                    <i class="fas fa-search"></i>
                </div>
                <div class="action-content">
                    <h3>Búsqueda Avanzada</h3>
                    <p>Encuentra exactamente lo que buscas con filtros detallados por género, autor, fecha y más</p>
                    <a href="<?php echo BASE_URL; ?>/public/buscar.php" class="btn btn-outline">
                        <i class="fas fa-search"></i> Buscar Obras
                    </a>
                </div>
            </div>
            
            <div class="action-card-advanced" data-aos="fade-up" data-aos-delay="100">
                <div class="action-icon-advanced">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="action-content">
                    <h3>Mis Favoritos</h3>
                    <p>Accede rápidamente a todas las obras que has guardado como favoritas</p>
                    <a href="<?php echo BASE_URL; ?>/public/favoritos.php" class="btn btn-outline">
                        <i class="fas fa-heart"></i> Ver Favoritos
                    </a>
                </div>
            </div>
            
            <div class="action-card-advanced" data-aos="fade-up" data-aos-delay="200">
                <div class="action-icon-advanced">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="action-content">
                    <h3>Publicar Obra</h3>
                    <p>Comparte tu contenido con la comunidad y llega a miles de lectores</p>
                    <a href="<?php echo BASE_URL; ?>/public/nueva_obra.php" class="btn btn-outline">
                        <i class="fas fa-plus"></i> Subir Obra
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Estilos CSS avanzados -->
<style>
/* Hero Section Avanzado */
.categorias-hero-advanced {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    color: white;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1;
}

.hero-pattern {
    position: absolute;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="categories" width="50" height="50" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="2" fill="rgba(255,255,255,0.1)"/><rect x="10" y="10" width="30" height="30" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23categories)"/></svg>');
    animation: drift 30s ease-in-out infinite;
}

.hero-gradient {
    position: absolute;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 30% 70%, rgba(255,255,255,0.1) 0%, transparent 50%);
}

.hero-content-grid {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(15px);
    padding: 10px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 25px;
    border: 1px solid rgba(255,255,255,0.3);
}

.hero-title {
    font-size: 4rem;
    font-weight: 800;
    margin-bottom: 25px;
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
    max-width: 500px;
}

.stats-grid-advanced {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card-advanced {
    display: flex;
    align-items: center;
    gap: 15px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    padding: 20px;
    border-radius: 15px;
    border: 1px solid rgba(255,255,255,0.2);
    transition: transform 0.3s ease;
}

.stat-card-advanced:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.stat-content .stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 2px;
}

.stat-content .stat-label {
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

/* Visual flotante */
.hero-visual {
    position: relative;
    height: 400px;
}

.floating-categories {
    position: relative;
    width: 100%;
    height: 100%;
}

.floating-cat {
    position: absolute;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(15px);
    padding: 20px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    animation: float 6s ease-in-out infinite;
    animation-delay: var(--delay);
}

.floating-cat:nth-child(1) { top: 10%; left: 20%; }
.floating-cat:nth-child(2) { top: 30%; right: 10%; }
.floating-cat:nth-child(3) { bottom: 30%; left: 10%; }
.floating-cat:nth-child(4) { bottom: 10%; right: 30%; }

.floating-cat .cat-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 24px;
}

.floating-cat span {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}

.floating-cat .cat-count {
    font-size: 12px;
    opacity: 0.8;
}

/* Navegación rápida avanzada */
.quick-nav-advanced {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin: 60px 0;
}

.quick-nav-item-advanced {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 30px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
    position: relative;
    overflow: hidden;
}

.quick-nav-item-advanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.quick-nav-item-advanced:hover::before {
    transform: scaleX(1);
}

.quick-nav-item-advanced:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.nav-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
    flex-shrink: 0;
}

.literatura .nav-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.comics .nav-icon {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.explorar .nav-icon {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}

.aleatorio .nav-icon {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
}

.nav-content h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.nav-content p {
    color: #666;
    font-size: 14px;
    margin: 0;
}

/* Sección de populares */
.populares-section {
    margin: 80px 0;
    padding: 60px 0;
    background: #f8f9fa;
    border-radius: 30px;
}

.section-header-advanced {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 50px;
    padding: 0 30px;
}

.section-title h2 {
    font-size: 2.5rem;
    color: #333;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.section-title h2 i {
    color: #667eea;
}

.section-title p {
    color: #666;
    margin: 0;
    font-size: 16px;
}

.populares-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 25px;
    padding: 0 30px;
}

.popular-item {
    background: white;
    padding: 30px 20px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    position: relative;
}

.popular-item:hover {
    transform: translateY(-10px);
}

.popular-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 24px;
}

.popular-item h4 {
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 8px;
    color: #333;
}

.popular-item p {
    color: #667eea;
    font-weight: 600;
    margin-bottom: 10px;
}

.popular-type {
    background: #f0f0f0;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    color: #666;
    font-weight: 500;
}

/* Secciones de categorías */
.categorias-section-advanced {
    margin: 80px 0;
}

.subsection {
    margin: 60px 0;
}

.subsection-title {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.subsection-title i {
    color: #667eea;
}

/* Grid de categorías avanzado */
.categorias-grid-advanced {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
}

.categoria-card-advanced {
    background: white;
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    transition: all 0.4s ease;
    border: 1px solid #f0f0f0;
    position: relative;
}

.categoria-card-advanced:hover {
    transform: translateY(-15px);
    box-shadow: 0 25px 60px rgba(0,0,0,0.15);
}

.categoria-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.categoria-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.categoria-card-advanced:hover .categoria-image {
    transform: scale(1.1);
}

.categoria-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 4rem;
}

.categoria-placeholder.literatura {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.categoria-placeholder.comics {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.categoria-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.8) 100%);
    display: flex;
    align-items: flex-end;
    padding: 20px;
}

.categoria-stats-overlay {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.obras-count {
    background: rgba(255,255,255,0.9);
    color: #333;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.rating-overlay {
    display: flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,193,7,0.9);
    color: white;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 14px;
    font-weight: 600;
}

.categoria-content-advanced {
    padding: 30px;
}

.categoria-header {
    margin-bottom: 15px;
}

.categoria-header h3 {
    font-size: 1.375rem;
    color: #333;
    margin: 0 0 8px 0;
    font-weight: 700;
}

.ultima-actualizacion {
    font-size: 12px;
    color: #999;
    font-weight: 500;
}

.categoria-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 15px;
}

.categoria-stats-grid {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
}

.stat-mini {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #666;
    font-size: 14px;
    font-weight: 500;
}

.stat-mini i {
    color: #667eea;
    width: 16px;
}

.categoria-actions-advanced {
    padding: 0 30px 30px;
}

/* Tipos de cómics */
.tipos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.tipo-card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    border: 1px solid #f0f0f0;
}

.tipo-card:hover {
    transform: translateY(-10px);
}

.tipo-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 28px;
}

.tipo-card h4 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: #333;
}

.tipo-card p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 15px;
    font-size: 14px;
}

.tipo-stats {
    margin-bottom: 20px;
    color: #fa709a;
    font-weight: 600;
}

/* Demografías */
.demografia-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
}

.demografia-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    border-left: 4px solid #fa709a;
}

.demografia-card:hover {
    transform: translateY(-5px);
}

.demografia-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.demografia-header h4 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #333;
    margin: 0;
}

.obras-badge {
    background: #fa709a;
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.demografia-card p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 14px;
}

.demografia-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #fa709a;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: gap 0.3s ease;
}

.demografia-link:hover {
    gap: 12px;
}

/* Acciones adicionales */
.additional-actions-advanced {
    margin: 100px 0;
    padding: 80px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 30px;
}

.actions-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 30px;
}

.action-card-advanced {
    display: flex;
    align-items: flex-start;
    gap: 25px;
    background: white;
    padding: 40px;
    border-radius: 25px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.action-card-advanced:hover {
    transform: translateY(-10px);
}

.action-icon-advanced {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 32px;
    flex-shrink: 0;
}

.action-content h3 {
    font-size: 1.375rem;
    color: #333;
    margin-bottom: 15px;
    font-weight: 700;
}

.action-content p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 25px;
}

/* Sin categorías */
.no-categories-advanced {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 20px;
    color: #666;
}

.no-cat-icon {
    font-size: 5rem;
    color: #ddd;
    margin-bottom: 30px;
}

.no-categories-advanced h3 {
    font-size: 2rem;
    margin-bottom: 15px;
    color: #333;
}

/* Botones */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 12px;
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

.btn-block {
    width: 100%;
}

/* Animaciones */
@keyframes drift {
    0%, 100% { transform: translateX(0px) translateY(0px); }
    25% { transform: translateX(10px) translateY(-10px); }
    50% { transform: translateX(-5px) translateY(-20px); }
    75% { transform: translateX(-10px) translateY(-10px); }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
}

/* Responsive */
@media (max-width: 768px) {
    .categorias-hero-advanced {
        min-height: 80vh;
        padding: 60px 0;
    }
    
    .hero-content-grid {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .stats-grid-advanced {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .floating-categories {
        display: none;
    }
    
    .quick-nav-advanced {
        grid-template-columns: 1fr;
    }
    
    .quick-nav-item-advanced {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .section-header-advanced {
        flex-direction: column;
        gap: 20px;
        align-items: flex-start;
    }
    
    .categorias-grid-advanced {
        grid-template-columns: 1fr;
    }
    
    .tipos-grid {
        grid-template-columns: 1fr;
    }
    
    .demografia-grid {
        grid-template-columns: 1fr;
    }
    
    .actions-container {
        grid-template-columns: 1fr;
        padding: 0 20px;
    }
    
    .action-card-advanced {
        flex-direction: column;
        text-align: center;
    }
    
    .populares-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .stats-grid-advanced {
        grid-template-columns: 1fr;
    }
    
    .populares-grid {
        grid-template-columns: 1fr;
    }
    
    .section-title h2 {
        font-size: 2rem;
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<!-- Scripts -->
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
    
    // Smooth scroll para navegación rápida
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
    
    // Parallax effect para elementos flotantes
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelectorAll('.floating-cat');
        const speed = 0.5;
        
        parallax.forEach(element => {
            const yPos = -(scrolled * speed);
            element.style.transform = `translateY(${yPos}px)`;
        });
    });
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
