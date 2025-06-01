<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Libros
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

// Cómics
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

// Estadísticas generales
$sql_stats = "SELECT 
              (SELECT COUNT(*) FROM libros) as total_libros,
              (SELECT COUNT(*) FROM comics) as total_comics,
              (SELECT COUNT(*) FROM categorias WHERE tipo = 'libro') as total_cat_libros,
              (SELECT COUNT(*) FROM categorias WHERE tipo = 'comic') as total_cat_comics,
              (SELECT COUNT(*) FROM usuarios) as total_usuarios,
              (SELECT COUNT(*) FROM favoritos) as total_favoritos";
$result_stats = $conexion->query($sql_stats);
$stats = $result_stats->fetch_assoc();

// Categorías más populares
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
            
                <h1 class="hero-title">Explorar por Categorías</h1>
                <p class="hero-description">Descubre contenido perfectamente organizado por géneros, temáticas y estilos que se adaptan a tus gustos</p>
                <!-- Estadísticas mejoradas -->
                <div class="stats-grid-advanced">
                    <div class="stat-card-advanced">
                        <div class="stat-icon"><i class="fas fa-book"></i></div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['total_libros']); ?></div>
                            <div class="stat-label">Libros</div>
                        </div>
                    </div>
                    <div class="stat-card-advanced">
                        <div class="stat-icon"><i class="fas fa-book-open"></i></div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['total_comics']); ?></div>
                            <div class="stat-label">Cómics</div>
                        </div>
                    </div>
                    <div class="stat-card-advanced">
                        <div class="stat-icon"><i class="fas fa-tags"></i></div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['total_cat_libros'] + $stats['total_cat_comics']; ?></div>
                            <div class="stat-label">Categorías</div>
                        </div>
                    </div>
                    <div class="stat-card-advanced">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
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
    <!-- Navegación rápida -->
    <div class="quick-nav-advanced">
        <a href="#literatura" class="quick-nav-item-advanced literatura">
            <div class="nav-icon"><i class="fas fa-book"></i></div>
            <div class="nav-content">
                <h3>Literatura</h3>
                <p><?php echo $stats['total_cat_libros']; ?> categorías</p>
            </div>
        </a>
        <a href="#comics" class="quick-nav-item-advanced comics">
            <div class="nav-icon"><i class="fas fa-book-open"></i></div>
            <div class="nav-content">
                <h3>Cómics & Manga</h3>
                <p><?php echo $stats['total_cat_comics']; ?> categorías</p>
            </div>
        </a>
        <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="quick-nav-item-advanced explorar">
            <div class="nav-icon"><i class="fas fa-compass"></i></div>
            <div class="nav-content">
                <h3>Explorar Todo</h3>
                <p>Descubrir obras</p>
            </div>
        </a>
        <a href="<?php echo BASE_URL; ?>/public/aleatorio.php" class="quick-nav-item-advanced aleatorio">
            <div class="nav-icon"><i class="fas fa-random"></i></div>
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
                $result_categorias_libros->data_seek(0);
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
                                    <span class="obras-count"><?php echo intval($categoria['total_obras']); ?> obras</span>
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
                                <?php if (!empty($categoria['ultima_obra'])): ?>
                                    <span class="ultima-actualizacion">
                                        Actualizado <?php echo date('d/m/Y', strtotime($categoria['ultima_obra'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="categoria-description">
                                <?php echo !empty($categoria['descripcion']) ? htmlspecialchars($categoria['descripcion']) : 'Explora obras de este fascinante género literario'; ?>
                            </p>
                            <div class="categoria-stats-grid">
                                <div class="stat-mini">
                                    <i class="fas fa-book"></i>
                                    <span><?php echo intval($categoria['total_obras']); ?> obras</span>
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
                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?categoria_id=<?php echo $categoria['id']; ?>&tipo=libro" 
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
                            <span><?php echo intval($tipo['total_obras']); ?> obras</span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?categoria_id=<?php echo $tipo['id']; ?>&tipo=comic" 
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
                            <span class="obras-badge"><?php echo intval($demo['total_obras']); ?></span>
                        </div>
                        <p><?php echo htmlspecialchars($demo['descripcion']); ?></p>
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?categoria_id=<?php echo $demo['id']; ?>&tipo=comic" 
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
                                    <span class="obras-count"><?php echo intval($categoria['total_obras']); ?> obras</span>
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
                                <?php if (!empty($categoria['ultima_obra'])): ?>
                                    <span class="ultima-actualizacion">
                                        Actualizado <?php echo date('d/m/Y', strtotime($categoria['ultima_obra'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="categoria-description">
                                <?php echo !empty($categoria['descripcion']) ? htmlspecialchars($categoria['descripcion']) : 'Explora obras de este género visual'; ?>
                            </p>
                            <div class="categoria-stats-grid">
                                <div class="stat-mini">
                                    <i class="fas fa-book-open"></i>
                                    <span><?php echo intval($categoria['total_obras']); ?> obras</span>
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
                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?categoria_id=<?php echo $categoria['id']; ?>&tipo=comic" 
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

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>