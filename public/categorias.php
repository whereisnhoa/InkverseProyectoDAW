<?php
/**
 * Página de Categorías Mejorada
 * Muestra todas las categorías disponibles para libros y cómics con diseño moderno
 */

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Obtener categorías de libros con conteo de obras
$sql_categorias_libros = "SELECT c.id, c.nombre, c.descripcion, 
                          COUNT(l.id) as total_obras,
                          (SELECT l2.portada FROM libros l2 WHERE l2.categoria_id = c.id AND l2.portada IS NOT NULL LIMIT 1) as portada_ejemplo
                          FROM categorias c 
                          LEFT JOIN libros l ON c.id = l.categoria_id 
                          WHERE c.tipo = 'libro' 
                          GROUP BY c.id, c.nombre, c.descripcion 
                          ORDER BY total_obras DESC, c.nombre";
$result_categorias_libros = $conexion->query($sql_categorias_libros);

// Obtener categorías de cómics con conteo de obras
$sql_categorias_comics = "SELECT c.id, c.nombre, c.descripcion, 
                          COUNT(co.id) as total_obras,
                          (SELECT co2.portada FROM comics co2 WHERE co2.categoria_id = c.id AND co2.portada IS NOT NULL LIMIT 1) as portada_ejemplo
                          FROM categorias c 
                          LEFT JOIN comics co ON c.id = co.categoria_id 
                          WHERE c.tipo = 'comic' 
                          GROUP BY c.id, c.nombre, c.descripcion 
                          ORDER BY total_obras DESC, c.nombre";
$result_categorias_comics = $conexion->query($sql_categorias_comics);

// Obtener estadísticas generales
$sql_stats = "SELECT 
              (SELECT COUNT(*) FROM libros) as total_libros,
              (SELECT COUNT(*) FROM comics) as total_comics,
              (SELECT COUNT(*) FROM categorias WHERE tipo = 'libro') as total_cat_libros,
              (SELECT COUNT(*) FROM categorias WHERE tipo = 'comic') as total_cat_comics";
$result_stats = $conexion->query($sql_stats);
$stats = $result_stats->fetch_assoc();
?>

