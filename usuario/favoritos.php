<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/funciones.php');
require_once(__DIR__ . '/../includes/conexion.php');

// Cargar idioma
$idioma = $_SESSION['idioma'] ?? 'es';
$archivo_idioma = __DIR__ . "/../includes/languages/{$idioma}.php";
if (!file_exists($archivo_idioma)) $archivo_idioma = __DIR__ . "/../includes/languages/es.php";
require_once($archivo_idioma);

// Verificar que el usuario esté logueado
verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

$filtro_tipo = $_GET['tipo'] ?? 'todos';
$filtro_categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$orden = $_GET['orden'] ?? 'reciente';

// Libros favoritos
$sql_libros = "SELECT l.*, c.nombre as categoria_nombre, f.fecha_agregado,
               (SELECT COUNT(*) FROM favoritos f2 WHERE f2.contenido_id = l.id AND f2.tipo_contenido = 'libro') as total_favoritos
               FROM libros l 
               JOIN favoritos f ON f.contenido_id = l.id 
               LEFT JOIN categorias c ON l.categoria_id = c.id 
               WHERE f.usuario_id = ? AND f.tipo_contenido = 'libro'";

$params_libros = [$usuario_id];
$param_types_libros = "i";

if ($filtro_tipo === 'libro' || $filtro_tipo === 'todos') {
    if ($filtro_categoria > 0) {
        $sql_libros .= " AND l.categoria_id = ?";
        $params_libros[] = $filtro_categoria;
        $param_types_libros .= "i";
    }
    switch ($orden) {
        case 'titulo':
            $sql_libros .= " ORDER BY l.titulo ASC";
            break;
        case 'autor':
            $sql_libros .= " ORDER BY l.autor ASC";
            break;
        case 'popular':
            $sql_libros .= " ORDER BY total_favoritos DESC";
            break;
        default:
            $sql_libros .= " ORDER BY f.fecha_agregado DESC";
    }
}

// Cómics favoritos
$sql_comics = "SELECT c.*, f.fecha_agregado,
               (SELECT COUNT(*) FROM favoritos f2 WHERE f2.contenido_id = c.id AND f2.tipo_contenido = 'comic') as total_favoritos
               FROM comics c 
               JOIN favoritos f ON f.contenido_id = c.id 
               WHERE f.usuario_id = ? AND f.tipo_contenido = 'comic'";

$params_comics = [$usuario_id];
$param_types_comics = "i";

switch ($orden) {
    case 'titulo':
        $sql_comics .= " ORDER BY c.titulo ASC";
        break;
    case 'autor':
        $sql_comics .= " ORDER BY c.autor ASC";
        break;
    case 'popular':
        $sql_comics .= " ORDER BY total_favoritos DESC";
        break;
    default:
        $sql_comics .= " ORDER BY f.fecha_agregado DESC";
}

$libros_favoritos = null;
$comics_favoritos = null;

if ($filtro_tipo === 'todos' || $filtro_tipo === 'libro') {
    $stmt_libros = $conexion->prepare($sql_libros);
    $stmt_libros->bind_param($param_types_libros, ...$params_libros);
    $stmt_libros->execute();
    $libros_favoritos = $stmt_libros->get_result();
}

if ($filtro_tipo === 'todos' || $filtro_tipo === 'comic') {
    $stmt_comics = $conexion->prepare($sql_comics);
    $stmt_comics->bind_param($param_types_comics, ...$params_comics);
    $stmt_comics->execute();
    $comics_favoritos = $stmt_comics->get_result();
}

// Categorías para filtro (solo de libros)
$sql_categorias = "SELECT DISTINCT c.id, c.nombre 
                   FROM categorias c 
                   JOIN libros l ON c.id = l.categoria_id 
                   JOIN favoritos f ON l.id = f.contenido_id 
                   WHERE f.usuario_id = ? AND f.tipo_contenido = 'libro'
                   ORDER BY c.nombre";
$stmt_cat = $conexion->prepare($sql_categorias);
$stmt_cat->bind_param("i", $usuario_id);
$stmt_cat->execute();
$categorias = $stmt_cat->get_result();

// Total favoritos
$total_favoritos = 0;
if ($libros_favoritos) $total_favoritos += $libros_favoritos->num_rows;
if ($comics_favoritos) $total_favoritos += $comics_favoritos->num_rows;

