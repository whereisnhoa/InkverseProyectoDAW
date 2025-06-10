
<?php
/**
 * Página de inicio adaptada y mejorada
 */
require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Obras destacadas
$sql_destacados = "(SELECT 'libro' as tipo, l.id, l.titulo, u.usuario AS autor, l.descripcion, l.portada, l.publico_adulto, 
     c.nombre as categoria, l.fecha_subida, l.visitas,
     (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = l.id AND f.tipo_contenido = 'libro') as total_favoritos
     FROM libros l
     LEFT JOIN categorias c ON l.categoria_id = c.id
     LEFT JOIN usuarios u ON l.usuario_id = u.id
     WHERE l.destacado = 1
     ORDER BY l.fecha_subida DESC
     LIMIT 4)
    UNION ALL
    (SELECT 'comic' as tipo, co.id, co.titulo, u.usuario AS autor, co.descripcion, co.portada, co.publico_adulto, 
     cat.nombre as categoria, co.fecha_subida, co.visitas,
     (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = co.id AND f.tipo_contenido = 'comic') as total_favoritos
     FROM comics co
     LEFT JOIN categorias cat ON co.categoria_id = cat.id
     LEFT JOIN usuarios u ON co.usuario_id = u.id
     WHERE co.destacado = 1
     ORDER BY co.fecha_subida DESC
     LIMIT 4)
    ORDER BY fecha_subida DESC
    LIMIT 8";
$result_destacados = $conexion->query($sql_destacados);

// Recientes
$sql_recientes = "(SELECT 'libro' as tipo, l.id, l.titulo, u.usuario AS autor, l.portada, l.fecha_subida
     FROM libros l
     LEFT JOIN usuarios u ON l.usuario_id = u.id
     ORDER BY l.fecha_subida DESC
     LIMIT 3)
    UNION ALL
    (SELECT 'comic' as tipo, c.id, c.titulo, u.usuario AS autor, c.portada, c.fecha_subida
     FROM comics c
     LEFT JOIN usuarios u ON c.usuario_id = u.id
     ORDER BY c.fecha_subida DESC
     LIMIT 3)
    ORDER BY fecha_subida DESC
    LIMIT 6";
$result_recientes = $conexion->query($sql_recientes);

// Populares
$sql_populares = "(SELECT 'libro' as tipo, l.id, l.titulo, u.usuario AS autor, l.portada, l.visitas
     FROM libros l
     LEFT JOIN usuarios u ON l.usuario_id = u.id
     ORDER BY l.visitas DESC
     LIMIT 3)
    UNION ALL
    (SELECT 'comic' as tipo, c.id, c.titulo, u.usuario AS autor, c.portada, c.visitas
     FROM comics c
     LEFT JOIN usuarios u ON c.usuario_id = u.id
     ORDER BY c.visitas DESC
     LIMIT 3)
    ORDER BY visitas DESC
    LIMIT 6";
$result_populares = $conexion->query($sql_populares);

// Categorías (soluciona multi-categoría)
$sql_categorias = "
SELECT c.id, c.nombre, c.tipo, c.descripcion,
    (
        CASE 
            WHEN c.tipo = 'libro' THEN (SELECT COUNT(*) FROM libro_categorias lc WHERE lc.categoria_id = c.id)
            WHEN c.tipo = 'comic' THEN (SELECT COUNT(*) FROM comic_categorias cc WHERE cc.categoria_id = c.id)
        END
    ) as total_obras
FROM categorias c
HAVING total_obras > 0
ORDER BY total_obras DESC, c.nombre
LIMIT 12";
$result_categorias = $conexion->query($sql_categorias);

// Stats
$sql_stats = "SELECT 
             (SELECT COUNT(*) FROM libros) as total_libros,
             (SELECT COUNT(*) FROM comics) as total_comics,
             (SELECT COUNT(*) FROM usuarios) as total_usuarios,
             (SELECT COUNT(*) FROM categorias) as total_categorias";