<!-- Hero Section -->
<div class="categorias-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <h1>Explorar por Categorías</h1>
            <p class="hero-description">Descubre contenido organizado por géneros y temáticas que más te gusten</p>
            
            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_libros']); ?></div>
                    <div class="stat-label">Libros</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total_comics']); ?></div>
                    <div class="stat-label">Cómics</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_cat_libros']; ?></div>
                    <div class="stat-label">Categorías de Literatura</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_cat_comics']; ?></div>
                    <div class="stat-label">Categorías de Cómics</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Navegación rápida -->
    <div class="quick-nav">
        <a href="#literatura" class="quick-nav-item">
            <i class="fas fa-book"></i>
            <span>Literatura</span>
        </a>
        <a href="#comics" class="quick-nav-item">
            <i class="fas fa-book-open"></i>
            <span>Cómics</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="quick-nav-item">
            <i class="fas fa-compass"></i>
            <span>Explorar Todo</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/public/aleatorio.php" class="quick-nav-item">
            <i class="fas fa-random"></i>
            <span>Sorpréndeme</span>
        </a>
    </div>

    <!-- Categorías de Literatura -->
    <section id="literatura" class="categorias-section">
        <div class="section-header">
            <div class="section-title">
                <h2><i class="fas fa-book"></i> Literatura</h2>
                <p>Explora novelas, poesía, ensayos y más</p>
            </div>
            <div class="section-actions">
                <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Ver todas las obras
                </a>
            </div>
        </div>
        
        <div class="categorias-grid">
            <?php if ($result_categorias_libros && $result_categorias_libros->num_rows > 0): ?>
                <?php while ($categoria = $result_categorias_libros->fetch_assoc()): ?>
                    <div class="categoria-card literatura-card" data-aos="fade-up">
                        <div class="categoria-image">
                            <?php if (!empty($categoria['portada_ejemplo'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($categoria['portada_ejemplo']); ?>" 
                                     alt="<?php echo htmlspecialchars($categoria['nombre']); ?>">
                            <?php else: ?>
                                <div class="categoria-placeholder">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                            <div class="categoria-overlay">
                                <div class="categoria-count">
                                    <?php echo $categoria['total_obras']; ?> obras
                                </div>
                            </div>
                        </div>
                        
                        <div class="categoria-content">
                            <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                            <p class="categoria-description">
                                <?php echo !empty($categoria['descripcion']) ? 
                                    htmlspecialchars($categoria['descripcion']) : 
                                    'Explora obras de este género literario'; ?>
                            </p>
                            
                            <div class="categoria-stats">
                                <span class="stat-item">
                                    <i class="fas fa-book"></i>
                                    <?php echo $categoria['total_obras']; ?> obras
                                </span>
                            </div>
                        </div>
                        
                        <div class="categoria-actions">
                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=libro" 
                               class="btn btn-outline btn-block">
                                <i class="fas fa-eye"></i> Explorar
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-categories">
                    <i class="fas fa-book"></i>
                    <h3>No hay categorías de literatura</h3>
                    <p>Aún no se han creado categorías para libros.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Categorías de Cómics -->
    <section id="comics" class="categorias-section">
        <div class="section-header">
            <div class="section-title">
                <h2><i class="fas fa-book-open"></i> Cómics y Manga</h2>
                <p>Sumérgete en historias visuales</p>
            </div>
            <div class="section-actions">
                <a href="<?php echo BASE_URL; ?>/public/comics.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Ver todas las obras
                </a>
            </div>
        </div>
        
        <div class="categorias-grid">
            <?php if ($result_categorias_comics && $result_categorias_comics->num_rows > 0): ?>
                <?php while ($categoria = $result_categorias_comics->fetch_assoc()): ?>
                    <div class="categoria-card comics-card" data-aos="fade-up">
                        <div class="categoria-image">
                            <?php if (!empty($categoria['portada_ejemplo'])): ?>
                                <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($categoria['portada_ejemplo']); ?>" 
                                     alt="<?php echo htmlspecialchars($categoria['nombre']); ?>">
                            <?php else: ?>
                                <div class="categoria-placeholder">
                                    <i class="fas fa-book-open"></i>
                                </div>
                            <?php endif; ?>
                            <div class="categoria-overlay">
                                <div class="categoria-count">
                                    <?php echo $categoria['total_obras']; ?> obras
                                </div>
                            </div>
                        </div>
                        
                        <div class="categoria-content">
                            <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                            <p class="categoria-description">
                                <?php echo !empty($categoria['descripcion']) ? 
                                    htmlspecialchars($categoria['descripcion']) : 
                                    'Explora obras de este género visual'; ?>
                            </p>
                            
                            <div class="categoria-stats">
                                <span class="stat-item">
                                    <i class="fas fa-book-open"></i>
                                    <?php echo $categoria['total_obras']; ?> obras
                                </span>
                            </div>
                        </div>
                        
                        <div class="categoria-actions">
                            <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=comic" 
                               class="btn btn-outline btn-block">
                                <i class="fas fa-eye"></i> Explorar
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-categories">
                    <i class="fas fa-book-open"></i>
                    <h3>No hay categorías de cómics</h3>
                    <p>Aún no se han creado categorías para cómics.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Sección de acciones adicionales -->
    <section class="additional-actions">
        <div class="action-cards">
            <div class="action-card" data-aos="fade-up">
                <div class="action-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Búsqueda Avanzada</h3>
                <p>Encuentra exactamente lo que buscas con filtros detallados</p>
                <a href="<?php echo BASE_URL; ?>/public/buscar.php" class="btn btn-outline">
                    <i class="fas fa-search"></i> Buscar
                </a>
            </div>
            
            <div class="action-card" data-aos="fade-up" data-aos-delay="100">
                <div class="action-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Mis Favoritos</h3>
                <p>Accede rápidamente a tus obras guardadas</p>
                <a href="<?php echo BASE_URL; ?>/public/favoritos.php" class="btn btn-outline">
                    <i class="fas fa-heart"></i> Ver Favoritos
                </a>
            </div>
            
            <div class="action-card" data-aos="fade-up" data-aos-delay="200">
                <div class="action-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <h3>Subir Obra</h3>
                <p>Comparte tu contenido con la comunidad</p>
                <a href="<?php echo BASE_URL; ?>/public/nueva_obra.php" class="btn btn-outline">
                    <i class="fas fa-plus"></i> Subir
                </a>
            </div>
        </div>
    </section>
</div>

<!-- Estilos CSS -->
<style>
/* Hero Section */
.categorias-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.categorias-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-icon {
    font-size: 60px;
    margin-bottom: 20px;
    opacity: 0.9;
}

.categorias-hero h1 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 15px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.hero-description {
    font-size: 20px;
    margin-bottom: 40px;
    opacity: 0.9;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

/* Estadísticas */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 30px;
    max-width: 800px;
    margin: 0 auto;
}

.stat-card {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 25px 20px;
    text-align: center;
    border: 1px solid rgba(255,255,255,0.2);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    opacity: 0.8;
    font-weight: 500;
}

/* Navegación rápida */
.quick-nav {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 40px 0;
    flex-wrap: wrap;
}

.quick-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
    min-width: 120px;
}

.quick-nav-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
    color: #4361ee;
}

.quick-nav-item i {
    font-size: 24px;
    color: #4361ee;
}

.quick-nav-item span {
    font-weight: 600;
    font-size: 14px;
}

/* Secciones */
.categorias-section {
    margin: 60px 0;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.section-title h2 {
    font-size: 32px;
    color: #333;
    margin: 0 0 5px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.section-title h2 i {
    color: #4361ee;
}

.section-title p {
    color: #666;
    margin: 0;
    font-size: 16px;
}

/* Grid de categorías */
.categorias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}

.categoria-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
}

.categoria-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.categoria-image {
    height: 200px;
    position: relative;
    overflow: hidden;
}

.categoria-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.categoria-card:hover .categoria-image img {
    transform: scale(1.1);
}

.categoria-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 60px;
}

