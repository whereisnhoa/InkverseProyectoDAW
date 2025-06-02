<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('BASE_URL')) {
    define('BASE_URL', '/cozitas/proyecto');
}
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Obtener categorías activas
$sql_categorias = "SELECT id, nombre, tipo FROM categorias ORDER BY tipo, nombre";
$result_categorias = $conexion->query($sql_categorias);
$categorias = ['libro' => [], 'comic' => []];
while ($row = $result_categorias->fetch_assoc()) {
    $categorias[$row['tipo']][] = $row;
}

$errores = [];
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipo = $_POST['tipo'] ?? 'libro';
    $publicacion_tipo = $_POST['publicacion_tipo'] ?? 'capitulos';
    $categorias_seleccionadas = $_POST['categorias'] ?? [];
    $usuario_id = $_SESSION['usuario_id'];
    $portada = $_FILES['portada'] ?? null;
    $nombre_portada = null;

    // Validaciones comunes
    if (empty($titulo)) $errores[] = "El título es obligatorio.";
    if (empty($descripcion)) $errores[] = "La descripción es obligatoria.";
    if (empty($categorias_seleccionadas) || !is_array($categorias_seleccionadas)) {
        $errores[] = "Debes seleccionar al menos una categoría.";
    }
    if ($portada && $portada['error'] === 0) {
        $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($portada['type'], $permitidos)) {
            $errores[] = "El tipo de archivo de la portada no es válido.";
        }
    } else {
        $errores[] = "Se requiere una portada.";
    }

    // Validaciones según tipo de publicación
    if ($publicacion_tipo === "capitulos") {
        $capitulo_texto = trim($_POST['capitulo_texto'] ?? '');
        if (empty($capitulo_texto)) {
            $errores[] = "Debes escribir el primer capítulo.";
        }
    } else if ($publicacion_tipo === "archivo") {
        $archivo_obra = $_FILES['archivo_obra'] ?? null;
        if (!$archivo_obra || $archivo_obra['error'] !== 0) {
            $errores[] = "Debes adjuntar el archivo PDF o imagen de la obra.";
        } else {
            $permitidos_obra = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($archivo_obra['type'], $permitidos_obra)) {
                $errores[] = "El archivo de la obra debe ser PDF o imagen.";
            }
        }
    }

    // Si todo OK, procesar las subidas y guardar en BD
    if (empty($errores)) {
        // Guardar portada
        if ($portada && $portada['error'] === 0) {
            $ext = pathinfo($portada['name'], PATHINFO_EXTENSION);
            $nombre_portada = uniqid('portada_') . '.' . $ext;
            $destino = __DIR__ . '/../uploads/portadas/' . $nombre_portada;
            if (!is_dir(__DIR__ . '/../uploads/portadas/')) {
                mkdir(__DIR__ . '/../uploads/portadas/', 0775, true);
            }
            if (!move_uploaded_file($portada['tmp_name'], $destino)) {
                $errores[] = "No se pudo guardar la portada.";
            }
        }

        // Guardar obra principal (PDF/imagen/capítulo) según tipo
        $nombre_archivo_obra = null;
        $primer_capitulo = null;

        if ($publicacion_tipo === "capitulos") {
            $primer_capitulo = $capitulo_texto;
        } else if ($publicacion_tipo === "archivo" && $archivo_obra && $archivo_obra['error'] === 0) {
            $ext_obra = pathinfo($archivo_obra['name'], PATHINFO_EXTENSION);
            $nombre_archivo_obra = uniqid('obra_') . '.' . $ext_obra;
            $destino_obra = __DIR__ . '/../uploads/obras/' . $nombre_archivo_obra;
            if (!is_dir(__DIR__ . '/../uploads/obras/')) {
                mkdir(__DIR__ . '/../uploads/obras/', 0775, true);
            }
            if (!move_uploaded_file($archivo_obra['tmp_name'], $destino_obra)) {
                $errores[] = "No se pudo guardar el archivo de la obra.";
            }
        }

        // Si no hay errores al subir archivos, guardar en BD
        if (empty($errores)) {
            // 1. Insertar en 'libros'
            $sql = "INSERT INTO libros (usuario_id, titulo, descripcion, portada, archivo_obra, fecha_subida) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("issss", $usuario_id, $titulo, $descripcion, $nombre_portada, $nombre_archivo_obra);
            if ($stmt->execute()) {
                $libro_id = $stmt->insert_id;
                // 2. Insertar el primer capítulo si aplica
                if ($publicacion_tipo === "capitulos") {
                    $titulo_cap = "Capítulo 1";
                    $contenido_cap = $primer_capitulo;
                    $stmt_cap = $conexion->prepare("INSERT INTO capitulos_libro (libro_id, numero, titulo, contenido, fecha_publicacion) VALUES (?, 1, ?, ?, NOW())");
                    $stmt_cap->bind_param("iss", $libro_id, $titulo_cap, $contenido_cap);
                    $stmt_cap->execute();
                }
                // 3. Insertar en libro_categorias (para varias categorías)
                foreach ($categorias_seleccionadas as $cat_id) {
                    $stmt_cat = $conexion->prepare("INSERT INTO libro_categorias (libro_id, categoria_id) VALUES (?, ?)");
                    $stmt_cat->bind_param("ii", $libro_id, $cat_id);
                    $stmt_cat->execute();
                }
                $exito = true;
                $_POST = [];
            } else {
                $errores[] = "Error al guardar la obra: " . $conexion->error;
            }
        }
    }
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/usuario.css">

