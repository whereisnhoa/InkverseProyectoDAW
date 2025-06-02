<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';

$busqueda = trim($_GET['q'] ?? '');
$tipo = $_GET['tipo'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$publico_adulto = isset($_GET['publico_adulto']) ? intval($_GET['publico_adulto']) : '';
$orden = $_GET['orden'] ?? 'relevancia';

$librosWhere = [];
$comicsWhere = [];
$tipos = '';
$paramsLibros = [];
$paramsComics = [];

// Filtros búsqueda
if ($busqueda !== '') {
    $librosWhere[] = '(l.titulo LIKE CONCAT("%", ?, "%") OR l.descripcion LIKE CONCAT("%", ?, "%"))';
    $comicsWhere[] = '(co.titulo LIKE CONCAT("%", ?, "%") OR co.descripcion LIKE CONCAT("%", ?, "%"))';
    $paramsLibros[] = $busqueda;
    $paramsLibros[] = $busqueda;
    $paramsComics[] = $busqueda;
    $paramsComics[] = $busqueda;
    $tipos .= 'ss';
}
if ($tipo === 'libro') {
    $librosWhere[] = '1=1';
    $comicsWhere[] = '0=1'; // no mostrar comics
} elseif ($tipo === 'comic') {
    $librosWhere[] = '0=1'; // no mostrar libros
    $comicsWhere[] = '1=1';
}
if ($categoria) {
    $librosWhere[] = "l.categoria_id = ?";
    $comicsWhere[] = "co.categoria_id = ?";
    $paramsLibros[] = $categoria;
    $paramsComics[] = $categoria;
    $tipos .= 'i';
}
if ($publico_adulto !== '') {
    $librosWhere[] = "l.publico_adulto = ?";
    $comicsWhere[] = "co.publico_adulto = ?";
    $paramsLibros[] = $publico_adulto;
    $paramsComics[] = $publico_adulto;
    $tipos .= 'i';
}

$orderBy = "ORDER BY ";
switch ($orden) {
    case 'fecha':
        $orderBy .= "fecha_subida DESC";
        break;
    case 'valoracion':
        $orderBy .= "valoracion DESC";
        break;
    case 'visitas':
        $orderBy .= "visitas DESC";
        break;
    default:
        $orderBy .= "fecha_subida DESC";
        break;
}

// Generamos el WHERE por separado para cada tabla
$librosWhereSQL = $librosWhere ? 'WHERE ' . implode(' AND ', $librosWhere) : '';
$comicsWhereSQL = $comicsWhere ? 'WHERE ' . implode(' AND ', $comicsWhere) : '';

// Consulta principal
$sql = "(SELECT 'libro' as tipo, l.id, l.titulo, l.descripcion, l.portada, l.fecha_subida, l.valoracion, l.visitas, c.nombre as categoria, l.publico_adulto
         FROM libros l
         LEFT JOIN categorias c ON l.categoria_id = c.id
         {$librosWhereSQL}
         {$orderBy} LIMIT 50)
       UNION ALL
       (SELECT 'comic' as tipo, co.id, co.titulo, co.descripcion, co.portada, co.fecha_subida, co.valoracion, co.visitas, c2.nombre as categoria, co.publico_adulto
         FROM comics co
         LEFT JOIN categorias c2 ON co.categoria_id = c2.id
         {$comicsWhereSQL}
         {$orderBy} LIMIT 50)";

