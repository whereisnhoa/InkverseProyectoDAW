<?php
/**
 * Página de inicio
 * Esta es la página principal del sitio que muestra contenido destacado y funcionalidades
 */

// Incluir el header
require_once(__DIR__ . '/../includes/header.php');

// Obtener obras destacadas (mezcla de libros y cómics)
$sql_destacados = "
    (SELECT 'libro' as tipo, l.id, l.titulo, l.autor, l.descripcion, l.portada, l.publico_adulto, 
     c.nombre as categoria, l.fecha_subida
     FROM libros l
     LEFT JOIN categorias c ON l.categoria_id = c.id
     ORDER BY l.fecha_subida DESC
     LIMIT 3)
    UNION
    (SELECT 'comic' as tipo, c.id, c.titulo, c.autor, c.descripcion, c.portada, c.publico_adulto, 
     NULL as categoria, c.fecha_subida
     FROM comics c
     ORDER BY c.fecha_subida DESC
     LIMIT 3)
    ORDER BY fecha_subida DESC
    LIMIT 6";

$result_destacados = $conexion->query($sql_destacados);

// Obtener categorías populares
$sql_categorias = "SELECT id, nombre FROM categorias ORDER BY nombre LIMIT 8";
$result_categorias = $conexion->query($sql_categorias);
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1>Explora mundos, crea historias</h1>
        <p class="hero-subtitle">Tu plataforma para descubrir y compartir obras literarias y cómics.</p>
        
        <?php if (!isset($_SESSION['usuario_id'])): ?>
            <div class="hero-buttons">
                <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-lg">Únete Gratis</a>
                <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-outline btn-lg">Explorar</a>
            </div>
        <?php else: ?>
            <div class="hero-buttons">
                <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn btn-lg">Publicar Obra</a>
                <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-outline btn-lg">Explorar</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Sección de Explorar Contenido -->
