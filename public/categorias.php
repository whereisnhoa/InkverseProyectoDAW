
<?php
/**
 * Página de Categorías
 * Muestra todas las categorías disponibles para libros y cómics
 */

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Obtener categorías de libros
$sql_categorias_libros = "SELECT id, nombre, descripcion FROM categorias WHERE tipo = 'libro' ORDER BY nombre";
$result_categorias_libros = $conexion->query($sql_categorias_libros);

// Obtener categorías de cómics
$sql_categorias_comics = "SELECT id, nombre, descripcion FROM categorias WHERE tipo = 'comic' ORDER BY nombre";
$result_categorias_comics = $conexion->query($sql_categorias_comics);
?>

<div class="container">
    <div class="categorias-header">
        <h1>Explorar por Categorías</h1>
        <p class="categorias-description">Descubre contenido organizado por géneros y temáticas</p>
    </div>
    
    <!-- Categorías de Literatura -->
    <section class="categorias-section">
        <div class="section-header">
            <h2>Literatura</h2>
            <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="btn btn-sm btn-outline">Ver todas las obras</a>
        </div>
        
        <div class="categorias-grid">
            <?php if ($result_categorias_libros && $result_categorias_libros->num_rows > 0): ?>
                <?php while ($categoria = $result_categorias_libros->fetch_assoc()): ?>
                    <div class="categoria-card literatura-categoria">
                        <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                        <p><?php echo !empty($categoria['descripcion']) ? htmlspecialchars($categoria['descripcion']) : 'Explora obras de este género'; ?></p>
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=libro" class="btn btn-outline">Explorar</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay categorías de literatura disponibles.</p>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Categorías de Cómics -->
    <section class="categorias-section">
        <div class="section-header">
            <h2>Cómics y Manga</h2>
            <a href="<?php echo BASE_URL; ?>/public/comics.php" class="btn btn-sm btn-outline">Ver todas las obras</a>
        </div>
        
        <div class="categorias-grid">
            <?php if ($result_categorias_comics && $result_categorias_comics->num_rows > 0): ?>
                <?php while ($categoria = $result_categorias_comics->fetch_assoc()): ?>
                    <div class="categoria-card comics-categoria">
                        <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                        <p><?php echo !empty($categoria['descripcion']) ? htmlspecialchars($categoria['descripcion']) : 'Explora obras de este género'; ?></p>
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=comic" class="btn btn-outline">Explorar</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay categorías de cómics disponibles.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