$result_stats = $conexion->query($sql_stats);
$stats = $result_stats->fetch_assoc();
?>

<!-- Hero Section Mejorado -->
<section class="hero-section-mini position-relative d-flex align-items-center min-vh-60 py-5">
    <div class="container position-relative z-2">
        <div class="row justify-content-between align-items-center flex-wrap">
            <!-- IZQUIERDA -->
            <div class="col-lg-7 col-md-12">
                <h1 class="hero-title mb-4 text-center text-lg-start">
                    <span class="d-block fw-bold" style="font-size:clamp(2.5rem,7vw,4.5rem);line-height:1.0;"><?= $TEXTOS['descubre_historias'] ?></span>
                    <span class="fs-3 fw-normal d-block mt-2"><?= $TEXTOS['universo_literatura'] ?></span>
                </h1>
                <p class="hero-subtitle mb-4 text-center text-lg-start">
                    <?= $TEXTOS['explora_comparte'] ?>
                </p>
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-center justify-content-lg-start mb-3">
                    <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-rocket"></i> <?= $TEXTOS['unete_gratis'] ?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-compass"></i> <?= $TEXTOS['explorar'] ?>
                    </a>
                </div>
            </div>
            <!-- DERECHA -->
            <div class="col-lg-5 col-md-12 d-flex flex-column align-items-center align-items-lg-end mt-5 mt-lg-0">
                <div class="stats-box">
                    <div>
                        <span class="stat-icon"><i class="fas fa-book"></i></span>
                        <span>
                            <span class="stat-number"><?php echo number_format($stats['total_libros'] + $stats['total_comics']); ?></span>
                            <span class="stat-label"><?= $TEXTOS['obras'] ?></span>
                        </span>
                    </div>
                    <div>
                        <span class="stat-icon"><i class="fas fa-user-friends"></i></span>
                        <span>
                            <span class="stat-number"><?php echo number_format($stats['total_usuarios']); ?></span>
                            <span class="stat-label"><?= $TEXTOS['lectores'] ?></span>
                        </span>
                    </div>
                    <div>
                        <span class="stat-icon"><i class="fas fa-tags"></i></span>
                        <span>
                            <span class="stat-number"><?php echo $stats['total_categorias']; ?></span>
                            <span class="stat-label"><?= $TEXTOS['generos'] ?></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.recent-card-link { 
  color: inherit; 
  text-decoration: none;
}
.recent-card-link:hover .recent-card, 
.recent-card-link:focus .recent-card {
  border-color: #906dfc;
  box-shadow: 0 16px 44px #7b2ff244;
  transform: translateY(-9px) scale(1.035);
}
.recent-card {
  background: #fff;
  border-radius: 1.7rem;
  box-shadow: 0 2px 18px #c7cefa23;
  border: 2px solid #ebe8fa;
  padding: 2.1rem 1.4rem 1.7rem 1.4rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  width: 100%;
  max-width: 340px;
  min-width: 220px;
  min-height: 0;
  position: relative;
  overflow: visible;
}

.recent-image {
  width: 120px;
  height: 120px;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 1.2rem;
  background: #f8f9ff;
  box-shadow: 0 4px 18px #7b2ff222;
}

.portada-obra {
  width: 120px;
  height: 120px;
  object-fit: cover;
  border-radius: 1.2rem !important;
  display: block;
  margin: 0 auto;
  transition: transform .18s;
  box-shadow: 0 2px 8px #7b2ff222;
}
.recent-card-link:hover .portada-obra { 
  transform: scale(1.07) rotate(-2deg);
}

.recent-title {
  font-size: 1.16rem;
  font-weight: 800;
  margin-top: 1.2rem;
  margin-bottom: .45rem;
  color: #24184b;
  line-height: 1.18;
  text-align: left;
  width: 100%;
  word-break: break-word;
  writing-mode: initial !important;
  text-orientation: initial !important;
  direction: ltr !important;
  white-space: normal !important;
}

