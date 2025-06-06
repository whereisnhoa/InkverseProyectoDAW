<?php
require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');
include_once(__DIR__ . '/../includes/idioma.php'); // Para $TEXTOS

// Obtener el tipo y la categoría seleccionada
$categoria_id = isset($_GET['categoria_id']) ? intval($_GET['categoria_id']) : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'libro';

// Obtener información de la categoría
$sql_categoria = "SELECT * FROM categorias WHERE id = ? AND tipo = ?";
$stmt_categoria = $conexion->prepare($sql_categoria);
$stmt_categoria->bind_param("is", $categoria_id, $tipo);
$stmt_categoria->execute();
$result_categoria = $stmt_categoria->get_result();
$categoria = $result_categoria->fetch_assoc();

if (!$categoria) {
    echo "<div class='container' style='margin-top:60px;'><div class='alert alert-warning' style='text-align:center; border-radius:17px;'><i class='fas fa-exclamation-triangle'></i> {$TEXTOS['categoria_no_encontrada']}</div></div>";
    include_once(__DIR__ . '/../includes/footer.php');
    exit;
}

// Consultas de obras
if ($tipo == 'libro') {
    $sql_obras_recientes = "SELECT o.*, u.usuario 
        FROM libros o
        JOIN usuarios u ON o.usuario_id = u.id 
        JOIN libro_categorias lc ON lc.libro_id = o.id
        WHERE lc.categoria_id = ?
        ORDER BY o.fecha_subida DESC
        LIMIT 12";

    $sql_obras_recomendadas = "SELECT o.*, u.usuario 
        FROM libros o
        JOIN usuarios u ON o.usuario_id = u.id 
        JOIN libro_categorias lc ON lc.libro_id = o.id
        WHERE lc.categoria_id = ? AND o.valoracion IS NOT NULL
        ORDER BY o.valoracion DESC, o.fecha_subida DESC
        LIMIT 12";

    $sql_obras_todas = "SELECT o.*, u.usuario 
        FROM libros o
        JOIN usuarios u ON o.usuario_id = u.id 
        JOIN libro_categorias lc ON lc.libro_id = o.id
        WHERE lc.categoria_id = ?
        ORDER BY o.fecha_subida DESC";
} else {
    $sql_obras_recientes = "SELECT o.*, u.usuario 
        FROM comics o
        JOIN usuarios u ON o.usuario_id = u.id 
        JOIN comic_categorias cc ON cc.comic_id = o.id
        WHERE cc.categoria_id = ?
        ORDER BY o.fecha_subida DESC
        LIMIT 12";

    $sql_obras_recomendadas = "SELECT o.*, u.usuario 
        FROM comics o
        JOIN usuarios u ON o.usuario_id = u.id 
        JOIN comic_categorias cc ON cc.comic_id = o.id
        WHERE cc.categoria_id = ? AND o.valoracion IS NOT NULL
        ORDER BY o.valoracion DESC, o.fecha_subida DESC
        LIMIT 12";

    $sql_obras_todas = "SELECT o.*, u.usuario 
        FROM comics o
        JOIN usuarios u ON o.usuario_id = u.id 
        JOIN comic_categorias cc ON cc.comic_id = o.id
        WHERE cc.categoria_id = ?
        ORDER BY o.fecha_subida DESC";
}
// Recientes
$stmt_obras_recientes = $conexion->prepare($sql_obras_recientes);
$stmt_obras_recientes->bind_param("i", $categoria_id);
$stmt_obras_recientes->execute();
$result_obras_recientes = $stmt_obras_recientes->get_result();
$obras_recientes = [];
while($r = $result_obras_recientes->fetch_assoc()) $obras_recientes[] = $r;

// Recomendadas
$stmt_obras_recomendadas = $conexion->prepare($sql_obras_recomendadas);
$stmt_obras_recomendadas->bind_param("i", $categoria_id);
$stmt_obras_recomendadas->execute();
$result_obras_recomendadas = $stmt_obras_recomendadas->get_result();
$obras_recomendadas = [];
while($r = $result_obras_recomendadas->fetch_assoc()) $obras_recomendadas[] = $r;

