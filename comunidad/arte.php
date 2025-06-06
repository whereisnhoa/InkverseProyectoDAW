
<?php
include '../includes/header.php';
include_once('../includes/funciones.php');
$usuario_logueado = isset($_SESSION['usuario_id']);

// Cargar categorías
$sql_categorias = "SELECT * FROM categorias_arte ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = [];
if ($result_categorias && $result_categorias->num_rows > 0)
  while ($row = $result_categorias->fetch_assoc()) $categorias[] = $row;

// Filtro de categoría seleccionada
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;

// Consulta base
$sql_base = "SELECT ga.*, ca.nombre as categoria_nombre, ca.icono as categoria_icono, u.usuario as autor_nombre 
  FROM galeria_arte ga
  JOIN categorias_arte ca ON ga.categoria_id = ca.id
  JOIN usuarios u ON ga.usuario_id = u.id
  WHERE 1=1";
if ($categoria_id > 0) $sql_base .= " AND ga.categoria_id = $categoria_id";
if (!$usuario_logueado || (isset($_SESSION['edad']) && $_SESSION['edad'] < 18)) $sql_base .= " AND ga.publico_adulto = 0";
$sql_base .= " ORDER BY ga.fecha_subida DESC";
$result_arte = $conexion->query($sql_base);

// Estadísticas de arte total por categoría
$stats_total = $conexion->query("SELECT COUNT(*) as total FROM galeria_arte")->fetch_assoc()['total'];
$stats_categoria = [];
$result_stats = $conexion->query("SELECT categoria_id, COUNT(*) as cuenta FROM galeria_arte GROUP BY categoria_id");
while($row = $result_stats->fetch_assoc()) $stats_categoria[$row['categoria_id']] = $row['cuenta'];
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">
<style>
body {
  background: linear-gradient(120deg, #f6f3ff 0%, #e3f0ff 100%);
  min-height: 100vh;
}
.arte-header {
  max-width: 1200px;
  margin: 40px auto 0 auto;
  padding: 0 2vw;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.arte-titulo-bg {
  border-radius: 44px;
  background: linear-gradient(95deg, #a16ae8 10%, #fcb7ff 100%);
  padding: 48px 0 28px 0;
  box-shadow: 0 3px 26px #a16ae81a, 0 1px 5px #fcb7ff17;
  text-align: center;
  margin-bottom: 30px;
  position: relative;
  width: 100%;
  overflow: hidden;
  max-width: 900px;
}
.arte-titulo {
  font-size: 3.1rem;
  font-weight: 900;
  letter-spacing: -1.7px;
  color: #fff;
  text-shadow: 0 2px 12px #6e38b155, 0 1.5px 4px #a16ae844;
  margin: 0 0 10px 0;
  line-height: 1.08;
}
.arte-descripcion {
  color: #fbefff;
  font-size: 1.32rem;
  margin: 0 auto 0 auto;
  font-weight: 400;
  max-width: 600px;
  text-shadow: 0 1px 8px #a16ae835;
  letter-spacing: .2px;
}
.arte-estadisticas {
  margin-top: 18px;
  display: flex;
  gap: 24px;
  justify-content: center;
  align-items: center;
  color: #fff;
  font-weight: 700;
  font-size: 1.12rem;
  background: linear-gradient(90deg,#ffffff10 40%, #ffd6fb0a 100%);
  border-radius: 18px;
  padding: 10px 24px;
  box-shadow: 0 2px 12px #6e38b128;
}
.arte-estadisticas .fa-palette {
  margin: 0 9px 0 0;
  font-size: 1.18em;
  color: #ffe1f9;
}
.arte-categorias-contenedor {
  max-width: 1200px;
  margin: 14px auto 0 auto;
  padding: 0 2vw;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.arte-categorias-strip {
  display: flex;
  flex-wrap: wrap;
  gap: 16px 22px;
  margin-bottom: 0;
  margin-top: 0;
  justify-content: center;
  width: 100%;
}
.arte-categoria-pill {
  background: #fff;
  color: #a16ae8;
  border: none;
  border-radius: 36px;
  padding: 13px 32px;
  font-weight: 900;
  font-size: 1.21rem;
  min-width: 52px;
  box-shadow: 0 2px 13px #a16ae81c, 0 1.5px 5px #fcb7ff10;
  text-decoration: none;
  transition: background .15s, color .15s, box-shadow .13s, transform .13s;
  display: flex; align-items: center; gap: 12px;
  white-space:nowrap;
  cursor:pointer;
  border:2px solid transparent;
  position: relative;
}
.arte-categoria-pill.active, .arte-categoria-pill:hover {
  background: linear-gradient(90deg, #a16ae8 15%, #fcb7ff 100%);
  color: #fff;
  border:2px solid #a16ae8;
  box-shadow: 0 6px 26px #a16ae833, 0 2px 9px #fcb7ff18;
  transform: scale(1.08);
  z-index: 2;
}
.arte-categoria-pill i {
  font-size: 1.25em;
  margin-right: 6px;
}
.arte-categoria-pill .categoria-count {
  background: #f4e6ff;
  color: #a16ae8;
  font-size: .93em;
  border-radius: 11px;
  padding: 2px 9px;
  margin-left: 3px;
  font-weight: 700;
  box-shadow: 0 1px 4px #a16ae810;
}
.arte-categoria-pill.active .categoria-count,
.arte-categoria-pill:hover .categoria-count {
  background: #fff;
  color: #a16ae8;
}
.arte-upload-btn {
  display:inline-block;
  background: linear-gradient(95deg, #a16ae8 30%, #fcb7ff 100%);
  color: #fff;
  font-weight: 900;
  font-size: 1.22rem;
  border: none;
  border-radius: 17px;
  padding: 14px 42px;
  margin: 28px 0 19px 0;
  box-shadow: 0 2px 18px #a16ae838, 0 1.5px 5px #fcb7ff1c;
  text-decoration: none;
  transition: filter .13s, transform .13s;
}
.arte-upload-btn:hover { filter: brightness(1.13); transform: scale(1.05); }
.arte-masonry {
  column-count: 5;
  column-gap: 28px;
  max-width: 1700px;
  margin: 12px auto 0 auto;
  padding: 0 1vw;
}
@media (max-width: 1500px) { .arte-masonry {column-count:4;} }
@media (max-width: 1200px) { .arte-masonry {column-count:3;} }
@media (max-width: 900px)  { .arte-masonry {column-count:2;} }
@media (max-width: 600px)  { .arte-masonry {column-count:1;} padding:0 .5vw;}
.arte-card {
  display: inline-block;
  background: #fff;
  border-radius: 34px;
  box-shadow: 0 2px 18px #a16ae838, 0 1.5px 5px #fcb7ff1c;
  margin: 0 0 28px 0;
  width: 100%;
  break-inside: avoid;
  position: relative;
  transition: box-shadow .18s, transform .17s, background .18s;
  overflow: hidden;
  border: none;
}
.arte-card:hover {
  box-shadow: 0 12px 40px #a16ae880, 0 4px 16px #fcb7ff35;
  transform: translateY(-2px) scale(1.022);
  background:rgba(255,255,255,.97);
}
.arte-img-top {
  width: 100%;
  display: block;
  border-radius: 34px 34px 0 0;
  background: #f5efff;
  object-fit: cover;
  aspect-ratio: 1/1.3;
  min-height: 180px;
  max-height: 500px;
  box-shadow: 0 2px 17px #a16ae82a;
}
.arte-card-content {
  padding: 16px 19px 10px 19px;
  min-height: 54px;
}
.arte-title {
  font-size: 1.19rem;
  font-weight: 900;
  color: #7d38c2;
  margin: 0 0 3px 0;
  word-break: break-word;
  line-height: 1.13;
}
.arte-author {
  color: #9a7dbc;
  font-size: .99rem;
  margin-bottom: 3px;
}
.arte-meta {
  display: flex; gap: 10px; color: #bfa1e0; font-size: .95rem; align-items: center;
  margin-bottom: 2px; flex-wrap: wrap;
}
.arte-meta i { margin-right: 4px;}
.arte-badge18 {
  background: #e23c7d; color: #fff; border-radius: 8px; font-size: .88rem; font-weight: 900;
  padding: 2px 8px; margin-left: 8px; box-shadow: 0 2px 6px #d0abf098;
}
.arte-card-link { text-decoration: none; color: inherit;}
.arte-comentarios-sec {
  background: #faf7ff;
  border-radius: 0 0 34px 34px;
  padding: 10px 20px 8px 20px;
  border-top:1px solid #ede3ff;
  text-align:center;
}
.arte-like-btn {
  background: none;
  border: none;
  color: #e23c7d;
  font-size: 1.08rem;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: color .13s, background .13s;
  border-radius: 8px;
  padding: 4px 14px;
}
.arte-like-btn.liked,
.arte-like-btn:hover {
  color: #a16ae8;
  background: #fbe7ff;
}
.arte-empty {
  color: #bfa1e0;
  text-align: center;
  margin: 45px 0 0 0;
  font-size: 1.22rem;
  font-weight: 600;
  letter-spacing: .1px;
}

/* Censura para obras +18 */
.censura-arte {
  filter: blur(22px) grayscale(0.5) brightness(1.2);
  position: relative;
  pointer-events: none;
  user-select: none;
}
.censura-overlay {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(38, 24, 65, 0.65);
  display: flex;
  justify-content: center;
  align-items: center;
  color: #fff;
  font-weight: bold;
  font-size: 1.2em;
  letter-spacing: 1px;
  z-index: 2;
  border-radius: 34px 34px 0 0;
  text-shadow: 0 2px 12px #000c;
  pointer-events: none;
  user-select: none;
}
</style>

<div class="arte-header">
  <div class="arte-titulo-bg">
    <h1 class="arte-titulo"><?= $TEXTOS['galeria_arte_titulo'] ?? 'Galería de Arte'; ?></h1>
    <p class="arte-descripcion"><?= $TEXTOS['galeria_arte_desc'] ?? 'Explora ilustraciones, portadas y arte conceptual creado por nuestra comunidad. Inspírate y comparte tus propias obras.'; ?></p>
    <div class="arte-estadisticas">
      <i class="fas fa-palette"></i>
      <?= $stats_total ?> obras subidas
    </div>
  </div>
</div>

<div class="arte-categorias-contenedor">
  <nav class="arte-categorias-strip" aria-label="Navegación de categorías">
    <a href="arte.php" class="arte-categoria-pill <?= $categoria_id==0?'active':''; ?>">
      <i class="fas fa-th-large"></i>
      <?= $TEXTOS['galeria_todas_categorias'] ?? 'Todas las categorías'; ?>
      <span class="categoria-count"><?= $stats_total ?></span>
    </a>
    <?php foreach($categorias as $cat): ?>
      <a href="arte.php?categoria=<?= $cat['id'] ?>" class="arte-categoria-pill <?= $categoria_id==$cat['id']?'active':''; ?>">
        <i class="fas <?= htmlspecialchars($cat['icono']??'fa-tag') ?>"></i>
        <?= htmlspecialchars($cat['nombre']) ?>
        <span class="categoria-count"><?= isset($stats_categoria[$cat['id']]) ? $stats_categoria[$cat['id']] : 0 ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <?php if($usuario_logueado): ?>
    <a href="subir_arte.php" class="arte-upload-btn">
      <i class="fas fa-plus"></i> <?= $TEXTOS['galeria_btn_subir'] ?? 'Subir nueva obra'; ?>
    </a>
  <?php endif; ?>
</div>

<div class="arte-masonry">
<?php if($result_arte && $result_arte->num_rows>0): foreach($result_arte as $arte): ?>
  <div class="arte-card">
    <a href="ver_arte.php?id=<?= $arte['id'] ?>" class="arte-card-link" style="position:relative;">
      <div style="position:relative;">
        <img class="arte-img-top<?= $arte['publico_adulto'] ? ' censura-arte' : '' ?>" src="<?= htmlspecialchars($arte['thumbnail']?:$arte['imagen']) ?>" alt="<?= htmlspecialchars($arte['titulo']) ?>">
        <?php if($arte['publico_adulto']): ?>
          <div class="censura-overlay">
            <i class="fas fa-eye-slash"></i> Contenido +18
          </div>
        <?php endif; ?>
      </div>
      <div class="arte-card-content">
        <div class="arte-title"><?= htmlspecialchars($arte['titulo']) ?></div>
        <div class="arte-author">
          <i class="fas fa-user"></i>
          <?= htmlspecialchars($arte['autor_nombre']) ?>
        </div>
        <div class="arte-meta">
          <span class="arte-like"><i class="fas fa-heart"></i> <?= (int)$arte['me_gusta'] ?></span>
          <span><i class="fas <?= htmlspecialchars($arte['categoria_icono']??'fa-tag') ?>"></i> <?= htmlspecialchars($arte['categoria_nombre']) ?></span>
          <span><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y',strtotime($arte['fecha_subida'])) ?></span>
          <?php if($arte['publico_adulto']): ?><span class="arte-badge18">+18</span><?php endif; ?>
        </div>
      </div>
    </a>
    <div class="arte-comentarios-sec">
      <a href="ver_arte.php?id=<?= $arte['id'] ?>#comentar" class="arte-like-btn" style="background:transparent;border:none;">
  <i class="fas fa-comment"></i> <?= $TEXTOS['galeria_comentar']??'Comentar'; ?>
</a>
    </div>
  </div>
<?php endforeach; else: ?>
  <div class="arte-empty">
    <i class="far fa-sad-tear"></i>
    <?= $TEXTOS['galeria_no_arte'] ?? 'No hay obras de arte disponibles'; ?>
  </div>
<?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>