<?php
/**
 * Página de inicio adaptada a Bootstrap
 */

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Obtener obras destacadas recientes (una mezcla de libros y cómics)
$sql_destacados = "
    (SELECT 'libro' as tipo, l.id, l.titulo, u.usuario AS autor, l.descripcion, l.portada, l.publico_adulto, 
     c.nombre as categoria, l.fecha_subida, l.visitas,
     (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = l.id AND f.tipo_contenido = 'libro') as total_favoritos
     FROM libros l
     LEFT JOIN categorias c ON l.categoria_id = c.id
     LEFT JOIN usuarios u ON l.usuario_id = u.id
     WHERE l.destacado = 1
     ORDER BY l.fecha_subida DESC
     LIMIT 4)
    UNION ALL
    (SELECT 'comic' as tipo, co.id, co.titulo, u.usuario AS autor, co.descripcion, co.portada, co.publico_adulto, 
     cat.nombre as categoria, co.fecha_subida, co.visitas,
     (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = co.id AND f.tipo_contenido = 'comic') as total_favoritos
     FROM comics co
     LEFT JOIN categorias cat ON co.categoria_id = cat.id
     LEFT JOIN usuarios u ON co.usuario_id = u.id
     WHERE co.destacado = 1
     ORDER BY co.fecha_subida DESC
     LIMIT 4)
    ORDER BY fecha_subida DESC
    LIMIT 8";
$result_destacados = $conexion->query($sql_destacados);

// Obtener obras recientes
$sql_recientes = "
    (SELECT 'libro' as tipo, l.id, l.titulo, u.usuario AS autor, l.portada, l.fecha_subida
     FROM libros l
     LEFT JOIN usuarios u ON l.usuario_id = u.id
     ORDER BY l.fecha_subida DESC
     LIMIT 3)
    UNION ALL
    (SELECT 'comic' as tipo, c.id, c.titulo, u.usuario AS autor, c.portada, c.fecha_subida
     FROM comics c
     LEFT JOIN usuarios u ON c.usuario_id = u.id
     ORDER BY c.fecha_subida DESC
     LIMIT 3)
    ORDER BY fecha_subida DESC
    LIMIT 6";
$result_recientes = $conexion->query($sql_recientes);

// Obtener obras populares
$sql_populares = "
    (SELECT 'libro' as tipo, l.id, l.titulo, u.usuario AS autor, l.portada, l.visitas
     FROM libros l
     LEFT JOIN usuarios u ON l.usuario_id = u.id
     ORDER BY l.visitas DESC
     LIMIT 3)
    UNION ALL
    (SELECT 'comic' as tipo, c.id, c.titulo, u.usuario AS autor, c.portada, c.visitas
     FROM comics c
     LEFT JOIN usuarios u ON c.usuario_id = u.id
     ORDER BY c.visitas DESC
     LIMIT 3)
    ORDER BY visitas DESC
    LIMIT 6";
$result_populares = $conexion->query($sql_populares);

// Obtener categorías populares
$sql_categorias = "
    SELECT c.id, c.nombre, c.tipo, c.descripcion,
           CASE 
               WHEN c.tipo = 'libro' THEN (SELECT COUNT(*) FROM libros l WHERE l.categoria_id = c.id)
               WHEN c.tipo = 'comic' THEN (SELECT COUNT(*) FROM comics co WHERE co.categoria_id = c.id)
           END as total_obras
    FROM categorias c
    HAVING total_obras > 0
    ORDER BY total_obras DESC, c.nombre
    LIMIT 12";
$result_categorias = $conexion->query($sql_categorias);

// Obtener estadísticas generales
$sql_stats = "SELECT 
             (SELECT COUNT(*) FROM libros) as total_libros,
             (SELECT COUNT(*) FROM comics) as total_comics,
             (SELECT COUNT(*) FROM usuarios) as total_usuarios,
             (SELECT COUNT(*) FROM categorias) as total_categorias";
$result_stats = $conexion->query($sql_stats);
$stats = $result_stats->fetch_assoc();
?>

