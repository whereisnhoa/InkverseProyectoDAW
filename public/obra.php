<?php
include '../includes/conexion.php';
include '../includes/header.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipo = $_GET['tipo'] ?? '';
$capSeleccionado = isset($_GET['cap']) ? intval($_GET['cap']) : 1;

if (!$id || ($tipo !== 'libro' && $tipo !== 'comic')) {
    echo "<div class='alerta alerta-error'>Obra no encontrada.</div>";
    include '../includes/footer.php';
    exit();
}

if ($tipo === 'libro') {
    $stmt = $conexion->prepare("SELECT l.*, c.nombre AS categoria_nombre, u.usuario AS autor_nombre
        FROM libros AS l
        LEFT JOIN categorias AS c ON l.categoria_id = c.id
        LEFT JOIN usuarios AS u ON l.usuario_id = u.id
        WHERE l.id = ?");
    $tablaCap = "capitulos_libro";
    $colObra = "libro_id";
} else {
    $stmt = $conexion->prepare("SELECT co.*, c.nombre AS categoria_nombre, u.usuario AS autor_nombre
        FROM comics AS co
        LEFT JOIN categorias AS c ON co.categoria_id = c.id
        LEFT JOIN usuarios AS u ON co.usuario_id = u.id
        WHERE co.id = ?");
    $tablaCap = "capitulos_comic";
    $colObra = "comic_id";
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$obra = $result->fetch_assoc();
$stmt->close();

$autor = !empty($obra['autor_nombre']) ? htmlspecialchars($obra['autor_nombre']) : 'Anónimo';
$titulo = htmlspecialchars($obra['titulo'] ?? 'Sin título');
$categoria = htmlspecialchars($obra['categoria_nombre'] ?? 'Sin categoría');
$fecha = !empty($obra['fecha_subida']) ? date('d/m/Y', strtotime($obra['fecha_subida'])) : '';
$descripcion = !empty($obra['descripcion']) ? nl2br(htmlspecialchars($obra['descripcion'])) : 'Sin descripción.';
$portada = !empty($obra['portada']) ? BASE_URL . $obra['portada'] : BASE_URL . '/public/img/portada_default.png';

$esFavorito = false;
if ($usuario_id) {
    $stmtFav = $conexion->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?");
    $stmtFav->bind_param("isi", $usuario_id, $tipo, $id);
    $stmtFav->execute();
    $stmtFav->store_result();
    $esFavorito = $stmtFav->num_rows > 0;
    $stmtFav->close();
}

$stmt_count = $conexion->prepare("SELECT COUNT(*) as total FROM $tablaCap WHERE $colObra = ?");
$stmt_count->bind_param("i", $id);
$stmt_count->execute();
$resCount = $stmt_count->get_result();
$totalCaps = $resCount->fetch_assoc()['total'] ?? 0;
$stmt_count->close();

$stmt_titulos = $conexion->prepare("SELECT numero, titulo FROM $tablaCap WHERE $colObra = ? ORDER BY numero ASC");
$stmt_titulos->bind_param("i", $id);
$stmt_titulos->execute();
$resTitulos = $stmt_titulos->get_result();
$listaCapitulos = [];
while ($row = $resTitulos->fetch_assoc()) {
    $listaCapitulos[] = $row;
}
$stmt_titulos->close();

$stmt_cap = $conexion->prepare("SELECT * FROM $tablaCap WHERE $colObra = ? AND numero = ?");
$stmt_cap->bind_param("ii", $id, $capSeleccionado);
$stmt_cap->execute();
$capitulo = $stmt_cap->get_result()->fetch_assoc();
$stmt_cap->close();
?>

<!-- FontAwesome para los íconos de favoritos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="obra-detalle-container">
    <div class="obra-header">
        <img class="obra-portada-detalle" src="<?php echo $portada; ?>" alt="Portada de <?php echo $titulo; ?>">
        <div class="obra-header-info">
            <h1 class="obra-titulo"><?php echo $titulo; ?></h1>
            <div class="obra-meta">
                <span class="obra-categoria"><?php echo strtoupper($categoria); ?></span>
                <span class="obra-autor-fecha">
                    Por <b><?php echo $autor; ?></b><?php echo $fecha ? " · $fecha" : ""; ?>
                </span>
            </div>
        </div>
    </div>
    <div class="obra-sinopsis">
        <?php echo $descripcion; ?>
    </div>

    <?php if ($totalCaps > 0 && $capitulo): ?>
        <div class="obra-capitulos">
            <div class="capitulo-paginacion">
                <?php if ($capSeleccionado > 1): ?>
                    <a href="obra.php?id=<?php echo $id; ?>&tipo=<?php echo $tipo; ?>&cap=<?php echo $capSeleccionado-1; ?>">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </a>
                <?php else: ?>
                    <button disabled><i class="fas fa-arrow-left"></i> Anterior</button>
                <?php endif; ?>

                <span class="paginacion-info">
                    Capítulo <?php echo $capSeleccionado; ?> de <?php echo $totalCaps; ?>
                </span>

                <?php if ($capSeleccionado < $totalCaps): ?>
                    <a href="obra.php?id=<?php echo $id; ?>&tipo=<?php echo $tipo; ?>&cap=<?php echo $capSeleccionado+1; ?>">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <button disabled>Siguiente <i class="fas fa-arrow-right"></i></button>
                <?php endif; ?>
            </div>

            <form class="capitulo-selector-form" method="get" action="">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
                <label for="cap">Ir al capítulo:</label>
                <select name="cap" id="cap" onchange="this.form.submit()">
                    <?php foreach ($listaCapitulos as $cap): ?>
                        <option value="<?php echo $cap['numero']; ?>" <?php if ($capSeleccionado == $cap['numero']) echo 'selected'; ?>>
                            Capítulo <?php echo intval($cap['numero']); ?>
                            <?php echo $cap['titulo'] ? ' - '.htmlspecialchars($cap['titulo']) : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <noscript><button type="submit">Ir</button></noscript>
            </form>

            <div class="obra-capitulo">
                <h3>
                    Capítulo <?php echo intval($capitulo['numero']); ?><?php echo $capitulo['titulo'] ? ": ".htmlspecialchars($capitulo['titulo']) : ""; ?>
                </h3>
                <?php if ($tipo === 'libro'): ?>
                    <div class="capitulo-texto">
                        <?php echo nl2br(htmlspecialchars($capitulo['contenido'])); ?>
                    </div>
                <?php else: ?>
                    <?php if (!empty($capitulo['imagen'])): ?>
                        <div class="capitulo-imagen">
                            <img src="<?php echo BASE_URL . htmlspecialchars($capitulo['imagen']); ?>" alt="Página">
                        </div>
                    <?php else: ?>
                        <div class="capitulo-texto">
                            <?php echo nl2br(htmlspecialchars($capitulo['contenido'])); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="obra-acciones">
        <?php if ($usuario_id): ?>
        <button class="btn-favorito<?php if($esFavorito) echo ' favorited'; ?>"
                data-obra-id="<?php echo $id; ?>"
                data-tipo="<?php echo $tipo; ?>"
                type="button"
                title="<?php echo $esFavorito ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>">
            <i class="<?php echo $esFavorito ? 'fas' : 'far'; ?> fa-heart"></i>
            <?php echo $esFavorito ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>
        </button>
        <?php else: ?>
        <button class="btn-favorito" type="button" onclick="window.location.href='<?php echo BASE_URL; ?>/public/login.php'">
            <i class="far fa-heart"></i> Añadir a favoritos
        </button>
        <?php endif; ?>
    </div>
    <div class="obra-comentarios">
        <h2>Comentarios</h2>
        <div class="obra-comentarios-lista">
            <p>Aún no hay comentarios.</p>
        </div>
        <h3>Deja tu comentario</h3>
        <form method="post" action="">
            <textarea name="comentario" rows="3" placeholder="Escribe tu comentario..." required></textarea>
            <button type="submit" class="btn btn-primary" style="margin-top:8px;">Enviar</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>