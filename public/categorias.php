<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/conexion.php');
require_once(__DIR__ . '/../includes/idioma.php');

// Libros (consulta usando la relación N:M libro_categorias)
$sql_categorias_libros = "SELECT 
    c.id, 
    c.nombre, 
    c.descripcion, 
    COUNT(lc.libro_id) AS total_obras,
    (SELECT AVG(l.valoracion) FROM libros l JOIN libro_categorias lc2 ON l.id = lc2.libro_id WHERE lc2.categoria_id = c.id) AS valoracion_promedio,
    (SELECT MAX(l.fecha_subida) FROM libros l JOIN libro_categorias lc3 ON l.id = lc3.libro_id WHERE lc3.categoria_id = c.id) AS ultima_obra,
    (SELECT l2.portada FROM libros l2 JOIN libro_categorias lc4 ON l2.id = lc4.libro_id WHERE lc4.categoria_id = c.id AND l2.portada IS NOT NULL ORDER BY l2.fecha_subida DESC LIMIT 1) as portada_ejemplo
FROM categorias c
LEFT JOIN libro_categorias lc ON lc.categoria_id = c.id
WHERE c.tipo = 'libro'
GROUP BY c.id, c.nombre, c.descripcion
ORDER BY total_obras DESC, c.nombre";
$result_categorias_libros = $conexion->query($sql_categorias_libros);

// Cómics (consulta usando la relación N:M comic_categorias)
$sql_categorias_comics = "SELECT 
    c.id, 
    c.nombre, 
    c.descripcion, 
    COUNT(cc.comic_id) AS total_obras,
    (SELECT AVG(co.valoracion) FROM comics co JOIN comic_categorias cc2 ON co.id = cc2.comic_id WHERE cc2.categoria_id = c.id) AS valoracion_promedio,
    (SELECT MAX(co.fecha_subida) FROM comics co JOIN comic_categorias cc3 ON co.id = cc3.comic_id WHERE cc3.categoria_id = c.id) AS ultima_obra,
    (SELECT co2.portada FROM comics co2 JOIN comic_categorias cc4 ON co2.id = cc4.comic_id WHERE cc4.categoria_id = c.id AND co2.portada IS NOT NULL ORDER BY co2.fecha_subida DESC LIMIT 1) as portada_ejemplo,
    CASE 
        WHEN c.nombre IN ('Cómic', 'Manga', 'Manhwa', 'Manhua') THEN 'tipo'
        WHEN c.nombre IN ('Shonen', 'Shojo', 'Seinen', 'Josei') THEN 'demografia'
        ELSE 'genero'
    END as categoria_tipo
FROM categorias c
LEFT JOIN comic_categorias cc ON cc.categoria_id = c.id
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

// Categorías más populares (solo en favoritos)
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