.recent-author {
  color: #a08be4;
  font-size: 1.01rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.4em;
  width: 100%;
  writing-mode: initial !important;
  text-orientation: initial !important;
  direction: ltr !important;
  white-space: normal !important;
}

.recent-categorias {
  width: 100%;
  display: flex;
  flex-wrap: wrap;
  gap: 0.18em;
  margin-bottom: 0.6em;
  writing-mode: initial !important;
  text-orientation: initial !important;
  direction: ltr !important;
  white-space: normal !important;
}

.recent-categorias .badge-categoria {
  background: #f3edff;
  color: #7b2ff2;
  font-size: 0.95em;
  border-radius: 0.7em;
  margin-right: 0.3em;
  margin-bottom: 0.12em;
  padding: 0.2em 0.8em;
  font-weight: 700;
  display: inline-block;
}

.recent-type-badge {
  background: linear-gradient(90deg, #7b2ff2 0%, #4cc9f0 100%);
  color: #fff !important;
  font-weight: 700;
  font-size: 1.07rem;
  padding: 0.43em 1.4em;
  border-radius: 1.6em;
  letter-spacing: 1px;
  border: none;
  box-shadow: 0 2px 10px #7b2ff233;
  position: absolute;
  left: 1.5rem;
  bottom: 1.2rem;
  transition: background .16s;
}

.cat-badge, .badge-categoria {
  background: #f3edff;
  color: #7b2ff2;
  font-size: 0.95em;
  border-radius: 0.7em;
  margin-right: 0.3em;
  margin-bottom: 0.12em;
  padding: 0.2em 0.8em;
  font-weight: 700;
  display: inline-block;
}

/* Resetea writing-mode y orientación para evitar texto vertical */
.recent-card, .recent-card *, .recent-title, .recent-author, .recent-categorias {
  writing-mode: initial !important;
  text-orientation: initial !important;
  direction: ltr !important;
  white-space: normal !important;
  word-break: break-word !important;
}

/* Responsive */
@media (max-width: 1300px) {
  .recent-card { max-width: 270px; }
}
@media (max-width: 1100px) {
  .recent-card { max-width: 44vw; min-width: 220px; }
}
@media (max-width: 800px) {
  .recent-card { max-width: 90vw; min-width: 180px;}
}
@media (max-width: 600px) {
  .recent-card { max-width: 97vw; min-width: 140px; padding: 1rem 0.5rem 1.1rem 0.5rem;}
  .recent-type-badge { font-size:0.98rem; left: 1rem; }
  .recent-title { font-size:1.03rem; }
}

/* CORRIGE writing-mode heredado y layout básico */
.feature-card, .feature-card *, .features-section * {
  writing-mode: initial !important;
  text-orientation: initial !important;
  direction: ltr !important;
  white-space: normal !important;
  word-break: break-word !important;
  box-sizing: border-box;
}

/* Estilo moderno y limpio para la sección de características */
.features-section {
  background: transparent;
  padding-top: 2.5rem;
  padding-bottom: 2.5rem;
}

.features-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 2.4rem 0;
  justify-content: center;
}

.feature-card {
  border-radius: 1.7rem;
  background: #fff;
  box-shadow: 0 2px 18px #c7cefa23;
  border: 2px solid #ebe8fa;
  padding: 2.6rem 1.7rem 2.2rem 1.7rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  transition: box-shadow 0.14s, border-color 0.14s, transform 0.16s;
  min-height: 330px;
  height: 100%;
  width: 100%;
  max-width: 410px;
  margin: 0 auto;
}

.feature-card:hover, .feature-card:focus-within {
  border-color: #a99bfc;
  box-shadow: 0 14px 38px #7b2ff244, 0 3px 14px #b2b5fa18;
  transform: translateY(-8px) scale(1.032);
  z-index: 2;
}

