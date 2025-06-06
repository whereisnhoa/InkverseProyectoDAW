<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/conexion.php');
require_once(__DIR__ . '/../includes/funciones.php');

// Cargar idioma
$idioma = $_SESSION['idioma'] ?? 'es';
$archivo_idioma = __DIR__ . "/../includes/languages/" . $idioma . ".php";
if (!file_exists($archivo_idioma)) $archivo_idioma = __DIR__ . "/../includes/languages/es.php";
require_once($archivo_idioma);

include(__DIR__ . '/../includes/header.php');

$usuario_id = $_SESSION['usuario_id'] ?? null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipo = $_GET['tipo'] ?? '';
$capSeleccionado = isset($_GET['cap']) ? intval($_GET['cap']) : 1;

if (!$id || ($tipo !== 'libro' && $tipo !== 'comic')) {
    echo "<div class='alerta alerta-error'>" . ($TEXTOS['obra_no_encontrada'] ?? 'Obra no encontrada.') . "</div>";
    include(__DIR__ . '/../includes/footer.php');
    exit();
}

// === OBTENER DATOS DE LA OBRA ===
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
    echo "<div class='alerta alerta-error'>" . ($TEXTOS['obra_no_encontrada'] ?? 'Obra no encontrada.') . "</div>";
    include(__DIR__ . '/../includes/footer.php');
    exit();
}

// --- OBTENER CATEGORÍA REAL (si tiene relación N:M)
function obtenerNombreCategoria($conexion, $obra_id, $tipo) {
    if ($tipo === 'libro') {
        $sql = "SELECT c.nombre FROM libro_categorias lc JOIN categorias c ON lc.categoria_id = c.id WHERE lc.libro_id = ? LIMIT 1";
    } else {
        $sql = "SELECT c.nombre FROM comic_categorias cc JOIN categorias c ON cc.categoria_id = c.id WHERE cc.comic_id = ? LIMIT 1";
    }
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $obra_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row && !empty($row['nombre']) ? htmlspecialchars($row['nombre']) : ($GLOBALS['TEXTOS']['sin_categoria'] ?? 'Sin categoría');
}
$categoria = obtenerNombreCategoria($conexion, $id, $tipo);

$titulo = htmlspecialchars($obra['titulo'] ?? ($TEXTOS['sin_titulo'] ?? 'Sin título'));
// MEJOR AUTOR: Prioriza autor_nombre, luego autor, luego "Anónimo"
if (!empty($obra['autor_nombre'])) {
    $autor = htmlspecialchars($obra['autor_nombre']);
} elseif (!empty($obra['autor'])) {
    $autor = htmlspecialchars($obra['autor']);
} else {
    $autor = $TEXTOS['autor_desconocido'] ?? 'Anónimo';
}
$fecha = (!empty($obra['fecha_subida'])) ? date('d/m/Y', strtotime($obra['fecha_subida'])) : '';
$descripcion = !empty($obra['descripcion']) ? nl2br(htmlspecialchars($obra['descripcion'])) : ($TEXTOS['sin_descripcion'] ?? 'Sin descripción.');
$portada = (!empty($obra['portada']) && strpos($obra['portada'], '/uploads/') === false)
    ? BASE_URL . '/uploads/portadas/' . $obra['portada']
    : (!empty($obra['portada']) ? BASE_URL . $obra['portada'] : BASE_URL . '/img/portada_default.png');