<!-- Hero Section -->
<section class="hero-section position-relative d-flex align-items-center min-vh-100 overflow-hidden">
    <div class="hero-background position-absolute top-0 start-0 w-100 h-100">
        <div class="hero-particles"></div>
        <div class="hero-gradient"></div>
    </div>
    <div class="container position-relative z-2">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 hero-text" data-aos="fade-up">
                <h1 class="hero-title mb-4">
                    <span class="title-main d-block">Descubre infinitas historias de</span>
                    <span class="title-accent d-block">infinitos mundos</span>
                </h1>
                <p class="hero-subtitle mb-4">
                    Tu plataforma definitiva para explorar, compartir y disfrutar tu lectura literaria, cómics y manga.
                    Únete a la comunidad.
                </p>
                <!-- Estadísticas -->
                <div class="row hero-stats mb-4">
                    <div class="col-auto stat-item">
                        <span class="stat-number fs-2 fw-bold text-success"><?php echo number_format($stats['total_libros'] + $stats['total_comics']); ?></span>
                        <span class="stat-label d-block">Obras disponibles</span>
                    </div>
                    <div class="col-auto stat-item">
                        <span class="stat-number fs-2 fw-bold text-success"><?php echo number_format($stats['total_usuarios']); ?></span>
                        <span class="stat-label d-block">Lectores activos</span>
                    </div>
                    <div class="col-auto stat-item">
                        <span class="stat-number fs-2 fw-bold text-success"><?php echo $stats['total_categorias']; ?></span>
                        <span class="stat-label d-block">Géneros</span>
                    </div>
                </div>
                <?php if (!isset($_SESSION['usuario_id'])): ?>
                <div class="hero-buttons d-flex gap-3 flex-wrap">
                    <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket"></i> Únete Gratis
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-compass"></i> Explorar Ahora
                    </a>
                </div>
                <?php else: ?>
                <div class="hero-buttons d-flex gap-3 flex-wrap">
                    <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Publicar Obra
                    </a>
                    <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-heart"></i> Mis Favoritos
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-6 hero-visual d-flex justify-content-center align-items-center" data-aos="fade-left" data-aos-delay="200">
                <div class="floating-books position-relative" style="height:400px;">
                    <div class="book book-1 position-absolute">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="book book-2 position-absolute">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="book book-3 position-absolute">
                        <i class="fas fa-scroll"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección de Explorar Contenido -->
<section class="explore-section py-5 bg-light">
    <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Explora las distintas categorías</h2>
        </div>
        <div class="row g-4 justify-content-center">
            <!-- literatura -->
            <div class="col-md-6">
                <div class="content-type-card literatura-card h-100 shadow-sm" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-background"></div>
                    <div class="card-content position-relative z-2">
                        <div class="card-icon mx-auto mb-3">
                            <i class="fas fa-feather-alt"></i>
                        </div>
                        <h3>Literatura</h3>
                        <p>Explora novelas, poesía, ensayos y todo tipo de obras literarias.</p>
                        <div class="card-stats mb-3">
                            <span><i class="fas fa-book"></i> <?php echo number_format($stats['total_libros']); ?> obras</span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i> Descubrir Literatura
                        </a>
                    </div>
                </div>
            </div>
            <!-- comics -->
            <div class="col-md-6">
                <div class="content-type-card comics-card h-100 shadow-sm" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-background"></div>
                    <div class="card-content position-relative z-2">
                        <div class="card-icon mx-auto mb-3">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3>Cómics y Manga</h3>
                        <p>Sumérgete en el mundo visual de cómics, manga e historias gráficas.</p>
                        <div class="card-stats mb-3">
                            <span><i class="fas fa-images"></i> <?php echo number_format($stats['total_comics']); ?> obras</span>
                        </div>
                        <a href="<?php echo BASE_URL; ?>/public/comics.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i> Explorar Cómics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección de Obras Destacadas -->
