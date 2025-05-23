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

// Obtener ID del usuario actual
$usuario_id = $_SESSION['usuario_id'];

// Consulta para obtener datos del usuario
$sql_usuario = "SELECT u.*, 
                (SELECT COUNT(*) FROM obras WHERE autor_id = u.id) as total_obras,
                (SELECT COUNT(*) FROM seguidores WHERE seguido_id = u.id) as total_seguidores,
                (SELECT COUNT(*) FROM seguidores WHERE seguidor_id = u.id) as total_seguidos,
                (SELECT COUNT(*) FROM favoritos WHERE usuario_id = u.id) as total_favoritos
                FROM usuarios u 
                WHERE u.id = ?";

$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$usuario = $stmt_usuario->get_result()->fetch_assoc();

// Si no existe el usuario, mostrar error
if (!$usuario) {
    echo '<div class="alerta alerta-error">Usuario no encontrado.</div>';
    include_once(__DIR__ . '/../includes/footer.php');
    exit();
}

// Obtener obras del usuario
$sql_obras = "SELECT o.*, c.nombre as categoria_nombre 
              FROM obras o 
              LEFT JOIN categorias c ON o.categoria_id = c.id 
              WHERE o.autor_id = ? 
              ORDER BY o.fecha_subida DESC 
              LIMIT 5";
$stmt_obras = $conexion->prepare($sql_obras);
$stmt_obras->bind_param("i", $usuario_id);
$stmt_obras->execute();
$obras = $stmt_obras->get_result();

// Obtener seguidores recientes
$sql_seguidores = "SELECT u.id, u.usuario, u.avatar 
                   FROM seguidores s 
                   JOIN usuarios u ON s.seguidor_id = u.id 
                   WHERE s.seguido_id = ? 
                   ORDER BY s.fecha_seguimiento DESC 
                   LIMIT 6";
$stmt_seguidores = $conexion->prepare($sql_seguidores);
$stmt_seguidores->bind_param("i", $usuario_id);
$stmt_seguidores->execute();
$seguidores = $stmt_seguidores->get_result();

// Obtener seguidos recientes
$sql_seguidos = "SELECT u.id, u.usuario, u.avatar 
                 FROM seguidores s 
                 JOIN usuarios u ON s.seguido_id = u.id 
                 WHERE s.seguidor_id = ? 
                 ORDER BY s.fecha_seguimiento DESC 
                 LIMIT 6";
$stmt_seguidos = $conexion->prepare($sql_seguidos);
$stmt_seguidos->bind_param("i", $usuario_id);
$stmt_seguidos->execute();
$seguidos = $stmt_seguidos->get_result();

