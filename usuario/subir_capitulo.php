
<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/funciones.php');
verificarLogin();
include_once(__DIR__ . '/../includes/header.php');

$tipo = $_GET['tipo'];
$obra_id = intval($_GET['obra_id']);

?>

<style>
    
.container {
    max-width: 600px;
    margin: 40px auto;
    background: #fff;
    padding: 32px 22px;
    border-radius: 13px;
    box-shadow: 0 3px 16px rgba(67,97,238,0.09);
    font-family: 'Segoe UI', Arial, sans-serif;
}
.container h2 {
    color: #4361ee;
    margin-bottom: 24px;
    text-align: center;
}
form {
    margin-top: 18px;
}
.form-group {
    margin-bottom: 17px;
}
.form-group label {
    font-weight: 600;
    margin-bottom: 7px;
    color: #222;
    display: block;
}
.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="file"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #d7dffb;
    border-radius: 5px;
    font-size: 15px;
    transition: border-color 0.2s;
    background: #f9faff;
}
.form-group textarea {
    resize: vertical;
    min-height: 90px;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #4361ee;
    outline: none;
}
.btn,
button[type="submit"] {
    background: #4361ee;
    color: #fff;
    border: none;
    padding: 11px 26px;
    border-radius: 5px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s, box-shadow 0.2s;
    margin-right: 10px;
    box-shadow: 0 2px 8px rgba(67,97,238,0.10);
}
.btn-outline,
a.btn-outline {
    background: #fff;
    color: #4361ee !important;
    border: 1.5px solid #4361ee;
    box-shadow: none;
}
.btn:hover,
button[type="submit"]:hover,
.btn-outline:hover,
a.btn-outline:hover {
    background: #3a56d4;
    color: #fff !important;
    border-color: #3a56d4;
}
@media (max-width: 600px) {
    .container {
        padding: 7px 2px;
    }
}

</style>

<div class="container">
    <h2>Subir capítulo a tu <?php echo ($tipo === 'libro') ? 'libro' : 'cómic'; ?></h2>
    <form action="<?php echo BASE_URL; ?>/controllers/procesar-capitulo.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
        <input type="hidden" name="obra_id" value="<?php echo htmlspecialchars($obra_id); ?>">
        <div class="form-group">
            <label for="numero">Número de capítulo *</label>
            <input type="number" name="numero" min="1" required>
        </div>
        <div class="form-group">
            <label for="titulo">Título del capítulo</label>
            <input type="text" name="titulo" maxlength="255">
        </div>
        <div class="form-group">
            <label>¿Cómo quieres subir el capítulo?</label>
            <select name="modo_capitulo" id="modo_capitulo" required>
                <option value="texto">Escribir aquí</option>
                <option value="pdf">Subir PDF</option>
                <option value="imagen">Subir Imagen</option>
            </select>
        </div>
        <div id="campo-texto" class="form-group">
            <label for="contenido">Contenido del capítulo (texto)</label>
            <textarea name="contenido" rows="8"></textarea>
        </div>
        <div id="campo-archivo" style="display:none;" class="form-group">
            <label for="archivo">Archivo (PDF o Imagen)</label>
            <input type="file" name="archivo">
        </div>
        <button type="submit" class="btn btn-primary">Subir capítulo</button>
    </form>
    <a href="mis_obras.php" class="btn btn-outline">Terminar</a>
</div>
<script>
// JS para mostrar/ocultar campos según modo
document.getElementById('modo_capitulo').addEventListener('change', function() {
    if (this.value === 'texto') {
        document.getElementById('campo-texto').style.display = 'block';
        document.getElementById('campo-archivo').style.display = 'none';
    } else {
        document.getElementById('campo-texto').style.display = 'none';
        document.getElementById('campo-archivo').style.display = 'block';
    }
});
</script>
<?php include_once(__DIR__ . '/../includes/footer.php'); ?>