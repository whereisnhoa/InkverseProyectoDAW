
<?php
require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Obtener ID de la categoría
$categoria_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($categoria_id <= 0) {
    header("Location: " . BASE_URL . "/public/categorias.php");
    exit();
}

// Obtener información de la categoría
$sql_categoria = "SELECT id, nombre FROM categorias WHERE id = ?";
$stmt_categoria = $conexion->prepare($sql_categoria);
$stmt_categoria->bind_param("i", $categoria_id);
$stmt_categoria->execute();
$categoria = $stmt_categoria->get_result()->fetch_assoc();

if (!$categoria) {
    echo '<div class="alerta alerta-error">Categoría no encontrada.</div>';
    include_once(__DIR__ . '/../includes/footer.php');
    exit();
}

// Obtener libros de esta categoría
$sql_libros = "SELECT l.id, l.titulo, l.autor, l.descripcion, l.portada, l.publico_adulto, l.fecha_subida
               FROM libros l
               WHERE l.categoria_id = ?
               ORDER BY l.fecha_subida DESC";
$stmt_libros = $conexion->prepare($sql_libros);
$stmt_libros->bind_param("i", $categoria_id);
$stmt_libros->execute();
$libros = $stmt_libros->get_result();
$total_libros = $libros->num_rows;
?>

<div class="container">
    <div class="categoria-header">
        <h1><?php echo htmlspecialchars($categoria['nombre']); ?></h1>
        <span class="badge badge-count"><?php echo $total_libros; ?> obras</span>
    </div>
    
    <?php if ($total_libros > 0): ?>
        <div class="obra-grid mt-4">
            <?php while ($libro = $libros->fetch_assoc()): ?>
                <div class="obra-card">
                    <div class="obra-card-header">
                        <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                        <?php if ($libro['publico_adulto']): ?>
                            <span class="badge badge-adulto">+18</span>
                        <?php endif; ?>
                    </div>
                    <div class="obra-card-body">
                        <?php if (!empty($libro['portada'])): ?>
                            <img src="<?php echo htmlspecialchars($libro['portada']); ?>" alt="Portada" class="obra-portada">
                        <?php endif; ?>
                        <p class="obra-autor">Por: <?php echo htmlspecialchars($libro['autor']); ?></p>
                        <p class="obra-descripcion"><?php echo substr(htmlspecialchars($libro['descripcion']), 0, 150) . '...'; ?></p>
                    </div>
                    <div class="obra-card-footer">
                        <span class="obra-fecha"><?php echo date('d/m/Y', strtotime($libro['fecha_subida'])); ?></span>
                        <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $libro['id']; ?>&tipo=libro" class="btn btn-outline">Ver más</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <i class="fas fa-book fa-3x"></i>
            <h2>No hay obras en esta categoría</h2>
            <p>Sé el primero en publicar una obra en esta categoría.</p>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn mt-3">Crear nueva obra</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>/public/categorias.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Volver a categorías
        </a>
    </div>
</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
