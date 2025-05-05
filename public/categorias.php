
<?php
include '../includes/header.php';
include '../includes/conexion.php';

// Obtener todas las categorías
$sql = "SELECT c.id, c.nombre, COUNT(l.id) as total_libros
        FROM categorias c
        LEFT JOIN libros l ON c.id = l.categoria_id
        GROUP BY c.id
        ORDER BY c.nombre";
$result = $conexion->query($sql);
?>

<div class="container">
    <h1>Categorías</h1>
    <p class="mb-4">Explora nuestras categorías literarias y encuentra tu próxima lectura favorita.</p>
    
    <div class="categorias-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($categoria = $result->fetch_assoc()): ?>
                <div class="categoria-card">
                    <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                    <p><?php echo $categoria['total_libros']; ?> obras</p>
                    <a href="/public/categoria.php?nombre=<?php echo urlencode($categoria['nombre']); ?>" class="btn btn-outline">Explorar</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay categorías disponibles.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>