// Todas
$stmt_obras_todas = $conexion->prepare($sql_obras_todas);
$stmt_obras_todas->bind_param("i", $categoria_id);
$stmt_obras_todas->execute();
$result_obras_todas = $stmt_obras_todas->get_result();
$obras_todas = [];
while($r = $result_obras_todas->fetch_assoc()) $obras_todas[] = $r;
?>
<link rel="stylesheet" href="/css/public.css">
<link rel="stylesheet" href="/css/usuario.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css">
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
<style>
.categoria-header-advanced {
    display: flex;
    align-items: center;
    gap: 34px;
    background: linear-gradient(120deg, #6a95e9 0%, #a179e9 100%);
    color: #fff;
    border-radius: 30px;
    box-shadow: 0 6px 22px #6a95e945, 0 1.5px 5px #a179e930;
    padding: 38px 48px;
    margin-bottom: 44px;
    margin-top: 60px;
    min-height: 140px;
}
.categoria-header-icon-gradient {
    background: linear-gradient(135deg, #ffe082 20%, #a179e9 80%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 90px;
    min-height: 90px;
    font-size: 2.6rem;
    box-shadow: 0 4px 18px #c86bfa33;
    color: #fff;
}
.categoria-header-content h1 {
    font-size: 2.5rem;
    font-weight: 900;
    letter-spacing: -1.2px;
    margin-bottom: 10px;
    color: #fff;
    text-shadow: 0 2px 8px #a179e950;
}
.categoria-header-content p {
    font-size: 1.18rem;
    color: #e0e8ff;
    margin: 0;
    font-weight: 400;
}
@media(max-width: 900px) {
    .categoria-header-advanced { flex-direction: column; gap: 18px; padding: 24px 5vw; }
    .categoria-header-icon-gradient { min-width: 70px; min-height: 70px; font-size:2rem;}
    .categoria-header-content h1 { font-size:1.6rem; }
}
.seccion-obras { margin-bottom: 36px; }
.section-title {
    font-size: 1.35rem;
    color: var(--primary-color);
    font-weight: 800;
    margin-bottom: 18px;
    letter-spacing: -.5px;
    display: flex;
    align-items: center;
    gap: 9px;
}
.section-icon {
    color: var(--secondary-color);
    font-size: 1.22em;
    margin-right: 4px;
}

.carrusel-centrado {
  max-width: 1400px;
  margin: 0 auto 44px auto;
  position: relative;
  padding: 0 15px;
}
.splide {
  padding-bottom: 50px;
}
.splide__slide {
  display: flex;
  height: 100%;
  margin: 0 10px;
  transition: box-shadow .18s, transform .14s;
}
.splide__slide.is-active {
  box-shadow: 0 8px 40px 0 rgba(123,47,242,0.13);
  transform: scale(1.03);
  z-index: 2;
}
.splide__arrow {
  background: linear-gradient(120deg, #a179e9 60%, #6a95e9 100%);
  color: #fff;
  box-shadow: 0 2px 12px #6a95e933;
  border-radius: 50%;
  width: 54px;
  height: 54px;
  top: 44%;
  opacity: 0.93;
  transition: background .12s, transform .16s;
  z-index: 10;
  border: none;
  font-size: 1.55rem;
}
.splide__arrow--prev { left: -34px; }
.splide__arrow--next { right: -34px; }
.splide__arrow:disabled { opacity: 0.3; pointer-events: none; }
.splide__arrow:hover,
.splide__arrow:focus {
  background: linear-gradient(90deg,#6a95e9 0%,#a179e9 100%);
  transform: scale(1.08);
  opacity: 1;
}
.splide__pagination {
  bottom: -16px;
}
.splide__pagination__page {
  background: #a179e9;
  opacity: 0.35;
  border: none;
  width: 15px;
  height: 15px;
  margin: 2px 8px;
  transition: opacity .17s, background .16s;
}
.splide__pagination__page.is-active {
  background: linear-gradient(90deg,#6a95e9 0%,#a179e9 100%);
  opacity: 1;
  box-shadow: 0 2px 7px #a179e955;
}
@media (max-width: 900px) {
  .splide__arrow--prev { left: -10px; }
  .splide__arrow--next { right: -10px; }
}
@media (max-width: 700px) {
  .carrusel-centrado { max-width: 98vw; padding: 0 2vw;}
  .splide__slide { margin: 0 2px;}
  .splide { padding-bottom: 20px;}
}
.no-obras-advanced {
    background: #fff7e7;
    color: #8e99c7;
    font-size: 1.09em;
    padding: 23px 12px;
    border-radius: 18px;
    text-align: center;
    margin-bottom: 0;
    box-shadow: 0 2px 10px #ffe7b1;
}
</style>

<div class="container">

    <!-- CABECERA DE CATEGORÍA -->
    <div class="categoria-header-advanced">
        <div class="categoria-header-icon-gradient">
            <?php if ($tipo == 'libro') : ?>
                <i class="fas fa-book"></i>
            <?php else : ?>
                <i class="fas fa-book-open"></i>
            <?php endif; ?>
        </div>
        <div class="categoria-header-content">
            <h1><?= htmlspecialchars($categoria['nombre']); ?></h1>
            <p><?= !empty($categoria['descripcion']) ? htmlspecialchars($categoria['descripcion']) : $TEXTOS['categoria_sin_descripcion']; ?></p>
        </div>
    </div>

    <!-- Carrusel Obras Recientes -->
    <div class="seccion-obras">
        <h2 class="section-title"><i class="fas fa-clock section-icon"></i> <?= $TEXTOS['categoria_obras_recientes']; ?></h2>
        <?php if (count($obras_recientes) > 0): ?>
        <div class="carrusel-centrado">
            <div class="splide" id="splide-recientes">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php foreach ($obras_recientes as $obra): ?>
                        <li class="splide__slide">
                            <?php include(__DIR__ . '/_obra_card.php'); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="no-obras-advanced">
                <i class="fas fa-info-circle"></i> <?= $TEXTOS['categoria_no_obras_recientes']; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Carrusel Recomendadas -->
    <div class="seccion-obras">
        <h2 class="section-title"><i class="fas fa-star section-icon"></i> <?= $TEXTOS['categoria_recomendadas']; ?></h2>
        <?php if (count($obras_recomendadas) > 0): ?>
        <div class="carrusel-centrado">
            <div class="splide" id="splide-recomendadas">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php foreach ($obras_recomendadas as $obra): ?>
                        <li class="splide__slide">
                            <?php include(__DIR__ . '/_obra_card.php'); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="no-obras-advanced">
                <i class="fas fa-info-circle"></i> <?= $TEXTOS['categoria_no_recomendadas']; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Carrusel Todas las Obras -->
    <div class="seccion-obras">
        <h2 class="section-title"><i class="fas fa-th-large section-icon"></i> <?= $TEXTOS['categoria_todas_obras']; ?></h2>
        <?php if (count($obras_todas) > 0): ?>
        <div class="carrusel-centrado">
            <div class="splide" id="splide-todas">
                <div class="splide__track">
                    <ul class="splide__list">
                        <?php foreach ($obras_todas as $obra): ?>
                        <li class="splide__slide">
                            <?php include(__DIR__ . '/_obra_card.php'); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="no-obras-advanced">
                <i class="fas fa-info-circle"></i> <?= $TEXTOS['categoria_no_obras']; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para la biblioteca (como en biblioteca.php) -->
<div class="modal fade biblio-modal" id="biblioModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="../usuario/biblioteca.php">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-bookmark"></i> Añadir a biblioteca</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <input type="hidden" id="biblioObraId" name="obra_id">
          <input type="hidden" id="biblioObraTipo" name="obra_tipo" value="<?= htmlspecialchars($tipo) ?>">
          <p id="biblioObraTitulo" class="mb-3 fw-bold"></p>
          <div class="biblio-state-list d-flex flex-wrap justify-content-center">
            <?php
            $estados = [
                'todos' => 'Todos',
                'leyendo' => 'Leyendo',
                'planeado' => 'Planeado',
                'terminado' => 'Terminado',
                'espera' => 'En espera',
                'abandonado' => 'Abandonado'
            ];
            foreach ($estados as $key => $label): ?>
              <button type="button" class="btn biblio-state-btn" data-value="<?php echo $key; ?>"><?php echo $label; ?></button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="estado" id="estadoBiblioInput" value="">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-gradient" disabled id="biblioSubmit">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  let opts = {
    perPage: 3,
    gap: '2.2rem',
    rewind: true,
    pagination: true,
    arrows: true,
    focus: 'center',
    speed: 600,
    breakpoints: {
      1200: { perPage: 2 },
      700: { perPage: 1, gap: '1rem' }
    }
  };
  if(document.getElementById('splide-recientes')) {
    new Splide('#splide-recientes', opts).mount();
  }
  if(document.getElementById('splide-recomendadas')) {
    new Splide('#splide-recomendadas', opts).mount();
  }
  if(document.getElementById('splide-todas')) {
    new Splide('#splide-todas', opts).mount();
  }

  // Modal biblioteca
  document.querySelectorAll('.biblio-state-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
          document.querySelectorAll('.biblio-state-btn').forEach(function(b){
              b.classList.remove('selected');
          });
          btn.classList.add('selected');
          document.getElementById('estadoBiblioInput').value = btn.getAttribute('data-value');
          document.getElementById('biblioSubmit').disabled = false;
      });
  });
});
function abrirBiblioModal(btn) {
    var obraId = btn.getAttribute('data-obra-id');
    var obraTitulo = btn.getAttribute('data-obra-titulo');
    var obraTipo = btn.getAttribute('data-obra-tipo');
    document.getElementById('biblioObraId').value = obraId;
    document.getElementById('biblioObraTitulo').innerText = obraTitulo;
    document.getElementById('biblioObraTipo').value = obraTipo;
    document.getElementById('estadoBiblioInput').value = '';
    document.getElementById('biblioSubmit').disabled = true;
    document.querySelectorAll('.biblio-state-btn').forEach(function(b){
        b.classList.remove('selected');
    });
    new bootstrap.Modal(document.getElementById('biblioModal')).show();
}
function abrirBiblioModalDesdeBiblioteca(id, titulo){
    document.getElementById('biblioObraId').value = id;
    document.getElementById('biblioObraTitulo').innerText = titulo;
    document.getElementById('estadoBiblioInput').value = '';
    document.getElementById('biblioSubmit').disabled = true;
    document.querySelectorAll('.biblio-state-btn').forEach(function(b){
        b.classList.remove('selected');
    });
    new bootstrap.Modal(document.getElementById('biblioModal')).show();
}
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>