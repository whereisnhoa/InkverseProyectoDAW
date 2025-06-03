<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/idioma.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0);
if (!$usuario_id) {
    header("Location: ../public/login.php"); exit;
}

// Obtener datos usuario
$sql_user = "SELECT id, usuario, nombre, fecha_nacimiento, avatar, banner_color, banner_img, biografia, sitio_web, ubicacion, X, instagram, facebook FROM usuarios WHERE id = ?";
$stmt_user = $conexion->prepare($sql_user);
$stmt_user->bind_param("i", $usuario_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$usuario = $result_user->fetch_assoc();
$stmt_user->close();

if (!$usuario) {
    echo "<p>{$TEXTOS['usuario_no_encontrado']}</p>";
    exit;
}

// Rutas avatar y banner
$user_avatar = !empty($usuario['avatar']) ? "../uploads/avatars/" . htmlspecialchars($usuario['avatar']) : "../public/img/avatar_default.png";
$user_banner_img = !empty($usuario['banner_img']) ? "../uploads/banners/" . htmlspecialchars($usuario['banner_img']) : "";
$user_banner_color = $usuario['banner_color'] ?: "#4361ee";
$banner_style = $user_banner_img
    ? "background: url('$user_banner_img') center/cover no-repeat;"
    : "background: linear-gradient(100deg, $user_banner_color 60%, #4cc9f0 100%);";

// ¿Es mi perfil?
$es_mi_perfil = (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $usuario['id']);

// Edad
$edad = $usuario['fecha_nacimiento'] ? (new DateTime())->diff(new DateTime($usuario['fecha_nacimiento']))->y : '-';

// Estadísticas
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
<html lang="<?= htmlspecialchars($TEXTOS['lang'] ?? 'es') ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($usuario['usuario']) ?> - <?= htmlspecialchars($TEXTOS['perfil'] ?? 'Perfil') ?> | InkVerse</title>
    <link rel="stylesheet" href="../css/usuario.css">
    <link rel="stylesheet" href="../public/fontawesome/css/all.min.css">
    <style>
    body { background: #f8faff; }
    .perfil-container {
        max-width: 1400px;
        margin: 40px auto 0 auto;
        background: #fff;
        border-radius: 40px;
        box-shadow: 0 10px 38px #4361ee19;
        overflow: hidden;
        padding-bottom: 32px;
        min-height: 350px;
        position: relative;
    }
    .perfil-header {
        position: relative;
        width: 100%;
        min-height: 220px;
        border-top-left-radius: 40px;
        border-top-right-radius: 40px;
        overflow: hidden;
        background: transparent;
    }
    .perfil-cover {
        width: 100%;
        height: 210px;
        <?= $banner_style ?>
        border-top-left-radius: 40px;
        border-top-right-radius: 40px;
        transition: background 0.3s;
        box-shadow: 0 2px 18px #4361ee18;
        position: relative;
        z-index: 1;
    }
    .perfil-avatar-outer {
        width: 100%;
        display: flex;
        justify-content: center;
        position: absolute;
        left: 0;
        top: 140px;
        z-index: 5;
        pointer-events: none;
    }
    .perfil-avatar {
        width: 132px; height: 132px;
        border-radius: 50%;
        background: #fff;
        border: 6px solid #fff;
        box-shadow: 0 4px 18px #4361ee18;
        overflow: hidden;
        display: flex; align-items: center; justify-content: center;
        z-index: 10;
        pointer-events: auto;
    }
    .perfil-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        display: block;
        box-shadow: 0 1px 8px #22316c17;
    }
    .perfil-header-info {
        width: 100%;
        margin-top: 84px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        text-align: center;
        padding-bottom: 30px;
    }
    .perfil-nombre h1 {
        font-size: 2.8rem;
        font-weight: 900;
        margin: 0;
        color: #4361ee;
        letter-spacing: -1px;
        text-align: center;
    }
    .perfil-nombre-completo {
        font-size: 1.5rem;
        color: #a4a4a4;
        font-weight: 500;
        margin-top: 12px;
        margin-bottom: 6px;
        text-align: center;
    }
    .perfil-estadisticas-row {
        display: flex;
        gap: 32px;
        margin-top: 22px;
        margin-bottom: 0px;
        justify-content: center;
    }
    .estadistica {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .estadistica .numero {
        color: #4361ee;
        font-size: 1.37rem;
        font-weight: 800;
    }
    .estadistica .etiqueta {
        color: #6a7bb5;
        font-size: 1.05rem;
        font-weight: 600;
    }
    .perfil-bio {
        margin: 32px auto 0 auto;
        color: #22316c;
        font-size: 1.13rem;
        background: #f8faff;
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 1px 7px #4361ee07;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        max-width: 650px;
    }
    .perfil-bio i { font-size: 1.2em; margin-top: 2px;}
    .perfil-links-extra {
        margin: 1.5em auto 1em auto;
        display: flex;
        gap: 22px;
        flex-wrap: wrap;
        font-size: 1.13rem;
        align-items: center;
        justify-content: center;
        max-width: 800px;
    }
    .perfil-links-extra a, .perfil-links-extra span {
        color: #4361ee;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .perfil-links-extra a:hover {
        text-decoration: underline;
        color: #2744c5;
    }
    .perfil-contenido {
        display: flex;
        gap: 30px;
        padding: 0 44px 0 44px;
        margin-top: 30px;
    }
    .perfil-columna-principal { flex: 2.2; }
    .perfil-columna-lateral { flex: 1; min-width: 230px; }
    .perfil-seccion {
        background: #f8faff;
        border-radius: 16px;
        padding: 22px 20px 21px 20px;
        margin-bottom: 23px;
        box-shadow: 0 1px 7px #4361ee08;
    }
    .seccion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 13px;
    }
    .seccion-header h2 {
        color: #4361ee;
        font-size: 1.25rem;
        font-weight: 800;
        margin: 0;
    }
    .ver-todo {
        color: #4cc9f0;
        font-weight: 700;
        text-decoration: none;
        font-size: 1.1rem;
        transition: color 0.13s;
    }
    .ver-todo:hover { color: #4361ee; text-decoration: underline; }
    .obras-grid {
        display: flex;
        gap: 17px;
        flex-wrap: wrap;
    }
    .obra-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 7px #4361ee10;
        width: 135px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: box-shadow 0.15s, transform 0.12s;
    }
    .obra-card:hover {
        box-shadow: 0 4px 18px #4cc9f014;
        transform: translateY(-3px) scale(1.035);
    }
    .obra-portada img, .obra-card .obra-portada img {
        width: 86px;
        height: 120px;
        object-fit: cover;
        border-radius: 7px;
        background: #e7e9fb;
        box-shadow: 0 1px 5px #22316c12;
        margin-bottom: 7px;
    }
    .obra-info {
        text-align: center;
        width: 100%;
    }
    .obra-info h3 {
        font-size: 1.06rem;
        font-weight: 700;
        margin-bottom: 3px;
        color: #22316c;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
    }
    .obra-categoria {
        font-size: 0.98rem;
        color: #6a7bb5;
        margin-bottom: 2px;
    }
    .badge {
        background: #4361ee;
        color: #fff;
        border-radius: 5px;
        padding: 2px 7px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-top: 4px;
        display: inline-block;
    }
    .no-resultados {
        text-align: center;
        color: #6a7bb5;
        margin: 18px 0;
        font-size: 1.09rem;
    }
    .usuarios-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 11px;
        margin-top: 5px;
    }
    .usuario-item {
        display: flex;
        align-items: center;
        gap: 7px;
        background: #fff;
        border-radius: 7px;
        padding: 7px 11px;
        color: #22316c;
        font-weight: 600;
        text-decoration: none;
        font-size: 1rem;
        box-shadow: 0 1px 5px #22316c12;
        transition: background 0.14s;
    }
    .usuario-item img {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        background: #f8faff;
        border: 2px solid #e7e9fb;
    }
    .usuario-item:hover {
        background: #e7f0fe;
        color: #4361ee;
    }
    .enlaces-rapidos {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 8px;
    }
    .enlace-rapido {
        display: flex;
        align-items: center;
        gap: 7px;
        background: #f8faff;
        border-radius: 7px;
        padding: 8px 14px;
        color: #4361ee;
        font-weight: 700;
        text-decoration: none;
        font-size: 1.04rem;
        box-shadow: 0 1px 5px #4361ee10;
        transition: background 0.14s, color 0.14s;
    }
    .enlace-rapido:hover {
        background: #4361ee;
        color: #fff;
    }
    @media (max-width:1000px) {
        .perfil-container { border-radius: 0; }
        .perfil-header { border-radius: 0; }
        .perfil-cover { border-radius: 0; }
        .perfil-contenido { flex-direction: column; gap: 25px; padding: 0 13px 0 13px;}
    }
    @media (max-width:600px) {
        .perfil-avatar { width:90px; height:90px; }
        .perfil-avatar-outer { top: 95px; }
        .perfil-header-info { margin-top: 55px; }
        .perfil-nombre h1 { font-size: 1.6rem;}
        .perfil-nombre-completo { font-size: 1.01rem;}
        .perfil-container { min-height: 220px; }
        .perfil-cover { height: 100px; }
    }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div style="height: 38px;"></div>

