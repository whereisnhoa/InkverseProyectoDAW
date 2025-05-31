<?php
session_start();
include '../includes/conexion.php';
include '../includes/header.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$autor = $_SESSION['usuario'];

// Obtener libros del autor
$sql_libros = "SELECT id, titulo, portada, descripcion, categoria_id, publico_adulto, fecha_subida 
               FROM libros 
               WHERE autor = ? 
               ORDER BY fecha_subida DESC";
$stmt_libros = $conexion->prepare($sql_libros);
$stmt_libros->bind_param("s", $autor);
$stmt_libros->execute();
$libros = $stmt_libros->get_result();

// Obtener cómics del autor
$sql_comics = "SELECT id, titulo, portada, descripcion, publico_adulto, fecha_subida 
               FROM comics 
               WHERE autor = ? 
               ORDER BY fecha_subida DESC";
$stmt_comics = $conexion->prepare($sql_comics);
$stmt_comics->bind_param("s", $autor);
$stmt_comics->execute();
$comics = $stmt_comics->get_result();
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/usuario.css">

<h2>Mis obras publicadas</h2>

<h3>Libros</h3>
<?php if ($libros->num_rows > 0): ?>
    <?php while ($libro = $libros->fetch_assoc()): ?>
        <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
            <?php if (!empty($libro['portada'])): ?>
                <img src="<?php echo htmlspecialchars($libro['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>" style="width:90px; height:120px; object-fit:cover; border-radius:8px; margin-right:16px;">
            <?php else: ?>
                <div style="width:90px; height:120px; background:#eee; display:flex; align-items:center; justify-content:center; border-radius:8px; margin-right:16px;">
                    <i class="fas fa-book" style="font-size:2em; color:#aaa;"></i>
                </div>
            <?php endif; ?>
            <div>
                <h4><?= htmlspecialchars($libro['titulo']) ?></h4>
                <p><strong>Tipo:</strong> Libro</p>
                <p><?= nl2br(htmlspecialchars($libro['descripcion'])) ?></p>
                <p>Publicado el: <?= $libro['fecha_subida'] ?></p>
                <?php if ($libro['publico_adulto']): ?>
                    <p style="color: red;">Contenido para adultos</p>
                <?php endif; ?>
                <a href="../public/obra.php?id=<?= $libro['id'] ?>&tipo=libro">Ver libro</a>
            </div>
        </div>
        <hr>
    <?php endwhile; ?>
<?php else: ?>
    <p>No has publicado ningún libro aún.</p>
<?php endif; ?>

<h3>Cómics</h3>
<?php if ($comics->num_rows > 0): ?>
    <?php while ($comic = $comics->fetch_assoc()): ?>
        <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
            <?php if (!empty($comic['portada'])): ?>
                <img src="<?php echo htmlspecialchars($comic['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($comic['titulo']); ?>" style="width:90px; height:120px; object-fit:cover; border-radius:8px; margin-right:16px;">
            <?php else: ?>
                <div style="width:90px; height:120px; background:#eee; display:flex; align-items:center; justify-content:center; border-radius:8px; margin-right:16px;">
                    <i class="fas fa-book" style="font-size:2em; color:#aaa;"></i>
                </div>
            <?php endif; ?>
            <div>
                <h4><?= htmlspecialchars($comic['titulo']) ?></h4>
                <p><strong>Tipo:</strong> Cómic/Manga</p>
                <p><?= nl2br(htmlspecialchars($comic['descripcion'])) ?></p>
                <p>Publicado el: <?= $comic['fecha_subida'] ?></p>
                <?php if ($comic['publico_adulto']): ?>
                    <p style="color: red;">Contenido para adultos</p>
                <?php endif; ?>
                <a href="../public/obra.php?id=<?= $comic['id'] ?>&tipo=comic">Ver cómic</a>
            </div>
        </div>
        <hr>
    <?php endwhile; ?>
<?php else: ?>
    <p>No has publicado ningún cómic aún.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>