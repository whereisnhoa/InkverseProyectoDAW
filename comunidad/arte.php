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

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">

<div class="container arte-container">
    <h1 class="text-center mb-2">Galería de Arte</h1>
    <p class="mb-4 text-center">Explora ilustraciones, portadas y arte conceptual creado por nuestra comunidad. Inspírate y comparte tus propias obras.</p>
    
    <!-- Navegación de categorías -->
    <nav class="categorias-strip mb-4" aria-label="Navegación de categorías">
        <div class="categorias-strip-inner">
            <a href="<?php echo BASE_URL; ?>/comunidad/arte.php" class="categoria-pill <?php echo $categoria_id == 0 ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i> Todas las categorías
            </a>
            <?php foreach ($categorias as $cat): ?>
                <a href="<?php echo BASE_URL; ?>/comunidad/arte.php?categoria=<?php echo $cat['id']; ?>" 
                   class="categoria-pill <?php echo $categoria_id == $cat['id'] ? 'active' : ''; ?>">
                    <i class="fas <?php echo htmlspecialchars($cat['icono']); ?>" aria-hidden="true"></i>
                    <?php echo htmlspecialchars($cat['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
    
    <!-- Sección de subida de arte (solo para usuarios logueados) -->
    <?php if ($usuario_logueado): ?>
        <section class="arte-upload-section mb-4">
            <div class="card card-upload text-center">
                <h2>Comparte tu arte</h2>
                <p>Sube tus creaciones artísticas relacionadas con la literatura y los cómics.</p>
                <a href="<?php echo BASE_URL; ?>/comunidad/subir_arte.php" class="btn btn-primary">
                    <i class="fas fa-cloud-upload-alt"></i> Subir nueva obra
                </a>
            </div>
        </section>
    <?php else: ?>
        <div class="alert alert-info text-center mt-3 mb-4" role="alert">
            <p>Necesitas iniciar sesión para compartir tu arte.</p>
            <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-outline-primary">Iniciar sesión</a>
        </div>
    <?php endif; ?>
    
    <!-- Galería de imágenes -->
    <section class="galeria-grid-section">
        <?php if ($result_arte && $result_arte->num_rows > 0): ?>
            <div class="galeria-grid">
                <?php while ($arte = $result_arte->fetch_assoc()): ?>
                    <article class="galeria-item" tabindex="0" aria-label="Obra: <?php echo htmlspecialchars($arte['titulo']); ?>">
                        <a href="<?php echo BASE_URL; ?>/comunidad/ver_arte.php?id=<?php echo $arte['id']; ?>" class="galeria-link">
                            <figure class="galeria-imagen">
                                <?php if (!empty($arte['thumbnail'])): ?>
                                    <img src="<?php echo htmlspecialchars($arte['thumbnail']); ?>" alt="Miniatura de <?php echo htmlspecialchars($arte['titulo']); ?>">
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($arte['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($arte['titulo']); ?>">
                                <?php endif; ?>
                                <?php if ($arte['publico_adulto']): ?>
                                    <span class="badge badge-adulto" title="Contenido +18">+18</span>
                                <?php endif; ?>
                            </figure>
                            <div class="galeria-info">
                                <h3 class="galeria-titulo"><?php echo htmlspecialchars($arte['titulo']); ?></h3>
                                <p class="galeria-autor">Por: <?php echo htmlspecialchars($arte['autor_nombre']); ?></p>
                                <div class="galeria-meta">
                                    <span title="Me gusta"><i class="fas fa-heart"></i> <?php echo (int)$arte['me_gusta']; ?></span>
                                    <span title="Categoría"><i class="fas <?php echo htmlspecialchars($categorias[$arte['categoria_id']-1]['icono'] ?? 'fa-tag'); ?>"></i> <?php echo htmlspecialchars($arte['categoria_nombre']); ?></span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results text-center my-5">
                <i class="fas fa-palette fa-3x mb-3"></i>
                <h2>No hay obras de arte disponibles</h2>
                <?php if ($categoria_id > 0): ?>
                    <p>No se encontraron obras en esta categoría.</p>
                    <a href="<?php echo BASE_URL; ?>/comunidad/arte.php" class="btn btn-outline-secondary mt-3">Ver todas las categorías</a>
                <?php else: ?>
                    <p>Sé el primero en compartir tu arte con la comunidad.</p>
                    <?php if ($usuario_logueado): ?>
                        <a href="<?php echo BASE_URL; ?>/comunidad/subir_arte.php" class="btn btn-primary mt-3">Subir arte</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-outline-primary mt-3">Iniciar sesión para subir arte</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include '../includes/footer.php'; ?>