<div class="categorias-hero-advanced">
    <div class="hero-background">
        <div class="hero-pattern"></div>
        <div class="hero-gradient"></div>
    </div>
    <div class="container">
        <div class="hero-content-grid">
            <div class="hero-text-content">
                <h1 class="hero-title"><?php echo $TEXTOS['categorias_titulo']; ?></h1>
                <p class="hero-description"><?php echo $TEXTOS['categorias_subtitulo']; ?></p>
                <div class="stats-grid-advanced">
                    <div class="stat-card-advanced">
                        <div class="stat-icon"><i class="fas fa-book"></i></div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['total_libros']); ?></div>
                            <div class="stat-label"><?php echo $TEXTOS['categorias_libros']; ?></div>
                        </div>
                    </div>
                    <div class="stat-card-advanced">
                        <div class="stat-icon"><i class="fas fa-book-open"></i></div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['total_comics']); ?></div>
                            <div class="stat-label"><?php echo $TEXTOS['categorias_comics']; ?></div>
                        </div>
                    </div>
                    <div class="stat-card-advanced">
                        <div class="stat-icon"><i class="fas fa-tags"></i></div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['total_cat_libros'] + $stats['total_cat_comics']; ?></div>
                            <div class="stat-label"><?php echo $TEXTOS['categorias_categorias']; ?></div>
                        </div>
                    </div>
                    <div class="stat-card-advanced">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo number_format($stats['total_usuarios']); ?></div>
                            <div class="stat-label"><?php echo $TEXTOS['categorias_usuarios']; ?></div>
                        </div>
                    </div>
                </div>
                <div class="hero-actions">
                    <a href="#literatura" class="btn btn-primary btn-lg">
                        <i class="fas fa-book"></i> <?php echo $TEXTOS['categorias_explorar_literatura']; ?>
                    </a>
                    <a href="#comics" class="btn btn-outline-white btn-lg">
                        <i class="fas fa-book-open"></i> <?php echo $TEXTOS['categorias_ver_comics']; ?>
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
    <div class="quick-nav-advanced">
        <a href="#literatura" class="quick-nav-item-advanced literatura">
            <div class="nav-icon"><i class="fas fa-book"></i></div>
            <div class="nav-content">
                <h3><?php echo $TEXTOS['categorias_literatura']; ?></h3>
                <p><?php echo $stats['total_cat_libros']; ?> <?php echo $TEXTOS['categorias_nav_categorias']; ?></p>
            </div>
        </a>
        <a href="#comics" class="quick-nav-item-advanced comics">
            <div class="nav-icon"><i class="fas fa-book-open"></i></div>
            <div class="nav-content">
                <h3><?php echo $TEXTOS['categorias_comics_manga']; ?></h3>
                <p><?php echo $stats['total_cat_comics']; ?> <?php echo $TEXTOS['categorias_nav_categorias']; ?></p>
            </div>
        </a>
        <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="quick-nav-item-advanced explorar">
            <div class="nav-icon"><i class="fas fa-compass"></i></div>
            <div class="nav-content">
                <h3><?php echo $TEXTOS['categorias_explorar_todo']; ?></h3>
                <p><?php echo $TEXTOS['categorias_descubrir_obras']; ?></p>
            </div>
        </a>
        <a href="<?php echo BASE_URL; ?>/public/aleatorio.php" class="quick-nav-item-advanced aleatorio">
            <div class="nav-icon"><i class="fas fa-random"></i></div>
            <div class="nav-content">
                <h3><?php echo $TEXTOS['categorias_sorprendeme']; ?></h3>
                <p><?php echo $TEXTOS['categorias_obra_aleatoria']; ?></p>
            </div>
        </a>
    </div>

    <!-- Categorías más populares -->
    <?php if (!empty($categorias_populares)): ?>
    <section class="populares-section">
        <div class="section-header-advanced">
            <div class="section-title">
                <h2><i class="fas fa-fire"></i> <?php echo $TEXTOS['categorias_mas_populares']; ?></h2>
                <p><?php echo $TEXTOS['categorias_mas_populares_desc']; ?></p>
            </div>
        </div>
        <div class="populares-grid">
            <?php foreach ($categorias_populares as $cat): ?>
                <div class="popular-item" data-aos="fade-up">
                    <div class="popular-icon">
                        <i class="fas <?php echo $cat['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                    </div>
                    <h4><?php echo htmlspecialchars($cat['nombre']); ?></h4>
                    <p><?php echo $cat['total_favoritos']; ?> <?php echo $TEXTOS['categorias_favoritos']; ?></p>
                    <span class="popular-type"><?php echo $cat['tipo'] == 'libro' ? $TEXTOS['categorias_literatura'] : $TEXTOS['categorias_comics']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Categorías de Literatura -->
    <section id="literatura" class="categorias-section-advanced">
        <div class="section-header-advanced">
            <div class="section-title">
                <h2><i class="fas fa-book"></i> <?php echo $TEXTOS['categorias_literatura']; ?></h2>
                <p><?php echo $TEXTOS['categorias_literatura_desc']; ?></p>
            </div>
            <div class="section-actions">
                <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> <?php echo $TEXTOS['categorias_ver_todas_literatura']; ?>
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
                                    <span class="obras-count">
                                        <?php echo intval($categoria['total_obras']); ?> <?php echo $TEXTOS['categorias_obras']; ?>
                                    </span>
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
                                        <?php echo $TEXTOS['categorias_actualizado']; ?> <?php echo date('d/m/Y', strtotime($categoria['ultima_obra'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="categoria-description">
                                <?php echo !empty($categoria['descripcion']) ? htmlspecialchars($categoria['descripcion']) : $TEXTOS['categorias_sin_desc']; ?>
                            </p>
                            <div class="categoria-stats-grid">
                                <div class="stat-mini">
                                    <i class="fas fa-book"></i>
                                    <span><?php echo intval($categoria['total_obras']); ?> <?php echo $TEXTOS['categorias_obras']; ?></span>
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
                                <i class="fas fa-eye"></i> <?php echo $TEXTOS['categorias_explorar_categoria']; ?>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-categories-advanced">
                    <div class="no-cat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3><?php echo $TEXTOS['categorias_no_literatura']; ?></h3>
                    <p><?php echo $TEXTOS['categorias_no_literatura_desc']; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Categorías de Cómics -->
    <section id="comics" class="categorias-section-advanced">
        <div class="section-header-advanced">
            <div class="section-title">
                <h2><i class="fas fa-book-open"></i> <?php echo $TEXTOS['categorias_comics_manga']; ?></h2>
                <p><?php echo $TEXTOS['categorias_comics_manga_desc']; ?></p>
            </div>
            <div class="section-actions">
                <a href="<?php echo BASE_URL; ?>/public/comics.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> <?php echo $TEXTOS['categorias_ver_todas_comics']; ?>
                </a>
            </div>
        </div>
        <?php if (!empty($comics_por_tipo['tipo'])): ?>
        <div class="subsection">
            <h3 class="subsection-title">
                <i class="fas fa-bookmark"></i> <?php echo $TEXTOS['categorias_tipos_comics']; ?>
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
                            <span><?php echo intval($tipo['total_obras']); ?> <?php echo $TEXTOS['categorias_obras']; ?></span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?categoria_id=<?php echo $tipo['id']; ?>&tipo=comic" 
                           class="btn btn-sm btn-outline"><?php echo $TEXTOS['categorias_explorar']; ?></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($comics_por_tipo['genero'])): ?>
        <div class="subsection">
            <h3 class="subsection-title">
                <i class="fas fa-tags"></i> <?php echo $TEXTOS['categorias_generos']; ?>
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
                                    <span class="obras-count"><?php echo intval($categoria['total_obras']); ?> <?php echo $TEXTOS['categorias_obras']; ?></span>
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
                                        <?php echo $TEXTOS['categorias_actualizado']; ?> <?php echo date('d/m/Y', strtotime($categoria['ultima_obra'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="categoria-description">
                                <?php echo !empty($categoria['descripcion']) ? htmlspecialchars($categoria['descripcion']) : $TEXTOS['categorias_sin_desc']; ?>
                            </p>
                            <div class="categoria-stats-grid">
                                <div class="stat-mini">
                                    <i class="fas fa-book-open"></i>
                                    <span><?php echo intval($categoria['total_obras']); ?> <?php echo $TEXTOS['categorias_obras']; ?></span>
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
                                <i class="fas fa-eye"></i> <?php echo $TEXTOS['categorias_explorar_categoria']; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>
</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>