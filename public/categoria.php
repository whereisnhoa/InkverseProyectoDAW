<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Obtener el tipo y la categoría seleccionada
$categoria_id = isset($_GET['categoria_id']) ? intval($_GET['categoria_id']) : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'libro';

// Obtener información de la categoría
$sql_categoria = "SELECT * FROM categorias WHERE id = ? AND tipo = ?";
$stmt_categoria = $conexion->prepare($sql_categoria);
$stmt_categoria->bind_param("is", $categoria_id, $tipo);
$stmt_categoria->execute();
$result_categoria = $stmt_categoria->get_result();
$categoria = $result_categoria->fetch_assoc();

if (!$categoria) {
    echo "<div class='container'><h2>Categoría no encontrada</h2></div>";
    include_once(__DIR__ . '/../includes/footer.php');
    exit;
}
?>

<div class="container" style="margin-top:40px;">

    <!-- CABECERA DE CATEGORÍA MEJORADA -->
    <div class="categoria-header-advanced">
        <div class="categoria-header-icon-gradient">
            <?php if ($tipo == 'libro') : ?>
                <i class="fas fa-book"></i>
            <?php else : ?>
                <i class="fas fa-book-open"></i>
            <?php endif; ?>
        </div>
        <div class="categoria-header-content">
            <h1><?php echo htmlspecialchars($categoria['nombre']); ?></h1>
            <p><?php echo !empty($categoria['descripcion']) ? htmlspecialchars($categoria['descripcion']) : 'Sin descripción.'; ?></p>
        </div>
    </div>

    <?php
    // Reciente: hasta 6 obras más recientes
    if ($tipo == 'libro') {
        $sql_obras_recientes = "SELECT o.*, u.usuario 
                      FROM libros o 
                      JOIN usuarios u ON o.usuario_id = u.id 
                      WHERE o.categoria_id = ? 
                      ORDER BY o.fecha_subida DESC
                      LIMIT 6";
    } else {
        $sql_obras_recientes = "SELECT o.*, u.usuario 
                      FROM comics o 
                      JOIN usuarios u ON o.usuario_id = u.id 
                      WHERE o.categoria_id = ? 
                      ORDER BY o.fecha_subida DESC
                      LIMIT 6";
    }
    $stmt_obras_recientes = $conexion->prepare($sql_obras_recientes);
    $stmt_obras_recientes->bind_param("i", $categoria_id);
    $stmt_obras_recientes->execute();
    $result_obras_recientes = $stmt_obras_recientes->get_result();

    // Recomendado: hasta 6 mejores valoradas
    if ($tipo == 'libro') {
        $sql_obras_recomendadas = "SELECT o.*, u.usuario 
                      FROM libros o 
                      JOIN usuarios u ON o.usuario_id = u.id 
                      WHERE o.categoria_id = ? AND o.valoracion IS NOT NULL
                      ORDER BY o.valoracion DESC, o.fecha_subida DESC LIMIT 6";
    } else {
        $sql_obras_recomendadas = "SELECT o.*, u.usuario 
                      FROM comics o 
                      JOIN usuarios u ON o.usuario_id = u.id 
                      WHERE o.categoria_id = ? AND o.valoracion IS NOT NULL
                      ORDER BY o.valoracion DESC, o.fecha_subida DESC LIMIT 6";
    }
    $stmt_obras_recomendadas = $conexion->prepare($sql_obras_recomendadas);
    $stmt_obras_recomendadas->bind_param("i", $categoria_id);
    $stmt_obras_recomendadas->execute();
    $result_obras_recomendadas = $stmt_obras_recomendadas->get_result();

    // Todas las obras de la categoría (sin límite)
    if ($tipo == 'libro') {
        $sql_obras_todas = "SELECT o.*, u.usuario 
                      FROM libros o 
                      JOIN usuarios u ON o.usuario_id = u.id 
                      WHERE o.categoria_id = ? 
                      ORDER BY o.fecha_subida DESC";
    } else {
        $sql_obras_todas = "SELECT o.*, u.usuario 
                      FROM comics o 
                      JOIN usuarios u ON o.usuario_id = u.id 
                      WHERE o.categoria_id = ? 
                      ORDER BY o.fecha_subida DESC";
    }
    $stmt_obras_todas = $conexion->prepare($sql_obras_todas);
    $stmt_obras_todas->bind_param("i", $categoria_id);
    $stmt_obras_todas->execute();
    $result_obras_todas = $stmt_obras_todas->get_result();
    ?>

    <!-- Sección Reciente -->
    <div class="seccion-obras">
        <h2 class="section-title"><i class="fas fa-clock section-icon"></i> Obras recientes</h2>
        <div class="obras-listado-advanced">
            <?php if ($result_obras_recientes && $result_obras_recientes->num_rows > 0): ?>
                <?php while ($obra = $result_obras_recientes->fetch_assoc()): ?>
                    <?php include(__DIR__ . '/_obra_card.php'); ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-obras-advanced">
                    <i class="fas fa-info-circle"></i> No hay obras recientes en esta categoría.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sección Recomendado -->
    <div class="seccion-obras">
        <h2 class="section-title"><i class="fas fa-star section-icon"></i> Recomendadas</h2>
        <div class="obras-listado-advanced">
            <?php if ($result_obras_recomendadas && $result_obras_recomendadas->num_rows > 0): ?>
                <?php while ($obra = $result_obras_recomendadas->fetch_assoc()): ?>
                    <?php include(__DIR__ . '/_obra_card.php'); ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-obras-advanced">
                    <i class="fas fa-info-circle"></i> No hay recomendaciones aún.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Todas las obras de la categoría -->
    <div class="seccion-obras">
        <h2 class="section-title"><i class="fas fa-th-large section-icon"></i> Todas las obras</h2>
        <div class="obras-listado-advanced">
            <?php if ($result_obras_todas && $result_obras_todas->num_rows > 0): ?>
                <?php while ($obra = $result_obras_todas->fetch_assoc()): ?>
                    <?php include(__DIR__ . '/_obra_card.php'); ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-obras-advanced">
                    <i class="fas fa-info-circle"></i> No hay obras en esta categoría.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>