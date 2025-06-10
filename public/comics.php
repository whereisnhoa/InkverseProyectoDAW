<?php
require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Estadísticas de cómics
$sql_comics = "SELECT COUNT(*) as total_comics, COUNT(DISTINCT usuario_id) as total_usuarios FROM comics";
$res_comics = $conexion->query($sql_comics);
$datos_comic = $res_comics ? $res_comics->fetch_assoc() : ['total_comics' => 0, 'total_usuarios' => 0];

// Todas las categorías de cómics
$sql_categorias = "SELECT id, nombre FROM categorias WHERE tipo = 'comic' ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = [];
if ($result_categorias) {
    while ($row = $result_categorias->fetch_assoc()) $categorias[] = $row;
}
$total_categorias = count($categorias);

// --- OBRAS DESTACADAS ---
$destacadas = [];
$sql_destacadas = "SELECT c.id, c.titulo, c.usuario_id, u.usuario as autor, c.descripcion, c.portada, c.publico_adulto, cat.nombre as categoria, c.fecha_subida, c.visitas,
                   (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = c.id AND f.tipo_contenido = 'comic') as total_favoritos
                   FROM comics c
                   LEFT JOIN categorias cat ON c.categoria_id = cat.id
                   LEFT JOIN usuarios u ON u.id = c.usuario_id
                   WHERE c.destacado = 1
                   ORDER BY c.fecha_destacado DESC, c.fecha_subida DESC
                   LIMIT 12";
$result_destacadas = $conexion->query($sql_destacadas);
if ($result_destacadas) {
    while ($row = $result_destacadas->fetch_assoc()) $destacadas[] = $row;
}

// --- OBRAS RECIENTES ---
$recientes = [];
$sql_recientes = "SELECT c.id, c.titulo, c.usuario_id, u.usuario as autor, c.descripcion, c.portada, c.publico_adulto, cat.nombre as categoria, c.fecha_subida, c.visitas,
                  (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = c.id AND f.tipo_contenido = 'comic') as total_favoritos
                  FROM comics c
                  LEFT JOIN categorias cat ON c.categoria_id = cat.id
                  LEFT JOIN usuarios u ON u.id = c.usuario_id
                  ORDER BY c.fecha_subida DESC
                  LIMIT 12";
$result_recientes = $conexion->query($sql_recientes);
if ($result_recientes) {
    while ($row = $result_recientes->fetch_assoc()) $recientes[] = $row;
}

// --- OBRAS TODAS (con paginación/filtros) ---
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$por_pagina = 12;
$offset = ($pagina - 1) * $por_pagina;

$where_conditions = ["1=1"];
$params = [];
$types = "";
if ($categoria_id > 0) {
    $where_conditions[] = "c.categoria_id = ?";
    $params[] = $categoria_id;
    $types .= "i";
}
if (!empty($busqueda)) {
    $where_conditions[] = "(c.titulo LIKE ? OR u.usuario LIKE ? OR c.descripcion LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $types .= "sss";
}
$where_clause = implode(" AND ", $where_conditions);
$sql_principal = "SELECT c.id, c.titulo, c.usuario_id, u.usuario as autor, c.descripcion, c.portada, c.publico_adulto, 
                  cat.nombre as categoria, c.fecha_subida, c.visitas,
                  (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = c.id AND f.tipo_contenido = 'comic') as total_favoritos
                  FROM comics c
                  LEFT JOIN categorias cat ON c.categoria_id = cat.id
                  LEFT JOIN usuarios u ON u.id = c.usuario_id
                  WHERE $where_clause
                  ORDER BY c.fecha_subida DESC
                  LIMIT ? OFFSET ?";
$stmt = $conexion->prepare($sql_principal);
if (!empty($params)) {
    $params[] = $por_pagina;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $por_pagina, $offset);
}
$stmt->execute();
$result_principal = $stmt->get_result();
$todas_obras = [];
if ($result_principal && $result_principal->num_rows > 0) {
    while ($comic = $result_principal->fetch_assoc()) {
        $todas_obras[] = $comic;
    }
}

// Contar total para paginación
$sql_count = "SELECT COUNT(*) as total FROM comics c LEFT JOIN usuarios u ON u.id = c.usuario_id WHERE $where_clause";
$stmt_count = $conexion->prepare($sql_count);
if (!empty($params) && count($params) > 2) {
    $params_count = array_slice($params, 0, -2);
    $types_count = substr($types, 0, -2);
    if (!empty($params_count)) $stmt_count->bind_param($types_count, ...$params_count);
}
$stmt_count->execute();
$total_obras = $stmt_count->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_obras / $por_pagina);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/public.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/usuario.css">

