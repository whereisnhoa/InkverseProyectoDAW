<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/conexion.php');
require_once(__DIR__ . '/../includes/funciones.php');

$idioma = isset($_SESSION['idioma']) ? $_SESSION['idioma'] : 'es';
$archivo_idioma = __DIR__ . "/../includes/languages/" . $idioma . ".php";
if (!file_exists($archivo_idioma)) $archivo_idioma = __DIR__ . "/../includes/languages/es.php";
require_once($archivo_idioma);

include(__DIR__ . '/../includes/header.php');

$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$capSeleccionado = isset($_GET['cap']) ? intval($_GET['cap']) : 1;

if (!$id || ($tipo !== 'libro' && $tipo !== 'comic')) {
    echo "<div class='alerta alerta-error'>" . (isset($TEXTOS['obra_no_encontrada']) ? $TEXTOS['obra_no_encontrada'] : 'Obra no encontrada.') . "</div>";
    include(__DIR__ . '/../includes/footer.php');
    exit();
}

// --- OBTENER DATOS DE LA OBRA ---
if ($tipo === 'libro') {
    $stmt = $conexion->prepare("SELECT l.*, c.nombre AS categoria_nombre, u.usuario AS autor_nombre, l.portada, l.categoria_id
        FROM libros AS l
        LEFT JOIN categorias AS c ON l.categoria_id = c.id
        LEFT JOIN usuarios AS u ON l.usuario_id = u.id
        WHERE l.id = ?");
    $tablaCap = "capitulos_libro";
    $colObra = "libro_id";
} else {
    $stmt = $conexion->prepare("SELECT co.*, c.nombre AS categoria_nombre, u.usuario AS autor_nombre, co.portada, co.categoria_id
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

if (!$obra) {
    echo "<div class='alerta alerta-error'>" . (isset($TEXTOS['obra_no_encontrada']) ? $TEXTOS['obra_no_encontrada'] : 'Obra no encontrada.') . "</div>";
    include(__DIR__ . '/../includes/footer.php');
    exit();
}

// --- OBTENER CATEGORÍA REAL (siempre la toma, aunque el LEFT JOIN falle) ---
function obtenerNombreCategoria($conexion, $categoria_id) {
    if (!$categoria_id) return 'Sin categoría';
    $stmt_cat = $conexion->prepare("SELECT nombre FROM categorias WHERE id = ?");
    $stmt_cat->bind_param("i", $categoria_id);
    $stmt_cat->execute();
    $res = $stmt_cat->get_result();
    $row = $res->fetch_assoc();
    $stmt_cat->close();
    if ($row && isset($row['nombre']) && trim($row['nombre']) !== '') {
        return htmlspecialchars($row['nombre']);
    }
    return 'Sin categoría';
}
$categoria = (isset($obra['categoria_nombre']) && trim($obra['categoria_nombre']) !== '' && $obra['categoria_nombre'] !== null)
    ? htmlspecialchars($obra['categoria_nombre'])
    : ((isset($obra['categoria_id']) && $obra['categoria_id']) 
        ? obtenerNombreCategoria($conexion, $obra['categoria_id']) 
        : (isset($TEXTOS['sin_categoria']) ? $TEXTOS['sin_categoria'] : 'Sin categoría'));

// --- DATOS SEGUROS RESTO ---
$titulo = htmlspecialchars(isset($obra['titulo']) ? $obra['titulo'] : (isset($TEXTOS['sin_titulo']) ? $TEXTOS['sin_titulo'] : 'Sin título'));
$autor = htmlspecialchars(isset($obra['autor_nombre']) && $obra['autor_nombre'] ? $obra['autor_nombre'] : (isset($TEXTOS['autor_desconocido']) ? $TEXTOS['autor_desconocido'] : 'Anónimo'));
$fecha = (isset($obra['fecha_subida']) && $obra['fecha_subida']) ? date('d/m/Y', strtotime($obra['fecha_subida'])) : '';
$descripcion = (isset($obra['descripcion']) && $obra['descripcion']) ? nl2br(htmlspecialchars($obra['descripcion'])) : (isset($TEXTOS['sin_descripcion']) ? $TEXTOS['sin_descripcion'] : 'Sin descripción.');
$portada = (isset($obra['portada']) && $obra['portada'] && strpos($obra['portada'], '/uploads/') === false)
    ? BASE_URL . '/uploads/portadas/' . $obra['portada']
    : ((isset($obra['portada']) && $obra['portada']) ? BASE_URL . $obra['portada'] : BASE_URL . '/img/portada_default.png');

// --- FAVORITOS ---
$esFavorito = false;
if ($usuario_id) {
    $stmtFav = $conexion->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?");
    $stmtFav->bind_param("isi", $usuario_id, $tipo, $id);
    $stmtFav->execute();
    $stmtFav->store_result();
    $esFavorito = $stmtFav->num_rows > 0;
    $stmtFav->close();
}

// --- CAPÍTULOS ---
$stmt_count = $conexion->prepare("SELECT COUNT(*) as total FROM $tablaCap WHERE $colObra = ?");
$stmt_count->bind_param("i", $id);
$stmt_count->execute();
$resCount = $stmt_count->get_result();
$totalCaps = $resCount->fetch_assoc();
$totalCaps = isset($totalCaps['total']) ? $totalCaps['total'] : 0;
$stmt_count->close();

$stmt_titulos = $conexion->prepare("SELECT numero, titulo FROM $tablaCap WHERE $colObra = ? ORDER BY numero ASC");
$stmt_titulos->bind_param("i", $id);
$stmt_titulos->execute();
$resTitulos = $stmt_titulos->get_result();
$listaCapitulos = array();
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="obra-detalle-container">
    <div class="obra-header">
        <img class="obra-portada-detalle" src="<?php echo htmlspecialchars($portada); ?>" alt="Portada de <?php echo $titulo; ?>">
        <div class="obra-header-info">
            <h1 class="obra-titulo"><?php echo $titulo; ?></h1>
            <div class="obra-meta">
                <span class="obra-categoria"><?php echo strtoupper($categoria); ?></span>
                <span class="obra-autor-fecha">
                    <?php echo (isset($TEXTOS['por']) ? $TEXTOS['por'] : 'Por'); ?> <b><?php echo $autor; ?></b><?php echo $fecha ? " · $fecha" : ""; ?>
                </span>
            </div>
            <div class="obra-actions-row">
                <?php if ($usuario_id): ?>
                    <button id="btn-favorito"
                        class="btn-favorito<?php echo $esFavorito ? ' favorited' : ''; ?>"
                        data-obra-id="<?php echo $id; ?>"
                        data-tipo="<?php echo $tipo; ?>"
                        type="button"
                        title="<?php echo $esFavorito ? (isset($TEXTOS['quitar_favorito']) ? $TEXTOS['quitar_favorito'] : 'Quitar de favoritos') : (isset($TEXTOS['anadir_favorito']) ? $TEXTOS['anadir_favorito'] : 'Añadir a favoritos'); ?>">
                        <i class="<?php echo $esFavorito ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                <?php else: ?>
                    <button class="btn-favorito" type="button" onclick="window.location.href='<?php echo BASE_URL; ?>/public/login.php'">
                        <i class="far fa-heart"></i>
                    </button>
                <?php endif; ?>
                <button class="btn btn-compartir"
                    type="button"
                    title="<?php echo isset($TEXTOS['compartir']) ? htmlspecialchars($TEXTOS['compartir']) : 'Compartir'; ?>"
                    data-url="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $id; ?>&tipo=<?php echo $tipo; ?>"
                    data-titulo="<?php echo htmlspecialchars($titulo); ?>">
                    <i class="fas fa-share"></i>
                </button>
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
                        <i class="fas fa-arrow-left"></i> <?php echo isset($TEXTOS['anterior']) ? $TEXTOS['anterior'] : 'Anterior'; ?>
                    </a>
                <?php else: ?>
                    <button disabled><i class="fas fa-arrow-left"></i> <?php echo isset($TEXTOS['anterior']) ? $TEXTOS['anterior'] : 'Anterior'; ?></button>
                <?php endif; ?>

                <span class="paginacion-info">
                    <?php
                    $capitulo_de = isset($TEXTOS['capitulo_de']) ? $TEXTOS['capitulo_de'] : 'Capítulo %d de %d';
                    echo sprintf($capitulo_de, $capSeleccionado, $totalCaps);
                    ?>
                </span>

                <?php if ($capSeleccionado < $totalCaps): ?>
                    <a href="obra.php?id=<?php echo $id; ?>&tipo=<?php echo $tipo; ?>&cap=<?php echo $capSeleccionado+1; ?>">
                        <?php echo isset($TEXTOS['siguiente']) ? $TEXTOS['siguiente'] : 'Siguiente'; ?> <i class="fas fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <button disabled><?php echo isset($TEXTOS['siguiente']) ? $TEXTOS['siguiente'] : 'Siguiente'; ?> <i class="fas fa-arrow-right"></i></button>
                <?php endif; ?>
            </div>

            <form class="capitulo-selector-form" method="get" action="">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
                <label for="cap"><?php echo isset($TEXTOS['ir_a_capitulo']) ? htmlspecialchars($TEXTOS['ir_a_capitulo']) : 'Ir al capítulo:'; ?></label>
                <select name="cap" id="cap" onchange="this.form.submit()">
                    <?php foreach ($listaCapitulos as $cap): ?>
                        <option value="<?php echo $cap['numero']; ?>"<?php if ($capSeleccionado == $cap['numero']) echo ' selected'; ?>>
                            <?php echo (isset($TEXTOS['capitulo']) ? $TEXTOS['capitulo'] : 'Capítulo') . ' ' . intval($cap['numero']); ?>
                            <?php echo $cap['titulo'] ? ' - ' . htmlspecialchars($cap['titulo']) : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <noscript><button type="submit">Ir</button></noscript>
            </form>

            <div class="obra-capitulo">
                <h3>
                    <?php echo (isset($TEXTOS['capitulo']) ? $TEXTOS['capitulo'] : 'Capítulo') . ' ' . intval($capitulo['numero']); ?><?php echo $capitulo['titulo'] ? ": " . htmlspecialchars($capitulo['titulo']) : ""; ?>
                </h3>
                <?php if ($tipo === 'libro'): ?>
                    <div class="capitulo-texto">
                        <?php echo nl2br(htmlspecialchars(isset($capitulo['contenido']) ? $capitulo['contenido'] : '')); ?>
                    </div>
                <?php else: ?>
                    <?php if (isset($capitulo['imagen']) && $capitulo['imagen']): ?>
                        <div class="capitulo-imagen">
                            <img src="<?php echo BASE_URL . htmlspecialchars($capitulo['imagen']); ?>" alt="Página">
                        </div>
                    <?php else: ?>
                        <div class="capitulo-texto">
                            <?php echo nl2br(htmlspecialchars(isset($capitulo['contenido']) ? $capitulo['contenido'] : '')); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="obra-comentarios">
        <h2><?php echo isset($TEXTOS['comentarios']) ? htmlspecialchars($TEXTOS['comentarios']) : 'Comentarios'; ?></h2>
        <div class="obra-comentarios-lista" id="comentarios-lista">
            <!-- Comentarios AJAX -->
        </div>
        <?php if ($usuario_id): ?>
        <form id="form-comentario" method="post" action="<?php echo BASE_URL; ?>/controllers/procesar-comentario.php">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
            <input type="hidden" name="contenido_id" value="<?php echo $id; ?>">
            <input type="hidden" name="tipo_contenido" value="<?php echo htmlspecialchars($tipo); ?>">
            <textarea name="contenido" rows="3" placeholder="<?php echo isset($TEXTOS['escribe_comentario']) ? htmlspecialchars($TEXTOS['escribe_comentario']) : 'Escribe tu comentario...'; ?>" required></textarea>
            <button type="submit" class="btn btn-gradient" style="margin-top:8px;"><?php echo isset($TEXTOS['enviar']) ? htmlspecialchars($TEXTOS['enviar']) : 'Enviar'; ?></button>
        </form>
        <?php else: ?>
        <div class="comentario-login-msg">
            <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-gradient"><?php echo isset($TEXTOS['inicia_para_comentar']) ? htmlspecialchars($TEXTOS['inicia_para_comentar']) : 'Inicia sesión para comentar'; ?></a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
body { background: #fafbfc; }
.obra-detalle-container { max-width: 950px; margin: 38px auto 0 auto; background: #fff; border-radius: 18px; box-shadow: 0 6px 24px #7b2ff211; padding: 28px 24px 32px 24px; }
.obra-header { display: flex; flex-wrap: wrap; gap: 26px; align-items: flex-start; }
.obra-portada-detalle { width: 220px; height: 320px; object-fit: cover; border-radius: 14px; background: #f3f3f9; box-shadow: 0 2px 6px #7b2ff21a; }
.obra-header-info { flex: 1; }
.obra-titulo { font-size: 2.3rem; margin: 0 0 12px 0; color: #333; font-weight: 800;}
.obra-meta { display: flex; gap: 20px; align-items: center; margin-bottom: 10px; }
.obra-categoria { background: #e3e6ff; color: #7b2ff2; padding: 7px 16px; border-radius: 13px; font-size: 1.08rem; font-weight: 700; letter-spacing: 1px;}
.obra-autor-fecha { color: #888; font-size: 1.04rem;}
.obra-actions-row { margin-top: 18px; display: flex; gap: 14px;}
.btn-favorito { background: #fff; color: #e74c3c; border: 2px solid #f8d7da; border-radius: 50%; width: 46px; height: 46px; font-size: 22px; display: flex; align-items: center; justify-content: center; transition: background 0.13s, color 0.13s, border 0.13s;}
.btn-favorito.favorited, .btn-favorito:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }
.btn.btn-compartir { background: #fff; color: #7b2ff2; border: 2px solid #d9c5fc; border-radius: 50%; width: 46px; height: 46px; font-size: 21px; display: flex; align-items: center; justify-content: center; transition: background 0.13s, color 0.13s, border 0.13s;}
.btn.btn-compartir:hover { background: #7b2ff2; color: #fff; }
.obra-sinopsis { margin: 26px 0 30px 0; font-size: 1.12rem; color: #444; line-height: 1.6; }
.obra-capitulos { margin: 28px 0 38px 0; }
.capitulo-paginacion { display: flex; gap: 18px; justify-content: flex-start; align-items: center; margin-bottom: 16px; }
.capitulo-paginacion a, .capitulo-paginacion button { background: #f7f5ff; color: #7b2ff2; border: none; border-radius: 12px; padding: 9px 17px; font-weight: 600; font-size: 1.04rem; transition: all .16s;}
.capitulo-paginacion a:hover { background: #7b2ff2; color: #fff; }
.capitulo-paginacion .paginacion-info { color: #555; font-size: 1.05rem; font-weight: 600; margin: 0 14px;}
.capitulo-selector-form { margin-bottom: 18px; }
.capitulo-selector-form label { font-weight: 600; }
.capitulo-selector-form select { border-radius: 7px; padding: 7px 15px; font-size: 1.04rem;}
.obra-capitulo h3 { font-size: 1.27rem; color: #7b2ff2; }
.capitulo-texto { margin: 20px 0 8px 0; font-size: 1.11rem; color: #333; line-height: 1.7;}
.capitulo-imagen img { max-width: 100%; border-radius: 10px; box-shadow: 0 2px 10px #2222;}
.obra-comentarios { margin-top: 42px; }
.obra-comentarios-lista { margin-bottom: 25px; }
.comentario-item { background: #f8f8ff; border-radius: 11px; margin-bottom: 15px; padding: 15px 16px 8px 14px; box-shadow: 0 1px 5px #7b2ff205;}
.comentario-header { display: flex; align-items: center; gap: 10px; margin-bottom: 5px;}
.comentario-usuario { font-weight: 700; color: #7b2ff2;}
.comentario-fecha { color: #999; font-size: 0.98rem;}
.comentario-contenido { color: #333; font-size: 1.07rem;}
#form-comentario textarea { width: 100%; border-radius: 7px; border: 1px solid #e2d8f7; padding: 9px 13px; font-size: 1.09rem;}
.btn.btn-gradient { background: linear-gradient(90deg, #7b2ff2 0%, #f357a8 100%); color: #fff; border: none; border-radius: 10px; padding: 10px 28px; font-size: 1.01rem; font-weight: 700; margin-top: 7px;}
.btn.btn-gradient:hover { background: linear-gradient(90deg, #f357a8 0%, #7b2ff2 100%);}
.comentario-login-msg { margin-top: 16px; }
@media (max-width: 900px) {
    .obra-detalle-container { padding: 2vw 3vw; }
    .obra-header { flex-direction: column; align-items: flex-start;}
    .obra-portada-detalle { width: 100%; max-width: 260px; height: auto; margin-bottom: 15px;}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Favoritos AJAX
    var btnFavorito = document.getElementById('btn-favorito');
    if (btnFavorito) {
        btnFavorito.addEventListener('click', function() {
            var obraId = this.getAttribute('data-obra-id');
            var tipo = this.getAttribute('data-tipo');
            fetch('<?php echo BASE_URL; ?>/controllers/procesar-favorito.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'accion=toggle&contenido_id=' + obraId + '&tipo_contenido=' + tipo
            })
            .then(function(r){return r.json()})
            .then(function(data) {
                if (data.success) {
                    btnFavorito.classList.toggle('favorited', !!data.agregado);
                    btnFavorito.firstElementChild.className = data.agregado ? 'fas fa-heart' : 'far fa-heart';
                }
            });
        });
    }
    // Compartir
    var compartirBtns = document.querySelectorAll('.btn-compartir');
    for (var i = 0; i < compartirBtns.length; i++) {
        compartirBtns[i].addEventListener('click', function() {
            var url = this.getAttribute('data-url');
            var titulo = this.getAttribute('data-titulo');
            if (navigator.share) {
                navigator.share({ title: titulo, text: titulo, url: url });
            } else {
                navigator.clipboard.writeText(url).then(function() {
                    alert('<?php echo isset($TEXTOS['copiado_portapapeles']) ? addslashes($TEXTOS['copiado_portapapeles']) : "Enlace copiado al portapapeles"; ?>');
                });
            }
        });
    }

    // =================== COMENTARIOS ===================
    function cargarComentarios() {
        fetch('<?php echo BASE_URL; ?>/controllers/listar-comentarios.php?contenido_id=<?php echo $id; ?>&tipo_contenido=<?php echo $tipo; ?>')
        .then(function(r){return r.json()})
        .then(function(data) {
            var html = '';
            if (data.length === 0) {
                html = "<div class='comentario-item'><?php echo isset($TEXTOS['sin_comentarios']) ? addslashes($TEXTOS['sin_comentarios']) : "Aún no hay comentarios."; ?></div>";
            } else {
                for(var i=0;i<data.length;i++){
                    var com = data[i];
                    html += '<div class="comentario-item">'+
                        '<div class="comentario-header">'+
                            '<span class="comentario-usuario">'+com.usuario+'</span>'+
                            '<span class="comentario-fecha">'+com.fecha+'</span>'+
                        '</div>'+
                        '<div class="comentario-contenido">'+com.contenido+'</div>'+
                    '</div>';
                }
            }
            document.getElementById('comentarios-lista').innerHTML = html;
        });
    }
    cargarComentarios();

    // Envío AJAX comentario
    var formComentario = document.getElementById('form-comentario');
    if (formComentario) {
        formComentario.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: new URLSearchParams(Array.from(formData))
            })
            .then(function(r){return r.json()})
            .then(function(data) {
                if (data.success) {
                    cargarComentarios();
                    formComentario.reset();
                } else {
                    alert(data.mensaje || '<?php echo isset($TEXTOS['error_comentario']) ? addslashes($TEXTOS['error_comentario']) : "Error al enviar comentario"; ?>');
                }
            });
        });
    }
});
</script>

<?php include(__DIR__ . '/../includes/footer.php'); ?>