<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/funciones.php');
require_once(__DIR__ . '/../includes/conexion.php');
verificarLogin();
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
include_once(__DIR__ . '/../includes/header.php');
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/usuario.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/fontawesome.min.css">
<style>
.editar-perfil-container {
    max-width: 730px;
    margin: 48px auto 48px auto;
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(67,97,238,0.14);
    padding: 0 0 36px 0;
    position: relative;
}
.editar-perfil-card {
    padding: 0 36px;
}
.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 38px;
    margin-bottom: 12px;
}
.card-header h1 {
    color: #4361ee;
    font-size: 2.1rem;
    font-weight: 800;
    margin: 0;
    letter-spacing: -1px;
}
.btn-volver {
    background: #f8faff;
    color: #4361ee;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 7px;
    font-weight: 600;
    font-size: 1rem;
    transition: background 0.18s, color 0.16s;
    border: 1px solid #e7e9fb;
    margin-left: 12px;
}
.btn-volver:hover {
    background: #4361ee;
    color: #fff;
}
.form-editar-perfil {
    margin-top: 8px;
}
.avatar-banner-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 38px;
    gap: 0;
    width: 100%;
}
.avatar-banner-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    gap: 0;
}
.form-group.avatar-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    min-width: 220px;
    flex: 1 1 220px;
}
#preview-avatar img {
    width: 118px;
    height: 118px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #4361ee33;
    box-shadow: 0 3px 22px #4361ee14;
    background: #f3f7ff;
    margin-bottom: 10px;
}
.form-group.avatar-group label {
    font-size: 1.2rem;
    font-weight: 700;
    color: #22316c;
    margin-bottom: 8px;
}
.form-group.banner-group {
    width: 100%;
    max-width: 560px;
    margin: 0 auto 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.form-group.banner-group label {
    font-size: 1.2rem;
    font-weight: 700;
    color: #22316c;
    margin-bottom: 0px;
    margin-top: 24px;
}
.banner-preview-box {
    width: 100%;
    min-width: 320px;
    max-width: 560px;
    height: 130px;
    border-radius: 24px;
    border: 2px solid #dee3f7;
    background: #e7e9fb;
    background-size: cover;
    background-position: center center;
    box-shadow: 0 6px 28px #4361ee16;
    position: relative;
    margin-bottom: 12px;
    margin-top: 12px;
    transition: box-shadow 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.banner-preview-remove {
    position: absolute;
    top: 13px;
    right: 24px;
    background: #fff;
    color: #d10000;
    border: 1.5px solid #d10000;
    border-radius: 50%;
    width: 31px; height: 31px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    box-shadow: 0 2px 12px #4361ee24;
    z-index: 9;
    transition: background 0.1s, color 0.13s, border 0.14s;
}
.banner-preview-remove:hover { background: #ffeaea;}
.banner-picker-fields {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 5px;
    width: 100%;
    align-items: center;
}
input[type="file"] {
    margin-top: 7px;
    max-width: 260px;
}
input[type="color"] {
    width: 62px;
    height: 38px;
    border-radius: 9px;
    border: 1.5px solid #dee3f7;
    background: #fff;
    cursor: pointer;
    margin-bottom: 0;
}
@media (max-width: 950px) {
    .editar-perfil-container, .editar-perfil-card { max-width: 99vw; padding-left: 0; padding-right: 0;}
    .form-group.banner-group { max-width: 98vw; width: 100%; }
    .banner-preview-box { min-width: 0; width: 100%; }
}
.form-row {
    display: flex;
    gap: 30px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}
.form-group {
    flex: 1 1 220px;
    min-width: 180px;
    margin-bottom: 10px;
}
.form-group label {
    display: block;
    font-weight: 700;
    color: #22316c;
    margin-bottom: 5px;
}
.form-group small {
    color: #6a7bb5;
    font-size: 0.93rem;
    margin-top: 2px;
    display: block;
}
input[type="text"], input[type="email"], input[type="url"], input[type="date"], textarea,
input[type="color"] {
    width: 100%;
    padding: 9px 13px;
    border: 1.5px solid #e7e9fb;
    border-radius: 8px;
    font-size: 1.09rem;
    color: #22316c;
    background: #f8faff;
    margin-bottom: 2px;
    transition: border-color 0.16s;
}
input[type="text"]:focus, input[type="email"]:focus, input[type="url"]:focus, input[type="date"]:focus, textarea:focus {
    border-color: #4361ee;
    outline: none;
}
textarea {
    min-height: 80px;
    resize: vertical;
}
.radio-group {
    display: flex;
    gap: 28px;
    margin-top: 5px;
    margin-bottom: 14px;
}
.radio-option {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    font-size: 1.03rem;
    background: #f8faff;
    border-radius: 7px;
    padding: 7px 11px;
    border: 1.5px solid #e7e9fb;
    cursor: pointer;
    transition: background 0.16s, border 0.15s;
}
.radio-option input[type="radio"] {
    margin-top: 5px;
    accent-color: #4361ee;
}
.radio-option:hover, .radio-option input[type="radio"]:focus + .radio-custom {
    background: #e7f0fe;
    border-color: #4361ee;
}
.radio-content strong { color: #22316c; }
.form-actions {
    display: flex;
    gap: 18px;
    margin-top: 17px;
}
.btn.btn-primary {
    background: linear-gradient(90deg, #4361ee 60%, #4cc9f0 100%);
    color: #fff;
    border: none;
    padding: 11px 24px;
    border-radius: 7px;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background 0.13s, color 0.13s;
    box-shadow: 0 2px 10px #4361ee18;
}
.btn.btn-primary:hover {
    background: linear-gradient(90deg, #2744c5 60%, #4361ee 100%);
    color: #fff;
}
.btn.btn-outline {
    background: #fff;
    color: #4361ee;
    border: 1.5px solid #4361ee;
    padding: 10px 21px;
    border-radius: 7px;
    font-weight: 700;
    font-size: 1.08rem;
    cursor: pointer;
    transition: background 0.12s, color 0.13s;
}
.btn.btn-outline:hover {
    background: #4361ee;
    color: #fff;
}
</style>

<div class="editar-perfil-container">
    <div class="editar-perfil-card">
        <div class="card-header">
            <h1><i class="fas fa-user-edit"></i> <?= htmlspecialchars($TEXTOS['editar_perfil_titulo'] ?? 'Editar Perfil') ?></h1>
            <a href="<?php echo BASE_URL; ?>/usuario/perfil.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i> <?= htmlspecialchars($TEXTOS['volver_perfil'] ?? 'Volver al perfil') ?>
            </a>
        </div>
        <div class="card-body">
            <?php mostrarMensaje(); ?>
            <form action="<?php echo BASE_URL; ?>/controllers/actualizar-perfil.php" method="POST" enctype="multipart/form-data" class="form-editar-perfil">
                <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                <div class="avatar-banner-stack">
                    <div class="avatar-banner-inner">
                        <div class="form-group avatar-group">
                            <label><?= htmlspecialchars($TEXTOS['avatar'] ?? 'Avatar') ?></label>
                            <div class="preview-container">
                                <div id="preview-avatar">
                                    <img src="<?php echo htmlspecialchars($usuario['avatar'] ? BASE_URL.'/uploads/avatars/'.$usuario['avatar'] : BASE_URL.'/includes/img/avatar-default.svg'); ?>" alt="Avatar">
                                </div>
                            </div>
                            <input type="file" id="avatar" name="avatar" accept="image/*">
                            <small><?= htmlspecialchars($TEXTOS['avatar_formatos'] ?? 'Formatos: JPG, PNG, GIF. Máx. 2MB.') ?></small>
                        </div>
                    </div>
                    <div class="form-group banner-group">
                        <label><?= htmlspecialchars($TEXTOS['banner'] ?? 'Banner') ?></label>
                        <div class="banner-preview-box" id="banner-preview-box"
                            style="
                                <?php
                                if ($usuario['banner_img']) {
                                    echo "background-image:url('".BASE_URL."/uploads/banners/".htmlspecialchars($usuario['banner_img'])."');";
                                } else {
                                    $color = $usuario['banner_color'] ?? '#4361ee';
                                    echo "background:$color;";
                                }
                                ?>
                            ">
                            <?php if ($usuario['banner_img']): ?>
                                <button type="button" class="banner-preview-remove" id="removeBannerImg" title="<?= htmlspecialchars($TEXTOS['eliminar_banner_img'] ?? 'Quitar imagen de banner') ?>">
                                    <i class="fas fa-times"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="banner-picker-fields">
                            <label style="font-weight:500; color:#4361ee;"><?= htmlspecialchars($TEXTOS['color_banner'] ?? 'Color de banner') ?></label>
                            <input type="color" id="banner_color" name="banner_color"
                                value="<?php echo htmlspecialchars($usuario['banner_color'] ?? '#4361ee'); ?>">
                            <small><?= htmlspecialchars($TEXTOS['elige_color_banner'] ?? 'Elige un color si no tienes imagen') ?></small>
                            <input type="file" id="banner_img" name="banner_img" accept="image/*">
                            <small><?= htmlspecialchars($TEXTOS['banner_recomendado'] ?? 'Recomendado 1200x300px. Máx. 2MB.') ?></small>
                            <input type="hidden" id="eliminar_banner_img" name="eliminar_banner_img" value="0">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario"><?= htmlspecialchars($TEXTOS['nombre_usuario'] ?? 'Nombre de usuario') ?> *</label>
                        <input type="text" id="usuario" name="usuario" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                        <small><?= htmlspecialchars($TEXTOS['nombre_usuario_info'] ?? 'Este será tu nombre público en la plataforma') ?></small>
                    </div>
                    <div class="form-group">
                        <label for="email"><?= htmlspecialchars($TEXTOS['correo_electronico'] ?? 'Correo electrónico') ?> *</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre"><?= htmlspecialchars($TEXTOS['nombre_completo'] ?? 'Nombre completo') ?></label>
                        <input type="text" id="nombre" name="nombre" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento"><?= htmlspecialchars($TEXTOS['fecha_nacimiento'] ?? 'Fecha de nacimiento') ?></label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control"
                               value="<?php echo $usuario['fecha_nacimiento'] ?? ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="biografia"><?= htmlspecialchars($TEXTOS['biografia'] ?? 'Biografía') ?></label>
                    <textarea id="biografia" name="biografia" class="form-control" rows="4"
                              placeholder="<?= htmlspecialchars($TEXTOS['biografia_placeholder'] ?? 'Cuéntanos algo sobre ti...') ?>"><?php echo htmlspecialchars($usuario['biografia'] ?? ''); ?></textarea>
                    <small><?= htmlspecialchars($TEXTOS['biografia_max'] ?? 'Máximo 500 caracteres') ?></small>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="sitio_web"><?= htmlspecialchars($TEXTOS['sitio_web'] ?? 'Sitio web') ?></label>
                        <input type="url" id="sitio_web" name="sitio_web" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['sitio_web'] ?? ''); ?>"
                               placeholder="https://ejemplo.com">
                    </div>
                    <div class="form-group">
                        <label for="ubicacion"><?= htmlspecialchars($TEXTOS['ubicacion'] ?? 'Ubicación') ?></label>
                        <input type="text" id="ubicacion" name="ubicacion" class="form-control"
                               value="<?php echo htmlspecialchars($usuario['ubicacion'] ?? ''); ?>"
                               placeholder="<?= htmlspecialchars($TEXTOS['ubicacion_placeholder'] ?? 'Ciudad, País') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="X">X</label>
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
                    <label><?= htmlspecialchars($TEXTOS['privacidad_perfil'] ?? 'Privacidad del perfil') ?></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="privacidad" value="publico"
                                   <?php echo (!isset($usuario['privacidad']) || $usuario['privacidad'] == 'publico') ? 'checked' : ''; ?>>
                            <span class="radio-custom"></span>
                            <div class="radio-content">
                                <strong><?= htmlspecialchars($TEXTOS['publico'] ?? 'Público') ?></strong>
                                <small><?= htmlspecialchars($TEXTOS['publico_info'] ?? 'Todos pueden ver tu perfil y obras') ?></small>
                            </div>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="privacidad" value="privado"
                                   <?php echo (isset($usuario['privacidad']) && $usuario['privacidad'] == 'privado') ? 'checked' : ''; ?>>
                            <span class="radio-custom"></span>
                            <div class="radio-content">
                                <strong><?= htmlspecialchars($TEXTOS['privado'] ?? 'Privado') ?></strong>
                                <small><?= htmlspecialchars($TEXTOS['privado_info'] ?? 'Solo tus seguidores pueden ver tu perfil') ?></small>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= htmlspecialchars($TEXTOS['guardar_cambios'] ?? 'Guardar cambios') ?>
                    </button>
                    <a href="<?php echo BASE_URL; ?>/usuario/perfil.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> <?= htmlspecialchars($TEXTOS['cancelar'] ?? 'Cancelar') ?>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// Banner color preview
const bannerColor = document.getElementById('banner_color');
const bannerPreviewBox = document.getElementById('banner-preview-box');
if (bannerColor) {
    bannerColor.addEventListener('input', function() {
        if (!document.getElementById('banner_img').files.length && !bannerPreviewBox.dataset.img) {
            bannerPreviewBox.style.background = this.value;
            bannerPreviewBox.style.backgroundImage = '';
        }
    });
}
const bannerImgInput = document.getElementById('banner_img');
if (bannerImgInput) {
    bannerImgInput.addEventListener('change', function(e){
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                bannerPreviewBox.style.background = '';
                bannerPreviewBox.style.backgroundImage = "url('" + ev.target.result + "')";
            }
            reader.readAsDataURL(this.files[0]);
            let removeBtn = document.getElementById('removeBannerImg');
            if(removeBtn) removeBtn.style.display = 'none';
            document.getElementById('eliminar_banner_img').value = "0";
        }
    });
}
const removeBannerBtn = document.getElementById('removeBannerImg');
if(removeBannerBtn){
    removeBannerBtn.addEventListener('click', function(){
        bannerPreviewBox.style.background = bannerColor.value;
        bannerPreviewBox.style.backgroundImage = '';
        document.getElementById('eliminar_banner_img').value = "1";
        this.style.display = "none";
    });
}
</script>
<?php include_once(__DIR__ . '/../includes/footer.php'); ?>