<!-- HERO Y ESTADÍSTICAS -->
<div class="lit-hero-bg-morado py-5 mb-5">
    <div class="container text-center">
        <h1 class="display-2 fw-bold lit-titulo-morado-visible mb-2">
            <i class="fas fa-book-open lit-morado-icon"></i> Cómics/Manga
        </h1>
        <p class="lead mb-4">Explora cómics, manga, webtoons y toda la historieta de la comunidad</p>
        <div class="row justify-content-center g-4 mb-4">
            <div class="col-12 col-sm-4 col-md-3">
                <div class="stat-card-morado">
                    <i class="fas fa-book-open lit-morado-icon fa-2x"></i>
                    <div class="stat-title">Cómics</div>
                    <div class="stat-value"><?php echo number_format($datos_comic['total_comics']); ?></div>
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-3">
                <div class="stat-card-morado">
                    <i class="fas fa-tags lit-morado-icon fa-2x"></i>
                    <div class="stat-title">Categorías</div>
                    <div class="stat-value"><?php echo $total_categorias; ?></div>
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-3">
                <div class="stat-card-morado">
                    <i class="fas fa-users lit-morado-icon fa-2x"></i>
                    <div class="stat-title">Autores</div>
                    <div class="stat-value"><?php echo number_format($datos_comic['total_usuarios']); ?></div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <?php foreach ($categorias as $cat): ?>
                <a href="?categoria=<?php echo $cat['id']; ?>" class="badge badge-pill lit-categoria-tag px-3 py-2 shadow-sm">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($cat['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="container py-4">
    <!-- Carrusel Obras Destacadas -->
    <section class="lit-carousel-section">
        <h2 class="section-title"><i class="fas fa-star lit-morado-icon"></i> Obras Destacadas</h2>
        <?php if (count($destacadas) > 0): ?>
        <div id="carouselDestacadas" class="carousel slide lit-carousel lit-carousel-multigrid" data-bs-ride="carousel" data-bs-interval="7000">
            <div class="carousel-inner">
            <?php
                $chunked = array_chunk($destacadas, 4);
                foreach ($chunked as $i => $slide):
            ?>
                <div class="carousel-item<?php if ($i == 0) echo ' active'; ?>">
                    <div class="row flex-nowrap">
                    <?php foreach ($slide as $obra): ?>
                        <div class="col-12 col-sm-6 col-md-3">
                            <?php
                            $tipo = 'comic';
                            include(__DIR__ . '/_obra_card.php');
                            ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselDestacadas" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselDestacadas" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        <?php else: ?>
            <p class="text-muted">No hay obras destacadas aún.</p>
        <?php endif; ?>
    </section>

    <!-- Carrusel Obras Recientes -->
    <section class="lit-carousel-section">
        <h2 class="section-title"><i class="fas fa-clock lit-morado-icon"></i> Obras Recientes</h2>
        <?php if (count($recientes) > 0): ?>
        <div id="carouselRecientes" class="carousel slide lit-carousel lit-carousel-multigrid" data-bs-ride="carousel" data-bs-interval="7000">
            <div class="carousel-inner">
            <?php
                $chunked = array_chunk($recientes, 4);
                foreach ($chunked as $i => $slide):
            ?>
                <div class="carousel-item<?php if ($i == 0) echo ' active'; ?>">
                    <div class="row flex-nowrap">
                    <?php foreach ($slide as $obra): ?>
                        <div class="col-12 col-sm-6 col-md-3">
                            <?php
                            $tipo = 'comic';
                            include(__DIR__ . '/_obra_card.php');
                            ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselRecientes" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselRecientes" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        <?php else: ?>
            <p class="text-muted">No hay obras recientes aún.</p>
        <?php endif; ?>
    </section>

    <!-- Carrusel Todas las Obras -->
    <section class="lit-carousel-section">
        <h2 class="section-title"><i class="fas fa-th lit-morado-icon"></i> Todos los Cómics/Manga</h2>
        <?php if (count($todas_obras) > 0): ?>
        <div id="carouselTodas" class="carousel slide lit-carousel lit-carousel-multigrid" data-bs-ride="carousel" data-bs-interval="7000">
            <div class="carousel-inner">
            <?php
                $chunked = array_chunk($todas_obras, 4);
                foreach ($chunked as $i => $slide):
            ?>
                <div class="carousel-item<?php if ($i == 0) echo ' active'; ?>">
                    <div class="row flex-nowrap">
                    <?php foreach ($slide as $obra): ?>
                        <div class="col-12 col-sm-6 col-md-3">
                            <?php
                            $tipo = 'comic';
                            include(__DIR__ . '/_obra_card.php');
                            ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselTodas" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselTodas" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        <?php else: ?>
            <div class="lit-no-results">
                <div class="lit-no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>No se encontraron cómics</h3>
                <p>No hay cómics que coincidan con los criterios de búsqueda.</p>
            </div>
        <?php endif; ?>

        <!-- Paginación -->
        <?php if ($total_paginas > 1): ?>
            <nav class="lit-pagination" aria-label="Paginación de cómics">
                <ul class="pagination justify-content-center">
                    <?php if ($pagina > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php
                    $inicio = max(1, $pagina - 2);
                    $fin = min($total_paginas, $pagina + 2);
                    for ($i = $inicio; $i <= $fin; $i++):
                    ?>
                        <li class="page-item<?php echo $i == $pagina ? ' active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($pagina < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </section>
</div>

<!-- Modal para la biblioteca -->
<div class="modal fade biblio-modal" id="biblioModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?php echo BASE_URL; ?>/usuario/biblioteca.php">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-bookmark"></i> Añadir a biblioteca</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <input type="hidden" id="biblioObraId" name="obra_id">
          <input type="hidden" id="biblioObraTipo" name="obra_tipo" value="comic">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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