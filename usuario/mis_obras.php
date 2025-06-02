<?php
session_start();
include '../includes/conexion.php';
include '../includes/header.php';
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/login.php");
    exit();
}
$usuario_id = $_SESSION['usuario_id'];
$sql_libros = "SELECT id, titulo, portada, descripcion, categoria_id, publico_adulto, fecha_subida 
               FROM libros 
               WHERE usuario_id = ? 
               ORDER BY fecha_subida DESC";
$stmt_libros = $conexion->prepare($sql_libros);
$stmt_libros->bind_param("i", $usuario_id);
$stmt_libros->execute();
$libros = $stmt_libros->get_result();
$sql_comics = "SELECT id, titulo, portada, descripcion, publico_adulto, fecha_subida 
               FROM comics 
               WHERE usuario_id = ? 
               ORDER BY fecha_subida DESC";
$stmt_comics = $conexion->prepare($sql_comics);
$stmt_comics->bind_param("i", $usuario_id);
$stmt_comics->execute();
$comics = $stmt_comics->get_result();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/usuario.css">

<div class="mis-obras-container">
    <div class="mis-obras-titulo"><i class="fas fa-book-open"></i> Mis obras publicadas</div>

    <div class="mis-obras-subtitulo">Libros</div>
    <?php if ($libros->num_rows > 0): ?>
        <?php while ($libro = $libros->fetch_assoc()): ?>
            <div class="obra-item">
                <?php if (!empty($libro['portada'])): ?>
                    <img class="obra-portada-img" src="<?php echo htmlspecialchars($libro['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>">
                <?php else: ?>
                    <div class="obra-portada-placeholder"><i class="fas fa-book"></i></div>
                <?php endif; ?>
                <div class="obra-info">
                    <h4><?= htmlspecialchars($libro['titulo']) ?></h4>
                    <span class="badge">Libro</span>
                    <?php if ($libro['publico_adulto']): ?>
                        <span class="badge badge-adulto">+18</span>
                    <?php endif; ?>
                    <div class="obra-descripcion"><?= nl2br(htmlspecialchars($libro['descripcion'])) ?></div>
                    <div class="obra-fecha"><i class="far fa-calendar"></i> Publicado el: <?= date('d/m/Y', strtotime($libro['fecha_subida'])) ?></div>
                    <a class="obra-link" href="../public/obra.php?id=<?= $libro['id'] ?>&tipo=libro">Ver libro</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="mis-obras-vacio"><i class="fas fa-search"></i> No has publicado ningún libro aún.</div>
    <?php endif; ?>

    <div class="mis-obras-subtitulo">Cómics</div>
    <?php if ($comics->num_rows > 0): ?>
        <?php while ($comic = $comics->fetch_assoc()): ?>
            <div class="obra-item">
                <?php if (!empty($comic['portada'])): ?>
                    <img class="obra-portada-img" src="<?php echo htmlspecialchars($comic['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($comic['titulo']); ?>">
                <?php else: ?>
                    <div class="obra-portada-placeholder"><i class="fas fa-book"></i></div>
                <?php endif; ?>
                <div class="obra-info">
                    <h4><?= htmlspecialchars($comic['titulo']) ?></h4>
                    <span class="badge">Cómic/Manga</span>
                    <?php if ($comic['publico_adulto']): ?>
                        <span class="badge badge-adulto">+18</span>
                    <?php endif; ?>
                    <div class="obra-descripcion"><?= nl2br(htmlspecialchars($comic['descripcion'])) ?></div>
                    <div class="obra-fecha"><i class="far fa-calendar"></i> Publicado el: <?= date('d/m/Y', strtotime($comic['fecha_subida'])) ?></div>
                    <a class="obra-link" href="../public/obra.php?id=<?= $comic['id'] ?>&tipo=comic">Ver cómic</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="mis-obras-vacio"><i class="fas fa-search"></i> No has publicado ningún cómic aún.</div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>