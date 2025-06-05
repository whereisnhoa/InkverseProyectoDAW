<?php
include '../includes/header.php';
include_once('../includes/funciones.php');
if (!isset($_SESSION['usuario_id'])) { redirigirConMensaje('login.php', $TEXTOS['galeria_debes_iniciar_sesion'],'error'); exit(); }
$sql_categorias = "SELECT * FROM categorias_arte ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">

<div class="arte-upload-form-container">
  <h1 class="arte-title"><?= $TEXTOS['galeria_subir_titulo'] ?? 'Subir nueva obra de arte'; ?></h1>
  <p class="arte-descripcion"><?= $TEXTOS['galeria_subir_desc'] ?? 'Comparte tus creaciones artísticas con la comunidad.'; ?></p>
  <form class="arte-upload-form" action="../controllers/procesar-arte.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
    <div class="form-group">
      <label for="titulo"><?= $TEXTOS['galeria_titulo_obra'] ?? 'Título de la obra:'; ?> <span style="color:#e23c7d">*</span></label>
      <input type="text" id="titulo" name="titulo" required maxlength="100" autocomplete="off">
    </div>
    <div class="form-group">
      <label for="descripcion"><?= $TEXTOS['galeria_descripcion'] ?? 'Descripción:'; ?></label>
      <textarea id="descripcion" name="descripcion" rows="4" maxlength="500"></textarea>
    </div>
    <div class="form-group">
      <label for="categoria_id"><?= $TEXTOS['galeria_categoria'] ?? 'Categoría:'; ?> <span style="color:#e23c7d">*</span></label>
      <select id="categoria_id" name="categoria_id" required>
        <option value=""><?= $TEXTOS['galeria_select_categoria'] ?? 'Selecciona una categoría'; ?></option>
        <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
          <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="form-group">
      <label for="imagen"><?= $TEXTOS['galeria_imagen'] ?? 'Imagen (JPG, PNG o GIF, máx. 5MB):'; ?> <span style="color:#e23c7d">*</span></label>
      <input type="file" id="imagen" name="imagen" accept="image/jpeg, image/png, image/gif" required>
      <small><?= $TEXTOS['galeria_recomendacion_img'] ?? 'Recomendamos imágenes de al menos 800x600 píxeles para una mejor visualización.'; ?></small>
    </div>
    <div class="form-group checkbox-group">
      <label class="checkbox-inline"><input type="checkbox" name="publico_adulto" value="1">
        <?= $TEXTOS['galeria_contenido_adulto'] ?? 'Esta obra contiene contenido para adultos (+18)'; ?>
      </label>
    </div>
    <div class="form-group checkbox-group">
      <label class="checkbox-inline">
        <input type="checkbox" name="acepta_terminos" required>
        <?= $TEXTOS['galeria_acepta_terminos'] ?? 'Confirmo que soy el autor de esta obra o tengo los derechos para publicarla, y acepto los <a href="../public/terminos.php" target="_blank">Términos y Condiciones</a>.'; ?>
      </label>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn"><?= $TEXTOS['galeria_btn_subir'] ?? 'Subir obra'; ?></button>
      <a href="arte.php" class="btn btn-outline"><?= $TEXTOS['galeria_cancelar'] ?? 'Cancelar'; ?></a>
    </div>
  </form>
</div>
<?php include '../includes/footer.php'; ?>