.feature-icon {
  width: 68px;
  height: 68px;
  border-radius: 1.1em;
  background: linear-gradient(135deg, #7b2ff2 0%, #4cc9f0 100%);
  color: #fff;
  font-size: 2.3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 18px #7b2ff222;
  margin-bottom: 1.1rem;
}

.feature-card h3 {
  font-size: 1.2rem;
  font-weight: bold;
  color: #24184b;
  margin-bottom: 0.8rem;
  margin-top: 0.5rem;
  text-align: center;
}

.feature-card p {
  color: #524b7d;
  font-size: 1.04rem;
  margin: 0;
  text-align: center;
}

.section-title {
  font-size: 2.2rem;
  font-weight: 900;
  background: linear-gradient(90deg, #7b2ff2 0%, #4cc9f0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-fill-color: transparent;
  margin-bottom: 0.5rem;
}

.section-subtitle {
  color: #7c88ad;
  font-size: 1.22rem;
  margin-bottom: 0;
}

@media (max-width: 1200px) {
  .feature-card { max-width: 330px; }
}
@media (max-width: 900px) {
  .feature-card { max-width: 100%; }
}
@media (max-width: 600px) {
  .features-grid { gap: 1.1rem 0; }
  .feature-card { padding: 1.6rem 0.7rem 1.2rem 0.7rem; min-height: 220px; }
  .feature-icon { width: 54px; height: 54px; font-size: 1.6rem; }
  .section-title { font-size: 1.3rem; }
}

.cta-section-premium {
  background: linear-gradient(90deg,#7b2ff2 0%,#4cc9f0 100%);
  color: #fff;
  padding: 4rem 0 4rem 0;
  position: relative;
}
.cta-section-premium .cta-icon-premium {
  width: 78px;
  height: 78px;
  background: rgba(255,255,255,0.14);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.6rem;
  margin-bottom: 1.3rem;
}
.premium-features-list {
  max-width: 480px;
  margin: 0 auto 1.8rem auto;
  padding: 0;
  list-style: none;
}
.premium-features-list li {
  font-size: 1.13rem;
  margin-bottom: .6rem;
  display: flex;
  align-items: center;
  gap: .7em;
}
.cta-section-premium .btn-warning {
  background: linear-gradient(90deg, #f7971e 0%, #ffd200 100%);
  border: none;
  color: #fff;
  font-weight: bold;
  cursor: not-allowed;
  opacity: 0.8;
}
</style>

<!-- Explora categorías -->
<section class="explore-section">
  <div class="container">
    <h2 class="section-title"><?= $TEXTOS['explora_genero'] ?></h2>
    <div class="row justify-content-center align-items-stretch g-4">
      <!-- Literatura -->
      <div class="col-12 col-md-6 col-lg-5 d-flex">
        <div class="content-type-card literatura-card flex-fill">
          <div class="card-icon">
            <i class="fas fa-feather-alt"></i>
          </div>
          <h3><?= $TEXTOS['literatura'] ?></h3>
          <p><?= $TEXTOS['literatura_desc'] ?></p>
          <div class="d-flex flex-column align-items-center gap-2 mt-4">
            <span class="badge">
              <i class="fas fa-book"></i>
              <?php echo number_format($stats['total_libros']); ?> <?= strtoupper($TEXTOS['obras']) ?>
            </span>
            <a href="literatura.php" class="explore-btn">
              <i class="fas fa-arrow-right"></i> <?= $TEXTOS['ver_literatura'] ?>
            </a>
          </div>
        </div>
      </div>
      <!-- Cómics y Manga -->
      <div class="col-12 col-md-6 col-lg-5 d-flex">
        <div class="content-type-card comics-card flex-fill">
          <div class="card-icon">
            <i class="fas fa-palette"></i>
          </div>
          <h3><?= $TEXTOS['comics_manga'] ?></h3>
          <p><?= $TEXTOS['comics_manga_desc'] ?></p>
          <div class="d-flex flex-column align-items-center gap-2 mt-4">
            <span class="badge">
              <i class="fas fa-image"></i>
              <?php echo number_format($stats['total_comics']); ?> <?= strtoupper($TEXTOS['obras']) ?>
            </span>
            <a href="comics.php" class="explore-btn">
              <i class="fas fa-arrow-right"></i> <?= $TEXTOS['ver_comics'] ?>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Obras Destacadas -->
<section class="featured-section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">
          <span class="icon-star"><i class="fas fa-star"></i></span>
          <?= $TEXTOS['obras_destacadas'] ?>
        </h2>
        <div class="section-subtitle">
          <?= $TEXTOS['seleccion_especial'] ?>
        </div>
      </div>
      <a href="explorar.php" class="view-all-link">
        <span class="icon-view"><i class="fas fa-eye"></i></span>
        <?= $TEXTOS['ver_todas'] ?>
      </a>
    </div>
    <div class="row g-4">
      <?php if($result_destacados && $result_destacados->num_rows > 0): ?>
        <?php while($obra = $result_destacados->fetch_assoc()): ?>
          <div class="col-12 col-md-6 col-lg-4 d-flex">
            <a href="<?= BASE_URL ?>/public/obra.php?id=<?= $obra['id'] ?>&tipo=<?= $obra['tipo'] ?>" class="featured-card obra-card flex-fill text-decoration-none">
              <div class="obra-image">
                <img src="<?= htmlspecialchars($obra['portada'] ?? 'default.jpg') ?>" alt="<?= htmlspecialchars($obra['titulo']) ?>" class="img-fluid rounded-4 shadow" style="object-fit:cover; width:100%;height:210px;">
              </div>
              <div class="obra-content py-3">
                <div class="obra-badges mb-2">
                  <span class="badge badge-libro"><i class="fas fa-book"></i> <?= ucfirst($obra['tipo']) ?></span>
                  <?php
                  // Muestra las categorías (multi)
                  if ($obra['tipo'] === 'libro') {
                    $cats_q = $conexion->query("SELECT c.nombre FROM libro_categorias lc JOIN categorias c ON lc.categoria_id = c.id WHERE lc.libro_id = {$obra['id']}");
                  } else {
                    $cats_q = $conexion->query("SELECT c.nombre FROM comic_categorias cc JOIN categorias c ON cc.categoria_id = c.id WHERE cc.comic_id = {$obra['id']}");
                  }
                  while($cat = $cats_q->fetch_assoc()) {
                    echo '<span class="badge badge-categoria">'.htmlspecialchars($cat['nombre']).'</span> ';
                  }
                  ?>
                  <?php if(!empty($obra['publico_adulto'])): ?>
                    <span class="badge badge-adulto"><i class="fas fa-exclamation-triangle"></i> +18</span>
                  <?php endif; ?>
                </div>
                <div class="obra-title"><?= htmlspecialchars($obra['titulo']) ?></div>
                <div class="obra-author"><?= $TEXTOS['por'] ?? 'por' ?> <?= htmlspecialchars($obra['autor']) ?></div>
                <div class="obra-description"><?= htmlspecialchars($obra['descripcion']) ?></div>
                <div class="obra-stats mt-2">
                  <span class="stat"><i class="fas fa-eye"></i> <?= (int)$obra['visitas'] ?></span>
                  <span class="stat"><i class="fas fa-heart"></i> <?= (int)($obra['valoracion'] ?? 0) ?></span>
                </div>
              </div>
            </a>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="empty-featured">
            <div class="big-icon">
              <i class="fas fa-star"></i>
            </div>
            <div class="empty-title">
              <?= $TEXTOS['proximamente_destacadas'] ?>
            </div>
            <div class="empty-desc">
              <?= $TEXTOS['preparando_contenido'] ?>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Características -->
<section class="features-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5" data-aos="fade-up">
            <h2 class="section-title"><?= $TEXTOS['porque_inkverse'] ?></h2>
            <p class="section-subtitle"><?= $TEXTOS['ventajas_inkverse'] ?></p>
        </div>
        <div class="row features-grid g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3><?= $TEXTOS['publica_facil'] ?></h3>
                    <p><?= $TEXTOS['publica_facil_desc'] ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3><?= $TEXTOS['comunidad_activa'] ?></h3>
                    <p><?= $TEXTOS['comunidad_activa_desc'] ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3><?= $TEXTOS['seccion_arte'] ?></h3>
                    <p><?= $TEXTOS['seccion_arte_desc'] ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3><?= $TEXTOS['acceso_multiplataforma'] ?></h3>
                    <p><?= $TEXTOS['acceso_multiplataforma_desc'] ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3><?= $TEXTOS['favoritos_sistema'] ?></h3>
                    <p><?= $TEXTOS['favoritos_sistema_desc'] ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card shadow-sm h-100 text-center" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3><?= $TEXTOS['interaccion_social'] ?></h3>
                    <p><?= $TEXTOS['interaccion_social_desc'] ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Final -->
<?php if (!isset($_SESSION['usuario_id'])): ?>
<section class="cta-section py-5 position-relative text-center text-white overflow-hidden">
    <div class="cta-background position-absolute top-0 start-0 w-100 h-100"></div>
    <div class="container position-relative z-2">
        <div class="cta-content" data-aos="fade-up">
            <div class="cta-icon mx-auto mb-4">
                <i class="fas fa-rocket"></i>
            </div>
            <h2 class="mb-3"><?= $TEXTOS['cta_titulo'] ?></h2>
            <p class="mb-4 fs-4"><?= $TEXTOS['cta_subtitulo'] ?></p>
            <div class="cta-buttons d-flex justify-content-center flex-wrap gap-3">
                <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i> <?= $TEXTOS['crear_cuenta_gratis'] ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-sign-in-alt"></i> <?= $TEXTOS['iniciar_sesion'] ?>
                </a>
            </div>
        </div>
    </div>
</section>
<?php else: ?>
<section class="cta-section-premium py-5 position-relative text-center text-white overflow-hidden" style="background: linear-gradient(90deg,#7b2ff2 0%,#4cc9f0 100%);">
    <div class="container position-relative z-2">
        <div class="cta-content" data-aos="fade-up">
            <div class="cta-icon-premium mx-auto mb-4">
                <i class="fas fa-gem"></i>
            </div>
            <h2 class="mb-3 fw-bold"><?= $TEXTOS['proximamente_premium'] ?></h2>
            <p class="mb-4 fs-4"><?= $TEXTOS['preparate_premium'] ?></p>
            <ul class="premium-features-list text-start mx-auto mb-4">
                <li><i class="fas fa-star"></i> <?= $TEXTOS['lectura_sin_anuncios'] ?></li>
                <li><i class="fas fa-cloud-download-alt"></i> <?= $TEXTOS['descarga_libros'] ?></li>
                <li><i class="fas fa-eye"></i> <?= $TEXTOS['estadisticas_avanzadas'] ?></li>
                <li><i class="fas fa-rocket"></i> <?= $TEXTOS['obras_exclusivas'] ?></li>
                <li><i class="fas fa-user-shield"></i> <?= $TEXTOS['insignia_premium'] ?></li>
            </ul>
            <div class="cta-buttons d-flex justify-content-center flex-wrap gap-3">
                <button class="btn btn-warning btn-lg text-white fw-bold" disabled>
                    <i class="fas fa-gem"></i> <?= $TEXTOS['muy_pronto'] ?>
                </button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
    // Tabs dinámicas
    document.addEventListener("DOMContentLoaded", function () {
        const tabBtns = document.querySelectorAll(".tab-btn");
        const tabContents = document.querySelectorAll(".tab-content");
        tabBtns.forEach(btn => {
            btn.addEventListener("click", function () {
                tabBtns.forEach(b => b.classList.remove("active"));
                tabContents.forEach(tc => tc.classList.remove("active"));
                btn.classList.add("active");
                document.getElementById("tab-" + btn.dataset.tab).classList.add("active");
            });
        });
    });
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>