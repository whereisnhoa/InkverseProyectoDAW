<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/funciones.php');
require_once(__DIR__ . '/../includes/conexion.php');

// Obtener ID del perfil a mostrar
$perfil_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($perfil_id <= 0) {
    echo '<div class="alerta alerta-error">Usuario no encontrado.</div>';
    include_once(__DIR__ . '/../includes/footer.php');
    exit();
}

// Si el usuario es el propio, redirigir a /usuario/perfil.php
if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $perfil_id) {
    header("Location: " . BASE_URL . "/usuario/perfil.php");
    exit();
}

// Obtener datos del usuario público
$sql_usuario = "SELECT u.*, 
                (SELECT COUNT(*) FROM obras WHERE autor_id = u.id) as total_obras,
                (SELECT COUNT(*) FROM seguidores WHERE seguido_id = u.id) as total_seguidores,
                (SELECT COUNT(*) FROM seguidores WHERE seguidor_id = u.id) as total_seguidos
                FROM usuarios u WHERE u.id = ?";
$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $perfil_id);
$stmt_usuario->execute();
$usuario = $stmt_usuario->get_result()->fetch_assoc();

if (!$usuario) {
    echo '<div class="alerta alerta-error">Usuario no encontrado.</div>';
    include_once(__DIR__ . '/../includes/footer.php');
    exit();
}

// Verificar privacidad: si es privado y no sigues, solo deja ver info mínima
$es_privado = (isset($usuario['privacidad']) && $usuario['privacidad'] == 'privado');
$puede_ver = true;
if ($es_privado) {
    $puede_ver = false;
    if (isset($_SESSION['usuario_id'])) {
        $siguiendo = verificarSeguimiento($conexion, $_SESSION['usuario_id'], $perfil_id);
        if ($siguiendo) $puede_ver = true;
    }
}

// Calcular edad
$edad = null;
if (!empty($usuario['fecha_nacimiento'])) {
    $fecha_nacimiento = new DateTime($usuario['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nacimiento)->y;
}

// Avatar y banner
$avatar = !empty($usuario['avatar']) ? $usuario['avatar'] : BASE_URL . '/public/img/avatar_default.png';
$banner = !empty($usuario['banner']) ? $usuario['banner'] : BASE_URL . '/public/img/banner_default.jpg';

include_once(__DIR__ . '/../includes/header.php');
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/usuario.css">

<div class="perfil-container">
    <!-- Cabecera del perfil -->
    <div class="perfil-header">
        <div class="perfil-cover" style="background-image:url('<?php echo htmlspecialchars($banner); ?>');"></div>
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
            <!-- REDES SOCIALES -->
            <div class="perfil-redes">
                <?php if (!empty($usuario['X'])): ?>
                    <a href="<?php echo htmlspecialchars($usuario['X']); ?>" target="_blank" title="X">
                        <i class="fa-brands fa-x-twitter"></i>
                    </a>
                <?php endif; ?>
                <?php if (!empty($usuario['instagram'])): ?>
                    <a href="<?php echo htmlspecialchars($usuario['instagram']); ?>" target="_blank" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                <?php endif; ?>
                <?php if (!empty($usuario['facebook'])): ?>
                    <a href="<?php echo htmlspecialchars($usuario['facebook']); ?>" target="_blank" title="Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                <?php endif; ?>
                <?php if (!empty($usuario['sitio_web'])): ?>
                    <a href="<?php echo htmlspecialchars($usuario['sitio_web']); ?>" target="_blank" title="Sitio Web">
                        <i class="fas fa-globe"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php if (!empty($usuario['biografia'])): ?>
                <div class="perfil-biografia">
                    <p><?php echo nl2br(htmlspecialchars($usuario['biografia'])); ?></p>
                </div>
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
            <?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != $perfil_id): ?>
                <div class="perfil-acciones">
                    <?php
                        $siguiendo = false;
                        if (isset($_SESSION['usuario_id'])) {
                            $siguiendo = verificarSeguimiento($conexion, $_SESSION['usuario_id'], $perfil_id);
                        }
                    ?>
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
    <?php if (!$puede_ver): ?>
        <div class="alerta alerta-info" style="margin:2em auto;">
            Este perfil es privado. Solo los seguidores pueden ver su contenido.
        </div>
    <?php else: ?>
    <!-- Contenido principal -->
    <div class="perfil-contenido">
        <div class="perfil-columna-principal">
            <!-- Obras del usuario -->
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Obras publicadas</h2>
                </div>
                <?php
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
                ?>
                <?php if ($obras->num_rows > 0): ?>
                    <div class="obras-grid">
                        <?php while ($obra = $obras->fetch_assoc()): ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php if (!empty($obra['portada'])): ?>
                                        <img src="<?php echo htmlspecialchars($obra['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($obra['titulo']); ?>" loading="lazy">
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
                <?php
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
                ?>
                <?php if ($seguidores->num_rows > 0): ?>
                    <div class="usuarios-grid">
                        <?php while ($seguidor = $seguidores->fetch_assoc()): ?>
                            <a href="<?php echo BASE_URL; ?>/public/perfil.php?id=<?php echo $seguidor['id']; ?>" class="usuario-item" title="<?php echo htmlspecialchars($seguidor['usuario']); ?>">
                                <img src="<?php echo !empty($seguidor['avatar']) ? htmlspecialchars($seguidor['avatar']) : BASE_URL . '/img/avatar_default.png'; ?>" alt="Avatar de <?php echo htmlspecialchars($seguidor['usuario']); ?>">
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
                <?php
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
                ?>
                <?php if ($seguidos->num_rows > 0): ?>
                    <div class="usuarios-grid">
                        <?php while ($seguido = $seguidos->fetch_assoc()): ?>
                            <a href="<?php echo BASE_URL; ?>/public/perfil.php?id=<?php echo $seguido['id']; ?>" class="usuario-item" title="<?php echo htmlspecialchars($seguido['usuario']); ?>">
                                <img src="<?php echo !empty($seguido['avatar']) ? htmlspecialchars($seguido['avatar']) : BASE_URL . '/img/avatar_default.png'; ?>" alt="Avatar de <?php echo htmlspecialchars($seguido['usuario']); ?>">
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
    <?php endif; ?>
</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>