include_once(__DIR__ . '/../includes/header.php');
?>

<div class="container-favoritos">
    <div class="favoritos-header">
        <h1><i class="fas fa-heart"></i> <?= htmlspecialchars($TEXTOS['mis_favoritos'] ?? 'Mis Favoritos') ?></h1>
        <p><?= htmlspecialchars($TEXTOS['descripcion_favoritos'] ?? 'Todas las obras que has guardado como favoritas') ?></p>
        <div class="favoritos-stats">
            <span class="stat-item">
                <i class="fas fa-bookmark"></i>
                <?= $total_favoritos . ' ' . htmlspecialchars($TEXTOS['obras_guardadas'] ?? 'obras guardadas') ?>
            </span>
        </div>
    </div>

    <div class="favoritos-filtros">
        <form method="GET" class="filtros-form">
            <div class="filtro-grupo">
                <label for="tipo"><?= htmlspecialchars($TEXTOS['tipo'] ?? 'Tipo:') ?></label>
                <select name="tipo" id="tipo">
                    <option value="todos" <?= $filtro_tipo === 'todos' ? 'selected' : ''; ?>><?= htmlspecialchars($TEXTOS['todos'] ?? 'Todos') ?></option>
                    <option value="libro" <?= $filtro_tipo === 'libro' ? 'selected' : ''; ?>><?= htmlspecialchars($TEXTOS['libros'] ?? 'Libros') ?></option>
                    <option value="comic" <?= $filtro_tipo === 'comic' ? 'selected' : ''; ?>><?= htmlspecialchars($TEXTOS['comics'] ?? 'Cómics') ?></option>
                </select>
            </div>
            <div class="filtro-grupo">
                <label for="categoria"><?= htmlspecialchars($TEXTOS['categoria'] ?? 'Categoría:') ?></label>
                <select name="categoria" id="categoria">
                    <option value="0"><?= htmlspecialchars($TEXTOS['todas_categorias'] ?? 'Todas las categorías') ?></option>
                    <?php 
                    $categorias->data_seek(0);
                    while ($cat = $categorias->fetch_assoc()): 
                    ?>
                        <option value="<?= $cat['id']; ?>" <?= $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filtro-grupo">
                <label for="orden"><?= htmlspecialchars($TEXTOS['ordenar_por'] ?? 'Ordenar por:') ?></label>
                <select name="orden" id="orden">
                    <option value="reciente" <?= $orden === 'reciente' ? 'selected' : ''; ?>><?= htmlspecialchars($TEXTOS['mas_recientes'] ?? 'Más recientes') ?></option>
                    <option value="titulo" <?= $orden === 'titulo' ? 'selected' : ''; ?>><?= htmlspecialchars($TEXTOS['titulo_az'] ?? 'Título A-Z') ?></option>
                    <option value="autor" <?= $orden === 'autor' ? 'selected' : ''; ?>><?= htmlspecialchars($TEXTOS['autor_az'] ?? 'Autor A-Z') ?></option>
                    <option value="popular" <?= $orden === 'popular' ? 'selected' : ''; ?>><?= htmlspecialchars($TEXTOS['mas_populares'] ?? 'Más populares') ?></option>
                </select>
            </div>
            <button type="submit" class="btn-gradient">
                <i class="fas fa-filter"></i> <?= htmlspecialchars($TEXTOS['aplicar_filtros'] ?? 'Aplicar filtros') ?>
            </button>
        </form>
    </div>

    <?php if ($total_favoritos > 0): ?>
        <div class="obras-grid">
            <?php if ($libros_favoritos && $libros_favoritos->num_rows > 0): ?>
                <?php while ($libro = $libros_favoritos->fetch_assoc()): ?>
                    <?php $autor = $libro['autor'] ?? $TEXTOS['autor_desconocido'] ?? 'Autor desconocido'; ?>
                    <div class="obra-card favorita" data-obra-id="<?= $libro['id']; ?>" data-tipo="libro">
                        <div class="obra-portada">
                            <?php if (!empty($libro['portada'])): ?>
                                <img src="<?= BASE_URL . '/uploads/portadas/' . htmlspecialchars($libro['portada']); ?>" alt="<?= htmlspecialchars($libro['titulo']) ?>">
                            <?php else: ?>
                                <div class="portada-placeholder">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                            <button class="btn-quitar-favorito" data-obra-id="<?= $libro['id']; ?>" data-tipo="libro" title="<?= htmlspecialchars($TEXTOS['quitar_favorito'] ?? 'Quitar de favoritos') ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="btn btn-outline btn-compartir" data-url="<?= BASE_URL; ?>/public/obra.php?id=<?= $libro['id']; ?>&tipo=libro" data-titulo="<?= htmlspecialchars($libro['titulo']); ?>" title="<?= htmlspecialchars($TEXTOS['compartir'] ?? 'Compartir') ?>">
                                <i class="fas fa-share"></i>
                            </button>
                        </div>
                        <div class="obra-info">
                            <div class="obra-header">
                                <h3><?= htmlspecialchars($libro['titulo']); ?></h3>
                                <div class="obra-badges">
                                    <span class="badge badge-tipo"><?= htmlspecialchars($TEXTOS['libro'] ?? 'Libro') ?></span>
                                    <?php if (!empty($libro['categoria_nombre'])): ?>
                                        <span class="badge badge-categoria"><?= htmlspecialchars($libro['categoria_nombre']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($libro['publico_adulto'])): ?>
                                        <span class="badge badge-adulto"><?= htmlspecialchars($TEXTOS['adulto'] ?? '+18') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="obra-autor">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($TEXTOS['por'] ?? 'Por:') ?> <?= htmlspecialchars($autor); ?>
                            </p>
                            <?php if (!empty($libro['descripcion'])): ?>
                                <p class="obra-descripcion">
                                    <?= substr(htmlspecialchars($libro['descripcion']), 0, 120) . '...'; ?>
                                </p>
                            <?php endif; ?>
                            <div class="obra-stats">
                                <span><i class="fas fa-heart"></i> <?= $libro['total_favoritos']; ?></span>
                                <span><i class="fas fa-calendar-plus"></i> <?= htmlspecialchars($TEXTOS['guardado'] ?? 'Guardado:') ?> <?= date('d/m/Y', strtotime($libro['fecha_agregado'])); ?></span>
                            </div>
                            <div class="obra-acciones">
                                <a href="<?= BASE_URL; ?>/public/obra.php?id=<?= $libro['id']; ?>&tipo=libro" class="btn btn-gradient">
                                    <i class="fas fa-eye"></i> <?= htmlspecialchars($TEXTOS['ver_obra'] ?? 'Ver obra') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>

            <?php if ($comics_favoritos && $comics_favoritos->num_rows > 0): ?>
                <?php while ($comic = $comics_favoritos->fetch_assoc()): ?>
                    <?php $autor = $comic['autor'] ?? $TEXTOS['autor_desconocido'] ?? 'Autor desconocido'; ?>
                    <div class="obra-card favorita" data-obra-id="<?= $comic['id']; ?>" data-tipo="comic">
                        <div class="obra-portada">
                            <?php if (!empty($comic['portada'])): ?>
                                <img src="<?= BASE_URL . '/uploads/portadas/' . htmlspecialchars($comic['portada']); ?>" alt="<?= htmlspecialchars($comic['titulo']); ?>">
                            <?php else: ?>
                                <div class="portada-placeholder">
                                    <i class="fas fa-book-open"></i>
                                </div>
                            <?php endif; ?>
                            <button class="btn-quitar-favorito" data-obra-id="<?= $comic['id']; ?>" data-tipo="comic" title="<?= htmlspecialchars($TEXTOS['quitar_favorito'] ?? 'Quitar de favoritos') ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="btn btn-outline btn-compartir" data-url="<?= BASE_URL; ?>/public/obra.php?id=<?= $comic['id']; ?>&tipo=comic" data-titulo="<?= htmlspecialchars($comic['titulo']); ?>" title="<?= htmlspecialchars($TEXTOS['compartir'] ?? 'Compartir') ?>">
                                <i class="fas fa-share"></i>
                            </button>
                        </div>
                        <div class="obra-info">
                            <div class="obra-header">
                                <h3><?= htmlspecialchars($comic['titulo']); ?></h3>
                                <div class="obra-badges">
                                    <span class="badge badge-tipo"><?= htmlspecialchars($TEXTOS['comic'] ?? 'Cómic') ?></span>
                                    <?php if (!empty($comic['publico_adulto'])): ?>
                                        <span class="badge badge-adulto"><?= htmlspecialchars($TEXTOS['adulto'] ?? '+18') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="obra-autor">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($TEXTOS['por'] ?? 'Por:') ?> <?= htmlspecialchars($autor); ?>
                            </p>
                            <?php if (!empty($comic['descripcion'])): ?>
                                <p class="obra-descripcion">
                                    <?= substr(htmlspecialchars($comic['descripcion']), 0, 120) . '...'; ?>
                                </p>
                            <?php endif; ?>
                            <div class="obra-stats">
                                <span><i class="fas fa-heart"></i> <?= $comic['total_favoritos']; ?></span>
                                <span><i class="fas fa-calendar-plus"></i> <?= htmlspecialchars($TEXTOS['guardado'] ?? 'Guardado:') ?> <?= date('d/m/Y', strtotime($comic['fecha_agregado'])); ?></span>
                            </div>
                            <div class="obra-acciones">
                                <a href="<?= BASE_URL; ?>/public/obra.php?id=<?= $comic['id']; ?>&tipo=comic" class="btn btn-gradient">
                                    <i class="fas fa-eye"></i> <?= htmlspecialchars($TEXTOS['ver_obra'] ?? 'Ver obra') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="no-favoritos">
            <div class="no-favoritos-icon">
                <i class="fas fa-heart-broken"></i>
            </div>
            <h3><?= htmlspecialchars($TEXTOS['no_tienes_favoritos'] ?? 'No tienes obras favoritas') ?></h3>
            <p><?= htmlspecialchars($TEXTOS['explicacion_favoritos'] ?? 'Cuando encuentres obras que te gusten, puedes guardarlas como favoritas haciendo clic en el corazón.') ?></p>
            <div class="no-favoritos-acciones">
                <a href="<?= BASE_URL; ?>/public/explorar.php" class="btn btn-gradient">
                    <i class="fas fa-compass"></i> <?= htmlspecialchars($TEXTOS['explorar_obras'] ?? 'Explorar obras') ?>
                </a>
                <a href="<?= BASE_URL; ?>/public/buscar.php" class="btn btn-outline">
                    <i class="fas fa-search"></i> <?= htmlspecialchars($TEXTOS['buscar_obras'] ?? 'Buscar obras') ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
