
<?php
include '../includes/header.php';
include_once('../includes/funciones.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para crear un tema.', 'error');
    exit();
}

// Obtener categoría preseleccionada si se proporciona
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;

// Obtener categorías de foro para el formulario
$sql_categorias = "SELECT * FROM categorias_foro ORDER BY nombre";
$result_categorias = $conexion->query($sql_categorias);
?>

<div class="container">
    <h1>Crear nuevo tema</h1>
    <p class="mb-4">Inicia una nueva discusión en los foros.</p>
    
    <form action="<?php echo BASE_URL; ?>/controllers/procesar-tema.php" method="POST" class="form">
        <!-- Token CSRF para seguridad -->
        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
        
        <div class="form-group">
            <label for="titulo">Título del tema:</label>
            <input type="text" id="titulo" name="titulo" required maxlength="255">
            <small>Elige un título descriptivo para tu tema.</small>
        </div>
        
        <div class="form-group">
            <label for="categoria_id">Categoría:</label>
            <select id="categoria_id" name="categoria_id" required>
                <option value="">Selecciona una categoría</option>
                <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
                    <option value="<?php echo $categoria['id']; ?>" <?php echo ($categoria_id == $categoria['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="contenido">Contenido:</label>
            <textarea id="contenido" name="contenido" rows="10" required></textarea>
            <small>Describe tu tema con detalle. Puedes usar saltos de línea para organizar el texto.</small>
        </div>
        
        <div class="form-group checkbox-group">
            <label class="checkbox-inline">
                <input type="checkbox" name="acepta_reglas" id="acepta_reglas" required>
                He leído y acepto las <a href="<?php echo BASE_URL; ?>/comunidad/foros.php#reglas" target="_blank">reglas del foro</a>
            </label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn">Crear tema</button>
            <a href="<?php echo BASE_URL; ?>/comunidad/foros.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
