<?php
// Iniciar sesión
session_start();

// Incluir configuración
require_once(__DIR__ . '/../includes/config.php');

// Incluir funciones
require_once(__DIR__ . '/../includes/funciones.php');

// Verificar que el usuario esté logueado
verificarLogin();

// Incluir header
include_once(__DIR__ . '/../includes/header.php');

// Obtener categorías para el formulario
$sql_categorias_libros = "SELECT id, nombre FROM categorias WHERE tipo = 'libro' ORDER BY nombre";
$sql_categorias_comics = "SELECT id, nombre FROM categorias WHERE tipo = 'comic' ORDER BY nombre";

$categorias_libros = $conexion->query($sql_categorias_libros);
$categorias_comics = $conexion->query($sql_categorias_comics);
?>

<div class="container">
    <div class="nueva-obra-container">
        <div class="nueva-obra-header">
            <h1><i class="fas fa-plus-circle"></i> Subir nueva obra</h1>
            <p>Comparte tu creatividad con la comunidad de InkVerse</p>
        </div>

        <form action="<?php echo BASE_URL; ?>/controllers/procesar-obra.php" method="POST" enctype="multipart/form-data" class="nueva-obra-form">
            
            <!-- Información básica -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Información básica</h3>
                
                <div class="form-group">
                    <label for="titulo">Título de la obra *</label>
                    <input type="text" id="titulo" name="titulo" required maxlength="255" placeholder="Ingresa el título de tu obra">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="5" placeholder="Describe tu obra, su trama, personajes principales..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo">Tipo de obra *</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Selecciona el tipo</option>
                            <option value="libro">Libro/Literatura</option>
                            <option value="comic">Manga/Cómic</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="categoria">Categoría *</label>
                        <select id="categoria" name="categoria_id" required>
                            <option value="">Primero selecciona el tipo</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="genero">Género adicional</label>
                        <input type="text" id="genero" name="genero" placeholder="Ej: Romance, Acción, Misterio...">
                    </div>

                    <div class="form-group">
                        <label for="idioma">Idioma</label>
                        <select id="idioma" name="idioma">
                            <option value="es">Español</option>
                            <option value="en">Inglés</option>
                            <option value="fr">Francés</option>
                            <option value="ca">Catalán</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Portada -->
            <div class="form-section">
                <h3><i class="fas fa-image"></i> Portada</h3>
                
                <div class="form-group">
                    <label for="portada">Imagen de portada</label>
                    <div class="file-upload-area">
                        <input type="file" id="portada" name="portada" accept="image/*">
                        <div class="file-upload-text">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Arrastra una imagen aquí o haz clic para seleccionar</p>
                            <small>Formatos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                        </div>
                        <div id="preview-portada" class="image-preview"></div>
                    </div>
                </div>
            </div>

            <!-- Configuración -->
            <div class="form-section">
                <h3><i class="fas fa-cog"></i> Configuración</h3>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="es_adulto" value="1">
                            <span class="checkmark"></span>
                            <div class="checkbox-content">
                                <strong>Contenido para adultos (+18)</strong>
                                <small>Esta obra contiene contenido no apto para menores de edad</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="permitir_comentarios" value="1" checked>
                            <span class="checkmark"></span>
                            <div class="checkbox-content">
                                <strong>Permitir comentarios</strong>
                                <small>Los usuarios podrán comentar en tu obra</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="estado">Estado de la obra</label>
                    <select id="estado" name="estado">
                        <option value="en_progreso">En progreso</option>
                        <option value="completada">Completada</option>
                        <option value="pausada">Pausada</option>
                    </select>
                </div>
            </div>

            <!-- Botones -->
            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/usuario/perfil.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Publicar obra
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Estilos para el formulario -->
<style>
.nueva-obra-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.nueva-obra-header {
    text-align: center;
    margin-bottom: 30px;
}

.nueva-obra-header h1 {
    color: #333;
    margin-bottom: 10px;
}

.nueva-obra-header p {
    color: #666;
    font-size: 16px;
}

.nueva-obra-form {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.form-section {
    padding: 25px;
    border-bottom: 1px solid #eee;
}

.form-section:last-child {
    border-bottom: none;
}

.form-section h3 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-section h3 i {
    color: #4361ee;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.file-upload-area {
    position: relative;
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    transition: all 0.2s;
    cursor: pointer;
}

.file-upload-area:hover {
    border-color: #4361ee;
    background-color: #f8f9ff;
}

.file-upload-area input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-upload-text i {
    font-size: 48px;
    color: #4361ee;
    margin-bottom: 15px;
}

.file-upload-text p {
    margin: 0 0 5px 0;
    font-size: 16px;
    color: #333;
}

.file-upload-text small {
    color: #666;
}

.image-preview {
    margin-top: 15px;
    display: none;
}

.image-preview img {
    max-width: 200px;
    max-height: 200px;
    border-radius: 5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.checkbox-group {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    margin-bottom: 0;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 3px;
    position: relative;
    flex-shrink: 0;
    margin-top: 2px;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
    background-color: #4361ee;
    border-color: #4361ee;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.checkbox-content strong {
    display: block;
    color: #333;
    margin-bottom: 2px;
}

.checkbox-content small {
    color: #666;
    font-size: 13px;
}

.form-actions {
    padding: 25px;
    background-color: #f8f9fa;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary {
    background-color: #4361ee;
    color: white;
}

.btn-primary:hover {
    background-color: #3a56d4;
    transform: translateY(-1px);
}

.btn-outline {
    background-color: transparent;
    border: 1px solid #ddd;
    color: #666;
}

.btn-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 10px;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- JavaScript para funcionalidad del formulario -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo');
    const categoriaSelect = document.getElementById('categoria');
    const portadaInput = document.getElementById('portada');
    const previewPortada = document.getElementById('preview-portada');
    
    // Categorías por tipo
    const categorias = {
        libro: <?php echo json_encode($categorias_libros->fetch_all(MYSQLI_ASSOC)); ?>,
        comic: <?php echo json_encode($categorias_comics->fetch_all(MYSQLI_ASSOC)); ?>
    };
    
    // Actualizar categorías según el tipo seleccionado
    tipoSelect.addEventListener('change', function() {
        const tipo = this.value;
        categoriaSelect.innerHTML = '<option value="">Selecciona una categoría</option>';
        
        if (tipo && categorias[tipo]) {
            categorias[tipo].forEach(function(categoria) {
                const option = document.createElement('option');
                option.value = categoria.id;
                option.textContent = categoria.nombre;
                categoriaSelect.appendChild(option);
            });
        }
    });
    
    // Vista previa de la portada
    portadaInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewPortada.innerHTML = '<img src="' + e.target.result + '" alt="Vista previa">';
                previewPortada.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            previewPortada.style.display = 'none';
        }
    });
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>