<div class="perfil-container">
    <div class="perfil-header">
        <div class="perfil-cover" style="<?= $banner_style ?>"></div>
        <div class="perfil-avatar-outer">
            <div class="perfil-avatar">
                <img src="<?= $user_avatar ?>" alt="<?= htmlspecialchars($usuario['usuario']) ?>">
            </div>
        </div>
    </div>
    <div class="perfil-header-info">
        <div class="perfil-nombre">
            <h1><?= htmlspecialchars($usuario['usuario']) ?></h1>
            <div class="perfil-nombre-completo">
                <?= htmlspecialchars($usuario['nombre']) ?>
                <?php if ($edad !== '-'): ?>
                    &middot; <?= $edad ?> <?= $TEXTOS['anios'] ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="perfil-estadisticas-row">
            <div class="estadistica">
                <span class="numero"><?= $stats['obras'] ?></span>
                <span class="etiqueta"><?= $TEXTOS['obras'] ?></span>
            </div>
            <div class="estadistica">
                <span class="numero"><?= $stats['seguidores'] ?></span>
                <span class="etiqueta"><?= $TEXTOS['seguidores'] ?></span>
            </div>
            <div class="estadistica">
                <span class="numero"><?= $stats['siguiendo'] ?></span>
                <span class="etiqueta"><?= $TEXTOS['siguiendo'] ?></span>
            </div>
            <div class="estadistica">
                <span class="numero"><?= $stats['favoritos'] ?></span>
                <span class="etiqueta"><?= $TEXTOS['favoritos'] ?></span>
            </div>
        </div>
        <?php if (!empty($usuario['biografia'])): ?>
            <div class="perfil-bio">
                <i class="fas fa-quote-left" style="color:#4cc9f0"></i>
                <?= nl2br(htmlspecialchars($usuario['biografia'])) ?>
            </div>
        <?php endif; ?>

        <div class="perfil-links-extra">
            <?php if (!empty($usuario['sitio_web'])): ?>
                <a href="<?= htmlspecialchars($usuario['sitio_web']) ?>" target="_blank"><i class="fas fa-link"></i><?= $TEXTOS['web'] ?></a>
            <?php endif; ?>
            <?php if (!empty($usuario['ubicacion'])): ?>
                <span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($usuario['ubicacion']) ?></span>
            <?php endif; ?>
            <?php if (!empty($usuario['X'])): ?>
                <a href="<?= htmlspecialchars($usuario['X']) ?>" target="_blank"><i class="fab fa-x-twitter"></i>X</a>
            <?php endif; ?>
            <?php if (!empty($usuario['instagram'])): ?>
                <a href="<?= htmlspecialchars($usuario['instagram']) ?>" target="_blank"><i class="fab fa-instagram"></i>Instagram</a>
            <?php endif; ?>
            <?php if (!empty($usuario['facebook'])): ?>
                <a href="<?= htmlspecialchars($usuario['facebook']) ?>" target="_blank"><i class="fab fa-facebook"></i>Facebook</a>
            <?php endif; ?>
        </div>
    </div>
    <!-- Sección principal y lateral -->
    <div class="perfil-contenido">
        <div class="perfil-columna-principal">
            <div class="perfil-seccion">
                <div class="seccion-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <h2><?= $TEXTOS['mis_obras_recientes'] ?></h2>
                    <a class="ver-todo" href="mis_obras.php?id=<?= $usuario['id'] ?>"><?= $TEXTOS['ver_todas'] ?></a>
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
                                    <span class="badge"><?= $obra['tipo'] == 'libro' ? $TEXTOS['libro'] : $TEXTOS['comic'] ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-resultados">
                            <i class="fas fa-book-open"></i>
                            <p><?= $TEXTOS['no_obras_publicadas'] ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="perfil-columna-lateral">
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2><?= $TEXTOS['seguidores_recientes'] ?></h2>
                    <a class="ver-todo" href="seguidores.php?id=<?= $usuario['id'] ?>"><?= $TEXTOS['ver_todos'] ?></a>
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
                            <p><?= $TEXTOS['no_tienes_seguidores'] ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2><?= $TEXTOS['seguidos_recientes'] ?></h2>
                    <a class="ver-todo" href="seguidos.php?id=<?= $usuario['id'] ?>"><?= $TEXTOS['ver_todos'] ?></a>
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
                            <p><?= $TEXTOS['no_sigues_nadie'] ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="perfil-seccion">
                <div class="seccion-header">
                    <h2><?= $TEXTOS['acciones_rapidas'] ?></h2>
                </div>
                <div class="enlaces-rapidos">
                    <?php if ($es_mi_perfil): ?>
                        <a class="enlace-rapido" href="editar_perfil.php"><i class="fas fa-user-edit"></i><?= $TEXTOS['editar_perfil'] ?></a>
                        <a class="enlace-rapido" href="nueva_obra.php"><i class="fas fa-plus"></i><?= $TEXTOS['nueva_obra'] ?></a>
                        <a class="enlace-rapido" href="favoritos.php"><i class="fas fa-heart"></i><?= $TEXTOS['ver_favoritos'] ?></a>
                        <a class="enlace-rapido" href="mis_obras.php?id=<?= $usuario['id'] ?>"><i class="fas fa-book"></i><?= $TEXTOS['mis_obras'] ?></a>
                        <a class="enlace-rapido" href="test.php"><i class="fas fa-question-circle"></i>Test</a>
                        <a class="enlace-rapido" href="biblioteca.php"><i class="fas fa-bookmark"></i><?= $TEXTOS['mi_biblioteca'] ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>