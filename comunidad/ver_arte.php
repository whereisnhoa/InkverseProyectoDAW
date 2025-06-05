<?php
include '../includes/header.php';
include_once('../includes/funciones.php');
$usuario_logueado = isset($_SESSION['usuario_id']);
$arte_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($arte_id <= 0) { redirigirConMensaje('arte.php', $TEXTOS['galeria_error_id'],'error'); exit(); }
$sql = "SELECT ga.*, ca.nombre as categoria_nombre, ca.icono as categoria_icono, u.usuario as autor_nombre, u.id as autor_id
  FROM galeria_arte ga
  JOIN categorias_arte ca ON ga.categoria_id = ca.id
  JOIN usuarios u ON ga.usuario_id = u.id
  WHERE ga.id = ?";
$stmt = $conexion->prepare($sql); $stmt->bind_param("i",$arte_id); $stmt->execute();
$arte = $stmt->get_result()->fetch_assoc();
if(!$arte){ redirigirConMensaje('arte.php', $TEXTOS['galeria_obra_no_encontrada'],'error'); exit(); }
if ($arte['publico_adulto'] && (!$usuario_logueado || (isset($_SESSION['edad']) && $_SESSION['edad'] < 18))) {
  redirigirConMensaje('arte.php', $TEXTOS['galeria_18_requerido'],'error'); exit();
}
$ha_dado_like = false;
if($usuario_logueado){
  $sql_like = "SELECT id FROM me_gusta_arte WHERE arte_id=? AND usuario_id=?";
  $stmt_like = $conexion->prepare($sql_like);
  $stmt_like->bind_param("ii", $arte_id, $_SESSION['usuario_id']); $stmt_like->execute();
  $ha_dado_like = $stmt_like->get_result()->num_rows > 0;
}
$sqlc = "SELECT c.*, u.usuario as nombre_usuario FROM comentarios_arte c JOIN usuarios u ON c.usuario_id = u.id WHERE c.arte_id=? ORDER BY c.fecha_comentario DESC";
$stmtc = $conexion->prepare($sqlc); $stmtc->bind_param("i", $arte_id); $stmtc->execute();
$comentarios = $stmtc->get_result();
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">
<style>
.arte-detalle-flex {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 74vh;
  padding: 48px 12px 24px 12px;
  gap: 40px;
  max-width: 1100px;
  margin: 0 auto;
}
.arte-detalle-imgbox {
  background: #f5efff;
  border-radius: 2.2em;
  box-shadow: 0 4px 32px #a16ae832, 0 2px 10px #fcb7ff18;
  overflow: hidden;
  min-width: 380px;
  max-width: 480px;
  width: 40vw;
  display: flex;
  align-items: center;
  justify-content: center;
  aspect-ratio: 4/5;
}
.arte-detalle-img {
  width: 100%;
  height: auto;
  object-fit: cover;
  display: block;
  border-radius: 2.2em;
}
.arte-detalle-info {
  background: #fff;
  border-radius: 1.8em;
  box-shadow: 0 2px 18px #a16ae822;
  padding: 35px 38px 35px 38px;
  min-width: 320px;
  max-width: 390px;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  animation: fadeInUp .5s;
  margin-top: 0;
  gap: 0.8em;
}
.arte-detalle-titulo {
  font-size: 2.1rem;
  font-weight: 900;
  color: #a16ae8;
  margin-bottom: .4em;
  letter-spacing: -1px;
  line-height: 1.13;
  background: linear-gradient(90deg,#a16ae8 20%,#fcb7ff 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; text-fill-color: transparent;
}
.arte-detalle-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 17px;
  font-size: 1.09rem;
  color: #a16ae8;
  margin-bottom: 5px;
}
.arte-detalle-meta i {
  margin-right: 5px;
  color: #a16ae8;
}
.arte-detalle-meta a {
  color: #a16ae8; font-weight: 700; text-decoration: none;
  transition: color .16s;
}
.arte-detalle-meta a:hover { color: #fcb7ff; }
.arte-badge18 {
  background: #e23c7d; color: #fff; border-radius: 8px; font-size: .86rem; font-weight: 900;
  padding: 2px 8px; margin-left: 8px; box-shadow: 0 2px 6px #d0abf098;
}
.arte-detalle-desc {
  color: #7d38c2;
  font-size: 1.09rem;
  background: #f8f3ff;
  border-radius: 10px;
  padding: 10px 14px;
  margin-bottom: 12px;
  font-weight: 500;
  text-align: left;
  width: 100%;
}
.arte-actions {
  display: flex; flex-wrap: wrap; gap: 13px; margin-bottom: 7px; width: 100%;
}
.arte-like-btn {
  background: linear-gradient(90deg,#fcb7ff 40%, #a16ae8 120%);
  color: #fff;
  font-weight: 800;
  border: none;
  border-radius: 12px;
  font-size: 1.07rem;
  padding: 10px 22px;
  box-shadow: 0 2px 8px #a16ae838;
  cursor: pointer;
  transition: filter .13s, transform .13s, background .12s;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  text-decoration: none;
}
.arte-like-btn.liked, .arte-like-btn:hover { filter: brightness(1.12); background: #a16ae8; color: #fff;}
.arte-like { background: #f8e7ff; color: #a16ae8; font-weight: 800; border-radius: 8px; padding: 5px 14px; font-size: 1.01rem; display: inline-block;}
.arte-like-btn[style*="background:#f0e4ff"] { background: #f0e4ff !important; color: var(--main3) !important; }
.arte-like-btn[style*="background:#ffe7f3"] { background: #ffe7f3 !important; color: #e23c7d !important; }
.arte-like-btn[style*="background:#f6e8ff"] { background: #f6e8ff !important; color: var(--main3) !important; }
.arte-comentarios-sec {
  width: 100%;
  background: #faf7ff;
  border-radius: 1.1em;
  padding: 17px 20px 18px 20px;
  border-top:1px solid #ede3ff;
  text-align:left;
  margin-top: 22px;
}
.arte-comment-form {
  display: flex; flex-direction:column; gap: 8px; margin-bottom: 17px;
}
.arte-comment-form textarea {
  border-radius: 10px;
  border: 1.5px solid #e3d0fa;
  padding: 7px 11px;
  font-size: 1.06rem;
  resize: vertical;
  background: #fff;
  min-height: 34px;
}
.arte-comment-form button {
  align-self: flex-end;
  background: linear-gradient(90deg,#a16ae8 60%, #fcb7ff 120%);
  color: #fff;
  font-weight: 800;
  border: none;
  border-radius: 8px;
  font-size: 1.01rem;
  padding: 8px 20px;
  cursor: pointer;
  transition: filter .13s, transform .13s;
}
.arte-comment-form button:hover { filter: brightness(1.09);}
.arte-comment {
  border-radius: 12px;
  background: #f7f1ff;
  padding: 10px 12px;
  margin-bottom: 11px;
  font-size: 1.01rem;
  box-shadow: 0 2px 10px #d3b5f516;
}
.arte-comment-author {
  color: #8b63b9;
  font-weight: 700;
  margin-right: 10px;
}
.arte-comment-date {
  color: #a6a2b9;
  font-size: .93em;
  margin-right: 7px;
}
.arte-empty {
  color: #bfa1e0;
  text-align: center;
  margin: 10px 0 0 0;
  font-size: 1.07rem;
  font-weight: 600;
}
@media (max-width: 950px) {
  .arte-detalle-flex { flex-direction: column; align-items:center; gap: 28px;}
  .arte-detalle-imgbox, .arte-detalle-info {max-width: 97vw; min-width:0;}
  .arte-detalle-info {padding: 30px 5vw;}
}
</style>

<div class="arte-detalle-flex">
  <div class="arte-detalle-imgbox">
    <img class="arte-detalle-img" src="<?= htmlspecialchars($arte['imagen']) ?>" alt="<?= htmlspecialchars($arte['titulo']) ?>">
  </div>
  <div class="arte-detalle-info">
    <h1 class="arte-detalle-titulo"><?= htmlspecialchars($arte['titulo']) ?></h1>
    <div class="arte-detalle-meta">
      <span><i class="fas fa-user"></i>
        <a href="../usuario/perfil.php?id=<?= $arte['autor_id'] ?>"><?= htmlspecialchars($arte['autor_nombre']) ?></a>
        <?php if($arte['publico_adulto']): ?><span class="arte-badge18">+18</span><?php endif; ?>
      </span>
      <span><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y',strtotime($arte['fecha_subida'])) ?></span>
      <span><i class="fas <?= htmlspecialchars($arte['categoria_icono']??'fa-tag') ?>"></i> <?= htmlspecialchars($arte['categoria_nombre']) ?></span>
    </div>
    <?php if($arte['descripcion']): ?>
    <div class="arte-detalle-desc"><?= nl2br(htmlspecialchars($arte['descripcion'])) ?></div>
    <?php endif; ?>
    <div class="arte-actions">
      <?php if($usuario_logueado): ?>
        <form action="../controllers/procesar-like-arte.php" method="POST" style="display:inline;">
          <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
          <input type="hidden" name="arte_id" value="<?= $arte_id; ?>">
          <input type="hidden" name="accion" value="<?= $ha_dado_like ? 'quitar':'dar'; ?>">
          <button type="submit" class="arte-like-btn<?= $ha_dado_like?' liked':''; ?>">
            <i class="fas fa-heart"></i> <?= $TEXTOS['galeria_me_gusta']??'Me gusta'; ?> (<?= $arte['me_gusta'] ?>)
          </button>
        </form>
        <?php if ($_SESSION['usuario_id'] == $arte['autor_id']): ?>
          <a href="editar_arte.php?id=<?= $arte_id ?>" class="arte-like-btn" style="background:#f0e4ff;color:var(--main3)"><i class="fas fa-edit"></i> <?= $TEXTOS['galeria_editar']??'Editar'; ?></a>
          <form action="../controllers/procesar-eliminar-arte.php" method="POST" style="display:inline;" onsubmit="return confirm('<?= $TEXTOS['galeria_confirmar_eliminar']??'¿Eliminar esta obra?'; ?>');">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
            <input type="hidden" name="arte_id" value="<?= $arte_id; ?>">
            <button type="submit" class="arte-like-btn" style="background:#ffe7f3;color:#e23c7d"><i class="fas fa-trash-alt"></i> <?= $TEXTOS['galeria_eliminar']??'Eliminar'; ?></button>
          </form>
        <?php endif; ?>
      <?php else: ?>
        <span class="arte-like"><i class="fas fa-heart"></i> <?= $arte['me_gusta'] ?> <?= $TEXTOS['galeria_me_gusta']??'Me gusta'; ?></span>
      <?php endif; ?>
      <a href="arte.php" class="arte-like-btn" style="background:#f6e8ff;color:var(--main3)"><i class="fas fa-arrow-left"></i> <?= $TEXTOS['galeria_volver']??'Volver a la galería'; ?></a>
    </div>
    <div class="arte-comentarios-sec" id="comentar">
      <div style="font-weight:700;color:var(--main3);margin-bottom:7px;"><?= $TEXTOS['galeria_comentarios']??'Comentarios'; ?> (<?= $comentarios->num_rows ?>)</div>
      <?php if($usuario_logueado): ?>
        <form action="../controllers/procesar-comentario-arte.php" method="POST" class="arte-comment-form">
          <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
          <input type="hidden" name="arte_id" value="<?= $arte_id ?>">
          <textarea name="comentario" rows="2" maxlength="400" required placeholder="<?= $TEXTOS['galeria_escribe_comentario']??'Escribe un comentario...'; ?>"></textarea>
          <button type="submit"><i class="fas fa-comment"></i> <?= $TEXTOS['galeria_comentar']??'Comentar'; ?></button>
        </form>
      <?php endif; ?>
      <?php if($comentarios->num_rows>0): foreach($comentarios as $c): ?>
        <div class="arte-comment">
          <span class="arte-comment-author"><?= htmlspecialchars($c['nombre_usuario']) ?></span>
          <span class="arte-comment-date"><?= date('d/m/Y H:i',strtotime($c['fecha_comentario'])) ?></span>
          <div><?= nl2br(htmlspecialchars($c['comentario'])) ?></div>
        </div>
      <?php endforeach; else: ?>
        <div class="arte-empty"><?= $TEXTOS['galeria_no_comentarios']??'No hay comentarios aún. ¡Sé el primero en comentar!'; ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>