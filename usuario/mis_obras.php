<?php
session_start();
include '../includes/conexion.php';
include '../includes/header.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";

// Eliminación de obras
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_obra']) && isset($_POST['obra_id']) && isset($_POST['obra_tipo'])) {
    $obra_id = intval($_POST['obra_id']);
    $obra_tipo = $_POST['obra_tipo'] === 'libro' ? 'libro' : 'comic';

    // Eliminar dependencias primero (capítulos, comentarios, favoritos)
    if ($obra_tipo === 'libro') {
        // Eliminar capítulos del libro
        $stmt = $conexion->prepare("DELETE FROM capitulos_libro WHERE libro_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $obra_id);
            $stmt->execute();
            $stmt->close();
        }
        // Eliminar comentarios y favoritos
        $stmt = $conexion->prepare("DELETE FROM comentarios WHERE contenido_id = ? AND tipo_contenido = 'libro'");
        if ($stmt) {
            $stmt->bind_param("i", $obra_id);
            $stmt->execute();
            $stmt->close();
        }
        $stmt = $conexion->prepare("DELETE FROM favoritos WHERE contenido_id = ? AND tipo_contenido = 'libro'");
        if ($stmt) {
            $stmt->bind_param("i", $obra_id);
            $stmt->execute();
            $stmt->close();
        }
        // Ahora sí, eliminar el libro
        $stmt = $conexion->prepare("DELETE FROM libros WHERE id = ? AND usuario_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $obra_id, $usuario_id);
            if ($stmt->execute()) {
                $mensaje = '<div class="alert-exito">Obra eliminada correctamente.</div>';
            } else {
                $mensaje = '<div class="alert-error">No se pudo eliminar la obra.</div>';
            }
            $stmt->close();
        } else {
            $mensaje = '<div class="alert-error">Error interno al preparar la consulta.</div>';
        }
    } else {
        // Puedes agregar aquí lógica similar para comics (por ejemplo, capítulos_comic)
        $stmt = $conexion->prepare("DELETE FROM comentarios WHERE contenido_id = ? AND tipo_contenido = 'comic'");
        if ($stmt) {
            $stmt->bind_param("i", $obra_id);
            $stmt->execute();
            $stmt->close();
        }
        $stmt = $conexion->prepare("DELETE FROM favoritos WHERE contenido_id = ? AND tipo_contenido = 'comic'");
        if ($stmt) {
            $stmt->bind_param("i", $obra_id);
            $stmt->execute();
            $stmt->close();
        }
        // Eliminar el cómic
        $stmt = $conexion->prepare("DELETE FROM comics WHERE id = ? AND usuario_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $obra_id, $usuario_id);
            if ($stmt->execute()) {
                $mensaje = '<div class="alert-exito">Obra eliminada correctamente.</div>';
            } else {
                $mensaje = '<div class="alert-error">No se pudo eliminar la obra.</div>';
            }
            $stmt->close();
        } else {
            $mensaje = '<div class="alert-error">Error interno al preparar la consulta.</div>';
        }
    }
}

// Recarga obras tras eliminar
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
        <?= $mensaje ?>
    </header>

    <!-- LIBROS -->
    <section>
        <h2 class="mis-obras-subtitulo"><?= htmlspecialchars($TEXTOS['libros'] ?? 'Libros') ?></h2>
        <?php if ($libros->num_rows > 0): ?>
            <?php while ($libro = $libros->fetch_assoc()): ?>
                <article class="obra-item">
                    <?php if (!empty($libro['portada'])): ?>
                        <img class="obra-portada-img" src="<?= BASE_URL ?>/uploads/portadas/<?= htmlspecialchars($libro['portada']); ?>" alt="<?= htmlspecialchars($TEXTOS['portada_de'] ?? 'Portada de', ENT_QUOTES) . ' ' . htmlspecialchars($libro['titulo'], ENT_QUOTES); ?>">
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
                        <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este libro?');" style="display:inline;">
                            <input type="hidden" name="obra_id" value="<?= $libro['id'] ?>">
                            <input type="hidden" name="obra_tipo" value="libro">
                            <button type="submit" name="eliminar_obra" class="obra-link btn-eliminar">
                                <i class="fas fa-trash-alt"></i> <?= htmlspecialchars($TEXTOS['eliminar'] ?? 'Eliminar') ?>
                            </button>
                        </form>
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
                        <img class="obra-portada-img" src="<?= BASE_URL ?>/uploads/portadas/<?= htmlspecialchars($comic['portada']); ?>" alt="<?= htmlspecialchars($TEXTOS['portada_de'] ?? 'Portada de', ENT_QUOTES) . ' ' . htmlspecialchars($comic['titulo'], ENT_QUOTES); ?>">
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
                        <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este cómic?');" style="display:inline;">
                            <input type="hidden" name="obra_id" value="<?= $comic['id'] ?>">
                            <input type="hidden" name="obra_tipo" value="comic">
                            <button type="submit" name="eliminar_obra" class="obra-link btn-eliminar">
                                <i class="fas fa-trash-alt"></i> <?= htmlspecialchars($TEXTOS['eliminar'] ?? 'Eliminar') ?>
                            </button>
                        </form>
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

<style>
.alert-exito { background:#e9ffe4; border-left:5px solid #56d364; color:#277a3d; padding:10px 24px; border-radius:7px; margin:0 auto 22px auto; max-width:420px; font-weight:600; }
.alert-error { background:#ffeaea; border-left:5px solid #ff6565; color:#a83a3a; padding:10px 24px; border-radius:7px; margin:0 auto 22px auto; max-width:420px; font-weight:600; }
.btn-eliminar { background:#ffeaea; color:#a83a3a; border:none; margin-left:7px; padding:6px 18px; border-radius:6px; font-weight:600; cursor:pointer; transition:background 0.2s; }
.btn-eliminar:hover { background:#ff6565; color:#fff; }
</style>