
<?php
require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Obtener obras recientes (libros y cómics)
$sql_recientes = "
    (SELECT 'libro' as tipo, l.id, l.titulo, l.autor, l.descripcion, l.portada, l.publico_adulto, 
     c.nombre as categoria, l.fecha_subida
     FROM libros l
     LEFT JOIN categorias c ON l.categoria_id = c.id
     ORDER BY l.fecha_subida DESC
     LIMIT 6)
    UNION
    (SELECT 'comic' as tipo, c.id, c.titulo, c.autor, c.descripcion, c.portada, c.publico_adulto, 
     NULL as categoria, c.fecha_subida
     FROM comics c
     ORDER BY c.fecha_subida DESC
     LIMIT 6)
    ORDER BY fecha_subida DESC
    LIMIT 12";

$result_recientes = $conexion->query($sql_recientes);

// Obtener categorías populares
$sql_categorias = "
    SELECT c.id, c.nombre, COUNT(l.id) as total_libros
    FROM categorias c
    LEFT JOIN libros l ON c.id = l.categoria_id
    GROUP BY c.id
    ORDER BY total_libros DESC
    LIMIT 6";

$result_categorias = $conexion->query($sql_categorias);
?>

<div class="container">
    <div class="explorar-header">
        <h1>Explorar</h1>
        <p>Descubre nuevas obras, autores y géneros para ampliar tus horizontes literarios.</p>
    </div>
    
    <section class="explorar-section">
        <div class="section-header">
            <h2>Obras Recientes</h2>
            <a href="<?php echo BASE_URL; ?>/public/buscar.php" class="btn btn-sm btn-outline">Ver todas</a>
        </div>
        
        <div class="obra-grid">
            <?php if ($result_recientes && $result_recientes->num_rows > 0): ?>
                <?php while ($obra = $result_recientes->fetch_assoc()): ?>
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
                            <?php endif; ?>
                            <p class="obra-autor">Por: <?php echo htmlspecialchars($obra['autor']); ?></p>
                            <p class="obra-descripcion"><?php echo substr(htmlspecialchars($obra['descripcion']), 0, 150) . '...'; ?></p>
                        </div>
                        <div class="obra-card-footer">
                            <span class="obra-fecha"><?php echo date('d/m/Y', strtotime($obra['fecha_subida'])); ?></span>
                            <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra['id']; ?>&tipo=<?php echo $obra['tipo']; ?>" class="btn btn-outline">Ver más</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay obras disponibles.</p>
            <?php endif; ?>
        </div>
    </section>
    
    <section class="explorar-section">
        <div class="section-header">
            <h2>Categorías Populares</h2>
            <a href="<?php echo BASE_URL; ?>/public/categorias.php" class="btn btn-sm btn-outline">Ver todas</a>
        </div>
        
        <div class="categorias-grid">
            <?php if ($result_categorias && $result_categorias->num_rows > 0): ?>
                <?php while($categoria = $result_categorias->fetch_assoc()): ?>
                    <div class="categoria-card">
                        <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                        <p><?php echo $categoria['total_libros']; ?> obras</p>
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>" class="btn btn-outline">Explorar</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay categorías disponibles.</p>
            <?php endif; ?>
        </div>
    </section>
    
    <section class="explorar-section">
        <div class="section-header">
            <h2>Comunidad</h2>
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
</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
