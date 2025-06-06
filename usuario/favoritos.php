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
$sql_libros = "SELECT l.*, u.usuario AS autor_nombre, c.nombre as categoria_nombre, f.fecha_agregado,
               (SELECT COUNT(*) FROM favoritos f2 WHERE f2.contenido_id = l.id AND f2.tipo_contenido = 'libro') as total_favoritos
               FROM libros l 
               JOIN favoritos f ON f.contenido_id = l.id AND f.tipo_contenido = 'libro'
               LEFT JOIN categorias c ON l.categoria_id = c.id 
               LEFT JOIN usuarios u ON l.usuario_id = u.id
               WHERE f.usuario_id = ?";
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
            $sql_libros .= " ORDER BY autor_nombre ASC";
            break;
        case 'popular':
            $sql_libros .= " ORDER BY total_favoritos DESC";
            break;
        default:
            $sql_libros .= " ORDER BY f.fecha_agregado DESC";
    }
}

// Cómics favoritos
$sql_comics = "SELECT c.*, u.usuario AS autor_nombre, f.fecha_agregado,
               (SELECT COUNT(*) FROM favoritos f2 WHERE f2.contenido_id = c.id AND f2.tipo_contenido = 'comic') as total_favoritos
               FROM comics c 
               JOIN favoritos f ON f.contenido_id = c.id AND f.tipo_contenido = 'comic'
               LEFT JOIN usuarios u ON c.usuario_id = u.id
               WHERE f.usuario_id = ?";
$params_comics = [$usuario_id];
$param_types_comics = "i";

