
<?php
include '../includes/header.php';
include_once('../includes/funciones.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para subir arte.', 'error');
    exit();
}

// Obtener categorías de arte para el formulario
$sql_categorias = "SELECT * FROM categorias_arte ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
?>

<div class="container">
    <h1>Subir nueva obra de arte</h1>
    <p class="mb-4">Comparte tus creaciones artísticas con la comunidad.</p>
    
    <form action="<?php echo BASE_URL; ?>/controllers/procesar-arte.php" method="POST" enctype="multipart/form-data" class="form">
        <!-- Token CSRF para seguridad -->
        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
        
        <div class="form-group">
            <label for="titulo">Título de la obra:</label>
            <input type="text" id="titulo" name="titulo" required>
        </div>
        
        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="4"></textarea>
        </div>
        
        <div class="form-group">
            <label for="categoria_id">Categoría:</label>
            <select id="categoria_id" name="categoria_id" required>
                <option value="">Selecciona una categoría</option>
                <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
                    <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="imagen">Imagen (JPG, PNG o GIF, máx. 5MB):</label>
            <input type="file" id="imagen" name="imagen" accept="image/jpeg, image/png, image/gif" required>
            <small>Recomendamos imágenes de al menos 800x600 píxeles para una mejor visualización.</small>
        </div>
        
        <div class="form-group checkbox-group">
            <label class="checkbox-inline">
                <input type="checkbox" name="publico_adulto" id="publico_adulto" value="1">
                Esta obra contiene contenido para adultos (+18)
            </label>
        </div>
        
        <div class="form-group checkbox-group">
            <label class="checkbox-inline">
                <input type="checkbox" name="acepta_terminos" id="acepta_terminos" required>
                Confirmo que soy el autor de esta obra o tengo los derechos para publicarla, y acepto los <a href="<?php echo BASE_URL; ?>/public/terminos.php" target="_blank">Términos y Condiciones</a>
            </label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn">Subir obra</button>
            <a href="<?php echo BASE_URL; ?>/comunidad/arte.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
