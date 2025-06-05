<?php
include_once('../includes/funciones.php');
include_once('../includes/conexion.php');
session_start();

$usuario_logueado = isset($_SESSION['usuario_id']);
$arte_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Procesar submit 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // validaciones
    header('Location: ver_arte.php?id=' . $arte_id);
    exit();
}

// incluir header
include '../includes/header.php';

// Seguridad básica
if (!$usuario_logueado || $arte_id <= 0) {
    header('Location: arte.php');
    exit();
}

// Obtener obra
$sql = "SELECT * FROM galeria_arte WHERE id = ? AND usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $arte_id, $_SESSION['usuario_id']);
$stmt->execute();
$arte = $stmt->get_result()->fetch_assoc();
if (!$arte) {
    echo "<div style='margin:2em auto;max-width:400px;color:red;'>No tienes permiso para editar esta obra, o no existe.</div>";
    include '../includes/footer.php';
    exit();
}

// Procesar submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = limpiarDatos($_POST['titulo']);
    $descripcion = limpiarDatos($_POST['descripcion']);
    $sql_update = "UPDATE galeria_arte SET titulo = ?, descripcion = ? WHERE id = ? AND usuario_id = ?";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("ssii", $titulo, $descripcion, $arte_id, $_SESSION['usuario_id']);
    $stmt_update->execute();
    header('Location: ver_arte.php?id=' . $arte_id);
    exit();
}
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">
<style>
.editar-arte-main {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 80vh;
  padding: 48px 12px 24px 12px;
  gap: 48px;
  max-width: 1100px;
  margin: 0 auto;
}
.editar-arte-imgbox {
  background: #f5efff;
  border-radius: 2.2em;
  box-shadow: 0 4px 32px #a16ae832, 0 2px 10px #fcb7ff18;
  overflow: hidden;
  min-width: 340px;
  max-width: 420px;
  width: 35vw;
  aspect-ratio: 4/5;
  display: flex;
  align-items: center;
  justify-content: center;
}
.editar-arte-img {
  width: 100%;
  height: auto;
  object-fit: cover;
  display: block;
  border-radius: 2.2em;
}
.editar-arte-formbox {
  background: #fff;
  border-radius: 1.8em;
  box-shadow: 0 2px 18px #a16ae822;
  padding: 35px 38px;
  min-width: 320px;
  max-width: 410px;
  width: 100%;
  display: flex;
  flex-direction: column;
  animation: fadeInUp .5s;
  margin-top: 0;
  gap: 1.5em;
}
.editar-arte-title {
  font-size: 2.1rem;
  font-weight: 900;
  color: #a16ae8;
  margin-bottom: .3em;
  letter-spacing: -1px;
  line-height: 1.13;
  background: linear-gradient(90deg,#a16ae8 20%,#fcb7ff 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; text-fill-color: transparent;
}
.editar-arte-form label {
  font-weight: 700;
  color: #7d38c2;
  margin-bottom: 6px;
  display: block;
}
.editar-arte-form input[type="text"],
.editar-arte-form textarea {
  width: 100%;
  margin-bottom: 18px;
  border-radius: 9px;
  padding: 10px 13px;
  border: 1.5px solid #e3d0fa;
  font-size: 1.09rem;
  background: #faf7ff;
  color: #491b7c;
  transition: border .15s;
}
.editar-arte-form input[type="text"]:focus,
.editar-arte-form textarea:focus {
  border-color: #a16ae8;
  outline: none;
}
.editar-arte-form textarea {
  min-height: 80px;
  resize: vertical;
}
.editar-arte-form button[type="submit"] {
  width: 100%;
  background: linear-gradient(90deg,#fcb7ff 40%, #a16ae8 120%);
  color: #fff;
  font-weight: 800;
  border: none;
  border-radius: 12px;
  font-size: 1.12rem;
  padding: 14px 0;
  box-shadow: 0 2px 8px #a16ae838;
  cursor: pointer;
  margin-top: 6px;
  transition: filter .13s, transform .13s, background .12s;
}
.editar-arte-form button[type="submit"]:hover {
  filter: brightness(1.12); background: #a16ae8;
}
.editar-arte-cancel {
  width: 100%;
  text-align: center;
  display: inline-block;
  margin-top: 8px;
  background: #f6e8ff;
  color: var(--main3);
  border-radius: 12px;
  font-weight: 700;
  padding: 11px 0;
  text-decoration: none;
  transition: background .13s;
}
.editar-arte-cancel:hover { background: #fcb7ff; color:#fff; }
@media (max-width: 900px) {
  .editar-arte-main { flex-direction: column; align-items:center; gap: 32px;}
  .editar-arte-imgbox, .editar-arte-formbox {max-width: 96vw; min-width:0;}
  .editar-arte-formbox {padding: 32px 6vw;}
}
</style>
<div class="editar-arte-main">
  <div class="editar-arte-imgbox">
    <img class="editar-arte-img" src="<?= htmlspecialchars($arte['imagen']) ?>" alt="<?= htmlspecialchars($arte['titulo']) ?>">
  </div>
  <div class="editar-arte-formbox">
    <h1 class="editar-arte-title">Editar obra</h1>
    <form method="POST" class="editar-arte-form" autocomplete="off">
      <label for="titulo">Título</label>
      <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($arte['titulo']) ?>" required maxlength="80">
      <label for="descripcion">Descripción</label>
      <textarea name="descripcion" id="descripcion" rows="5" maxlength="600"><?= htmlspecialchars($arte['descripcion']) ?></textarea>
      <button type="submit">Guardar cambios</button>
    </form>
    <a href="ver_arte.php?id=<?= $arte_id ?>" class="editar-arte-cancel">Cancelar</a>
  </div>
</div>
<?php include '../includes/footer.php'; ?>