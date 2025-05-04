
<?php
session_start();
include '../includes/conexion.php';
include '../includes/header.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener libros favoritos
$sql_libros = "SELECT l.* FROM libros l 
               JOIN favoritos f ON f.contenido_id = l.id 
               WHERE f.usuario_id = ? AND f.tipo_contenido = 'libro'
               ORDER BY f.fecha_guardado DESC";

$stmt_libros = $conexion->prepare($sql_libros);
$stmt_libros->bind_param("i", $usuario_id);
$stmt_libros->execute();
$libros = $stmt_libros->get_result();

// Obtener cómics favoritos
$sql_comics = "SELECT c.* FROM comics c 
               JOIN favoritos f ON f.contenido_id = c.id 
               WHERE f.usuario_id = ? AND f.tipo_contenido = 'comic'
               ORDER BY f.fecha_guardado DESC";

$stmt_comics = $conexion->prepare($sql_comics);
$stmt_comics->bind_param("i", $usuario_id);
$stmt_comics->execute();
$comics = $stmt_comics->get_result();
?>

<h2>Mis obras favoritas</h2>

<h3>Libros</h3>
<?php if ($libros->num_rows > 0): ?>
    <?php while ($libro = $libros->fetch_assoc()): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <h4><?= htmlspecialchars($libro['titulo']) ?></h4>
            <p><strong>Autor:</strong> <?= htmlspecialchars($libro['autor']) ?></p>
            <a href="../public/obra.php?id=<?= $libro['id'] ?>&tipo=libro">Ver libro</a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No has añadido libros a favoritos aún.</p>
<?php endif; ?>

<h3>Cómics</h3>
<?php if ($comics->num_rows > 0): ?>
    <?php while ($comic = $comics->fetch_assoc()): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <h4><?= htmlspecialchars($comic['titulo']) ?></h4>
            <p><strong>Autor:</strong> <?= htmlspecialchars($comic['autor']) ?></p>
            <a href="../public/obra.php?id=<?= $comic['id'] ?>&tipo=comic">Ver cómic</a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No has añadido cómics a favoritos aún.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>