$resultados = [];
if ($busqueda !== '' || $tipo || $categoria || $publico_adulto !== '') {
    $stmt = $conexion->prepare($sql);
    // Unimos parámetros de libros y comics
    $allParams = array_merge($paramsLibros, $paramsComics);
    $allTipos = $tipos . $tipos; // porque es UNION ALL
    if ($allParams) {
        $stmt->bind_param($allTipos, ...$allParams);
    }
    $stmt->execute();
    $resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Obtener categorías para el buscador avanzado
$categorias = [];
$qc = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre");
while($row = $qc->fetch_assoc()) $categorias[] = $row;
?>

<title>Buscar | InkVerse</title>
    
<?php include '../includes/header.php'; ?>

<div class="container my-5 buscar-espaciado-externo">
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <form class="buscar-form row g-2 align-items-center" method="get" action="buscar.php">
                <div class="col-sm">
                    <input type="text" class="form-control" name="q" placeholder="Buscar obras o descripciones..." value="<?=htmlspecialchars($busqueda)?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                </div>
                <div class="col-auto">
                    <a class="btn btn-link text-secondary btn-avanzada" data-bs-toggle="collapse" href="#busquedaAvanzada" role="button" aria-expanded="false" aria-controls="busquedaAvanzada">
                        <i class="fas fa-sliders-h"></i> Avanzada
                    </a>
                </div>
            </form>
            <div class="collapse mt-3" id="busquedaAvanzada">
                <form class="row g-2 align-items-center" method="get" action="buscar.php">
                    <input type="hidden" name="q" value="<?=htmlspecialchars($busqueda)?>">
                    <div class="col-md-3">
                        <select class="form-select" name="tipo">
                            <option value="">Tipo: Todos</option>
                            <option value="libro" <?=$tipo=='libro'?'selected':''?>>Libros</option>
                            <option value="comic" <?=$tipo=='comic'?'selected':''?>>Cómics</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="categoria">
                            <option value="">Categoría: Todas</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?=$cat['id']?>" <?=$categoria==$cat['id']?'selected':''?>><?=htmlspecialchars($cat['nombre'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="publico_adulto">
                            <option value="">Público</option>
                            <option value="0" <?=$publico_adulto==='0'?'selected':''?>>Todos</option>
                            <option value="1" <?=$publico_adulto==='1'?'selected':''?>>+18</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="orden">
                            <option value="fecha" <?=$orden=='fecha'?'selected':''?>>Más recientes</option>
                            <option value="valoracion" <?=$orden=='valoracion'?'selected':''?>>Mejor valorados</option>
                            <option value="visitas" <?=$orden=='visitas'?'selected':''?>>Más vistos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100"><i class="fas fa-filter"></i> Filtrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($busqueda !== '' || $tipo || $categoria || $publico_adulto !== ''): ?>
        <h2 class="buscar-titulo mb-4 text-primary text-center fs-4">Resultados <?= $busqueda ? 'para "<span class=\'text-dark\'>'.htmlspecialchars($busqueda).'</span>"' : '' ?></h2>
        <?php if (count($resultados)): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 resultados-grid">
                <?php foreach($resultados as $res): ?>
                    <div class="col">
                        <a class="resultado-card card h-100 text-decoration-none shadow-sm" href="<?= $res['tipo']=='libro' ? '../usuario/libro.php?id='.$res['id'] : '../usuario/comic.php?id='.$res['id'] ?>">
                            <div class="resultado-portada card-img-top d-flex align-items-center justify-content-center bg-light" style="height:170px;">
                                <?php if ($res['portada']): ?>
                                    <img src="../uploads/portadas/<?=htmlspecialchars($res['portada'])?>" alt="Portada" class="img-fluid rounded" style="max-height:160px;">
                                <?php else: ?>
                                    <div class="portada-placeholder text-center w-100">
                                        <i class="fas fa-book-open fa-2x text-primary"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="resultado-info card-body pb-2 text-center">
                                <span class="badge bg-primary mb-1"><?=ucfirst($res['tipo'])?></span>
                                <?php if($res['categoria']): ?>
                                    <span class="badge bg-secondary mb-1"><?=htmlspecialchars($res['categoria'])?></span>
                                <?php endif; ?>
                                <?php if($res['publico_adulto']): ?>
                                    <span class="badge bg-danger mb-1">+18</span>
                                <?php endif; ?>
                                <h5 class="card-title mt-1 mb-2"><?=htmlspecialchars($res['titulo'])?></h5>
                                <?php if($res['descripcion']): ?>
                                    <p class="card-text text-muted small"><?=mb_strimwidth(strip_tags($res['descripcion']),0,80,'…')?></p>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center small bg-light">
                                <span title="Valoración"><i class="fas fa-star text-warning"></i> <?=number_format($res['valoracion'],1)?></span>
                                <span title="Visitas"><i class="fas fa-eye text-secondary"></i> <?=intval($res['visitas'])?></span>
                                <span title="Fecha de subida"><i class="far fa-calendar-alt text-info"></i> <?=date('d/m/Y',strtotime($res['fecha_subida']))?></span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center mt-4">
                <i class="fas fa-search"></i> No se encontraron resultados para esta búsqueda.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="buscar-indicacion text-center text-muted py-5">
            <i class="fas fa-search fa-2x mb-2"></i>
            <p class="mb-0">Introduce un término o usa la búsqueda avanzada.</p>
        </div>
    <?php endif; ?>
</div>


<?php include '../includes/footer.php'; ?>