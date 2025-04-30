
<?php

session_start();
include '../includes/conexion.php';
include '../includes/header.php';

if (!isset($_GET['id'])) 
{
    echo "<p>Obra no especificada.</p>";
    include '../includes/footer.php';
    exit();
}

$obra_id = $_GET['id'];

// obtener datos de la obra
$sql = "SELECT * FROM obras WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $obra_id);
$stmt->execute();
$obra = $stmt->get_result()->fetch_assoc();

if (!$obra) 
{
    echo "<p>Obra no encontrada.</p>";
    include '../includes/footer.php';
    exit();
}

?>

<h2><?= htmlspecialchars($obra['titulo']) ?></h2>
<p><strong>Tipo:</strong> <?= $obra['tipo'] ?> | <strong>Género:</strong> <?= $obra['genero'] ?></p>
<p><?= nl2br(htmlspecialchars($obra['descripcion'])) ?></p>
<?php if ($obra['es_adulto']) echo "<p style='color:red;'> contenido para adultos</p>"; ?>

<hr>

<h3>Comentarios</h3>

<?php

// mostrar comentarios
$sql_com = "SELECT usuarios.usuario, contenido, fecha FROM comentarios JOIN usuarios ON usuarios.id = comentarios.usuario_id WHERE obra_id = ? ORDER BY fecha DESC";
$stmt_com = $conexion->prepare($sql_com);
$stmt_com->bind_param("i", $obra_id);
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
        <input type="hidden" name="obra_id" value="<?= $obra_id ?>">
        <textarea name="contenido" rows="4" cols="50" required></textarea><br>
        <button type="submit">Comentar</button>
    </form>

<?php else: ?>
    <p>Debes iniciar sesión para comentar.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
