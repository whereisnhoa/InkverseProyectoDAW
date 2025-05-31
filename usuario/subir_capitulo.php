<?php
include '../includes/header.php';
include_once('../includes/funciones.php');

// Verificar si el usuario está logueado (ajusta según tu lógica)
if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para subir capítulos.', 'error');
    exit();
}

$obra_id = isset($_GET['obra_id']) ? intval($_GET['obra_id']) : 0;
// Puedes obtener el título del libro si lo necesitas:
// $titulo_libro = obtenerTituloLibro($obra_id);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">

<body class="body-subir">
<div class="subir-main-container">
    <h1 class="titulo-subir">Subir capítulo a tu libro</h1>
    <p class="subir-descripcion">Publica tu nuevo capítulo de forma sencilla y atractiva.</p>
    <form action="procesar_capitulo.php" method="POST" class="form-card" enctype="multipart/form-data" aria-label="Formulario para subir capítulo">
        <input type="hidden" name="obra_id" value="<?php echo $obra_id; ?>">
        <div class="form-group">
            <label for="numero">Número de capítulo <span style="color:#e23c7d">*</span></label>
            <input type="number" id="numero" name="numero" min="1" required>
        </div>
        <div class="form-group">
            <label for="titulo">Título del capítulo</label>
            <input type="text" id="titulo" name="titulo" maxlength="150" autocomplete="off">
        </div>
        <div class="form-group">
            <label for="modo_subida">¿Cómo quieres subir el capítulo?</label>
            <select id="modo_subida" name="modo_subida" required>
                <option value="escribir">Escribir aquí</option>
                <option value="archivo">Subir archivo .txt</option>
            </select>
        </div>
        <div class="form-group" id="contenido_texto_group">
            <label for="contenido">Contenido del capítulo (texto)</label>
            <textarea id="contenido" name="contenido" rows="12"></textarea>
        </div>
        <div class="form-group" id="contenido_archivo_group" style="display:none;">
            <label for="archivo">Archivo de texto (.txt, máx. 1MB)</label>
            <input type="file" id="archivo" name="archivo" accept=".txt">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Subir capítulo</button>
            <a href="mis_obras.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
<script>
document.getElementById('modo_subida').addEventListener('change', function() {
    let modo = this.value;
    document.getElementById('contenido_texto_group').style.display = (modo === 'escribir') ? 'block' : 'none';
    document.getElementById('contenido_archivo_group').style.display = (modo === 'archivo') ? 'block' : 'none';
});
</script>
</body>
<?php include '../includes/footer.php'; ?>