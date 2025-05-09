
<?php
/**
 * Página de Literatura
 * Muestra todas las obras de tipo libro con recomendaciones y secciones específicas
 */

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Determinar qué sección mostrar
$mostrar_destacados = isset($_GET['destacados']) && $_GET['destacados'] == 1;
$mostrar_recientes = isset($_GET['recientes']) && $_GET['recientes'] == 1;
$mostrar_populares = isset($_GET['populares']) && $_GET['populares'] == 1;

// Título de la página según la sección
$titulo_pagina = "Literatura";
if ($mostrar_destacados) {
    $titulo_pagina .= " - Destacados";
} elseif ($mostrar_recientes) {
    $titulo_pagina .= " - Recientes";
} elseif ($mostrar_populares) {
    $titulo_pagina .= " - Populares";
}

// Obtener categorías de libros
$sql_categorias = "SELECT id, nombre FROM categorias WHERE tipo = 'libro' ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = [];
if ($result_categorias && $result_categorias->num_rows > 0) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Consulta base para libros
$sql_base = "SELECT l.id, l.titulo, l.autor, l.descripcion, l.portada, l.publico_adulto, 
             c.nombre as categoria, l.fecha_subida
             FROM libros l
             LEFT JOIN categorias c ON l.categoria_id = c.id
             WHERE 1=1";

// Modificar consulta según la sección
if ($mostrar_destacados) {
    $sql_base .= " AND l.destacado = 1";
    $orden = "l.fecha_destacado DESC";
} elseif ($mostrar_recientes) {
    $orden = "l.fecha_subida DESC";
} elseif ($mostrar_populares) {
    $orden = "l.visitas DESC, l.favoritos DESC";
} else {
    $orden = "l.fecha_subida DESC";
}

$sql_base .= " ORDER BY " . $orden;

// Limitar resultados para la página principal
$sql_principal = $sql_base . " LIMIT 12";
$result_principal = $conexion->query($sql_principal);

// Obtener recomendaciones (libros mejor valorados)
$sql_recomendaciones = "SELECT l.id, l.titulo, l.autor, l.descripcion, l.portada, l.publico_adulto, 
                        c.nombre as categoria, l.fecha_subida
                        FROM libros l
                        LEFT JOIN categorias c ON l.categoria_id = c.id
                        ORDER BY l.valoracion DESC
                        LIMIT 6";
$result_recomendaciones = $conexion->query($sql_recomendaciones);