<section class="featured-section py-5">
    <div class="container">
        <div class="section-header d-flex flex-wrap justify-content-between align-items-end mb-5 gap-3" data-aos="fade-up">
            <div class="header-content">
                <h2 class="section-title d-flex align-items-center gap-2">
                    <i class="fas fa-star text-warning"></i> Obras Destacadas
                </h2>
                <p class="section-subtitle">Lo mejor de nuestra colección seleccionado para ti</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-outline-secondary">
                <i class="fas fa-eye"></i> Ver todas
            </a>
        </div>
        <?php if ($result_destacados && $result_destacados->num_rows > 0): ?>
            <div class="row obras-grid featured-grid g-4">
                <?php while ($obra = $result_destacados->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="obra-card featured-card h-100 d-flex flex-column" data-aos="fade-up">
                        <div class="obra-image position-relative">
                            <?php if (!empty($obra['portada'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" 
                                     alt="<?php echo htmlspecialchars($obra['titulo']); ?>"
                                     loading="lazy" class="img-fluid w-100 h-100 object-fit-cover">
                            <?php else: ?>
                                <div class="obra-placeholder d-flex align-items-center justify-content-center w-100 h-100">
                                    <i class="fas <?php echo $obra['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                                </div>
                            <?php endif; ?>
                            <div class="obra-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-between p-3">
                                <div class="obra-badges d-flex gap-2 flex-wrap">
                                    <span class="badge bg-primary badge-<?php echo $obra['tipo']; ?>">
                                        <?php echo $obra['tipo'] == 'libro' ? 'Libro' : 'Cómic'; ?>
                                    </span>
                                    <?php if ($obra['publico_adulto']): ?>
                                        <span class="badge bg-warning badge-adulto">+18</span>
                                    <?php endif; ?>
                                </div>
                                <div class="obra-actions d-flex gap-2">
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra['id']; ?>&tipo=<?php echo $obra['tipo']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <?php if (isset($_SESSION['usuario_id'])): ?>
                                        <button class="btn btn-outline-light btn-sm btn-favorito" 
                                                data-obra-id="<?php echo $obra['id']; ?>" 
                                                data-tipo="<?php echo $obra['tipo']; ?>">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="obra-content p-4 flex-grow-1 d-flex flex-column">
                            <h3 class="obra-title mb-2"><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                            <p class="obra-author mb-2 text-muted small d-flex align-items-center gap-2">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($obra['autor']); ?>
                            </p>
                            <?php if (!empty($obra['categoria'])): ?>
                                <span class="obra-category d-inline-block mb-2 px-2 py-1 bg-light text-primary rounded-pill small">
                                    <?php echo htmlspecialchars($obra['categoria']); ?>
                                </span>
                            <?php endif; ?>
                            <p class="obra-description mb-3 text-truncate" style="max-height: 70px;">
                                <?php echo substr(htmlspecialchars($obra['descripcion']), 0, 100) . '...'; ?>
                            </p>
                            <div class="obra-stats d-flex gap-3 mt-auto">
                                <span class="stat d-flex align-items-center gap-1">
                                    <i class="fas fa-eye"></i>
                                    <?php echo number_format($obra['visitas']); ?>
                                </span>
                                <span class="stat d-flex align-items-center gap-1">
                                    <i class="fas fa-heart"></i>
                                    <?php echo $obra['total_favoritos']; ?>
                                </span>
                                <span class="stat d-flex align-items-center gap-1">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($obra['fecha_subida'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-content text-center py-5" data-aos="fade-up">
                <div class="no-content-icon mb-3">
                    <i class="fas fa-star"></i>
                </div>
                <h3>¡Próximamente obras destacadas!</h3>
                <p>Estamos preparando contenido increíble para ti</p>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i> Sé el primero en publicar
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Sección de Contenido Dinámico -->
<section class="dynamic-content py-5 bg-light">
    <div class="container">
        <div class="content-tabs bg-white rounded-4 p-4 shadow-sm" data-aos="fade-up">
            <div class="tab-buttons d-flex gap-3 mb-4 border-bottom">
                <button class="tab-btn btn btn-link active" data-tab="recientes">
                    <i class="fas fa-clock"></i> Recientes
                </button>
                <button class="tab-btn btn btn-link" data-tab="populares">
                    <i class="fas fa-fire"></i> Populares
                </button>
                <button class="tab-btn btn btn-link" data-tab="categorias">
                    <i class="fas fa-tags"></i> Categorías
                </button>
            </div>
            <!-- Tab Recientes -->
            <div class="tab-content active" id="tab-recientes">
                <div class="row obras-carousel g-3">
                    <?php if ($result_recientes && $result_recientes->num_rows > 0): ?>
                        <?php while ($obra = $result_recientes->fetch_assoc()): ?>
                        <div class="col-6 col-md-4">
                            <div class="mini-card bg-light rounded-3 p-3 text-center h-100 shadow-sm">
                                <div class="mini-image mx-auto mb-2">
                                    <?php if (!empty($obra['portada'])): ?>
                                    <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" 
                                         alt="<?php echo htmlspecialchars($obra['titulo']); ?>" class="img-fluid rounded-2">
                                    <?php else: ?>
                                    <div class="mini-placeholder d-flex align-items-center justify-content-center w-100 h-100">
                                        <i class="fas <?php echo $obra['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mini-content">
                                    <h4 class="mb-1"><?php echo htmlspecialchars($obra['titulo']); ?></h4>
                                    <p class="mb-1 text-muted small"><?php echo htmlspecialchars($obra['autor']); ?></p>
                                    <span class="mini-type badge bg-primary"><?php echo $obra['tipo'] == 'libro' ? 'Libro' : 'Cómic'; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Tab Populares -->
            <div class="tab-content" id="tab-populares">
                <div class="row obras-carousel g-3">
                    <?php if ($result_populares && $result_populares->num_rows > 0): ?>
                        <?php while ($obra = $result_populares->fetch_assoc()): ?>
                        <div class="col-6 col-md-4">
                            <div class="mini-card bg-light rounded-3 p-3 text-center h-100 shadow-sm">
                                <div class="mini-image mx-auto mb-2">
                                    <?php if (!empty($obra['portada'])): ?>
                                    <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" 
                                         alt="<?php echo htmlspecialchars($obra['titulo']); ?>" class="img-fluid rounded-2">
                                    <?php else: ?>
                                    <div class="mini-placeholder d-flex align-items-center justify-content-center w-100 h-100">
                                        <i class="fas <?php echo $obra['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mini-content">
                                    <h4 class="mb-1"><?php echo htmlspecialchars($obra['titulo']); ?></h4>
                                    <p class="mb-1 text-muted small"><?php echo htmlspecialchars($obra['autor']); ?></p>
                                    <div class="mini-stats d-flex justify-content-center gap-2">
                                        <span><i class="fas fa-eye"></i> <?php echo number_format($obra['visitas']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Tab Categorías -->
            <div class="tab-content" id="tab-categorias">
                <div class="row categorias-grid g-3">
                    <?php if ($result_categorias && $result_categorias->num_rows > 0): ?>
                        <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=<?php echo $categoria['tipo']; ?>" 
                               class="categoria-item d-flex align-items-center gap-3 p-3 bg-light rounded-3 text-decoration-none text-dark shadow-sm h-100">
                                <div class="categoria-icon d-flex align-items-center justify-content-center">
                                    <i class="fas <?php echo $categoria['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                                </div>
                                <div class="categoria-info">
                                    <h4 class="mb-1"><?php echo htmlspecialchars($categoria['nombre']); ?></h4>
                                    <span class="categoria-count small"><?php echo $categoria['total_obras']; ?> obras</span>
                                </div>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección de Características -->
<section class="features-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">¿Por qué elegir InkVerse?</h2>
            <p class="section-subtitle">Descubre todas las ventajas de formar parte de la comunidad</p>
        </div>
        <div class="row features-grid g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3>Publica Fácilmente</h3>
                    <p>Comparte tus historias con lectores de todo el mundo en minutos.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Comunidad Activa</h3>
                    <p>Conecta con autores y lectores. Recibe feedback de tu contenido y participa en discusiones.</p>
                    <div class="feature-highlight bg-light text-primary px-3 py-1 rounded-pill d-inline-block">
                        <span><?php echo number_format($stats['total_usuarios']); ?> miembros</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Sección de Arte</h3>
                    <p>Al igual que puedes compartir tus teorías y opiniones, tienes la oportunidad de compartir tu arte a los demás usuarios.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Acceso Multiplataforma</h3>
                    <p>Lee y publica desde cualquier dispositivo, se adapta perfectamente a móviles, tablets y escritorio.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Sistema de Favoritos</h3>
                    <p>Guarda tus obras favoritas y crea listas personalizadas.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Interacción Social</h3>
                    <p>Comenta, valora y comparte opiniones sobre las obras de los demás usuarios.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección de CTA Final -->
<?php if (!isset($_SESSION['usuario_id'])): ?>
<section class="cta-section py-5 position-relative text-center text-white overflow-hidden">
    <div class="cta-background position-absolute top-0 start-0 w-100 h-100"></div>
    <div class="container position-relative z-2">
        <div class="cta-content" data-aos="fade-up">
            <div class="cta-icon mx-auto mb-4">
                <i class="fas fa-rocket"></i>
            </div>
            <h2 class="mb-3">¿Listo para comenzar tu aventura?</h2>
            <p class="mb-4 fs-4">Únete a miles de lectores y escritores que ya forman parte de nuestra comunidad. Es gratis y solo toma unos segundos.</p>
            <div class="cta-benefits d-flex justify-content-center flex-wrap gap-4 mb-4">
                <div class="benefit d-flex align-items-center gap-2">
                    <i class="fas fa-check"></i>
                    <span>Acceso ilimitado a todas las obras</span>
                </div>
                <div class="benefit d-flex align-items-center gap-2">
                    <i class="fas fa-check"></i>
                    <span>Publica tus propias historias</span>
                </div>
                <div class="benefit d-flex align-items-center gap-2">
                    <i class="fas fa-check"></i>
                    <span>Conecta con otros lectores</span>
                </div>
            </div>
            <div class="cta-buttons d-flex justify-content-center flex-wrap gap-3">
                <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i> Crear Cuenta Gratis
                </a>
                <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- (Deja tus estilos y scripts existentes, solo asegúrate de tener Bootstrap cargado en tu header/footer) -->

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>