<section class="explore-content-section">
    <div class="container">
        <h2 class="text-center mb-4">Explora Nuestro Contenido</h2>
        
        <div class="content-type-cards">
            <div class="content-type-card literature-card">
                <div class="content-type-overlay"></div>
                <div class="content-type-content">
                    <div class="content-type-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Literatura</h3>
                    <p>Explora novelas, poesía, ensayos y todo tipo de obras literarias</p>
                    <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="btn btn-lg">Descubrir Literatura</a>
                </div>
            </div>
            
            <div class="content-type-card comics-card">
                <div class="content-type-overlay"></div>
                <div class="content-type-content">
                    <div class="content-type-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Cómics y Manga</h3>
                    <p>Sumérgete en el mundo visual de cómics, manga y novelas gráficas</p>
                    <a href="<?php echo BASE_URL; ?>/public/comics.php" class="btn btn-lg">Explorar Cómics</a>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <!-- Sección de Obras Destacadas -->
    <section class="home-section">
        <div class="section-header">
            <h2>Obras Destacadas</h2>
            <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-sm btn-outline">Ver más</a>
        </div>
        
        <div class="obra-grid">
            <?php if ($result_destacados && $result_destacados->num_rows > 0): ?>
                <?php while ($obra = $result_destacados->fetch_assoc()): ?>
                    <div class="obra-card">
                        <div class="obra-card-header">
                            <h3><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                            <div class="flex gap-1 mt-1">
                                <?php if ($obra['tipo'] == 'libro' && !empty($obra['categoria'])): ?>
                                    <span class="badge badge-categoria"><?php echo htmlspecialchars($obra['categoria']); ?></span>
                                <?php endif; ?>
                                <span class="badge badge-tipo"><?php echo $obra['tipo'] == 'libro' ? 'Libro' : 'Cómic'; ?></span>
                                <?php if ($obra['publico_adulto']): ?>
                                    <span class="badge badge-adulto">+18</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="obra-card-body">
                            <?php if (!empty($obra['portada'])): ?>
                                <img src="<?php echo htmlspecialchars($obra['portada']); ?>" alt="Portada" class="obra-portada">
                            <?php else: ?>
                                <div class="obra-portada-placeholder">
                                    <i class="fas <?php echo $obra['tipo'] == 'libro' ? 'fa-book' : 'fa-book-open'; ?> fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            <p class="obra-autor">Por: <?php echo htmlspecialchars($obra['autor']); ?></p>
                            <p class="obra-descripcion"><?php echo substr(htmlspecialchars($obra['descripcion']), 0, 100) . '...'; ?></p>
                        </div>
                        <div class="obra-card-footer">
                            <span class="obra-fecha"><?php echo date('d/m/Y', strtotime($obra['fecha_subida'])); ?></span>
                            <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra['id']; ?>&tipo=<?php echo $obra['tipo']; ?>" class="btn btn-outline">Ver más</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-book fa-3x"></i>
                    <h3>No hay obras disponibles</h3>
                    <p>¡Sé el primero en publicar una obra!</p>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn mt-3">Publicar Obra</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn mt-3">Registrarse</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Sección de Categorías -->
    <section class="home-section">
        <div class="section-header">
            <h2>Explora por Categorías</h2>
            <a href="<?php echo BASE_URL; ?>/public/categorias.php" class="btn btn-sm btn-outline">Ver todas</a>
        </div>
        
        <div class="categorias-home">
            <?php if ($result_categorias && $result_categorias->num_rows > 0): ?>
                <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
                    <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>" class="categoria-home-item">
                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay categorías disponibles.</p>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Sección de Características -->
    <section class="home-section">
        <h2 class="text-center">¿Por qué unirte a nuestra comunidad?</h2>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3>Comparte tus Obras</h3>
                <p>Publica tus libros, cómics y manga para que otros usuarios puedan descubrirlos y disfrutarlos.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Comunidad Activa</h3>
                <p>Conecta con otros autores y lectores, participa en foros de discusión y recibe feedback sobre tus creaciones.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h3>Galería de Arte</h3>
                <p>Explora ilustraciones y arte conceptual relacionado con literatura y cómics, o comparte tus propias creaciones.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Foros de Discusión</h3>
                <p>Participa en conversaciones sobre tus obras favoritas, técnicas de escritura y más.</p>
            </div>
        </div>
    </section>
    
    <!-- Sección de Comunidad -->
    <section class="home-section">
        <div class="section-header">
            <h2>Explora Nuestra Comunidad</h2>
            <a href="<?php echo BASE_URL; ?>/comunidad/foros.php" class="btn btn-sm btn-outline">Ver más</a>
        </div>
        
        <div class="comunidad-preview">
            <div class="comunidad-card">
                <div class="comunidad-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="comunidad-content">
                    <h3>Foros de Discusión</h3>
                    <p>Participa en conversaciones sobre literatura, comparte opiniones y conecta con otros lectores.</p>
                    <a href="<?php echo BASE_URL; ?>/comunidad/foros.php" class="btn btn-sm">Visitar foros</a>
                </div>
            </div>
            
            <div class="comunidad-card">
                <div class="comunidad-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <div class="comunidad-content">
                    <h3>Galería de Arte</h3>
                    <p>Explora ilustraciones, portadas y arte conceptual creado por nuestra comunidad.</p>
                    <a href="<?php echo BASE_URL; ?>/comunidad/arte.php" class="btn btn-sm">Ver galería</a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Sección de CTA -->
    <?php if (!isset($_SESSION['usuario_id'])): ?>
        <section class="cta-section">
            <div class="cta-content">
                <h2>¿Listo para compartir tus creaciones?</h2>
                <p>Únete a nuestra comunidad y comienza a compartir tus obras con lectores de todo el mundo.</p>
                <div class="cta-buttons">
                    <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn">Registrarse</a>
                    <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-outline">Iniciar Sesión</a>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php
// Incluir el footer
require_once(__DIR__ . '/../includes/footer.php');
?>