.literatura-card .categoria-placeholder {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.comics-card .categoria-placeholder {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.categoria-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
    display: flex;
    align-items: flex-end;
    padding: 20px;
}

.categoria-count {
    background: rgba(255,255,255,0.9);
    color: #333;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.categoria-content {
    padding: 25px;
}

.categoria-content h3 {
    font-size: 22px;
    color: #333;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.categoria-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 15px;
}

.categoria-stats {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #666;
    font-size: 14px;
    font-weight: 500;
}

.stat-item i {
    color: #4361ee;
}

.categoria-actions {
    padding: 0 25px 25px;
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

.btn-block {
    width: 100%;
}

/* Sin categorías */
.no-categories {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-categories i {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.no-categories h3 {
    font-size: 24px;
    margin-bottom: 10px;
    color: #333;
}

/* Acciones adicionales */
.additional-actions {
    margin: 80px 0;
    padding: 60px 0;
    background: #f8f9fa;
    border-radius: 20px;
}

.action-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    max-width: 900px;
    margin: 0 auto;
    padding: 0 30px;
}

.action-card {
    background: white;
    padding: 40px 30px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.action-card:hover {
    transform: translateY(-10px);
}

.action-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #4361ee, #3a56d4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 32px;
}

.action-card h3 {
    font-size: 20px;
    color: #333;
    margin-bottom: 15px;
}

.action-card p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 25px;
}

/* Responsive */
@media (max-width: 768px) {
    .categorias-hero {
        padding: 60px 0;
    }
    
    .categorias-hero h1 {
        font-size: 36px;
    }
    
    .hero-description {
        font-size: 18px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
    
    .categorias-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-nav {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .action-cards {
        grid-template-columns: 1fr;
        padding: 0 20px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-nav {
        flex-direction: column;
        align-items: center;
    }
    
    .quick-nav-item {
        width: 100%;
        max-width: 200px;
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
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
