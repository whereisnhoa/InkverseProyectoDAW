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

    <header>
        <h1 class="mis-obras-titulo">
            <i class="fas fa-book-open"></i>
            <?= htmlspecialchars($TEXTOS['mis_obras_publicadas'] ?? 'Mis obras publicadas') ?>
        </h1>
        <p style="text-align:center; color:#6c7ae0; margin-bottom:28px;">
            <?= htmlspecialchars($TEXTOS['mis_obras_subtitulo'] ?? 'Aquí puedes ver y gestionar tus obras publicadas.') ?>
        </p>
        <div style="text-align:center; margin-bottom:20px;">
            <a class="obra-link" href="../usuario/nueva_obra.php">
                <i class="fas fa-plus"></i> <?= htmlspecialchars($TEXTOS['publicar_nueva_obra'] ?? 'Publicar nueva obra') ?>
            </a>
        </div>
    </header>

    <!-- LIBROS -->
    <section>
        <h2 class="mis-obras-subtitulo"><?= htmlspecialchars($TEXTOS['libros'] ?? 'Libros') ?></h2>
        <?php if ($libros->num_rows > 0): ?>
            <?php while ($libro = $libros->fetch_assoc()): ?>
                <article class="obra-item">
                    <?php if (!empty($libro['portada'])): ?>
                        <img class="obra-portada-img" src="<?= BASE_URL ?>/uploads/portadas/<?= htmlspecialchars($libro['portada']); ?>" alt="<?= htmlspecialchars($TEXTOS['portada_de'] ?? 'Portada de') . ' ' . htmlspecialchars($libro['titulo']); ?>">
                    <?php else: ?>
                        <div class="obra-portada-placeholder"><i class="fas fa-book"></i></div>
                    <?php endif; ?>
                    <div class="obra-info">
                        <h4><?= htmlspecialchars($libro['titulo']) ?></h4>
                        <span class="badge"><?= htmlspecialchars($TEXTOS['libro'] ?? 'Libro') ?></span>
                        <?php if ($libro['publico_adulto']): ?>
                            <span class="badge badge-adulto">+18</span>
                        <?php endif; ?>
                        <div class="obra-descripcion"><?= nl2br(htmlspecialchars($libro['descripcion'])) ?></div>
                        <div class="obra-fecha"><i class="far fa-calendar"></i> <?= htmlspecialchars($TEXTOS['publicado_el'] ?? 'Publicado el:') ?> <?= date('d/m/Y', strtotime($libro['fecha_subida'])) ?></div>
                        <a class="obra-link" href="../public/obra.php?id=<?= $libro['id'] ?>&tipo=libro">
                            <i class="fas fa-eye"></i> <?= htmlspecialchars($TEXTOS['ver_libro'] ?? 'Ver libro') ?>
                        </a>
                        <!--
                        <a class="obra-link" style="background:#f1f3fa;color:#4361ee;margin-left:7px;" href="../usuario/editar_obra.php?id=<?= $libro['id'] ?>&tipo=libro">
                            <i class="fas fa-edit"></i> <?= htmlspecialchars($TEXTOS['editar'] ?? 'Editar') ?>
                        </a>
                        -->
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="mis-obras-vacio">
                <i class="fas fa-search"></i>
                <?= htmlspecialchars($TEXTOS['no_libros_publicados'] ?? 'No has publicado ningún libro aún.') ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- COMICS -->
    <section>
        <h2 class="mis-obras-subtitulo"><?= htmlspecialchars($TEXTOS['comics_manga'] ?? 'Cómics/Manga') ?></h2>
        <?php if ($comics->num_rows > 0): ?>
            <?php while ($comic = $comics->fetch_assoc()): ?>
                <article class="obra-item">
                    <?php if (!empty($comic['portada'])): ?>
                        <img class="obra-portada-img" src="<?= BASE_URL ?>/uploads/portadas/<?= htmlspecialchars($comic['portada']); ?>" alt="<?= htmlspecialchars($TEXTOS['portada_de'] ?? 'Portada de') . ' ' . htmlspecialchars($comic['titulo']); ?>">
                    <?php else: ?>
                        <div class="obra-portada-placeholder"><i class="fas fa-book"></i></div>
                    <?php endif; ?>
                    <div class="obra-info">
                        <h4><?= htmlspecialchars($comic['titulo']) ?></h4>
                        <span class="badge"><?= htmlspecialchars($TEXTOS['comics_manga'] ?? 'Cómic/Manga') ?></span>
                        <?php if ($comic['publico_adulto']): ?>
                            <span class="badge badge-adulto">+18</span>
                        <?php endif; ?>
                        <div class="obra-descripcion"><?= nl2br(htmlspecialchars($comic['descripcion'])) ?></div>
                        <div class="obra-fecha"><i class="far fa-calendar"></i> <?= htmlspecialchars($TEXTOS['publicado_el'] ?? 'Publicado el:') ?> <?= date('d/m/Y', strtotime($comic['fecha_subida'])) ?></div>
                        <a class="obra-link" href="../public/obra.php?id=<?= $comic['id'] ?>&tipo=comic">
                            <i class="fas fa-eye"></i> <?= htmlspecialchars($TEXTOS['ver_comic'] ?? 'Ver cómic') ?>
                        </a>
                        <!--
                        <a class="obra-link" style="background:#f1f3fa;color:#4361ee;margin-left:7px;" href="../usuario/editar_obra.php?id=<?= $comic['id'] ?>&tipo=comic">
                            <i class="fas fa-edit"></i> <?= htmlspecialchars($TEXTOS['editar'] ?? 'Editar') ?>
                        </a>
                        -->
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="mis-obras-vacio">
                <i class="fas fa-search"></i>
                <?= htmlspecialchars($TEXTOS['no_comics_publicados'] ?? 'No has publicado ningún cómic aún.') ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php include '../includes/footer.php'; ?>