body { background: #fafbfc; }
.container-favoritos { max-width: 1220px; margin: 40px auto 0 auto; padding: 0 24px; }
.favoritos-header { margin-bottom: 30px; }
.favoritos-header h1 { font-size: 2.2rem; color: #c0392b; margin-bottom: 0; }
.favoritos-header p { color: #666; margin: 8px 0 20px 1px; font-size: 1.08rem; }
.favoritos-stats { margin-bottom: 12px; }
.stat-item { display: flex; align-items: center; gap: 8px; color: #666; font-weight: 500; }
.favoritos-filtros { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); padding: 20px 18px 10px 18px; margin-bottom: 32px; }
.filtros-form { display: flex; gap: 18px; align-items: flex-end; flex-wrap: wrap; }
.filtro-grupo { display: flex; flex-direction: column; gap: 5px; }
.filtro-grupo label { font-weight: 600; color: #333; font-size: 14px; }
.filtro-grupo select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 7px; font-size: 15px; min-width: 145px; }
.btn-gradient {
    background: linear-gradient(90deg, #7b2ff2 0%, #f357a8 100%);
    color: #fff;
    border: none;
    border-radius: 11px;
    padding: 12px 28px;
    font-size: 1.01rem;
    font-weight: 700;
    box-shadow: 0 6px 24px rgba(123,47,242,0.10);
    transition: box-shadow 0.2s, transform 0.2s;
}
.btn-gradient:hover { transform: translateY(-2px) scale(1.04); box-shadow: 0 12px 40px rgba(123,47,242,0.12);}
.btn-outline {
    background: #fff;
    color: #7b2ff2;
    border: 2px solid #e2d8f7;
    border-radius: 11px;
    padding: 12px 28px;
    font-size: 1.01rem;
    font-weight: 700;
    transition: background 0.2s, color 0.2s;
}
.btn-outline:hover {
    background: linear-gradient(90deg, #7b2ff2 0%, #f357a8 100%);
    color: #fff;
}
.obras-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 28px; }
.obra-card { background: #fff; border-radius: 13px; box-shadow: 0 4px 24px rgba(123,47,242,0.07); overflow: hidden; transition: box-shadow .2s, transform .2s; position: relative; }
.obra-card:hover { transform: translateY(-6px) scale(1.02); box-shadow: 0 8px 32px rgba(123,47,242,0.13); }
.obra-portada { height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: relative; display: flex; justify-content: center; align-items: center; }
.obra-portada img { width: 100%; height: 100%; object-fit: cover; }
.portada-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #fff9; font-size: 48px; }
.btn-quitar-favorito, .btn-compartir {
    position: absolute; top: 10px; width: 38px; height: 38px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; font-size: 17px; cursor: pointer; transition: background 0.2s, color 0.2s;
}
.btn-quitar-favorito { right: 10px; background: #e74c3c; color: #fff; z-index: 2; }
.btn-quitar-favorito:hover { background: #c0392b; }
.btn-compartir { right: 54px; background: #fff; color: #7b2ff2; border: 2px solid #e2d8f7; }
.btn-compartir:hover { background: #7b2ff2; color: #fff; }
.obra-info { padding: 22px 20px 19px 20px; }
.obra-header h3 { margin: 0 0 9px 0; font-size: 1.18rem; color: #222; }
.obra-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
.badge { padding: 3px 9px; border-radius: 11px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
.badge-tipo { background: #e3f2fd; color: #7b2ff2; }
.badge-categoria { background: #f3e5f5; color: #f357a8; }
.badge-adulto { background: #ffebee; color: #d32f2f; }
.obra-autor { color: #666; margin: 10px 0 0 0; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 7px; }
.obra-descripcion { color: #555; line-height: 1.5; margin: 12px 0 0 0; font-size: 14px; }
.obra-stats { display: flex; gap: 15px; margin: 13px 0 0 0; font-size: 13px; color: #777; }
.obra-stats span { display: flex; align-items: center; gap: 4px; }
.obra-acciones { display: flex; gap: 9px; margin-top: 13px; }
.no-favoritos { text-align: center; padding: 60px 20px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);}
.no-favoritos-icon { font-size: 64px; color: #ddd; margin-bottom: 20px;}
.no-favoritos h3 { color: #c0392b; margin-bottom: 10px;}
.no-favoritos p { color: #666; margin-bottom: 30px; max-width: 400px; margin-left: auto; margin-right: auto;}
.no-favoritos-acciones { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
@media (max-width: 900px) {
    .obras-grid { grid-template-columns: 1fr; }
    .container-favoritos { padding: 0 7px; }
    .favoritos-header h1 { font-size: 1.4rem;}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-quitar-favorito').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const obraId = this.dataset.obraId;
            const tipo = this.dataset.tipo;
            if (confirm('<?= addslashes($TEXTOS['confirmar_quitar_favorito'] ?? "¿Estás seguro de que quieres quitar esta obra de tus favoritos?") ?>')) {
                quitarDeFavoritos(obraId, tipo);
            }
        });
    });
    function quitarDeFavoritos(obraId, tipo) {
        fetch('<?php echo BASE_URL; ?>/controllers/procesar-favorito.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
            body: `accion=quitar&contenido_id=${obraId}&tipo_contenido=${tipo}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const card = document.querySelector(`[data-obra-id="${obraId}"][data-tipo="${tipo}"]`);
                if (card) {
                    card.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => {
                        card.remove();
                        if (document.querySelectorAll('.obra-card').length === 0) {
                            location.reload();
                        }
                    }, 300);
                }
            } else {
                alert('<?= addslashes($TEXTOS['error_quitar_favorito'] ?? "Error al quitar de favoritos") ?>');
            }
        })
        .catch(error => {
            alert('<?= addslashes($TEXTOS['error_conexion'] ?? "Error de conexión") ?>');
        });
    }

    document.querySelectorAll('.btn-compartir').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const titulo = this.dataset.titulo;
            if (navigator.share) {
                navigator.share({ title: titulo, text: titulo, url: url });
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    alert('<?= addslashes($TEXTOS['copiado_portapapeles'] ?? "Enlace copiado al portapapeles") ?>');
                });
            }
        });
    });
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>