// Calcular edad si hay fecha de nacimiento
$edad = null;
if (!empty($usuario['fecha_nacimiento'])) {
    $fecha_nacimiento = new DateTime($usuario['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nacimiento)->y;
}

// Avatar: usar el del usuario o el por defecto
$avatar = !empty($usuario['avatar']) ? $usuario['avatar'] : BASE_URL . '/img/default-avatar.png';

// Incluir header DESPUÉS de procesar los datos
include_once(__DIR__ . '/../includes/header.php');
?>

<div class="perfil-container">
    <?php mostrarMensaje(); ?>
    
    <!-- Cabecera del perfil -->
    <div class="perfil-header">
        <div class="perfil-cover"></div>
        <div class="perfil-avatar-container">
            <div class="perfil-avatar">
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar de <?php echo htmlspecialchars($usuario['usuario']); ?>">
                <button id="cambiar-avatar" class="btn-cambiar-avatar" title="Cambiar avatar">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
        </div>
        <div class="perfil-info">
            <h1><?php echo htmlspecialchars($usuario['usuario']); ?></h1>
            <?php if (!empty($usuario['nombre']) || $edad): ?>
                <p class="perfil-nombre-completo">
                    <?php echo !empty($usuario['nombre']) ? htmlspecialchars($usuario['nombre']) : ''; ?>
                    <?php echo $edad ? ' · ' . $edad . ' años' : ''; ?>
                </p>
            <?php endif; ?>
            
            <div class="perfil-estadisticas">
                <div class="estadistica">
                    <span class="numero"><?php echo $usuario['total_obras']; ?></span>
                    <span class="etiqueta">Obras</span>
                </div>
                <div class="estadistica">
                    <a href="<?php echo BASE_URL; ?>/usuario/seguidores.php">
                        <span class="numero"><?php echo $usuario['total_seguidores']; ?></span>
                        <span class="etiqueta">Seguidores</span>
                    </a>
                </div>
                <div class="estadistica">
                    <a href="<?php echo BASE_URL; ?>/usuario/seguidos.php">
                        <span class="numero"><?php echo $usuario['total_seguidos']; ?></span>
                        <span class="etiqueta">Siguiendo</span>
                    </a>
                </div>
                <div class="estadistica">
                    <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php">
                        <span class="numero"><?php echo $usuario['total_favoritos']; ?></span>
                        <span class="etiqueta">Favoritos</span>
                    </a>
                </div>
            </div>
            
            <?php if (!empty($usuario['biografia'])): ?>
                <div class="perfil-biografia">
                    <p><?php echo nl2br(htmlspecialchars($usuario['biografia'])); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="perfil-acciones">
                <a href="<?php echo BASE_URL; ?>/usuario/editar_perfil.php" class="btn btn-outline">
                    <i class="fas fa-edit"></i> Editar perfil
                </a>
                <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva obra
                </a>
            </div>
        </div>
    </div>
    
    <!-- Contenido principal -->
    <div class="perfil-contenido">
        <div class="perfil-columna-principal">
            <!-- Mis obras recientes -->
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Mis obras recientes</h2>
                    <a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php" class="ver-todo">Ver todas</a>
                </div>
                
                <?php if ($obras->num_rows > 0): ?>
                    <div class="obras-grid">
                        <?php while ($obra = $obras->fetch_assoc()): ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php if (!empty($obra['portada'])): ?>
                                        <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($obra['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="portada-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="obra-info">
                                    <h3><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                                    <?php if (!empty($obra['categoria_nombre'])): ?>
                                        <span class="obra-categoria"><?php echo htmlspecialchars($obra['categoria_nombre']); ?></span>
                                    <?php endif; ?>
                                    <div class="obra-stats">
                                        <span><i class="fas fa-eye"></i> <?php echo isset($obra['visitas']) ? $obra['visitas'] : '0'; ?></span>
                                        <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($obra['fecha_subida'])); ?></span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra['id']; ?>" class="btn btn-sm">Ver obra</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-resultados">
                        <i class="fas fa-book"></i>
                        <p>Aún no has publicado ninguna obra</p>
                        <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="btn btn-sm">Crear mi primera obra</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="perfil-columna-lateral">
            <!-- Seguidores recientes -->
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Seguidores recientes</h2>
                    <a href="<?php echo BASE_URL; ?>/usuario/seguidores.php" class="ver-todo">Ver todos</a>
                </div>
                
                <?php if ($seguidores->num_rows > 0): ?>
                    <div class="usuarios-grid">
                        <?php while ($seguidor = $seguidores->fetch_assoc()): ?>
                            <a href="<?php echo BASE_URL; ?>/public/perfil.php?id=<?php echo $seguidor['id']; ?>" class="usuario-item" title="<?php echo htmlspecialchars($seguidor['usuario']); ?>">
                                <img src="<?php echo !empty($seguidor['avatar']) ? htmlspecialchars($seguidor['avatar']) : BASE_URL . '/img/default-avatar.png'; ?>" alt="Avatar de <?php echo htmlspecialchars($seguidor['usuario']); ?>">
                                <span><?php echo htmlspecialchars($seguidor['usuario']); ?></span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-resultados">
                        <i class="fas fa-users"></i>
                        <p>Aún no tienes seguidores</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Siguiendo a -->
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Siguiendo a</h2>
                    <a href="<?php echo BASE_URL; ?>/usuario/seguidos.php" class="ver-todo">Ver todos</a>
                </div>
                
                <?php if ($seguidos->num_rows > 0): ?>
                    <div class="usuarios-grid">
                        <?php while ($seguido = $seguidos->fetch_assoc()): ?>
                            <a href="<?php echo BASE_URL; ?>/public/perfil.php?id=<?php echo $seguido['id']; ?>" class="usuario-item" title="<?php echo htmlspecialchars($seguido['usuario']); ?>">
                                <img src="<?php echo !empty($seguido['avatar']) ? htmlspecialchars($seguido['avatar']) : BASE_URL . '/img/default-avatar.png'; ?>" alt="Avatar de <?php echo htmlspecialchars($seguido['usuario']); ?>">
                                <span><?php echo htmlspecialchars($seguido['usuario']); ?></span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-resultados">
                        <i class="fas fa-user-plus"></i>
                        <p>Aún no sigues a nadie</p>
                        <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-sm">Explorar usuarios</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Enlaces rápidos -->
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Acciones rápidas</h2>
                </div>
                <div class="enlaces-rapidos">
                    <a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php" class="enlace-rapido">
                        <i class="fas fa-book"></i>
                        <span>Mis obras</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/usuario/favoritos.php" class="enlace-rapido">
                        <i class="fas fa-heart"></i>
                        <span>Favoritos</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php" class="enlace-rapido">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nueva obra</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/usuario/editar_perfil.php" class="enlace-rapido">
                        <i class="fas fa-user-edit"></i>
                        <span>Editar perfil</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="enlace-rapido">
                        <i class="fas fa-compass"></i>
                        <span>Explorar</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/logout.php" class="enlace-rapido">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar avatar -->
<div id="modal-avatar" class="modal">
    <div class="modal-contenido">
        <span class="cerrar-modal">&times;</span>
        <h2>Cambiar foto de perfil</h2>
        
        <form id="form-avatar" action="<?php echo BASE_URL; ?>/controllers/actualizar-avatar.php" method="post" enctype="multipart/form-data">
            <div class="preview-container">
                <div id="preview-avatar">
                    <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Vista previa">
                </div>
            </div>
            
            <div class="form-group">
                <label for="avatar" class="btn btn-outline btn-block">
                    <i class="fas fa-upload"></i> Seleccionar imagen
                </label>
                <input type="file" id="avatar" name="avatar" accept="image/*" style="display: none;">
                <small>Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB.</small>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-outline" id="cancelar-avatar">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Estilos específicos para el perfil -->
<style>
.perfil-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

.perfil-header {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}

.perfil-cover {
    height: 200px;
    background-color: #4361ee;
    background-image: linear-gradient(135deg, #4361ee, #3a0ca3);
}

.perfil-avatar-container {
    position: absolute;
    left: 30px;
    top: 130px;
}

.perfil-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid #fff;
    overflow: hidden;
    position: relative;
    background-color: #f0f0f0;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.perfil-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.btn-cambiar-avatar {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: #4361ee;
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    opacity: 0.8;
}

.btn-cambiar-avatar:hover {
    opacity: 1;
    transform: scale(1.1);
}

.perfil-info {
    padding: 20px 30px 30px 200px;
}

.perfil-info h1 {
    margin: 0;
    font-size: 28px;
    color: #333;
}

.perfil-nombre-completo {
    color: #666;
    margin: 5px 0 15px;
    font-size: 16px;
}

.perfil-estadisticas {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.estadistica {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.estadistica a {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: inherit;
}

.estadistica .numero {
    font-size: 20px;
    font-weight: bold;
    color: #333;
}

.estadistica .etiqueta {
    font-size: 14px;
    color: #666;
}

.perfil-biografia {
    margin: 15px 0;
    color: #444;
    line-height: 1.5;
}

.perfil-acciones {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.perfil-contenido {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.perfil-seccion {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
}

.seccion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.seccion-header h2 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.ver-todo {
    font-size: 14px;
    color: #4361ee;
    text-decoration: none;
}

.ver-todo:hover {
    text-decoration: underline;
}

.obras-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

.obra-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.obra-card:hover {
    transform: translateY(-5px);
}

.obra-portada {
    height: 120px;
    overflow: hidden;
}

.obra-portada img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.portada-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f0f0f0;
    color: #999;
    font-size: 24px;
}

.obra-info {
    padding: 12px;
}

.obra-info h3 {
    margin: 0 0 5px;
    font-size: 16px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.obra-categoria {
    display: inline-block;
    font-size: 12px;
    color: #666;
    background-color: #f0f0f0;
    padding: 2px 8px;
    border-radius: 12px;
    margin-bottom: 8px;
}

.obra-stats {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #777;
    margin-bottom: 10px;
}

.usuarios-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}

.usuario-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none;
    color: #333;
    padding: 10px;
    border-radius: 8px;
    transition: background-color 0.2s;
}

.usuario-item:hover {
    background-color: #f5f5f5;
}

.usuario-item img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 5px;
}

.usuario-item span {
    font-size: 12px;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.enlaces-rapidos {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.enlace-rapido {
    display: flex;
    align-items: center;
    padding: 12px;
    background-color: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.2s;
}

.enlace-rapido:hover {
    background-color: #e9ecef;
    transform: translateY(-2px);
}

.enlace-rapido i {
    margin-right: 10px;
    color: #4361ee;
    font-size: 18px;
}

.no-resultados {
    text-align: center;
    padding: 30px 0;
    color: #777;
}

.no-resultados i {
    font-size: 40px;
    margin-bottom: 10px;
    color: #ccc;
}

.no-resultados p {
    margin-bottom: 15px;
}

/* Botones */
.btn {
    display: inline-block;
    padding: 8px 16px;
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
}

.btn-outline {
    background-color: transparent;
    border: 1px solid #4361ee;
    color: #4361ee;
}

.btn-outline:hover {
    background-color: #4361ee;
    color: white;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.btn-block {
    width: 100%;
    text-align: center;
}

/* Modal para cambiar avatar */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-contenido {
    background-color: #fff;
    margin: 10% auto;
    padding: 25px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    position: relative;
}

.cerrar-modal {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 24px;
    cursor: pointer;
    color: #777;
}

.preview-container {
    display: flex;
    justify-content: center;
    margin: 20px 0;
}

#preview-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #eee;
}

#preview-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.alerta {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alerta-exito {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alerta-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alerta-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

/* Responsive */
@media (max-width: 768px) {
    .perfil-contenido {
        grid-template-columns: 1fr;
    }
    
    .perfil-info {
        padding: 100px 20px 20px 20px;
        text-align: center;
    }
    
    .perfil-avatar-container {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .perfil-estadisticas {
        justify-content: center;
    }
    
    .perfil-acciones {
        justify-content: center;
    }
    
    .obras-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .usuarios-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .enlaces-rapidos {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- JavaScript para el modal de avatar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modal-avatar');
    const btnCambiarAvatar = document.getElementById('cambiar-avatar');
    const cerrarModal = document.querySelector('.cerrar-modal');
    const cancelarAvatar = document.getElementById('cancelar-avatar');
    const inputAvatar = document.getElementById('avatar');
    const previewImg = document.querySelector('#preview-avatar img');
    
    // Abrir modal
    if (btnCambiarAvatar) {
        btnCambiarAvatar.addEventListener('click', function() {
            modal.style.display = 'block';
        });
    }
    
    // Cerrar modal
    function cerrarModalAvatar() {
        modal.style.display = 'none';
    }
    
    if (cerrarModal) {
        cerrarModal.addEventListener('click', cerrarModalAvatar);
    }
    
    if (cancelarAvatar) {
        cancelarAvatar.addEventListener('click', cerrarModalAvatar);
    }
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            cerrarModalAvatar();
        }
    });
    
    // Vista previa de la imagen
    if (inputAvatar) {
        inputAvatar.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (previewImg) {
                        previewImg.src = e.target.result;
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
