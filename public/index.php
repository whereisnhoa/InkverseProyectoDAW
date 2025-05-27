<?php
/**
 * Página de inicio
 */

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Obtener obras destacadas recientes (una mezcla de libros y cómics)
$sql_destacados = "
    (SELECT 'libro' as tipo, l.id, l.titulo, l.autor, l.descripcion, l.portada, l.publico_adulto, 
     c.nombre as categoria, l.fecha_subida, l.visitas,
     (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = l.id AND f.tipo_contenido = 'libro') as total_favoritos
     FROM libros l
     LEFT JOIN categorias c ON l.categoria_id = c.id
     WHERE l.destacado = 1
     ORDER BY l.fecha_destacado DESC, l.fecha_subida DESC
     LIMIT 4)
    UNION ALL
    (SELECT 'comic' as tipo, co.id, co.titulo, co.autor, co.descripcion, co.portada, co.publico_adulto, 
     cat.nombre as categoria, co.fecha_subida, co.visitas,
     (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = co.id AND f.tipo_contenido = 'comic') as total_favoritos
     FROM comics co
     LEFT JOIN categorias cat ON co.categoria_id = cat.id
     WHERE co.destacado = 1
     ORDER BY co.fecha_destacado DESC, co.fecha_subida DESC
     LIMIT 4)
    ORDER BY fecha_subida DESC
    LIMIT 8";

$result_destacados = $conexion->query($sql_destacados);

// Obtener obras recientes
$sql_recientes = "
    (SELECT 'libro' as tipo, l.id, l.titulo, l.autor, l.portada, l.fecha_subida
     FROM libros l
     ORDER BY l.fecha_subida DESC
     LIMIT 3)
    UNION ALL
    (SELECT 'comic' as tipo, c.id, c.titulo, c.autor, c.portada, c.fecha_subida
     FROM comics c
     ORDER BY c.fecha_subida DESC
     LIMIT 3)
    ORDER BY fecha_subida DESC
    LIMIT 6";

$result_recientes = $conexion->query($sql_recientes);