// --- FAVORITOS ---
$tipo_favorito = ($tipo === 'libro' || $tipo === 'comic') ? $tipo : '';
$esFavorito = false;
if ($usuario_id && $tipo_favorito && $id) {
    $stmtFav = $conexion->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?");
    $stmtFav->bind_param("isi", $usuario_id, $tipo_favorito, $id);
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
$totalCaps = $totalCaps['total'] ?? 0;
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="obra-detalle-mainbox glass-card">
    <div class="obra-detalle-flex">
        <img class="obra-portada-detalle neon-border" src="<?php echo htmlspecialchars($portada); ?>" alt="Portada de <?php echo $titulo; ?>">
        <div class="obra-detalle-datos">
            <h1 class="obra-titulo gradient-text"><?php echo $titulo; ?></h1>
            <div class="obra-meta">
                <span class="obra-categoria badge-cat"><?php echo strtoupper($categoria); ?></span>
                <span class="obra-autor-fecha">
                    <?php echo ($TEXTOS['por'] ?? 'Por'); ?> <b><?php echo $autor; ?></b><?php echo $fecha ? " · $fecha" : ""; ?>
                </span>
            </div>
            <div class="obra-actions-row">
                <?php if ($usuario_id): ?>
                    <button id="btn-favorito"
                        class="btn-favorito<?php echo $esFavorito ? ' favorited' : ''; ?>"
                        data-obra-id="<?php echo $id; ?>"
                        data-tipo_contenido="<?php echo $tipo; ?>"
                        type="button"
                        title="<?php echo $esFavorito ? ($TEXTOS['quitar_favorito'] ?? 'Quitar de favoritos') : ($TEXTOS['anadir_favorito'] ?? 'Añadir a favoritos'); ?>">
                        <i class="<?php echo $esFavorito ? 'fas' : 'far'; ?> fa-heart"></i>
                        <span class="fav-txt">
                          <?php echo $esFavorito ? ($TEXTOS['quitar_favorito'] ?? 'Quitar de favoritos') : ($TEXTOS['anadir_favorito'] ?? 'Añadir a favoritos'); ?>
                        </span>
                    </button>
                <?php else: ?>
                    <button class="btn-favorito" type="button" onclick="window.location.href='<?php echo BASE_URL; ?>../public/login.php'">
                        <i class="far fa-heart"></i>
                        <span class="fav-txt"><?php echo $TEXTOS['anadir_favorito'] ?? 'Añadir a favoritos'; ?></span>
                    </button>
                <?php endif; ?>
                <button class="btn btn-compartir"
                    type="button"
                    title="<?php echo htmlspecialchars($TEXTOS['compartir'] ?? 'Compartir'); ?>"
                    data-url="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $id; ?>&tipo=<?php echo $tipo; ?>"
                    data-titulo="<?php echo htmlspecialchars($titulo); ?>">
                    <i class="fas fa-share"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="obra-sinopsis glass-sinopsis">
        <?php echo $descripcion; ?>
    </div>

    <?php if ($totalCaps > 0 && $capitulo): ?>
        <div class="obra-capitulos">
            <div class="capitulo-paginacion">
                <?php if ($capSeleccionado > 1): ?>
                    <a href="obra.php?id=<?php echo $id; ?>&tipo=<?php echo $tipo; ?>&cap=<?php echo $capSeleccionado-1; ?>">
                        <i class="fas fa-arrow-left"></i> <?php echo $TEXTOS['anterior'] ?? 'Anterior'; ?>
                    </a>
                <?php else: ?>
                    <button disabled><i class="fas fa-arrow-left"></i> <?php echo $TEXTOS['anterior'] ?? 'Anterior'; ?></button>
                <?php endif; ?>

                <span class="paginacion-info">
                    <?php
                    $capitulo_de = $TEXTOS['capitulo_de'] ?? 'Capítulo %d de %d';
                    echo sprintf($capitulo_de, $capSeleccionado, $totalCaps);
                    ?>
                </span>

                <?php if ($capSeleccionado < $totalCaps): ?>
                    <a href="obra.php?id=<?php echo $id; ?>&tipo=<?php echo $tipo; ?>&cap=<?php echo $capSeleccionado+1; ?>">
                        <?php echo $TEXTOS['siguiente'] ?? 'Siguiente'; ?> <i class="fas fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <button disabled><?php echo $TEXTOS['siguiente'] ?? 'Siguiente'; ?> <i class="fas fa-arrow-right"></i></button>
                <?php endif; ?>
            </div>

            <form class="capitulo-selector-form" method="get" action="">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
                <label for="cap"><?php echo htmlspecialchars($TEXTOS['ir_a_capitulo'] ?? 'Ir al capítulo:'); ?></label>
                <select name="cap" id="cap" onchange="this.form.submit()">
                    <?php foreach ($listaCapitulos as $cap): ?>
                        <option value="<?php echo $cap['numero']; ?>"<?php if ($capSeleccionado == $cap['numero']) echo ' selected'; ?>>
                            <?php echo ($TEXTOS['capitulo'] ?? 'Capítulo') . ' ' . intval($cap['numero']); ?>
                            <?php echo $cap['titulo'] ? ' - ' . htmlspecialchars($cap['titulo']) : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <noscript><button type="submit">Ir</button></noscript>
            </form>

            <div class="obra-capitulo glass-chapter">
                <h3>
                    <?php echo ($TEXTOS['capitulo'] ?? 'Capítulo') . ' ' . intval($capitulo['numero']); ?><?php echo $capitulo['titulo'] ? ": " . htmlspecialchars($capitulo['titulo']) : ''; ?>
                </h3>
                <?php if ($tipo === 'libro'): ?>
                    <div class="capitulo-texto">
                        <?php echo nl2br(htmlspecialchars($capitulo['contenido'] ?? '')); ?>
                    </div>
                <?php else: ?>
                    <?php if (isset($capitulo['imagen']) && $capitulo['imagen']): ?>
                        <div class="capitulo-imagen">
                            <img src="<?php echo BASE_URL . htmlspecialchars($capitulo['imagen']); ?>" alt="Página">
                        </div>
                    <?php else: ?>
                        <div class="capitulo-texto">
                            <?php echo nl2br(htmlspecialchars($capitulo['contenido'] ?? '')); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="obra-comentarios">
        <h2 class="comentarios-h2"><i class="far fa-comments"></i> <?php echo htmlspecialchars($TEXTOS['comentarios'] ?? 'Comentarios'); ?></h2>
        <div class="obra-comentarios-lista" id="comentarios-lista">
            <!-- Comentarios AJAX -->
        </div>
        <?php if ($usuario_id): ?>
        <form id="form-comentario" method="post" action="<?php echo BASE_URL; ?>/controllers/procesar-comentario.php">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
            <input type="hidden" name="contenido_id" value="<?php echo $id; ?>">
            <input type="hidden" name="tipo_contenido" value="<?php echo htmlspecialchars($tipo); ?>">
            <textarea name="contenido" rows="3" maxlength="650" placeholder="<?php echo htmlspecialchars($TEXTOS['escribe_comentario'] ?? 'Escribe tu comentario...'); ?>" required></textarea>
            <button type="submit" class="btn btn-gradient" style="margin-top:8px;"><?php echo htmlspecialchars($TEXTOS['enviar'] ?? 'Enviar'); ?></button>
        </form>
        <?php else: ?>
        <div class="comentario-login-msg">
            <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-gradient"><?php echo htmlspecialchars($TEXTOS['inicia_para_comentar'] ?? 'Inicia sesión para comentar'); ?></a>
        </div>
        <?php endif; ?>
    </div>
    <div id="alerta-flotante" style="display:none; position:fixed;top:25px;right:30px;z-index:3002;min-width:220px;"
         class="alerta-flotante"></div>
</div>

<style>
body {
    background: linear-gradient(120deg, #e0e0ff 45%, #f8e7ff 100%);
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
}
.glass-card {
    max-width: 1100px;
    margin: 50px auto 0 auto;
    background: rgba(255, 255, 255, 0.66);
    border-radius: 2.2rem;
    box-shadow: 0 12px 50px #7b2ff222, 0 1.5px 6px #7b2ff211;
    padding: 38px 36px 38px 36px;
    backdrop-filter: blur(10px);
    border: 1.7px solid #ededfc;
    transition: box-shadow .22s;
}
.obra-detalle-flex {
    display: flex;
    flex-wrap: wrap;
    gap: 46px;
    align-items: flex-start;
}
.obra-portada-detalle {
    width: 290px; height: 400px;
    object-fit: cover;
    border-radius: 2rem;
    background: #f3f3f9;
    box-shadow: 0 4px 18px #a179e933;
    border:3px solid #e4e1fa;
    transition: box-shadow .18s, border .18s;
}
.neon-border {
    box-shadow: 0 0 0 2px #7b2ff2aa, 0 0 16px 1.5px #a179e9aa;
    border:3px solid #a179e9;
}
.obra-detalle-datos {
    flex: 1;
    min-width: 220px;
    padding-top: 3px;
}
.gradient-text {
    background: linear-gradient(90deg, #7b2ff2 20%, #f357a8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-size: 3rem;
    font-weight: 900;
    margin: 0 0 10px 0;
    letter-spacing: 1px;
}
.obra-meta { display: flex; gap: 27px; align-items: center; margin-bottom: 14px; }
.badge-cat {
    background: linear-gradient(90deg, #eceaff 0%, #d8f3ff 100%);
    color: #7b2ff2;
    padding: 8px 20px;
    border-radius: 16px;
    font-size: 1.13rem;
    font-weight: 900;
    letter-spacing: 1px;
    border: 1.5px solid #e4e1fa;
    box-shadow: 0 2px 7px #a179e920;
    text-shadow: 0px 1px 0 #fff;
}
.obra-autor-fecha { color: #7b88b2; font-size: 1.16rem;}
.obra-actions-row { margin-top: 22px; display: flex; gap: 18px;}
.btn-favorito {
    background: linear-gradient(90deg, #ff6464 0%, #ff8f8f 100%);
    color: #fff; border: none;
    border-radius: 32px;
    min-width: 58px; height: 54px; font-size: 1.26rem;
    display: flex; align-items: center; gap: 13px; padding: 0 26px 0 21px;
    cursor: pointer; font-weight: 900; box-shadow: 0 2px 15px #e74c3c15; letter-spacing: .5px; transition: all .17s;
    outline: none; position: relative;
}
.btn-favorito .fav-txt { color: #fff; font-size: 1.18rem; font-weight: 900; letter-spacing: .4px;}
.btn-favorito.favorited, .btn-favorito:hover {
    background: linear-gradient(90deg, #7b2ff2 0%, #f357a8 100%);
    color: #fff;
}
.btn-favorito.favorited .fav-txt, .btn-favorito:hover .fav-txt {
    color: #fff;
}
.btn.btn-compartir {
    background: #fff; color: #7b2ff2; border: 2px solid #e9dafe; border-radius: 50%;
    width: 54px; height: 54px; font-size: 27px; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 14px #7b2ff215; transition: all .18s;
}
.btn.btn-compartir:hover { background: #7b2ff2; color: #fff; }
.obra-sinopsis {
    margin: 36px 0 30px 0;
    font-size: 1.22rem;
    color: #303049;
    line-height: 1.8;
    background: #f7f7fd;
    padding: 27px 32px;
    border-radius: 20px;
    font-weight: 500;
    letter-spacing: .01em;
    border: 1.5px solid #edeaff;
    box-shadow: 0 2px 19px #7b2ff209;
}
.glass-sinopsis {
    background: rgba(242,242,255,0.63);
    border: 1.5px solid #e0d5ff;
    box-shadow: 0 0 19px #d6bfff18;
    backdrop-filter: blur(4px);
}
.obra-capitulos { margin: 30px 0 42px 0; }
.capitulo-paginacion { display: flex; gap: 18px; justify-content: flex-start; align-items: center; margin-bottom: 18px; }
.capitulo-paginacion a, .capitulo-paginacion button {
    background: linear-gradient(90deg, #edeaff 0%, #d8f3ff 100%);
    color: #7b2ff2;
    border: none;
    border-radius: 15px;
    padding: 11px 26px;
    font-weight: 800; font-size: 1.09rem;
    transition: all .14s;
    box-shadow: 0 1px 7px #7b2ff215;
}
.capitulo-paginacion a:hover { background: #7b2ff2; color: #fff; }
.capitulo-paginacion .paginacion-info { color: #555; font-size: 1.09rem; font-weight: 700; margin: 0 16px;}
.capitulo-selector-form { margin-bottom: 18px; }
.capitulo-selector-form label { font-weight: 700; }
.capitulo-selector-form select { border-radius: 8px; padding: 7px 22px; font-size: 1.13rem;}
.obra-capitulo h3 { font-size: 1.35rem; color: #7b2ff2; }
.capitulo-texto { margin: 19px 0 8px 0; font-size: 1.23rem; color: #232a54; line-height: 1.77;}
.glass-chapter {
    background: rgba(255,255,255,0.7);
    border-radius: 16px;
    box-shadow: 0 1px 11px #a179e912;
    border: 1.3px solid #edeaff;
    padding: 22px 26px;
}
.capitulo-imagen img { max-width: 100%; border-radius: 13px; box-shadow: 0 2px 10px #2222;}
.obra-comentarios { margin-top: 58px; }
.comentarios-h2 { margin-bottom: 18px; color: #7b2ff2; font-size: 1.5rem; font-weight: 900; letter-spacing: 0.2px;}
.obra-comentarios-lista { margin-bottom: 27px; }
.comentario-item {
    background: rgba(242,242,255,0.63);
    border-radius: 16px;
    margin-bottom: 22px;
    padding: 18px 19px 10px 19px;
    box-shadow: 0 1px 9px #7b2ff209;
    border: 1.3px solid #e8e6fd;
}
.comentario-header {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 5px;
}
.comentario-avatar {
    width: 37px;
    height: 37px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 10px;
    background: #f2f2f2;
    border: 2px solid #eaeaea;
}
.comentario-usuario { font-weight: 900; color: #7b2ff2; font-size: 1.11rem;}
.comentario-fecha { color: #999; font-size: 1.01rem;}
.comentario-contenido { color: #232a54; font-size: 1.15rem;}
#form-comentario textarea {
    width: 100%; border-radius: 9px; border: 1.8px solid #e2d8f7; padding: 13px 17px; font-size: 1.17rem; background: #fafaff;
    box-shadow: 0 1px 7px #7b2ff209;
}
.btn.btn-gradient {
    background: linear-gradient(90deg, #7b2ff2 0%, #f357a8 100%);
    color: #fff; border: none; border-radius: 12px; padding: 13px 36px; font-size: 1.13rem; font-weight: 700; margin-top: 8px;
    transition: background .17s;
    box-shadow: 0 1px 7px #f357a820;
}
.btn.btn-gradient:hover { background: linear-gradient(90deg, #f357a8 0%, #7b2ff2 100%);}
.comentario-login-msg { margin-top: 16px; }
.alerta-flotante {font-size:1.13rem; font-weight:800;}
@media (max-width: 1100px) {
    .glass-card {padding: 2vw 3vw;}
    .obra-detalle-flex { flex-direction: column; gap: 20px;}
    .obra-portada-detalle { width: 100%; max-width: 330px; height: auto; margin-bottom: 17px;}
}
@media (max-width: 700px) {
    .glass-card {padding: 0.7rem;}
    .obra-portada-detalle {max-width:90vw;}
    .obra-sinopsis {padding: 15px;}
    .glass-chapter {padding: 12px;}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btnFavorito = document.getElementById('btn-favorito');
    if (btnFavorito) {
        btnFavorito.addEventListener('click', function() {
            var obraId = this.getAttribute('data-obra-id');
            var tipo = this.getAttribute('data-tipo_contenido');
            if (tipo === 'libros') tipo = 'libro';
            if (tipo === 'comics') tipo = 'comic';
            fetch('<?php echo BASE_URL; ?>/controllers/procesar-favorito.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'accion=toggle&contenido_id=' + encodeURIComponent(obraId) + '&tipo_contenido=' + encodeURIComponent(tipo)
            })
            .then(r => r.json())
            .then(function(data) {
                if (typeof data.agregado !== "undefined") {
                    btnFavorito.classList.toggle('favorited', !!data.agregado);
                    btnFavorito.firstElementChild.className = data.agregado ? 'fas fa-heart' : 'far fa-heart';
                    btnFavorito.querySelector('.fav-txt').textContent = data.agregado
                        ? "<?php echo addslashes($TEXTOS['quitar_favorito'] ?? 'Quitar de favoritos'); ?>"
                        : "<?php echo addslashes($TEXTOS['anadir_favorito'] ?? 'Añadir a favoritos'); ?>";
                }
            });
        });
    }

    // Compartir y comentarios igual que antes...
    var compartirBtns = document.querySelectorAll('.btn-compartir');
    compartirBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            var url = this.getAttribute('data-url');
            var titulo = this.getAttribute('data-titulo');
            if (navigator.share) {
                navigator.share({ title: titulo, text: titulo, url: url });
            } else {
                navigator.clipboard.writeText(url);
            }
        });
    });

    function cargarComentarios() {
        fetch('<?php echo BASE_URL; ?>/controllers/listar-comentarios.php?contenido_id=<?php echo $id; ?>&tipo_contenido=<?php echo $tipo; ?>')
        .then(r => r.json())
        .then(function(data) {
            var html = '';
            if (data.length === 0) {
                html = "<div class='comentario-item'><?php echo addslashes($TEXTOS['sin_comentarios'] ?? "Aún no hay comentarios."); ?></div>";
            } else {
                for(var i=0;i<data.length;i++){
                    var com = data[i];
                    html += '<div class="comentario-item">'+
                        '<div class="comentario-header">'+
                            '<img class="comentario-avatar" src="'+com.avatar+'" alt="avatar" />'+
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

    var formComentario = document.getElementById('form-comentario');
    if (formComentario) {
        formComentario.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: new URLSearchParams(Array.from(formData))
            })
            .then(r => r.json())
            .then(function(data) {
                if (data.success) {
                    cargarComentarios();
                    formComentario.reset();
                }
            });
        });
    }
});
</script>

<?php include(__DIR__ . '/../includes/footer.php'); ?>