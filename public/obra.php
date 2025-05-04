<?php
session_start();
include '../includes/conexion.php';
include '../includes/header.php';

if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
    echo '<div class="alerta alerta-error">Obra no especificada correctamente.</div>';
    include '../includes/footer.php';
    exit();
}

$contenido_id = $_GET['id'];
$tipo_contenido = $_GET['tipo']; // 'libro' o 'comic'

// Obtener datos de la obra según su tipo
if ($tipo_contenido === 'libro') {
    $sql = "SELECT l.*, c.nombre as categoria FROM libros l 
            LEFT JOIN categorias c ON l.categoria_id = c.id 
            WHERE l.id = ?";
} else {
    $sql = "SELECT * FROM comics WHERE id = ?";
}

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $contenido_id);
$stmt->execute();
$obra = $stmt->get_result()->fetch_assoc();

if (!$obra) {
    echo '<div class="alerta alerta-error">Obra no encontrada.</div>';
    include '../includes/footer.php';
    exit();
}
?>

<div class="mb-4">
  <h1><?= htmlspecialchars($obra['titulo']) ?></h1>
  
  <div class="flex gap-2 mb-2">
    <?php if ($tipo_contenido === 'libro' && isset($obra['categoria'])): ?>
      <span class="badge badge-categoria"><?= htmlspecialchars($obra['categoria']) ?></span>
    <?php endif; ?>
    
    <?php if ($obra['publico_adulto']): ?>
      <span class="badge badge-adulto">+18</span>
    <?php endif; ?>
  </div>
  
  <p class="mb-3"><strong>Autor:</strong> <?= htmlspecialchars($obra['autor']) ?></p>
  
  <div class="obra-card mb-4">
    <div class="obra-card-body">
      <p><?= nl2br(htmlspecialchars($obra['descripcion'])) ?></p>
    </div>
  </div>
  
  <!-- Favoritos -->
  <?php
  if (isset($_SESSION['usuario_id'])):
      // Verificar si la obra ya está en favoritos
      $ya_favorito = false;
      $check = $conexion->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND tipo_contenido = ? AND contenido_id = ?");
      $check->bind_param("isi", $_SESSION['usuario_id'], $tipo_contenido, $contenido_id);
      $check->execute();
      $check->store_result();
      $ya_favorito = $check->num_rows > 0;
  ?>
  
  <form action="../controllers/procesar-favorito.php" method="POST" class="mb-4">
      <input type="hidden" name="contenido_id" value="<?= $contenido_id ?>">
      <input type="hidden" name="tipo_contenido" value="<?= $tipo_contenido ?>">
      <?php if ($ya_favorito): ?>
          <input type="hidden" name="accion" value="quitar">
          <button type="submit" class="btn btn-danger"><i class="fas fa-heart-broken"></i> Quitar de favoritos</button>
      <?php else: ?>
          <input type="hidden" name="accion" value="agregar">
          <button type="submit" class="btn"><i class="fas fa-heart"></i> Añadir a favoritos</button>
      <?php endif; ?>
  </form>
  <?php endif; ?>
</div>

<h2>Comentarios</h2>

<?php
// Mostrar comentarios
$sql_com = "SELECT u.usuario, c.contenido, c.fecha 
            FROM comentarios c 
            JOIN usuarios u ON u.id = c.usuario_id 
            WHERE c.tipo_contenido = ? AND c.contenido_id = ? 
            ORDER BY c.fecha DESC";
$stmt_com = $conexion->prepare($sql_com);
$stmt_com->bind_param("si", $tipo_contenido, $contenido_id);
$stmt_com->execute();
$resultado_com = $stmt_com->get_result();

if ($resultado_com->num_rows > 0):
    while ($comentario = $resultado_com->fetch_assoc()):
?>
    <div class="comentario">
      <div class="comentario-header">
        <span class="comentario-autor"><?= htmlspecialchars($comentario['usuario']) ?></span>
        <span class="comentario-fecha"><?= date('d/m/Y H:i', strtotime($comentario['fecha'])) ?></span>
      </div>
      <p class="comentario-contenido"><?= nl2br(htmlspecialchars($comentario['contenido'])) ?></p>
    </div>
<?php endwhile; else: ?>
    <p class="text-center mb-4">Aún no hay comentarios.</p>
<?php endif; ?>

<?php if (isset($_SESSION['usuario_id'])): ?>
    <div class="mb-4">
      <h3>Deja tu comentario</h3>
      <form action="../controllers/procesar-comentario.php" method="POST">
          <input type="hidden" name="contenido_id" value="<?= $contenido_id ?>">
          <input type="hidden" name="tipo_contenido" value="<?= $tipo_contenido ?>">
          <div class="form-group">
            <textarea name="contenido" rows="4" required placeholder="Escribe tu comentario aquí..."></textarea>
          </div>
          <button type="submit" class="btn"><i class="fas fa-comment"></i> Comentar</button>
      </form>
    </div>
<?php else: ?>
    <div class="alerta alerta-info">
      <p>Debes <a href="/public/login.php">iniciar sesión</a> para comentar.</p>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>