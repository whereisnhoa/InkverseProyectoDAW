
<?php
include '../includes/header.php';
include_once('../includes/funciones.php');

// Verificar si hay un usuario logueado para funciones de subida
$usuario_logueado = isset($_SESSION['usuario_id']);

// Obtener categorías de arte
$sql_categorias = "SELECT * FROM categorias_arte ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = [];
if ($result_categorias && $result_categorias->num_rows > 0) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Determinar qué categoría mostrar
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;

// Consulta base para obtener arte
$sql_base = "SELECT ga.*, ca.nombre as categoria_nombre, u.usuario as autor_nombre 
             FROM galeria_arte ga
             JOIN categorias_arte ca ON ga.categoria_id = ca.id
             JOIN usuarios u ON ga.usuario_id = u.id
             WHERE 1=1";

// Filtrar por categoría si se especificó
if ($categoria_id > 0) {
    $sql_base .= " AND ga.categoria_id = $categoria_id";
}

// Filtrar contenido para adultos si el usuario no está logueado o es menor de edad
if (!$usuario_logueado || (isset($_SESSION['edad']) && $_SESSION['edad'] < 18)) {
    $sql_base .= " AND ga.publico_adulto = 0";
}

// Ordenar por fecha de subida (más reciente primero)
$sql_base .= " ORDER BY ga.fecha_subida DESC LIMIT 24";

// Ejecutar consulta
$result_arte = $conexion->query($sql_base);
?>

<div class="container">
    <h1>Galería de Arte</h1>
    <p class="mb-4">Explora ilustraciones, portadas y arte conceptual creado por nuestra comunidad.</p>
    
    <!-- Navegación de categorías -->
    <div class="categorias-strip">
        <div class="categorias-strip-inner">
            <a href="<?php echo BASE_URL; ?>/comunidad/arte.php" class="categoria-pill <?php echo $categoria_id == 0 ? 'active' : ''; ?>">
                Todas las categorías
            </a>
            <?php foreach ($categorias as $cat): ?>
                <a href="<?php echo BASE_URL; ?>/comunidad/arte.php?categoria=<?php echo $cat['id']; ?>" 
                   class="categoria-pill <?php echo $categoria_id == $cat['id'] ? 'active' : ''; ?>">
                    <i class="fas <?php echo htmlspecialchars($cat['icono']); ?>"></i>
                    <?php echo htmlspecialchars($cat['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Sección de subida de arte (solo para usuarios logueados) -->
    <?php if ($usuario_logueado): ?>
        <div class="arte-upload-section">
            <h2>Comparte tu arte</h2>
            <p>Sube tus creaciones artísticas relacionadas con la literatura y los cómics.</p>
            
            <a href="<?php echo BASE_URL; ?>/comunidad/subir_arte.php" class="btn">
                <i class="fas fa-cloud-upload-alt"></i> Subir nueva obra
            </a>
        </div>
    <?php else: ?>
        <div class="text-center mt-3 mb-4">
            <p>Necesitas iniciar sesión para compartir tu arte.</p>
            <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn">Iniciar sesión</a>
        </div>
    <?php endif; ?>
    
    <!-- Galería de imágenes -->
    <div class="galeria-grid">
        <?php if ($result_arte && $result_arte->num_rows > 0): ?>
            <?php while ($arte = $result_arte->fetch_assoc()): ?>
                <div class="galeria-item">
                    <a href="<?php echo BASE_URL; ?>/comunidad/ver_arte.php?id=<?php echo $arte['id']; ?>" class="galeria-link">
                        <div class="galeria-imagen">
                            <?php if (!empty($arte['thumbnail'])): ?>
                                <img src="<?php echo htmlspecialchars($arte['thumbnail']); ?>" alt="<?php echo htmlspecialchars($arte['titulo']); ?>">
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars($arte['imagen']); ?>" alt="<?php echo htmlspecialchars($arte['titulo']); ?>">
                            <?php endif; ?>
                            
                            <?php if ($arte['publico_adulto']): ?>
                                <span class="badge-adulto">+18</span>
                            <?php endif; ?>
                        </div>
                        <div class="galeria-info">
                            <h3><?php echo htmlspecialchars($arte['titulo']); ?></h3>
                            <p class="galeria-autor">Por: <?php echo htmlspecialchars($arte['autor_nombre']); ?></p>
                            <div class="galeria-meta">
                                <span><i class="fas fa-heart"></i> <?php echo $arte['me_gusta']; ?></span>
                                <span><i class="fas <?php echo htmlspecialchars($categorias[$arte['categoria_id']-1]['icono']); ?>"></i> <?php echo htmlspecialchars($arte['categoria_nombre']); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-palette fa-3x"></i>
                <h2>No hay obras de arte disponibles</h2>
                <?php if ($categoria_id > 0): ?>
                    <p>No se encontraron obras en esta categoría.</p>
                    <a href="<?php echo BASE_URL; ?>/comunidad/arte.php" class="btn mt-3">Ver todas las categorías</a>
                <?php else: ?>
                    <p>Sé el primero en compartir tu arte con la comunidad.</p>
                    <?php if ($usuario_logueado): ?>
                        <a href="<?php echo BASE_URL; ?>/comunidad/subir_arte.php" class="btn mt-3">Subir arte</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn mt-3">Iniciar sesión para subir arte</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
