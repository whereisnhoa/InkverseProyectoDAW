<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0);
if (!$usuario_id) {
    header("Location: ../public/login.php"); exit;
}

// Obtener datos usuario
$sql_user = "SELECT id, usuario, nombre, fecha_nacimiento, avatar, banner_color, banner_img, biografia FROM usuarios WHERE id = ?";
$stmt_user = $conexion->prepare($sql_user);
$stmt_user->bind_param("i", $usuario_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$usuario = $result_user->fetch_assoc();
$stmt_user->close();

if (!$usuario) {
    echo "<p>Usuario no encontrado.</p>";
    exit;
}

// Avatar y banner
$user_avatar = $usuario['avatar'] ? "../uploads/avatars/" . htmlspecialchars($usuario['avatar']) : "../public/img/avatar_default.png";
$user_banner_color = $usuario['banner_color'] ?: "#4361ee";
$user_banner_img = $usuario['banner_img'] ? "../uploads/banners/" . htmlspecialchars($usuario['banner_img']) : "";
$banner_style = $user_banner_img
    ? "background: url('$user_banner_img') center/cover no-repeat;"
    : "background: linear-gradient(100deg, $user_banner_color 60%, #4cc9f0 100%);";

// Es el propio perfil
$es_mi_perfil = (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $usuario['id']);

// Edad
$edad = $usuario['fecha_nacimiento'] ? (new DateTime())->diff(new DateTime($usuario['fecha_nacimiento']))->y : '-';

// Estadísticas - cuenta libros + comics como "obras"
$sql_stats = "SELECT 
    ((SELECT COUNT(*) FROM libros WHERE usuario_id = ?) + (SELECT COUNT(*) FROM comics WHERE usuario_id = ?)) AS obras,
    (SELECT COUNT(*) FROM seguidores WHERE seguido_id = ?) AS seguidores,
    (SELECT COUNT(*) FROM seguidores WHERE usuario_id = ?) AS siguiendo,
    (SELECT COUNT(*) FROM favoritos WHERE usuario_id = ?) AS favoritos
";
$stmt_stats = $conexion->prepare($sql_stats);
$stmt_stats->bind_param("iiiii", $usuario_id, $usuario_id, $usuario_id, $usuario_id, $usuario_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

// Últimas obras (máx 4, libros y comics juntos)
$sql_ultimas_obras = "
    (SELECT l.id, l.titulo, l.portada, c.nombre AS categoria, l.fecha_subida AS fecha, 'libro' AS tipo
        FROM libros l 
        LEFT JOIN categorias c ON l.categoria_id = c.id 
        WHERE l.usuario_id = ?)
    UNION ALL
    (SELECT co.id, co.titulo, co.portada, c.nombre AS categoria, co.fecha_subida AS fecha, 'comic' AS tipo
        FROM comics co 
        LEFT JOIN categorias c ON co.categoria_id = c.id 
        WHERE co.usuario_id = ?)
    ORDER BY fecha DESC
    LIMIT 4
";
$stmt_obras = $conexion->prepare($sql_ultimas_obras);
$stmt_obras->bind_param("ii", $usuario_id, $usuario_id);
$stmt_obras->execute();
$ultimas_obras = $stmt_obras->get_result();
$stmt_obras->close();

// Seguidores recientes (máx 6)
$sql_seguidores = "SELECT u.id, u.usuario, u.avatar 
                   FROM seguidores s 
                   JOIN usuarios u ON s.usuario_id = u.id
                   WHERE s.seguido_id = ?
                   ORDER BY s.fecha DESC LIMIT 6";
$stmt_seguidores = $conexion->prepare($sql_seguidores);
$stmt_seguidores->bind_param("i", $usuario_id);
$stmt_seguidores->execute();
$seguidores = $stmt_seguidores->get_result();
$stmt_seguidores->close();

// Seguidos recientes (máx 6)
$sql_seguidos = "SELECT u.id, u.usuario, u.avatar 
                   FROM seguidores s 
                   JOIN usuarios u ON s.seguido_id = u.id
                   WHERE s.usuario_id = ?
                   ORDER BY s.fecha DESC LIMIT 6";
$stmt_seguidos = $conexion->prepare($sql_seguidos);
$stmt_seguidos->bind_param("i", $usuario_id);
$stmt_seguidos->execute();
$seguidos = $stmt_seguidos->get_result();
$stmt_seguidos->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($usuario['usuario']) ?> - Perfil | InkVerse</title>
    <link rel="stylesheet" href="../css/usuario.css">
    <link rel="stylesheet" href="../public/fontawesome/css/all.min.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div style="height: 38px;"></div> <!-- Separación entre el header y el perfil -->

<div class="perfil-container">
    <div class="perfil-header">
        <div class="perfil-cover" style="<?= $banner_style ?>"></div>
        <div class="perfil-header-barra">
            <div class="perfil-avatar">
                <img src="<?= $usuario['avatar']
                    ? "../uploads/avatars/" . htmlspecialchars($usuario['avatar'])
                    : "../public/img/avatar_default.png" ?>"
                alt="Avatar de <?= htmlspecialchars($usuario['usuario']) ?>">
            </div>
            <div class="perfil-header-info">
                <div class="perfil-nombre">
                    <h1><?= htmlspecialchars($usuario['usuario']) ?></h1>
                    <div class="perfil-nombre-completo">
                        <?= htmlspecialchars($usuario['nombre'] ?? '') ?><?= $edad != '-' ? " · $edad años" : "" ?>
                    </div>
                </div>
                <div class="perfil-estadisticas-row">
                    <div class="estadistica">
                        <span class="numero"><?= $stats['obras'] ?></span>
                        <span class="etiqueta">Obras</span>
                    </div>
                    <div class="estadistica">
                        <span class="numero"><?= $stats['seguidores'] ?></span>
                        <span class="etiqueta">Seguidores</span>
                    </div>
                    <div class="estadistica">
                        <span class="numero"><?= $stats['siguiendo'] ?></span>
                        <span class="etiqueta">Siguiendo</span>
                    </div>
                    <div class="estadistica">
                        <span class="numero"><?= $stats['favoritos'] ?></span>
                        <span class="etiqueta">Favoritos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="perfil-contenido">
        <div class="perfil-columna-principal">
            <div class="perfil-seccion">
                <div class="seccion-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <h2>Mis obras recientes</h2>
                    <a class="ver-todo" href="mis_obras.php?id=<?= $usuario['id'] ?>">Ver todas</a>
                </div>
                <div class="obras-grid">
                    <?php if ($ultimas_obras->num_rows): ?>
                        <?php while ($obra = $ultimas_obras->fetch_assoc()): ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php if ($obra['portada']): ?>
                                        <img src="../uploads/portadas/<?= htmlspecialchars($obra['portada']) ?>" alt="Portada">
                                    <?php else: ?>
                                        <div class="portada-placeholder"><i class="fas fa-book"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="obra-info">
                                    <h3><?= htmlspecialchars($obra['titulo']) ?></h3>
                                    <div class="obra-categoria"><?= htmlspecialchars($obra['categoria']) ?></div>
                                    <span class="badge"><?= $obra['tipo'] == 'libro' ? 'Libro' : 'Cómic' ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-resultados">
                            <i class="fas fa-book-open"></i>
                            <p>No tienes obras publicadas todavía.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="perfil-columna-lateral">
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Seguidores recientes</h2>
                    <a class="ver-todo" href="seguidores.php?id=<?= $usuario['id'] ?>">Ver todos</a>
                </div>
                <div class="usuarios-grid">
                    <?php if ($seguidores->num_rows): ?>
                        <?php while ($seg = $seguidores->fetch_assoc()): ?>
                            <a class="usuario-item" href="perfil.php?id=<?= $seg['id'] ?>">
                                <img src="<?= $seg['avatar'] ? "../uploads/avatars/" . htmlspecialchars($seg['avatar']) : "../public/img/avatar_default.png" ?>" alt="Avatar">
                                <span><?= htmlspecialchars($seg['usuario']) ?></span>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-resultados">
                            <i class="fas fa-users"></i>
                            <p>Aún no tienes seguidores.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Seguidos recientes</h2>
                    <a class="ver-todo" href="seguidos.php?id=<?= $usuario['id'] ?>">Ver todos</a>
                </div>
                <div class="usuarios-grid">
                    <?php if ($seguidos->num_rows): ?>
                        <?php while ($seg = $seguidos->fetch_assoc()): ?>
                            <a class="usuario-item" href="perfil.php?id=<?= $seg['id'] ?>">
                                <img src="<?= $seg['avatar'] ? "../uploads/avatars/" . htmlspecialchars($seg['avatar']) : "../public/img/avatar_default.png" ?>" alt="Avatar">
                                <span><?= htmlspecialchars($seg['usuario']) ?></span>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-resultados">
                            <i class="fas fa-users"></i>
                            <p>Aún no sigues a nadie.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2>Acciones rápidas</h2>
                </div>
                <div class="enlaces-rapidos">
                    <?php if ($es_mi_perfil): ?>
                        <a class="enlace-rapido" href="editar_perfil.php"><i class="fas fa-user-edit"></i>Editar perfil</a>
                        <a class="enlace-rapido" href="nueva_obra.php"><i class="fas fa-plus"></i>Nueva obra</a>
                        <a class="enlace-rapido" href="favoritos.php"><i class="fas fa-heart"></i>Ver favoritos</a>
                        <a class="enlace-rapido" href="mis_obras.php?id=<?= $usuario['id'] ?>"><i class="fas fa-book"></i>Mis obras</a>
                        <a class="enlace-rapido" href="test.php"><i class="fas fa-question-circle"></i>Test</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>