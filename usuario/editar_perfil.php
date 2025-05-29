<?php
// Iniciar sesión
session_start();

// Incluir configuración primero
require_once(__DIR__ . '/../includes/config.php');

// Incluir archivos necesarios
require_once(__DIR__ . '/../includes/funciones.php');
require_once(__DIR__ . '/../includes/conexion.php');

// Verificar que el usuario esté logueado
verificarLogin();

// Obtener información del usuario
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Incluir header
include_once(__DIR__ . '/../includes/header.php');
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/usuario.css">

<div class="editar-perfil-container">
    <div class="editar-perfil-card">
        <div class="card-header">
            <h1><i class="fas fa-user-edit"></i> Editar Perfil</h1>
            <a href="<?php echo BASE_URL; ?>/usuario/perfil.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al perfil
            </a>
        </div>
        <div class="card-body">
            <?php mostrarMensaje(); ?>
            <form action="<?php echo BASE_URL; ?>/controllers/actualizar-perfil.php" method="POST" enctype="multipart/form-data" class="form-editar-perfil">
                <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                <!-- Sección imágenes de perfil -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Avatar</label>
                        <div class="preview-container">
                            <div id="preview-avatar">
                                <img src="<?php echo htmlspecialchars($usuario['avatar'] ?? BASE_URL.'/public/img/avatar_default.png'); ?>" alt="Avatar actual">
                            </div>
                        </div>
                        <input type="file" id="avatar" name="avatar" accept="image/*">
                        <small>Formatos: JPG, PNG, GIF. Máx. 2MB.</small>
                    </div>
                    <div class="form-group">
                        <label>Banner</label>
                        <div class="preview-container">
                            <div id="preview-banner">
                                <img src="<?php echo htmlspecialchars($usuario['banner'] ?? BASE_URL.'/public/img/banner_default.jpg'); ?>" alt="Banner actual">
                            </div>
                        </div>
                        <input type="file" id="banner" name="banner" accept="image/*">
                        <small>Recomendado 1200x300px. Máx. 2MB.</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario">Nombre de usuario *</label>
                        <input type="text" id="usuario" name="usuario" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                        <small>Este será tu nombre público en la plataforma</small>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo electrónico *</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control"
                               value="<?php echo $usuario['fecha_nacimiento'] ?? ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="biografia">Biografía</label>
                    <textarea id="biografia" name="biografia" class="form-control" rows="4"
                              placeholder="Cuéntanos algo sobre ti..."><?php echo htmlspecialchars($usuario['biografia'] ?? ''); ?></textarea>
                    <small>Máximo 500 caracteres</small>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="sitio_web">Sitio web</label>
                        <input type="url" id="sitio_web" name="sitio_web" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['sitio_web'] ?? ''); ?>"
                               placeholder="https://ejemplo.com">
                    </div>
                    <div class="form-group">
                        <label for="ubicacion">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['ubicacion'] ?? ''); ?>"
                               placeholder="Ciudad, País">
                    </div>
                </div>
                <!-- Redes sociales -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="twitter">X</label>
                        <input type="url" id="X" name="X" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['X'] ?? ''); ?>"
                               placeholder="https://x.com/usuario">
                    </div>
                    <div class="form-group">
                        <label for="instagram">Instagram</label>
                        <input type="url" id="instagram" name="instagram" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['instagram'] ?? ''); ?>"
                               placeholder="https://instagram.com/usuario">
                    </div>
                    <div class="form-group">
                        <label for="facebook">Facebook</label>
                        <input type="url" id="facebook" name="facebook" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['facebook'] ?? ''); ?>"
                               placeholder="https://facebook.com/usuario">
                    </div>
                </div>
                <div class="form-group">
                    <label>Privacidad del perfil</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="privacidad" value="publico"
                                   <?php echo (!isset($usuario['privacidad']) || $usuario['privacidad'] == 'publico') ? 'checked' : ''; ?>>
                            <span class="radio-custom"></span>
                            <div class="radio-content">
                                <strong>Público</strong>
                                <small>Todos pueden ver tu perfil y obras</small>
                            </div>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="privacidad" value="privado"
                                   <?php echo (isset($usuario['privacidad']) && $usuario['privacidad'] == 'privado') ? 'checked' : ''; ?>>
                            <span class="radio-custom"></span>
                            <div class="radio-content">
                                <strong>Privado</strong>
                                <small>Solo tus seguidores pueden ver tu perfil</small>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar cambios
                    </button>
                    <a href="<?php echo BASE_URL; ?>/usuario/perfil.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