// Obtener libros por categoría (para mostrar secciones por género)
$libros_por_categoria = [];
foreach (array_slice($categorias, 0, 4) as $categoria) {
    $sql_categoria = "SELECT l.id, l.titulo, l.autor, l.descripcion, l.portada, l.publico_adulto, 
                     l.fecha_subida
                     FROM libros l
                     WHERE l.categoria_id = ?
                     ORDER BY l.fecha_subida DESC
                     LIMIT 4";
    $stmt = $conexion->prepare($sql_categoria);
    $stmt->bind_param("i", $categoria['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $libros = [];
    while ($libro = $result->fetch_assoc()) {
        $libros[] = $libro;
    }
    
    $libros_por_categoria[$categoria['id']] = [
        'nombre' => $categoria['nombre'],
        'libros' => $libros
    ];
}
?>

<div class="container">
    <div class="literatura-header">
        <h1><?php echo $titulo_pagina; ?></h1>
        
        <!-- Navegación de secciones -->
        <div class="section-nav">
            <a href="<?php echo BASE_URL; ?>/public/literatura.php" class="<?php echo (!$mostrar_destacados && !$mostrar_recientes && !$mostrar_populares) ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i> Todas
            </a>
            <a href="<?php echo BASE_URL; ?>/public/literatura.php?destacados=1" class="<?php echo $mostrar_destacados ? 'active' : ''; ?>">
                <i class="fas fa-star"></i> Destacados
            </a>
            <a href="<?php echo BASE_URL; ?>/public/literatura.php?recientes=1" class="<?php echo $mostrar_recientes ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Recientes
            </a>
            <a href="<?php echo BASE_URL; ?>/public/literatura.php?populares=1" class="<?php echo $mostrar_populares ? 'active' : ''; ?>">
                <i class="fas fa-fire"></i> Populares
            </a>
        </div>
    </div>
    
    <!-- Categorías populares -->
    <div class="categorias-strip">
        <div class="categorias-strip-inner">
            <?php foreach ($categorias as $categoria): ?>
                <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $categoria['id']; ?>&tipo=libro" class="categoria-pill">
                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php if (!$mostrar_destacados && !$mostrar_recientes && !$mostrar_populares): ?>
        <!-- Sección de recomendaciones (solo en la página principal) -->
        <section class="literatura-section">
            <div class="section-header">
                <h2>Recomendados para ti</h2>
                <a href="<?php echo BASE_URL; ?>/public/literatura.php?populares=1" class="btn btn-sm btn-outline">Ver más</a>
            </div>
            
            <div class="obra-grid">
                <?php if ($result_recomendaciones && $result_recomendaciones->num_rows > 0): ?>
                    <?php while ($libro = $result_recomendaciones->fetch_assoc()): ?>
                        <div class="obra-card">
                            <div class="obra-card-header">
                                <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                                <div class="flex gap-1 mt-1">
                                    <?php if (!empty($libro['categoria'])): ?>
                                        <span class="badge badge-categoria"><?php echo htmlspecialchars($libro['categoria']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($libro['publico_adulto']): ?>
                                        <span class="badge badge-adulto">+18</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="obra-card-body">
                                <?php if (!empty($libro['portada'])): ?>
                                    <img src="<?php echo htmlspecialchars($libro['portada']); ?>" alt="Portada" class="obra-portada">
                                <?php else: ?>
                                    <div class="obra-portada-placeholder">
                                        <i class="fas fa-book fa-3x"></i>
                                    </div>
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
                <?php else: ?>
                    <p>No hay recomendaciones disponibles.</p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Sección principal de libros -->
    <section class="literatura-section">
        <div class="section-header">
            <h2>
                <?php 
                if ($mostrar_destacados) {
                    echo "Obras Destacadas";
                } elseif ($mostrar_recientes) {
                    echo "Obras Recientes";
                } elseif ($mostrar_populares) {
                    echo "Obras Populares";
                } else {
                    echo "Todas las Obras";
                }
                ?>
            </h2>
        </div>
        
        <div class="obra-grid">
            <?php if ($result_principal && $result_principal->num_rows > 0): ?>
                <?php while ($libro = $result_principal->fetch_assoc()): ?>
                    <div class="obra-card">
                        <div class="obra-card-header">
                            <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                            <div class="flex gap-1 mt-1">
                                <?php if (!empty($libro['categoria'])): ?>
                                    <span class="badge badge-categoria"><?php echo htmlspecialchars($libro['categoria']); ?></span>
                                <?php endif; ?>
                                <?php if ($libro['publico_adulto']): ?>
                                    <span class="badge badge-adulto">+18</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="obra-card-body">
                            <?php if (!empty($libro['portada'])): ?>
                                <img src="<?php echo htmlspecialchars($libro['portada']); ?>" alt="Portada" class="obra-portada">
                            <?php else: ?>
                                <div class="obra-portada-placeholder">
                                    <i class="fas fa-book fa-3x"></i>
                                </div>
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
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-book fa-3x"></i>
                    <h2>No hay obras disponibles</h2>
                    <p>No se encontraron obras que coincidan con los criterios seleccionados.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php if (!$mostrar_destacados && !$mostrar_recientes && !$mostrar_populares): ?>
        <!-- Secciones por categoría (solo en la página principal) -->
        <?php foreach ($libros_por_categoria as $id => $categoria): ?>
            <?php if (count($categoria['libros']) > 0): ?>
                <section class="literatura-section">
                    <div class="section-header">
                        <h2><?php echo htmlspecialchars($categoria['nombre']); ?></h2>
                        <a href="<?php echo BASE_URL; ?>/public/categoria.php?id=<?php echo $id; ?>&tipo=libro" class="btn btn-sm btn-outline">Ver más</a>
                    </div>
                    
                    <div class="obra-grid">
                        <?php foreach ($categoria['libros'] as $libro): ?>
                            <div class="obra-card">
                                <div class="obra-card-header">
                                    <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                                    <div class="flex gap-1 mt-1">
                                        <span class="badge badge-categoria"><?php echo htmlspecialchars($categoria['nombre']); ?></span>
                                        <?php if ($libro['publico_adulto']): ?>
                                            <span class="badge badge-adulto">+18</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="obra-card-body">
                                    <?php if (!empty($libro['portada'])): ?>
                                        <img src="<?php echo htmlspecialchars($libro['portada']); ?>" alt="Portada" class="obra-portada">
                                    <?php else: ?>
                                        <div class="obra-portada-placeholder">
                                            <i class="fas fa-book fa-3x"></i>
                                        </div>
                                    <?php endif; ?>
                                    <p class="obra-autor">Por: <?php echo htmlspecialchars($libro['autor']); ?></p>
                                    <p class="obra-descripcion"><?php echo substr(htmlspecialchars($libro['descripcion']), 0, 150) . '...'; ?></p>
                                </div>
                                <div class="obra-card-footer">
                                    <span class="obra-fecha"><?php echo date('d/m/Y', strtotime($libro['fecha_subida'])); ?></span>
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $libro['id']; ?>&tipo=libro" class="btn btn-outline">Ver más</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
