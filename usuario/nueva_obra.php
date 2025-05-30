<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/funciones.php');
verificarLogin();
include_once(__DIR__ . '/../includes/header.php');

// Obtener categorías
$sql_categorias_libros = "SELECT id, nombre FROM categorias WHERE tipo = 'libro' ORDER BY nombre";
$sql_categorias_comics = "SELECT id, nombre FROM categorias WHERE tipo = 'comic' ORDER BY nombre";
$categorias_libros = $conexion->query($sql_categorias_libros);
$categorias_comics = $conexion->query($sql_categorias_comics);
?>

<style>

.nueva-obra-container {
    max-width: 700px;
    margin: 30px auto;
    background: #fff;
    padding: 32px 24px;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(67,97,238,0.07);
    font-family: 'Segoe UI', Arial, sans-serif;
}
.nueva-obra-container h1 {
    color: #3a56d4;
    margin-bottom: 20px;
    font-size: 2em;
    text-align: center;
}
.nueva-obra-form {
    margin: 0;
    padding: 0;
}
.form-group {
    margin-bottom: 18px;
}
.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 7px;
    color: #222;
}
.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="file"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1.5px solid #d7dffb;
    border-radius: 5px;
    font-size: 15px;
    transition: border-color 0.2s;
    background: #f9faff;
}
.form-group textarea {
    resize: vertical;
    min-height: 100px;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #4361ee;
    outline: none;
}
.form-row {
    display: flex;
    gap: 16px;
}
.form-row .form-group {
    flex: 1;
    min-width: 120px;
}
.btn,
button[type="submit"] {
    background: #4361ee;
    color: #fff;
    border: none;
    padding: 12px 28px;
    border-radius: 5px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s, box-shadow 0.2s;
    margin-right: 10px;
    box-shadow: 0 2px 8px rgba(67,97,238,0.10);
}
.btn:hover,
button[type="submit"]:hover {
    background: #3a56d4;
}
input[type="checkbox"] {
    accent-color: #4361ee;
    width: 18px;
    height: 18px;
    margin-right: 8px;
    vertical-align: middle;
}
@media (max-width: 650px) {
    .nueva-obra-container {
        padding: 10px 3px;
    }
    .form-row {
        flex-direction: column;
        gap: 0;
    }
}

</style>

<div class="container">
    <div class="nueva-obra-container">
        <h1><i class="fas fa-plus-circle"></i> Subir nueva obra</h1>
        <form action="<?php echo BASE_URL; ?>/controllers/procesar-obra.php" method="POST" enctype="multipart/form-data" class="nueva-obra-form">
            <!-- Básicos -->
            <div class="form-group">
                <label for="titulo">Título *</label>
                <input type="text" id="titulo" name="titulo" required maxlength="255">
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="tipo">Tipo *</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">Selecciona</option>
                        <option value="libro">Libro/Literatura</option>
                        <option value="comic">Manga/Cómic</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="categoria">Categoría *</label>
                    <select id="categoria" name="categoria_id" required>
                        <option value="">Selecciona el tipo primero</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Método de publicación *</label>
                <select id="modo_publicacion" name="modo_publicacion" required>
                    <option value="completa">Obra completa (PDF/Imagen)</option>
                    <option value="capitulos">Por capítulos</option>
                </select>
            </div>
            <!-- Si es completa: archivo PDF o imagen -->
            <div id="subida-completa" style="display:block;">
                <div class="form-group">
                    <label for="archivo">Archivo de la obra (PDF, opcional)</label>
                    <input type="file" id="archivo" name="archivo" accept=".pdf">
                </div>
            </div>
            <!-- Portada -->
            <div class="form-group">
                <label for="portada">Portada (opcional)</label>
                <input type="file" id="portada" name="portada" accept="image/*">
            </div>
            <!-- Público adulto -->
            <div class="form-group">
                <label><input type="checkbox" name="es_adulto" value="1"> Contenido para adultos</label>
            </div>
            <button type="submit" class="btn btn-primary">Crear obra</button>
        </form>
    </div>
</div>
<script>
// JS: Cambia categorías según tipo
const tipoSelect = document.getElementById('tipo');
const categoriaSelect = document.getElementById('categoria');
const categorias = {
    libro: <?php echo json_encode($categorias_libros->fetch_all(MYSQLI_ASSOC)); ?>,
    comic: <?php echo json_encode($categorias_comics->fetch_all(MYSQLI_ASSOC)); ?>
};
tipoSelect.addEventListener('change', function() {
    const tipo = this.value;
    categoriaSelect.innerHTML = '<option value="">Selecciona</option>';
    if (categorias[tipo]) {
        categorias[tipo].forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.nombre;
            categoriaSelect.appendChild(option);
        });
    }
});
// JS: Mostrar/ocultar subida completa/capítulos
document.getElementById('modo_publicacion').addEventListener('change', function() {
    document.getElementById('subida-completa').style.display = (this.value === 'completa') ? 'block' : 'none';
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>