switch ($orden) {
    case 'titulo':
        $sql_comics .= " ORDER BY c.titulo ASC";
        break;
    case 'autor':
        $sql_comics .= " ORDER BY autor_nombre ASC";
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
                   JOIN favoritos f ON l.id = f.contenido_id AND f.tipo_contenido = 'libro'
                   WHERE f.usuario_id = ?
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

    <form method="GET" class="filtros-form" style="display:flex;gap:16px;align-items:center;margin-bottom:18px;flex-wrap:wrap;">
        <label for="tipo">Tipo:</label>
        <select name="tipo" id="tipo">
            <option value="todos" <?= $filtro_tipo === 'todos' ? 'selected' : ''; ?>>Todos</option>
            <option value="libro" <?= $filtro_tipo === 'libro' ? 'selected' : ''; ?>>Libros</option>
            <option value="comic" <?= $filtro_tipo === 'comic' ? 'selected' : ''; ?>>Cómics</option>
        </select>
        <label for="categoria">Categoría:</label>
        <select name="categoria" id="categoria">
            <option value="0">Todas las categorías</option>
            <?php $categorias->data_seek(0); while ($cat = $categorias->fetch_assoc()): ?>
                <option value="<?= $cat['id']; ?>" <?= $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($cat['nombre']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <label for="orden">Ordenar por:</label>
        <select name="orden" id="orden">
            <option value="reciente" <?= $orden === 'reciente' ? 'selected' : ''; ?>>Más recientes</option>
            <option value="titulo" <?= $orden === 'titulo' ? 'selected' : ''; ?>>Título A-Z</option>
            <option value="autor" <?= $orden === 'autor' ? 'selected' : ''; ?>>Autor A-Z</option>
            <option value="popular" <?= $orden === 'popular' ? 'selected' : ''; ?>>Más populares</option>
        </select>
        <button type="submit" class="btn-filtros">
            <i class="fas fa-filter"></i> Aplicar filtros
        </button>
    </form>
    <div class="obras-grid">
    <?php
    // Libros favoritos
    if ($libros_favoritos && $libros_favoritos->num_rows > 0):
        while ($libro = $libros_favoritos->fetch_assoc()):
            $autor = !empty($libro['autor_nombre']) ? $libro['autor_nombre'] : (!empty($libro['autor']) ? $libro['autor'] : 'Autor desconocido');
            ?>
            <div class="tarjeta-obra" data-obra-id="<?= $libro['id']; ?>" data-tipo="libro">
                <div class="tarjeta-portada">
                    <?php if (!empty($libro['portada'])): ?>
                        <img src="<?= BASE_URL . '/uploads/portadas/' . htmlspecialchars($libro['portada']); ?>" alt="<?= htmlspecialchars($libro['titulo']) ?>">
                    <?php endif; ?>
                    <button class="btn-quitar-favorito" data-obra-id="<?= $libro['id']; ?>" data-tipo="libro" title="Quitar de favoritos">
                        <i class="fas fa-heart"></i>
                    </button>
                    <button class="btn-compartir" data-url="<?= BASE_URL; ?>/public/obra.php?id=<?= $libro['id']; ?>&tipo=libro" data-titulo="<?= htmlspecialchars($libro['titulo']); ?>" title="Compartir">
                        <i class="fas fa-share"></i>
                    </button>
                </div>
                <div class="tarjeta-info">
                    <div class="tarjeta-titulo"><?= htmlspecialchars($libro['titulo']); ?></div>
                    <div class="tarjeta-autor"><?= htmlspecialchars($autor); ?></div>
                </div>
            </div>
        <?php endwhile;
    endif;
    // Comics favoritos
    if ($comics_favoritos && $comics_favoritos->num_rows > 0):
        while ($comic = $comics_favoritos->fetch_assoc()):
            $autor = !empty($comic['autor_nombre']) ? $comic['autor_nombre'] : (!empty($comic['autor']) ? $comic['autor'] : 'Autor desconocido');
            ?>
            <div class="tarjeta-obra" data-obra-id="<?= $comic['id']; ?>" data-tipo="comic">
                <div class="tarjeta-portada">
                    <?php if (!empty($comic['portada'])): ?>
                        <img src="<?= BASE_URL . '/uploads/portadas/' . htmlspecialchars($comic['portada']); ?>" alt="<?= htmlspecialchars($comic['titulo']); ?>">
                    <?php endif; ?>
                    <button class="btn-quitar-favorito" data-obra-id="<?= $comic['id']; ?>" data-tipo="comic" title="Quitar de favoritos">
                        <i class="fas fa-heart"></i>
                    </button>
                    <button class="btn-compartir" data-url="<?= BASE_URL; ?>/public/obra.php?id=<?= $comic['id']; ?>&tipo=comic" data-titulo="<?= htmlspecialchars($comic['titulo']); ?>" title="Compartir">
                        <i class="fas fa-share"></i>
                    </button>
                </div>
                <div class="tarjeta-info">
                    <div class="tarjeta-titulo"><?= htmlspecialchars($comic['titulo']); ?></div>
                    <div class="tarjeta-autor"><?= htmlspecialchars($autor); ?></div>
                </div>
            </div>
        <?php endwhile;
    endif;
    ?>
    </div>
</div>

<style>
body { background: #f7f8fa; }
.container-favoritos { max-width: 1200px; margin: 32px auto 0 auto; padding: 0 20px;}
.favoritos-header h1 { font-size: 2.4rem; color: #232a54; font-weight: 900;}
.favoritos-header p { color: #666; margin: 8px 0 20px 1px; font-size: 1.08rem; }
.favoritos-stats { margin-bottom: 4px; }
.stat-item { display: flex; align-items: center; gap: 8px; color: #666; font-weight: 500; font-size: 1.05rem;}
.filtros-form label {font-weight:600;}
.filtros-form select {margin-right:10px;}
.btn-filtros {
    background: linear-gradient(90deg, #7b2ff2 0%, #f357a8 100%);
    color: #fff;
    border: none;
    border-radius: 9px;
    height: 35px;
    padding: 0 16px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 8px #7b2ff210;
    transition: background .17s;
}
.btn-filtros:hover { background: linear-gradient(90deg, #f357a8 0%, #7b2ff2 100%);}
.obras-grid { display: flex; flex-wrap: wrap; gap: 32px 22px; margin-top: 12px;}
.tarjeta-obra {
    background: #fff;
    border-radius: 22px;
    box-shadow: 0 2px 16px #7b2ff212;
    overflow: hidden;
    width: 320px;
    margin-bottom: 18px;
    display: flex;
    flex-direction: column;
    transition: box-shadow .13s, transform .12s;
    border: 2.5px solid #eceaff;
}
.tarjeta-obra:hover {
    box-shadow: 0 6px 32px #7b2ff221;
    border-color: #a179e9;
    transform: translateY(-4px) scale(1.015);
}
.tarjeta-portada {
    width: 100%;
    height: 210px;
    background: linear-gradient(120deg,#eceaff 50%,#fff 100%);
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}
.tarjeta-portada img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.btn-quitar-favorito, .btn-compartir {
    position: absolute;
    top: 15px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    cursor: pointer;
    box-shadow: 0 2px 8px #0003;
    transition: background 0.16s, color 0.16s;
    background: #fff;
    opacity: 0.93;
}
.btn-quitar-favorito { left: 15px; color: #e74c3c; }
.btn-quitar-favorito:hover { background: #e74c3c; color: #fff; }
.btn-compartir { left: 65px; color: #7b2ff2;}
.btn-compartir:hover { background: #7b2ff2; color: #fff; }
.tarjeta-info { padding: 18px 18px 18px 18px;}
.tarjeta-titulo { font-size: 1.16rem; font-weight: 700; color: #232a54;margin-bottom:6px; }
.tarjeta-autor { color: #666; font-size: .99rem; }
@media (max-width: 950px) {
    .obras-grid { justify-content: center; }
    .tarjeta-obra { width: 94vw; max-width: 340px;}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-quitar-favorito').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const obraId = this.dataset.obraId;
            let tipo = this.dataset.tipo;
            if (tipo === 'libros') tipo = 'libro';
            if (tipo === 'comics') tipo = 'comic';
            if (confirm('¿Quitar esta obra de tus favoritos?')) {
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
                                if (document.querySelectorAll('.tarjeta-obra').length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                    } else {
                        alert('Error al quitar de favoritos');
                    }
                })
                .catch(error => {
                    alert('Error de conexión');
                });
            }
        });
    });

    document.querySelectorAll('.btn-compartir').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const titulo = this.dataset.titulo;
            if (navigator.share) {
                navigator.share({ title: titulo, text: titulo, url: url });
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    alert('Enlace copiado al portapapeles');
                });
            }
        });
    });
});
</script>

<?php include(__DIR__ . '/../includes/footer.php'); ?>