// Obtener obras populares
$sql_populares = "
    (SELECT 'libro' as tipo, l.id, l.titulo, l.autor, l.portada, l.visitas
     FROM libros l
     ORDER BY l.visitas DESC
     LIMIT 3)
    UNION ALL
    (SELECT 'comic' as tipo, c.id, c.titulo, c.autor, c.portada, c.visitas
     FROM comics c
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
<section class="hero-section">
    <div class="hero-background">
        <div class="hero-particles"></div>
        <div class="hero-gradient"></div>
    </div>
    
    <div class="container">
        <div class="hero-content">
            <div class="hero-text" data-aos="fade-up">
                <h1 class="hero-title">
                    <span class="title-main">Descubre infinitas historias de</span>
                    <span class="title-accent">infinitos mundos</span>
                </h1>
                <p class="hero-subtitle">
                    Tu plataforma definitiva para explorar, compartir y disfrutar tu lectura literaria, cómics y manga. 
                    Únete a la comunidad.
                </p>
                
                <!-- Estadísticas del hero -->
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($stats['total_libros'] + $stats['total_comics']); ?></span>
                        <span class="stat-label">Obras disponibles</span>
                    </div>

                    <div class="stat-item">
                        <span class="stat-number"><?php echo number_format($stats['total_usuarios']); ?></span>
                        <span class="stat-label">Lectores activos</span>
                    </div>

                    <div class="stat-item">
                        <span class="stat-number"><?php echo $stats['total_categorias']; ?></span>
                        <span class="stat-label">Géneros</span>
                    </div>
                </div>
                
                <!-- si el usuario no hizo login -> unirse // si hizo login -> publicar obra -->
                <?php if (!isset($_SESSION['usuario_id'])): ?>
                    <div class="hero-buttons">
                        <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket"></i> Únete Gratis
                        </a>
                        <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-outline btn-lg">
                            <i class="fas fa-compass"></i> Explorar Ahora
                        </a>
                    </div>
                <?php else: ?>
                    <div class="hero-buttons">
                        <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Publicar Obra
                        </a>
                        <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php" class="btn btn-outline btn-lg">
                            <i class="fas fa-heart"></i> Mis Favoritos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- libros flotantes -->
            <div class="hero-visual" data-aos="fade-left" data-aos-delay="200">
                <div class="floating-books">
                    <div class="book book-1">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="book book-2">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="book book-3">
                        <i class="fas fa-scroll"></i>
                    </div>
                </div>
            </div>

        </div>

    </div>

</section>

<!-- Sección de Explorar Contenido -->
<section class="explore-section">

    <div class="container">

        <div class="section-header text-center" data-aos="fade-up">

            <h2 class="section-title">Explora las distintas categorías</h2>

        </div>
        
        <div class="content-types">

            <!-- literatura -->
            <div class="content-type-card literatura-card" data-aos="fade-up" data-aos-delay="100">

                <div class="card-background"></div>

                <div class="card-content">

                    <div class="card-icon">
                        <i class="fas fa-feather-alt"></i>
                    </div>

                    <h3>Literatura</h3>
                    <p>Explora novelas, poesía, ensayos y todo tipo de obras literarias.</p>

                    <div class="card-stats">
                        <span><i class="fas fa-book"></i> <?php echo number_format($stats['total_libros']); ?> obras</span>
                    </div>

                    <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Descubrir Literatura
                    </a>

                </div>

            </div>
            
            <!-- comics -->
            <div class="content-type-card comics-card" data-aos="fade-up" data-aos-delay="200">

                <div class="card-background"></div>

                <div class="card-content">

                    <div class="card-icon">
                        <i class="fas fa-palette"></i>
                    </div>

                    <h3>Cómics y Manga</h3>
                    <p>Sumérgete en el mundo visual de cómics, manga e historias gráficas.</p>

                    <div class="card-stats">
                        <span><i class="fas fa-images"></i> <?php echo number_format($stats['total_comics']); ?> obras</span>
                    </div>

                    <a href="<?php echo BASE_URL; ?>/public/comics.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Explorar Cómics
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- Sección de Obras Destacadas -->
<section class="featured-section">

    <div class="container">

        <div class="section-header" data-aos="fade-up">

            <div class="header-content">

                <h2 class="section-title">
                    <i class="fas fa-star"></i> Obras Destacadas
                </h2>

                <p class="section-subtitle">Lo mejor de nuestra colección seleccionado para ti</p>

            </div>

            <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-outline">
                <i class="fas fa-eye"></i> Ver todas
            </a>

        </div>
        
        <!-- si hay obras destacadas -->
        <?php if ($result_destacados && $result_destacados->num_rows > 0): ?>
            <div class="obras-grid featured-grid">
                <?php while ($obra = $result_destacados->fetch_assoc()): ?>
                    <div class="obra-card featured-card" data-aos="fade-up">
                        <div class="obra-image">
                            <?php if (!empty($obra['portada'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" 
                                     alt="<?php echo htmlspecialchars($obra['titulo']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="obra-placeholder">
                                    <i class="fas <?php echo $obra['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="obra-overlay">
                                <div class="obra-badges">
                                    <span class="badge badge-<?php echo $obra['tipo']; ?>">
                                        <?php echo $obra['tipo'] == 'libro' ? 'Libro' : 'Cómic'; ?>
                                    </span>
                                    <?php if ($obra['publico_adulto']): ?>
                                        <span class="badge badge-adulto">+18</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="obra-actions">
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra['id']; ?>&tipo=<?php echo $obra['tipo']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <?php if (isset($_SESSION['usuario_id'])): ?>
                                        <button class="btn btn-outline btn-sm btn-favorito" 
                                                data-obra-id="<?php echo $obra['id']; ?>" 
                                                data-tipo="<?php echo $obra['tipo']; ?>">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="obra-content">
                            <h3 class="obra-title"><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                            <p class="obra-author">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($obra['autor']); ?>
                            </p>
                            
                            <?php if (!empty($obra['categoria'])): ?>
                                <span class="obra-category"><?php echo htmlspecialchars($obra['categoria']); ?></span>
                            <?php endif; ?>
                            
                            <p class="obra-description">
                                <?php echo substr(htmlspecialchars($obra['descripcion']), 0, 100) . '...'; ?>
                            </p>
                            
                            <div class="obra-stats">
                                <span class="stat">
                                    <i class="fas fa-eye"></i>
                                    <?php echo number_format($obra['visitas']); ?>
                                </span>
                                <span class="stat">
                                    <i class="fas fa-heart"></i>
                                    <?php echo $obra['total_favoritos']; ?>
                                </span>
                                <span class="stat">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($obra['fecha_subida'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        <!-- si no hay obras destacadas -->
        <?php else: ?>

            <div class="no-content" data-aos="fade-up">
                <div class="no-content-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>¡Próximamente obras destacadas!</h3>
                <p>Estamos preparando contenido increíble para ti</p>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Sé el primero en publicar
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

</section>

<!-- Sección de Contenido Dinámico -->
<section class="dynamic-content">
    <div class="container">
        <div class="content-tabs" data-aos="fade-up">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="recientes">
                    <i class="fas fa-clock"></i> Recientes
                </button>
                <button class="tab-btn" data-tab="populares">
                    <i class="fas fa-fire"></i> Populares
                </button>
                <button class="tab-btn" data-tab="categorias">
                    <i class="fas fa-tags"></i> Categorías
                </button>
            </div>
            
            <!-- Tab Recientes -->
            <div class="tab-content active" id="tab-recientes">
                <div class="obras-carousel">
                    <?php if ($result_recientes && $result_recientes->num_rows > 0): ?>
                        <?php while ($obra = $result_recientes->fetch_assoc()): ?>
                            <div class="carousel-item">
                                <div class="mini-card">
                                    <div class="mini-image">
                                        <?php if (!empty($obra['portada'])): ?>
                                            <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" 
                                                 alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
                                        <?php else: ?>
                                            <div class="mini-placeholder">
                                                <i class="fas <?php echo $obra['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mini-content">
                                        <h4><?php echo htmlspecialchars($obra['titulo']); ?></h4>
                                        <p><?php echo htmlspecialchars($obra['autor']); ?></p>
                                        <span class="mini-type"><?php echo $obra['tipo'] == 'libro' ? 'Libro' : 'Cómic'; ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tab Populares -->
            <div class="tab-content" id="tab-populares">
                <div class="obras-carousel">
                    <?php if ($result_populares && $result_populares->num_rows > 0): ?>
                        <?php while ($obra = $result_populares->fetch_assoc()): ?>
                            <div class="carousel-item">
                                <div class="mini-card">
                                    <div class="mini-image">
                                        <?php if (!empty($obra['portada'])): ?>
                                            <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" 
                                                 alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
                                        <?php else: ?>
                                            <div class="mini-placeholder">
                                                <i class="fas <?php echo $obra['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mini-content">
                                        <h4><?php echo htmlspecialchars($obra['titulo']); ?></h4>
                                        <p><?php echo htmlspecialchars($obra['autor']); ?></p>
                                        <div class="mini-stats">
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
                <div class="categorias-grid">
                    <?php if ($result_categorias && $result_categorias->num_rows > 0): ?>
                        <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=<?php echo $categoria['tipo']; ?>" 
                               class="categoria-item">
                                <div class="categoria-icon">
                                    <i class="fas <?php echo $categoria['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
                                </div>
                                <div class="categoria-info">
                                    <h4><?php echo htmlspecialchars($categoria['nombre']); ?></h4>
                                    <span class="categoria-count"><?php echo $categoria['total_obras']; ?> obras</span>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección de Características -->
<section class="features-section">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2 class="section-title">¿Por qué elegir InkVerse?</h2>
            <p class="section-subtitle">Descubre todas las ventajas de formar parte de la comunidad</p>
        </div>
        
        <div class="features-grid">

            <!-- tarjeta 1 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-icon">
                    <i class="fas fa-upload"></i>
                </div>
                <h3>Publica Fácilmente</h3>
                <p>Comparte tus historias con lectores de todo el mundo en minutos.</p>
            </div>
            
            <!-- tarjeta 2 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Comunidad Activa</h3>
                <p>Conecta con autores y lectores. Recibe feedback de tu contenido y participa en discusiones.</p>
                <div class="feature-highlight">
                    <span><?php echo number_format($stats['total_usuarios']); ?> miembros</span>
                </div>
            </div>
            
            <!-- tarjeta 3 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h3>Sección de Arte</h3>
                <p>Al igual que puedes compartir tus teorías y opiniones, tienes la oportunidad de compartir tu arte a los demás usuarios.</p>
            </div>
            
            <!-- tarjeta 4 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Acceso Multiplataforma</h3>
                <p>Lee y publica desde cualquier dispositivo, se adapta perfectamente a móviles, tablets y escritorio.</p>
            </div>
            
            <!-- tarjeta 5 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Sistema de Favoritos</h3>
                <p>Guarda tus obras favoritas y crea listas personalizadas.</p>
            </div>
            
            <!-- tarjeta 6 -->
            <div class="feature-card" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Interacción Social</h3>
                <p>Comenta, valora y comparte opiniones sobre las obras de los demás usuarios.</p>
            </div>

        </div>

    </div>

</section>

<!-- Sección de CTA Final -->
<?php if (!isset($_SESSION['usuario_id'])): ?>

<section class="cta-section">

    <div class="cta-background"></div>

    <div class="container">

        <div class="cta-content" data-aos="fade-up">

            <div class="cta-icon">
                <i class="fas fa-rocket"></i>
            </div>

            <h2>¿Listo para comenzar tu aventura?</h2>
            <p>Únete a miles de lectores y escritores que ya forman parte de nuestra comunidad. Es gratis y solo toma unos segundos.</p>
            
            <div class="cta-benefits">

                <div class="benefit">
                    <i class="fas fa-check"></i>
                    <span>Acceso ilimitado a todas las obras</span>
                </div>
                <div class="benefit">
                    <i class="fas fa-check"></i>
                    <span>Publica tus propias historias</span>
                </div>
                <div class="benefit">
                    <i class="fas fa-check"></i>
                    <span>Conecta con otros lectores</span>
                </div>

            </div>
            
            <div class="cta-buttons">

                <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i> Crear Cuenta Gratis
                </a>

                <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>

            </div>

        </div>

    </div>

</section>

<?php endif; ?>

<!-- Estilos CSS -->
<style>
/* Variables CSS */
:root {
    --primary-color: #4361ee;
    --primary-dark: #3a56d4;
    --secondary-color: #f72585;
    --accent-color: #4cc9f0;
    --success-color: #06ffa5;
    --warning-color: #ffb700;
    --text-dark: #2d3436;
    --text-light: #636e72;
    --bg-light: #f8f9fa;
    --white: #ffffff;
    --shadow: 0 10px 30px rgba(0,0,0,0.1);
    --shadow-hover: 0 20px 40px rgba(0,0,0,0.15);
    --border-radius: 15px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Reset y base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    line-height: 1.6;
    color: var(--text-dark);
    overflow-x: hidden;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Hero Section */
.hero-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
}

.hero-particles {
    position: absolute;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="particles" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="5" cy="5" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23particles)"/></svg>');
    animation: float 20s ease-in-out infinite;
}

.hero-gradient {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(67,97,238,0.8) 0%, rgba(247,37,133,0.6) 100%);
}

.hero-content {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
    color: white;
}

.hero-title {
    font-size: clamp(3rem, 8vw, 5rem);
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 20px;
}

.title-main {
    display: block;
}

.title-accent {
    display: block;
    background: linear-gradient(45deg, #06ffa5, #4cc9f0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 1.25rem;
    margin-bottom: 30px;
    opacity: 0.9;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    gap: 30px;
    margin-bottom: 40px;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: var(--success-color);
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.hero-buttons {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.floating-books {
    position: relative;
    height: 400px;
}

.book {
    position: absolute;
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    animation: float 6s ease-in-out infinite;
}

.book-1 {
    top: 20%;
    left: 20%;
    animation-delay: 0s;
}

.book-2 {
    top: 50%;
    right: 20%;
    animation-delay: 2s;
}

.book-3 {
    bottom: 20%;
    left: 50%;
    animation-delay: 4s;
}

/* Botones */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    border-radius: var(--border-radius);
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.btn-lg {
    padding: 16px 32px;
    font-size: 1.1rem;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 0.9rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    box-shadow: var(--shadow);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

.btn-outline {
    background: transparent;
    border: 2px solid rgba(255,255,255,0.3);
    color: white;
    backdrop-filter: blur(10px);
}

.btn-outline:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.5);
}

/* Secciones */
.section-header {
    margin-bottom: 60px;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 15px;
}

.section-subtitle {
    font-size: 1.2rem;
    color: var(--text-light);
    max-width: 600px;
    margin: 0 auto;
}

/* Explore Section */
.explore-section {
    padding: 100px 0;
    background: var(--bg-light);
}

.content-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 40px;
}

.content-type-card {
    position: relative;
    background: white;
    border-radius: 25px;
    padding: 40px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    overflow: hidden;
}

.content-type-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.card-background {
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    opacity: 0.1;
    transition: var(--transition);
}

.literatura-card .card-background {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.comics-card .card-background {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.card-content {
    position: relative;
    z-index: 2;
}

.card-icon {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    margin-bottom: 25px;
}

.literatura-card .card-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.comics-card .card-icon {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.card-content h3 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: var(--text-dark);
}

.card-content p {
    color: var(--text-light);
    margin-bottom: 20px;
    line-height: 1.6;
}

.card-stats {
    margin-bottom: 25px;
    color: var(--text-light);
    font-weight: 500;
}

/* Featured Section */
.featured-section {
    padding: 100px 0;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 60px;
}

.header-content h2 {
    display: flex;
    align-items: center;
    gap: 15px;
    color: var(--text-dark);
}

.header-content h2 i {
    color: var(--warning-color);
}

.obras-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

.obra-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.obra-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.obra-image {
    height: 300px;
    position: relative;
    overflow: hidden;
}

.obra-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.obra-card:hover .obra-image img {
    transform: scale(1.1);
}

.obra-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.obra-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.8) 100%);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px;
    opacity: 0;
    transition: var(--transition);
}

.obra-card:hover .obra-overlay {
    opacity: 1;
}

.obra-badges {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-libro {
    background: rgba(79, 172, 254, 0.9);
    color: white;
}

.badge-comic {
    background: rgba(247, 112, 154, 0.9);
    color: white;
}

.badge-adulto {
    background: rgba(255, 183, 0, 0.9);
    color: white;
}

.obra-actions {
    display: flex;
    gap: 10px;
}

.obra-content {
    padding: 25px;
}

.obra-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 10px;
    color: var(--text-dark);
    line-height: 1.3;
}

.obra-author {
    color: var(--text-light);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.obra-category {
    display: inline-block;
    background: var(--bg-light);
    color: var(--primary-color);
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.obra-description {
    color: var(--text-light);
    line-height: 1.6;
    margin-bottom: 20px;
}

.obra-stats {
    display: flex;
    gap: 15px;
    font-size: 0.9rem;
    color: var(--text-light);
}

.stat {
    display: flex;
    align-items: center;
    gap: 5px;
}

.stat i {
    color: var(--primary-color);
}

/* Dynamic Content */
.dynamic-content {
    padding: 100px 0;
    background: var(--bg-light);
}

.content-tabs {
    background: white;
    border-radius: 25px;
    padding: 40px;
    box-shadow: var(--shadow);
}

.tab-buttons {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
    border-bottom: 2px solid #f0f0f0;
}

.tab-btn {
    padding: 15px 25px;
    background: none;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-light);
    cursor: pointer;
    transition: var(--transition);
    border-bottom: 3px solid transparent;
    display: flex;
    align-items: center;
    gap: 10px;
}

.tab-btn.active,
.tab-btn:hover {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.obras-carousel {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.mini-card {
    background: var(--bg-light);
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    transition: var(--transition);
}

.mini-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.mini-image {
    width: 80px;
    height: 100px;
    margin: 0 auto 15px;
    border-radius: 10px;
    overflow: hidden;
}

.mini-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mini-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.mini-content h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-dark);
}

.mini-content p {
    font-size: 0.9rem;
    color: var(--text-light);
    margin-bottom: 10px;
}

.mini-type {
    background: var(--primary-color);
    color: white;
    padding: 4px 8px;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: 600;
}

.categorias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.categoria-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: var(--bg-light);
    border-radius: 15px;
    text-decoration: none;
    color: var(--text-dark);
    transition: var(--transition);
}

.categoria-item:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
    background: white;
}

.categoria-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.categoria-info h4 {
    font-weight: 600;
    margin-bottom: 5px;
}

.categoria-count {
    font-size: 0.9rem;
    color: var(--text-light);
}

/* Features Section */
.features-section {
    padding: 100px 0;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: 20px;
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    color: white;
    font-size: 2rem;
}

.feature-card h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: var(--text-dark);
}

.feature-card p {
    color: var(--text-light);
    line-height: 1.6;
    margin-bottom: 20px;
}

.feature-highlight {
    background: var(--bg-light);
    color: var(--primary-color);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    display: inline-block;
}

/* CTA Section */
.cta-section {
    padding: 100px 0;
    position: relative;
    overflow: hidden;
    text-align: center;
    color: white;
}

.cta-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
}

.cta-content {
    position: relative;
    z-index: 2;
}

.cta-icon {
    width: 100px;
    height: 100px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    font-size: 3rem;
    color: var(--success-color);
}

.cta-content h2 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.cta-content > p {
    font-size: 1.3rem;
    margin-bottom: 40px;
    opacity: 0.9;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-benefits {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.benefit {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.benefit i {
    color: var(--success-color);
    font-size: 1.2rem;
}

.cta-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

/* No Content */
.no-content {
    text-align: center;
    padding: 80px 20px;
    color: var(--text-light);
}

.no-content-icon {
    font-size: 4rem;
    color: var(--warning-color);
    margin-bottom: 20px;
}

.no-content h3 {
    font-size: 1.8rem;
    color: var(--text-dark);
    margin-bottom: 15px;
}

/* Animaciones */
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

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

/* Responsive */
@media (max-width: 1024px) {
    .hero-content {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }
    
    .community-content {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }
    
    .section-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .hero-stats {
        flex-direction: column;
        gap: 20px;
    }
    
    .hero-buttons {
        justify-content: center;
    }
    
    .content-types {
        grid-template-columns: 1fr;
    }
    
    .tab-buttons {
        flex-direction: column;
        gap: 10px;
    }
    
    .obras-carousel {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .categorias-grid {
        grid-template-columns: 1fr;
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .community-stats {
        grid-template-columns: 1fr;
    }
    
    .cta-benefits {
        flex-direction: column;
        gap: 20px;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 15px;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .cta-content h2 {
        font-size: 2rem;
    }
    
    .obras-grid {
        grid-template-columns: 1fr;
    }
}

/* Mejoras de accesibilidad */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Focus states */
.btn:focus,
.tab-btn:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Loading states */
.obra-image img {
    background: var(--bg-light);
}

/* Print styles */
@media print {
    .hero-section,
    .cta-section {
        background: white !important;
        color: black !important;
    }
}
</style>

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar AOS
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 100,
        disable: 'mobile'
    });
    
    // Tabs functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.dataset.tab;
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            document.getElementById(`tab-${targetTab}`).classList.add('active');
        });
    });
    
    // Smooth scrolling for anchor links
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
    
    // Parallax effect for hero
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.hero-particles');
        if (parallax) {
            const speed = scrolled * 0.5;
            parallax.style.transform = `translateY(${speed}px)`;
        }
    });
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);
    
    // Observe animated elements
    document.querySelectorAll('.floating-books .book').forEach(book => {
        observer.observe(book);
    });
    
    // Favoritos functionality
    document.querySelectorAll('.btn-favorito').forEach(btn => {
        btn.addEventListener('click', function() {
            const obraId = this.dataset.obraId;
            const tipo = this.dataset.tipo;
            
            if (typeof window.toggleFavorito === 'function') {
                window.toggleFavorito(obraId, tipo, this);
            }
        });
    });
    
    // Loading states for images
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('load', function() {
            this.style.opacity = '1';
        });
        
        img.addEventListener('error', function() {
            this.style.display = 'none';
            const placeholder = this.parentNode.querySelector('.obra-placeholder, .mini-placeholder');
            if (placeholder) {
                placeholder.style.display = 'flex';
            }
        });
    });
    
    // Performance optimization: Lazy load images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Dynamic content loading
    function loadMoreContent(section) {
        // Placeholder for dynamic content loading
        console.log(`Loading more content for ${section}`);
    }
    
    // Search functionality enhancement
    const searchInputs = document.querySelectorAll('input[type="search"]');
    searchInputs.forEach(input => {
        let searchTimeout;
        input.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Implement live search functionality
                console.log('Searching for:', this.value);
            }, 300);
        });
    });
    
    // Theme detection and adaptation
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.body.classList.add('dark-theme-preferred');
    }
    
    // Error handling for failed requests
    window.addEventListener('error', function(e) {
        console.error('Error occurred:', e.error);
    });
    
    // Performance monitoring
    if ('performance' in window) {
        window.addEventListener('load', function() {
            setTimeout(() => {
                const perfData = performance.getEntriesByType('navigation')[0];
                console.log('Page load time:', perfData.loadEventEnd - perfData.loadEventStart);
            }, 0);
        });
    }
});

// Global functions
window.toggleFavorito = function(obraId, tipo, button) {
    // Implement AJAX call to toggle favorite
    fetch(`${BASE_URL}/ajax/toggle_favorito.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            obra_id: obraId,
            tipo: tipo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = button.querySelector('i');
            if (data.favorito) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.classList.add('favorito-activo');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.classList.remove('favorito-activo');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
};

// Service Worker registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('SW registered: ', registration);
            })
            .catch(function(registrationError) {
                console.log('SW registration failed: ', registrationError);
            });
    });
}
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
