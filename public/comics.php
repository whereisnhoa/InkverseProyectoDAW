<?php
require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Estadísticas
$stmt = $conexion->prepare("
    SELECT 
        (SELECT COUNT(*) FROM comics) AS total_comics,
        (SELECT COUNT(DISTINCT categoria_id) FROM comics) AS total_categorias,
        (SELECT COUNT(DISTINCT usuario_id) FROM comics) AS total_autores
");
$stmt->execute();
$stmt->bind_result($total_comics, $total_categorias, $total_autores);
$stmt->fetch();
$stmt->close();

// Destacados
$destacados = [];
$result = $conexion->query("
    SELECT c.*, u.usuario 
    FROM comics c 
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.destacado = 1
    ORDER BY c.fecha_destacado DESC, c.fecha_subida DESC
    LIMIT 6
");
while ($row = $result->fetch_assoc()) {
    $destacados[] = $row;
}

// Todos los cómics
$comics = [];
$result = $conexion->query("
    SELECT c.*, u.usuario 
    FROM comics c 
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    ORDER BY c.fecha_subida DESC
");
while ($row = $result->fetch_assoc()) {
    $comics[] = $row;
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/comics.css">

<div class="comics-hero-bg py-5 mb-5">
    <div class="container text-center">
        <h1 class="display-2 fw-bold comics-hero-titulo mb-3">
            <i class="fas fa-book-open comics-azul-icon"></i> Cómics
        </h1>
        <p class="comics-hero-desc mb-4">
            Explora cómics, manga, webtoons y todo el arte secuencial de la comunidad.
        </p>
        <div class="row justify-content-center g-4 mb-2">
            <div class="col-12 col-sm-4 col-md-3">
                <div class="comics-stat-card comics-stat-total">
                    <i class="fas fa-book-open"></i>
                    <div class="comics-stat-title">Cómics</div>
                    <div class="comics-stat-value"><?php echo $total_comics; ?></div>
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-3">
                <div class="comics-stat-card comics-stat-categoria">
                    <i class="fas fa-tags"></i>
                    <div class="comics-stat-title">Categorías</div>
                    <div class="comics-stat-value"><?php echo $total_categorias; ?></div>
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-3">
                <div class="comics-stat-card comics-stat-autor">
                    <i class="fas fa-users"></i>
                    <div class="comics-stat-title">Autores</div>
                    <div class="comics-stat-value"><?php echo $total_autores; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Carrusel de destacados -->
    <?php if (count($destacados) > 0): ?>
    <h2 class="comics-section-title mb-3"><i class="fas fa-star"></i> Destacados</h2>
    <div id="carouselComicsDestacados" class="carousel slide comics-carousel-multigrid mb-5" data-bs-ride="carousel" data-bs-interval="7000">
        <div class="carousel-inner">
            <?php 
            $chunks = array_chunk($destacados, 3);
            foreach ($chunks as $i => $chunk): ?>
                <div class="carousel-item<?php if ($i == 0) echo ' active'; ?>">
                    <div class="row g-4">
                        <?php foreach ($chunk as $comic): ?>
                            <?php $obra = $comic; $tipo = 'comic'; ?>
                            <div class="col-md-4">
                                <?php include(__DIR__.'/components/_obra_card.php'); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($chunks) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselComicsDestacados" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselComicsDestacados" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <h2 class="comics-section-title"><i class="fas fa-th-large"></i> Todos los Cómics</h2>
    <div class="row g-4">
        <?php foreach ($comics as $comic): ?>
            <?php $obra = $comic; $tipo = 'comic'; ?>
            <div class="col-md-4">
                <?php include(__DIR__.'/_obra_card.php'); ?>
            </div>
        <?php endforeach; ?>
        <?php if (count($comics) == 0): ?>
            <div class="col-12 text-center text-muted">Aún no hay cómics publicados.</div>
        <?php endif; ?>
    </div>
</div>


<?php include_once(__DIR__ . '/../includes/footer.php'); ?>