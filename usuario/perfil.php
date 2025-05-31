<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/funciones.php');
require_once(__DIR__ . '/../includes/conexion.php');

// Verificar que el usuario esté logueado
verificarLogin();

// Obtener ID y nombre del usuario actual
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario'];

// Consulta para obtener datos del usuario
$sql_usuario = "SELECT u.*, 
                (SELECT COUNT(*) FROM libros WHERE autor = ?) as total_libros,
                (SELECT COUNT(*) FROM comics WHERE autor = ?) as total_comics,
                (SELECT COUNT(*) FROM seguidores WHERE seguido_id = u.id) as total_seguidores,
                (SELECT COUNT(*) FROM seguidores WHERE seguidor_id = u.id) as total_seguidos,
                (SELECT COUNT(*) FROM favoritos WHERE usuario_id = u.id) as total_favoritos
                FROM usuarios u 
                WHERE u.id = ?";

$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("ssi", $usuario_nombre, $usuario_nombre, $usuario_id);
$stmt_usuario->execute();
$usuario = $stmt_usuario->get_result()->fetch_assoc();
$stmt_usuario->close();

if (!$usuario) {
    echo '<div class="alerta alerta-error">Usuario no encontrado.</div>';
    include_once(__DIR__ . '/../includes/footer.php');
    exit();
}

// Obtener libros del usuario
$sql_libros = "SELECT id, titulo, portada, descripcion, categoria_id, fecha_subida 
               FROM libros 
               WHERE autor = ? 
               ORDER BY fecha_subida DESC 
               LIMIT 3";
$stmt_libros = $conexion->prepare($sql_libros);
$stmt_libros->bind_param("s", $usuario_nombre);
$stmt_libros->execute();
$libros = $stmt_libros->get_result();

// Obtener cómics del usuario
$sql_comics = "SELECT id, titulo, portada, descripcion, categoria_id, fecha_subida 
               FROM comics 
               WHERE autor = ? 
               ORDER BY fecha_subida DESC 
               LIMIT 3";
$stmt_comics = $conexion->prepare($sql_comics);
$stmt_comics->bind_param("s", $usuario_nombre);
$stmt_comics->execute();
$comics = $stmt_comics->get_result();

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

$avatar = !empty($usuario['avatar']) ? $usuario['avatar'] : BASE_URL . '/public/img/avatar_default.png';

include_once(__DIR__ . '/../includes/header.php');
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/usuario.css">

<div class="perfil-container">
    <?php mostrarMensaje(); ?>
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
                    <span class="numero"><?php echo ($usuario['total_libros'] + $usuario['total_comics']); ?></span>
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
    <div class="perfil-contenido">
        <div class="perfil-columna-principal">
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Mis obras recientes</h2>
                    <a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php" class="ver-todo">Ver todas</a>
                </div>
                <?php if ($libros->num_rows > 0 || $comics->num_rows > 0): ?>
                    <div class="obras-grid">
                        <?php while ($libro = $libros->fetch_assoc()): ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php if (!empty($libro['portada'])): ?>
                                        <img src="<?php echo BASE_URL . htmlspecialchars($libro['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="portada-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="obra-info">
                                    <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                                    <span class="obra-categoria">Libro</span>
                                    <div class="obra-stats">
                                        <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($libro['fecha_subida'])); ?></span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $libro['id']; ?>&tipo=libro" class="btn btn-sm">Ver obra</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <?php while ($comic = $comics->fetch_assoc()): ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php if (!empty($comic['portada'])): ?>
                                        <img src="<?php echo BASE_URL . htmlspecialchars($comic['portada']); ?>" alt="Portada de <?php echo htmlspecialchars($comic['titulo']); ?>">
                                    <?php else: ?>
                                        <div class="portada-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="obra-info">
                                    <h3><?php echo htmlspecialchars($comic['titulo']); ?></h3>
                                    <span class="obra-categoria">Cómic</span>
                                    <div class="obra-stats">
                                        <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($comic['fecha_subida'])); ?></span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $comic['id']; ?>&tipo=comic" class="btn btn-sm">Ver obra</a>
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
<?php include_once(__DIR__ . '/../includes/footer.php'); ?>