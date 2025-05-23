<?php
// Incluir archivos necesarios
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/conexion.php');
require_once(__DIR__ . '/../includes/funciones.php');

// Obtener ID del usuario a mostrar
$perfil_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($perfil_id <= 0) {
    echo '<div class="alerta alerta-error">Usuario no encontrado.</div>';
    include_once(__DIR__ . '/../includes/footer.php');
    exit();
}

// Verificar si el usuario está viendo su propio perfil
$es_perfil_propio = false;
if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $perfil_id) {
    // Redirigir a la página de perfil personal
    header("Location: " . BASE_URL . "/usuario/perfil.php");
    exit();
}

// Consulta para obtener datos del usuario
$sql_usuario = "SELECT u.*, 
                (SELECT COUNT(*) FROM obras WHERE autor_id = u.id) as total_obras,
                (SELECT COUNT(*) FROM seguidores WHERE seguido_id = u.id) as total_seguidores,
                (SELECT COUNT(*) FROM seguidores WHERE seguidor_id = u.id) as total_seguidos,
                (SELECT COUNT(*) FROM favoritos WHERE usuario_id = u.id) as total_favoritos
                FROM usuarios u 
                WHERE u.id = ?";

$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $perfil_id);
$stmt_usuario->execute();
$usuario = $stmt_usuario->get_result()->fetch_assoc();

// Si no existe el usuario, mostrar error
if (!$usuario) {
    echo '<div class="alerta alerta-error">Usuario no encontrado.</div>';
    include_once(__DIR__ . '/../includes/footer.php');
    exit();
}

// Verificar si el perfil es privado y el usuario no está logueado
if ($usuario['privacidad'] == 'privado' && (!isset($_SESSION['usuario_id']) || !verificarSeguimiento($conexion, $_SESSION['usuario_id'], $perfil_id))) {
    echo '<div class="alerta alerta-info">Este perfil es privado. Solo los seguidores pueden ver su contenido.</div>';
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
$stmt_obras->bind_param("i", $perfil_id);
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
$stmt_seguidores->bind_param("i", $perfil_id);
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
$stmt_seguidos->bind_param("i", $perfil_id);
$stmt_seguidos->execute();
$seguidos = $stmt_seguidos->get_result();

// Verificar si el usuario actual sigue a este perfil
$siguiendo = false;
if (isset($_SESSION['usuario_id'])) {
    $siguiendo = verificarSeguimiento($conexion, $_SESSION['usuario_id'], $perfil_id);
}

// Calcular edad si hay fecha de nacimiento
$edad = null;
if (!empty($usuario['fecha_nacimiento'])) {
    $fecha_nacimiento = new DateTime($usuario['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nacimiento)->y;
}

// Avatar por defecto si no tiene
$avatar = !empty($usuario['avatar']) ? $usuario['avatar'] : BASE_URL . '/img/default-avatar.png';
?>

<div class="perfil-container">
    <!-- Cabecera del perfil -->
    <div class="perfil-header">
        <div class="perfil-cover"></div>
        <div class="perfil-avatar-container">
            <div class="perfil-avatar">
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar de <?php echo htmlspecialchars($usuario['usuario']); ?>">
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
                    <a href="<?php echo BASE_URL; ?>/public/seguidores.php?id=<?php echo $perfil_id; ?>">
                        <span class="numero"><?php echo $usuario['total_seguidores']; ?></span>
                        <span class="etiqueta">Seguidores</span>
                    </a>
                </div>
                <div class="estadistica">
                    <a href="<?php echo BASE_URL; ?>/public/seguidos.php?id=<?php echo $perfil_id; ?>">
                        <span class="numero"><?php echo $usuario['total_seguidos']; ?></span>
                        <span class="etiqueta">Siguiendo</span>
                    </a>
                </div>
            </div>
            
            <?php if (!empty($usuario['biografia'])): ?>
                <div class="perfil-biografia">
                    <p><?php echo nl2br(htmlspecialchars($usuario['biografia'])); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != $perfil_id): ?>
                <div class="perfil-acciones">
                    <?php if ($siguiendo): ?>
                        <form action="<?php echo BASE_URL; ?>/controllers/dejar-seguir.php" method="post">
                            <input type="hidden" name="usuario_id" value="<?php echo $perfil_id; ?>">
                            <button type="submit" class="btn btn-outline">
                                <i class="fas fa-user-minus"></i> Dejar de seguir
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="<?php echo BASE_URL; ?>/controllers/seguir-usuario.php" method="post">
                            <input type="hidden" name="usuario_id" value="<?php echo $perfil_id; ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Seguir
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Contenido principal -->
    <div class="perfil-contenido">
        <div class="perfil-columna-principal">
            <!-- Obras del usuario -->
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Obras publicadas</h2>
                </div>
                
                <?php if ($obras->num_rows > 0): ?>
                    <div class="obras-grid">
                        <?php while ($obra = $obras->fetch_assoc()): ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php if (!empty($obra['portada'])): ?>
                                        <img src="<?php echo htmlspecialchars($obra['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($obra['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="portada-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="obra-info">
                                    <h3><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                                    <span class="obra-categoria"><?php echo htmlspecialchars($obra['categoria_nombre']); ?></span>
                                    <div class="obra-stats">
                                        <span><i class="fas fa-eye"></i> <?php echo $obra['visitas']; ?></span>
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
                        <p>Este usuario aún no ha publicado ninguna obra</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="perfil-columna-lateral">
            <!-- Seguidores recientes -->
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Seguidores</h2>
                    <a href="<?php echo BASE_URL; ?>/public/seguidores.php?id=<?php echo $perfil_id; ?>" class="ver-todo">Ver todos</a>
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
                        <p>Este usuario aún no tiene seguidores</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Siguiendo a -->
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Siguiendo a</h2>
                    <a href="<?php echo BASE_URL; ?>/public/seguidos.php?id=<?php echo $perfil_id; ?>" class="ver-todo">Ver todos</a>
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
                        <p>Este usuario aún no sigue a nadie</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
}
</style>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
