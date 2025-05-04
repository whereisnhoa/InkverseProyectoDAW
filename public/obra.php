<?php
session_start();
include '../includes/conexion.php';
include '../includes/header.php';

if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
    echo "<p>Obra no especificada correctamente.</p>";
    include '../includes/footer.php';
    exit();
}

$contenido_id = $_GET['id'];
$tipo_contenido = $_GET['tipo']; // 'libro' o 'comic'

// Obtener datos de la obra según su tipo
if ($tipo_contenido === 'libro') {
    $sql = "SELECT l.*, c.nombre as categoria FROM libros l 
            LEFT JOIN categorias c ON l.categoria_id = c.id 
            WHERE l.id = ?";
} else {
    $sql = "SELECT * FROM comics WHERE id = ?";
}

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $contenido_id);
$stmt->execute();
$obra = $stmt->get_result()->fetch_assoc();

if (!$obra) {
    echo "<p>Obra no encontrada.</p>";
    include '../includes/footer.php';
    exit();
}

?>

<h2><?= htmlspecialchars($obra['titulo']) ?></h2>
<p><strong>Autor:</strong> <?= htmlspecialchars($obra['autor']) ?></p>
<?php if ($tipo_contenido === 'libro' && isset($obra['categoria'])): ?>
    <p><strong>Categoría:</strong> <?= htmlspecialchars($obra['categoria']) ?></p>
<?php endif; ?>
<p><?= nl2br(htmlspecialchars($obra['descripcion'])) ?></p>
<?php if ($obra['publico_adulto']): ?>
    <p style='color:red;'>Contenido para adultos</p>
<?php endif; ?>

<!-- Favoritos -->
<?php
if (isset($_SESSION['usuario_id'])):
    // Verificar si la obra ya está en favoritos
    $ya_favorito = false;
    $check = $conexion->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?");
    $check->bind_param("isi", $_SESSION['usuario_id'], $tipo_contenido, $contenido_id);
    $check->execute();
    $check->store_result();
    $ya_favorito = $check->num_rows > 0;
?>

<form action="../controllers/procesar-favorito.php" method="POST" style="margin: 15px 0;">
    <input type="hidden" name="contenido_id" value="<?= $contenido_id ?>">
    <input type="hidden" name="tipo_contenido" value="<?= $tipo_contenido ?>">
    <?php if ($ya_favorito): ?>
        <input type="hidden" name="accion" value="quitar">
        <button type="submit">❌ Quitar de favoritos</button>
    <?php else: ?>
        <input type="hidden" name="accion" value="agregar">
        <button type="submit">❤️ Añadir a favoritos</button>
    <?php endif; ?>
</form>

<?php endif; ?>

<hr>

<h3>Comentarios</h3>

<?php
// Mostrar comentarios
$sql_com = "SELECT u.usuario, c.contenido, c.fecha 
            FROM comentarios c 
            JOIN usuarios u ON u.id = c.usuario_id 
            WHERE c.tipo_contenido = ? AND c.contenido_id = ? 
            ORDER BY c.fecha DESC";
$stmt_com = $conexion->prepare($sql_com);
$stmt_com->bind_param("si", $tipo_contenido, $contenido_id);
$stmt_com->execute();
$resultado_com = $stmt_com->get_result();

if ($resultado_com->num_rows > 0):
    while ($comentario = $resultado_com->fetch_assoc()):
?>
    <p><strong><?= htmlspecialchars($comentario['usuario']) ?>:</strong> <?= nl2br(htmlspecialchars($comentario['contenido'])) ?> <br>
    <small><?= $comentario['fecha'] ?></small></p>
    <hr>
<?php endwhile; else: ?>
    <p>Aún no hay comentarios.</p>
<?php endif; ?>

<?php if (isset($_SESSION['usuario_id'])): ?>
    <h4>Deja tu comentario:</h4>
    <form action="../controllers/procesar-comentario.php" method="POST">
        <input type="hidden" name="contenido_id" value="<?= $contenido_id ?>">
        <input type="hidden" name="tipo_contenido" value="<?= $tipo_contenido ?>">
        <textarea name="contenido" rows="4" cols="50" required></textarea><br>
        <button type="submit">Comentar</button>
    </form>
<?php else: ?>
    <p>Debes iniciar sesión para comentar.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>