<div class="container" style="max-width: 700px; margin-top: 40px;">
    <h1 class="mb-4">Publicar nueva obra</h1>
    <?php if ($exito): ?>
        <div class="alert alert-success">
            ¡Obra publicada correctamente!
            <a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php" class="btn btn-sm btn-primary ml-3">Ver mis obras</a>
        </div>
    <?php endif; ?>
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errores as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="nueva-obra-form" autocomplete="off">
        <div class="form-group">
            <label for="titulo">Título <span class="text-danger">*</span></label>
            <input type="text" name="titulo" id="titulo" class="form-control" maxlength="200" required value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="descripcion">Descripción <span class="text-danger">*</span></label>
            <textarea name="descripcion" id="descripcion" rows="4" class="form-control" required><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="tipo">Tipo de obra <span class="text-danger">*</span></label>
            <select name="tipo" id="tipo" class="form-control" required onchange="mostrarCategoriasPorTipo(this.value)">
                <option value="libro" <?php echo (($_POST['tipo'] ?? '') === 'libro') ? 'selected' : ''; ?>>Literatura</option>
                <option value="comic" <?php echo (($_POST['tipo'] ?? '') === 'comic') ? 'selected' : ''; ?>>Cómic/Manga</option>
            </select>
        </div>

        <div class="form-group">
            <label>Modo de publicación <span class="text-danger">*</span></label>
            <div style="display: flex; gap: 15px;">
                <label style="cursor:pointer">
                    <input type="radio" name="publicacion_tipo" value="capitulos" <?php if(($_POST['publicacion_tipo'] ?? 'capitulos') == 'capitulos') echo 'checked'; ?> onclick="mostrarModoObra()">
                    <span style="margin-left:4px;">Por capítulos</span>
                </label>
                <label style="cursor:pointer">
                    <input type="radio" name="publicacion_tipo" value="archivo" <?php if(($_POST['publicacion_tipo'] ?? '') == 'archivo') echo 'checked'; ?> onclick="mostrarModoObra()">
                    <span style="margin-left:4px;">Archivo PDF / Imagen</span>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label>Categorías <span class="text-danger">*</span></label>
            <div id="categorias-libro" class="categoria-select-multi" style="display:<?php echo (($_POST['tipo'] ?? '') === 'comic') ? 'none' : 'block'; ?>;">
                <select name="categorias[]" id="categorias_libro" class="form-control" multiple size="6" onchange="actualizarChips('libro')">
                    <?php foreach ($categorias['libro'] as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (!empty($_POST['categorias']) && in_array($cat['id'], $_POST['categorias'])) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Ctrl+Click para seleccionar varias</small>
                <div class="categoria-chips" id="chips-libro"></div>
            </div>
            <div id="categorias-comic" class="categoria-select-multi" style="display:<?php echo (($_POST['tipo'] ?? '') === 'comic') ? 'block' : 'none'; ?>;">
                <select name="categorias[]" id="categorias_comic" class="form-control" multiple size="6" onchange="actualizarChips('comic')">
                    <?php foreach ($categorias['comic'] as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (!empty($_POST['categorias']) && in_array($cat['id'], $_POST['categorias'])) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Ctrl+Click para seleccionar varias</small>
                <div class="categoria-chips" id="chips-comic"></div>
            </div>
        </div>
        <div class="form-group">
            <label for="portada">Portada <span class="text-danger">*</span></label>
            <input type="file" name="portada" id="portada" class="form-control-file" accept="image/*" required onchange="previewPortada(this)">
            <div class="portada-preview" id="portada-preview" style="margin-top:8px;"></div>
            <small>JPG, PNG, GIF, WEBP</small>
        </div>

        <!-- Campo condicional según modo de publicación -->
        <div class="form-group" id="campo-capitulo" style="display:none;">
            <label for="capitulo_texto">Primer capítulo <span class="text-danger">*</span></label>
            <textarea name="capitulo_texto" id="capitulo_texto" rows="7" class="form-control"><?php echo htmlspecialchars($_POST['capitulo_texto'] ?? ''); ?></textarea>
            <small>Escribe aquí el primer capítulo de tu obra.</small>
        </div>
        <div class="form-group" id="campo-archivo" style="display:none;">
            <label for="archivo_obra">Archivo PDF/Imagen <span class="text-danger">*</span></label>
            <input type="file" name="archivo_obra" id="archivo_obra" class="form-control-file" accept=".pdf,image/*">
            <small>Adjunta el archivo de tu obra (PDF, JPG, PNG, GIF, WEBP)</small>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Publicar obra</button>
    </form>
</div>

<script>
// Mostrar select correcto según tipo y manejar required dinámico
function mostrarCategoriasPorTipo(tipo) {
    document.getElementById('categorias-libro').style.display = tipo === 'libro' ? 'block' : 'none';
    document.getElementById('categorias-comic').style.display = tipo === 'comic' ? 'block' : 'none';
    actualizarChips(tipo);
    // Required solo en el visible
    let selectLibro = document.getElementById('categorias_libro');
    let selectComic = document.getElementById('categorias_comic');
    if (tipo === 'libro') {
        selectLibro.required = true;
        selectComic.required = false;
    } else {
        selectLibro.required = false;
        selectComic.required = true;
    }
}

// Chips de categorías seleccionadas
function actualizarChips(tipo) {
    let select = tipo === 'libro'
        ? document.getElementById('categorias_libro')
        : document.getElementById('categorias_comic');
    let chipsDiv = tipo === 'libro'
        ? document.getElementById('chips-libro')
        : document.getElementById('chips-comic');
    chipsDiv.innerHTML = '';
    Array.from(select.selectedOptions).forEach(option => {
        let chip = document.createElement('span');
        chip.className = 'chip-categoria';
        chip.textContent = option.text;
        let close = document.createElement('span');
        close.className = 'chip-close';
        close.innerHTML = '&times;';
        close.onclick = function() {
            option.selected = false;
            actualizarChips(tipo);
        };
        chip.appendChild(close);
        chipsDiv.appendChild(chip);
    });
}

// Preview de la portada
function previewPortada(input) {
    let preview = document.getElementById('portada-preview');
    preview.innerHTML = '';
    if (input.files && input.files[0]) {
        let file = input.files[0];
        let reader = new FileReader();
        reader.onload = function(e) {
            let img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '90px';
            img.style.height = '120px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '7px';
            img.style.border = '1.5px solid #dee3f7';
            img.alt = 'Portada seleccionada';
            preview.appendChild(img);
        }
        reader.readAsDataURL(file);
    }
}

// Mostrar/ocultar campos según modo de publicación y required dinámico
function mostrarModoObra() {
    let modo = document.querySelector('input[name="publicacion_tipo"]:checked').value;
    let cap = document.getElementById('campo-capitulo');
    let arc = document.getElementById('campo-archivo');
    let inputCap = document.getElementById('capitulo_texto');
    let inputArc = document.getElementById('archivo_obra');
    cap.style.display = (modo === 'capitulos') ? 'block' : 'none';
    arc.style.display = (modo === 'archivo') ? 'block' : 'none';
    if (modo === 'capitulos') {
        inputCap.required = true;
        inputArc.required = false;
    } else {
        inputCap.required = false;
        inputArc.required = true;
    }
}

// Inicializar campos al cargar
document.addEventListener('DOMContentLoaded', function() {
    mostrarCategoriasPorTipo(document.getElementById('tipo').value);
    actualizarChips(document.getElementById('tipo').value);
    mostrarModoObra();

    document.getElementById('tipo').addEventListener('change', function() {
        mostrarCategoriasPorTipo(this.value);
    });

    let radios = document.querySelectorAll('input[name="publicacion_tipo"]');
    radios.forEach(function(radio) {
        radio.addEventListener('change', mostrarModoObra);
    });
});
</script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>