<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
require_once '../includes/funciones.php';
require_once '../includes/idioma.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$usuario_id) {
    echo '<div class="alerta alerta-error">' . ($TEXTOS['perfil_error_no_usuario'] ?? 'Usuario no encontrado.') . '</div>';
    include_once(__DIR__ . '/../includes/footer.php');
    exit;
}

// Si el usuario es el propio, redirigir a /usuario/perfil.php
if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $usuario_id) {
    header("Location: " . BASE_URL . "/usuario/perfil.php");
    exit();
}

// Obtener datos usuario
$sql_user = "SELECT id, usuario, nombre, fecha_nacimiento, avatar, banner_color, banner_img, biografia, sitio_web, ubicacion, X, instagram, facebook, privacidad FROM usuarios WHERE id = ?";
$stmt_user = $conexion->prepare($sql_user);
$stmt_user->bind_param("i", $usuario_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$usuario = $result_user->fetch_assoc();
$stmt_user->close();

if (!$usuario) {
    echo "<p>{$TEXTOS['usuario_no_encontrado']}</p>";
    include_once(__DIR__ . '/../includes/footer.php');
    exit;
}

// Rutas avatar y banner
$user_avatar = !empty($usuario['avatar']) ? "../uploads/avatars/" . htmlspecialchars($usuario['avatar']) : "../public/img/avatar_default.png";
$user_banner_img = !empty($usuario['banner_img']) ? "../uploads/banners/" . htmlspecialchars($usuario['banner_img']) : "";
$user_banner_color = $usuario['banner_color'] ?: "#4361ee";
$banner_style = $user_banner_img
    ? "background: url('$user_banner_img') center/cover no-repeat;"
    : "background: linear-gradient(135deg, $user_banner_color 70%, #4cc9f0 100%);";

// Edad
$edad = $usuario['fecha_nacimiento'] ? (new DateTime())->diff(new DateTime($usuario['fecha_nacimiento']))->y : '-';

// Estadísticas
$sql_stats = "SELECT 
    ((SELECT COUNT(*) FROM libros WHERE usuario_id = ?) + (SELECT COUNT(*) FROM comics WHERE usuario_id = ?)) AS obras,
    (SELECT COUNT(*) FROM seguidores WHERE seguido_id = ?) AS seguidores,
    (SELECT COUNT(*) FROM seguidores WHERE usuario_id = ?) AS siguiendo
";
$stmt_stats = $conexion->prepare($sql_stats);
$stmt_stats->bind_param("iiii", $usuario_id, $usuario_id, $usuario_id, $usuario_id);
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

// Privacidad: si es privado y no sigues, solo deja ver info mínima
$es_privado = (isset($usuario['privacidad']) && $usuario['privacidad'] == 'privado');
$puede_ver = true;
if ($es_privado) {
    $puede_ver = false;
    if (isset($_SESSION['usuario_id'])) {
        $sql_sigue = "SELECT 1 FROM seguidores WHERE usuario_id = ? AND seguido_id = ?";
        $stmt_sigue = $conexion->prepare($sql_sigue);
        $stmt_sigue->bind_param("ii", $_SESSION['usuario_id'], $usuario_id);
        $stmt_sigue->execute();
        $stmt_sigue->store_result();
        if ($stmt_sigue->num_rows > 0) $puede_ver = true;
        $stmt_sigue->close();
    }
}

include_once(__DIR__ . '/../includes/header.php');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($TEXTOS['lang'] ?? 'es') ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($usuario['usuario'] ?? '') ?> - <?= htmlspecialchars($TEXTOS['perfil'] ?? 'Perfil') ?> | InkVerse</title>
    <link rel="stylesheet" href="../public/fontawesome/css/all.min.css">
    <style>
    /* ... Pega aquí el mismo CSS que en usuario/perfil.php ... */
    body {
        background: linear-gradient(120deg, #ece9f7 0%, #f6f7fb 100%);
        font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        color: #2d2d50;
    }
    .perfil-container {
        max-width: 1280px;
        margin: 40px auto 0 auto;
        background: #fff;
        border-radius: 22px;
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
        border-top-left-radius: 22px;
        border-top-right-radius: 22px;
        overflow: hidden;
    }
    .perfil-cover {
        width: 100%;
        height: 210px;
        <?= $banner_style ?>
        border-top-left-radius: 22px;
        border-top-right-radius: 22px;
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
        width: 110px; height: 110px;
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
        margin-top: 70px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        text-align: center;
        padding-bottom: 30px;
    }
    .perfil-nombre h1 {
        font-size: 2.2rem;
        font-weight: 900;
        margin: 0;
        color: #4361ee;
        letter-spacing: -1px;
        text-align: center;
    }
    .perfil-nombre-completo {
        font-size: 1.15rem;
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
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 36px 36px;
      justify-items: center;
      padding: 10px 0 5px 0;
    }
    .obra-card {
      background: #fff;
      border-radius: 22px;
      box-shadow: 0 6px 32px #4361ee18, 0 2px 5px #4cc9f015;
      width: 230px;
      padding: 20px 20px 18px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      transition: transform 0.18s, box-shadow 0.18s;
      position: relative;
      cursor: pointer;
      border: 2px solid #f3f6ff;
    }
    .obra-card:hover {
      transform: translateY(-7px) scale(1.045);
      box-shadow: 0 14px 42px #4361ee33, 0 4px 12px #4cc9f021;
      border-color: #4cc9f0;
    }
    .obra-portada img, .obra-card .obra-portada img {
      width: 100%;
      height: 260px;
      object-fit: cover;
      border-radius: 13px;
      background: #e7e9fb;
      box-shadow: 0 3px 14px #22316c12;
      margin-bottom: 18px;
      transition: box-shadow 0.16s;
    }
    .obra-card:hover .obra-portada img {
      box-shadow: 0 8px 28px #4361ee19;
    }
    .obra-info {
      width: 100%;
      text-align: left;
    }
    .obra-info h3 {
      font-size: 1.12rem;
      font-weight: 900;
      color: #22316c;
      margin-bottom: 5px;
      margin-top: 0;
      line-height: 1.18;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .obra-categoria {
      font-size: 1rem;
      font-weight: 600;
      color: #4cc9f0;
      margin-bottom: 9px;
      letter-spacing: 0.02em;
    }
    .badge {
      background: #4361ee;
      color: #fff;
      border-radius: 7px;
      padding: 3px 12px;
      font-size: 1.01rem;
      font-weight: 700;
      display: inline-block;
      margin-top: 0px;
      margin-bottom: 2px;
      letter-spacing: 0.04em;
    }
    .portada-placeholder {
      width: 100%;
      height: 260px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
      color: #a0a8ff;
      background: linear-gradient(120deg,#e7e9fb 70%, #c8e0fc 100%);
      border-radius: 13px;
      margin-bottom: 18px;
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
    @media (max-width:1000px) {
        .perfil-container { border-radius: 0; }
        .perfil-header { border-radius: 0; }
        .perfil-cover { border-radius: 0; }
        .perfil-contenido { flex-direction: column; gap: 25px; padding: 0 13px 0 13px;}
    }
    @media (max-width:600px) {
        .perfil-avatar { width:90px; height:90px; }
        .perfil-avatar-outer { top: 95px; }
        .perfil-header-info { margin-top: 42px; }
        .perfil-nombre h1 { font-size: 1.2rem;}
        .perfil-nombre-completo { font-size: 1.01rem;}
        .perfil-container { min-height: 220px; }
        .perfil-cover { height: 100px; }
        .obra-card { width: 97vw; max-width: 320px;}
        .obras-grid { gap: 20px 10px;}
    }
    .btn {
        display: inline-block;
        background: #4361ee;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 5px;
        font-size: 0.95rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s;
    }
    .btn:hover {
        background: #3a56d4;
        transform: translateY(-2px);
    }
    .btn-outline {
        background: transparent;
        border: 1px solid #4361ee;
        color: #4361ee;
    }
    .btn-outline:hover {
        background: #4361ee;
        color: white;
    }
    </style>
</head>
<body>
<div style="height: 38px;"></div>
<div class="perfil-container">
    <div class="perfil-header">
        <div class="perfil-cover" style="<?= $banner_style ?>"></div>
        <div class="perfil-avatar-outer">
            <div class="perfil-avatar">
                <img src="<?= $user_avatar ?>" alt="<?= htmlspecialchars($usuario['usuario'] ?? '') ?>">
            </div>
        </div>
    </div>
    <div class="perfil-header-info">
        <div class="perfil-nombre">
            <h1><?= htmlspecialchars($usuario['usuario'] ?? '') ?></h1>
            <div class="perfil-nombre-completo">
                <?= htmlspecialchars($usuario['nombre'] ?? '') ?>
                <?php if ($edad !== '-'): ?>
                    &middot; <?= $edad ?> <?= $TEXTOS['anios'] ?>
                <?php endif; ?>
            </div>
            <?php
            // Botón seguir/dejar de seguir SOLO si está logueado y no es su propio perfil
            if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] != $usuario['id']) {
                // Comprobar si ya lo sigue
                $ya_sigue = false;
                $stmt_follow = $conexion->prepare("SELECT 1 FROM seguidores WHERE usuario_id = ? AND seguido_id = ?");
                $stmt_follow->bind_param("ii", $_SESSION['usuario_id'], $usuario['id']);
                $stmt_follow->execute();
                $stmt_follow->store_result();
                if ($stmt_follow->num_rows > 0) $ya_sigue = true;
                $stmt_follow->close();
                ?>
                <div style="margin: 15px 0;">
                    <?php if ($ya_sigue): ?>
                        <form action="<?= BASE_URL ?>/controllers/dejar-seguir.php" method="POST" style="display:inline;">
                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                            <button type="submit" class="btn btn-outline"><?= $TEXTOS['dejar_de_seguir'] ?? "Dejar de seguir" ?></button>
                        </form>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>/controllers/seguir-usuario.php" method="POST" style="display:inline;">
                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                            <button type="submit" class="btn"><?= $TEXTOS['seguir'] ?? "Seguir" ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php } ?>
        </div>
        <div class="perfil-estadisticas-row">
            <div class="estadistica">
                <span class="numero"><?= $stats['obras'] ?? 0 ?></span>
                <span class="etiqueta"><?= $TEXTOS['obras'] ?></span>
            </div>
            <div class="estadistica">
                <span class="numero"><?= $stats['seguidores'] ?? 0 ?></span>
                <span class="etiqueta"><?= $TEXTOS['seguidores'] ?></span>
            </div>
            <div class="estadistica">
                <span class="numero"><?= $stats['siguiendo'] ?? 0 ?></span>
                <span class="etiqueta"><?= $TEXTOS['siguiendo'] ?></span>
            </div>
        </div>
        <?php if (!empty($usuario['biografia'])): ?>
            <div class="perfil-bio">
                <i class="fas fa-quote-left" style="color:#4cc9f0"></i>
                <?= nl2br(htmlspecialchars($usuario['biografia'] ?? '')) ?>
            </div>
        <?php endif; ?>
        <div class="perfil-links-extra">
            <?php if (!empty($usuario['sitio_web'])): ?>
                <a href="<?= htmlspecialchars($usuario['sitio_web'] ?? '') ?>" target="_blank"><i class="fas fa-link"></i><?= $TEXTOS['web'] ?></a>
            <?php endif; ?>
            <?php if (!empty($usuario['ubicacion'])): ?>
                <span><i class="fas fa-map-marker-alt"></i><?= htmlspecialchars($usuario['ubicacion'] ?? '') ?></span>
            <?php endif; ?>
            <?php if (!empty($usuario['X'])): ?>
                <a href="<?= htmlspecialchars($usuario['X'] ?? '') ?>" target="_blank"><i class="fab fa-x-twitter"></i>X</a>
            <?php endif; ?>
            <?php if (!empty($usuario['instagram'])): ?>
                <a href="<?= htmlspecialchars($usuario['instagram'] ?? '') ?>" target="_blank"><i class="fab fa-instagram"></i>Instagram</a>
            <?php endif; ?>
            <?php if (!empty($usuario['facebook'])): ?>
                <a href="<?= htmlspecialchars($usuario['facebook'] ?? '') ?>" target="_blank"><i class="fab fa-facebook"></i>Facebook</a>
            <?php endif; ?>
        </div>
    </div>
    <!-- Sección principal y lateral -->
    <div class="perfil-contenido">
        <div class="perfil-columna-principal">
            <div class="perfil-seccion">
                <div class="seccion-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <h2><?= $TEXTOS['mis_obras_recientes'] ?></h2>
                </div>
                <div class="obras-grid">
                    <?php if ($puede_ver && $ultimas_obras->num_rows): ?>
                        <?php while ($obra = $ultimas_obras->fetch_assoc()): ?>
                            <div class="obra-card">
                                <div class="obra-portada">
                                    <?php 
                                    $ruta_portada = (!empty($obra['portada'])) ? __DIR__ . '/../uploads/portadas/' . $obra['portada'] : '';
                                    if (!empty($obra['portada']) && file_exists($ruta_portada)): ?>
                                        <img src="../uploads/portadas/<?= htmlspecialchars($obra['portada'] ?? '') ?>" alt="Portada">
                                    <?php else: ?>
                                        <div class="portada-placeholder"><i class="fas fa-book"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="obra-info">
                                    <div class="obra-categoria"><?= htmlspecialchars($obra['categoria'] ?? '') ?></div>
                                    <h3 title="<?= htmlspecialchars($obra['titulo'] ?? '') ?>">
                                      <?= htmlspecialchars($obra['titulo'] ?? '') ?>
                                    </h3>
                                    <span class="badge"><?= ($obra['tipo'] ?? '') == 'libro' ? $TEXTOS['libro'] : $TEXTOS['comic'] ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php elseif (!$puede_ver && $es_privado): ?>
                        <div class="no-resultados">
                            <i class="fas fa-lock"></i>
                            <p><?= $TEXTOS['perfil_privado'] ?? 'Este perfil es privado. Solo los seguidores pueden ver su contenido.' ?></p>
                        </div>
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
                    <a class="ver-todo" href="seguidores.php?id=<?= $usuario['id'] ?? 0 ?>"><?= $TEXTOS['ver_todos'] ?></a>
                </div>
                <div class="usuarios-grid">
                    <?php if ($seguidores->num_rows): ?>
                        <?php while ($seg = $seguidores->fetch_assoc()): ?>
                            <a class="usuario-item" href="perfil.php?id=<?= $seg['id'] ?? 0 ?>">
                                <img src="<?= !empty($seg['avatar']) ? "../uploads/avatars/" . htmlspecialchars($seg['avatar']) : "../public/img/avatar_default.png" ?>" alt="Avatar">
                                <span><?= htmlspecialchars($seg['usuario'] ?? '') ?></span>
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
                    <a class="ver-todo" href="seguidos.php?id=<?= $usuario['id'] ?? 0 ?>"><?= $TEXTOS['ver_todos'] ?></a>
                </div>
                <div class="usuarios-grid">
                    <?php if ($seguidos->num_rows): ?>
                        <?php while ($seg = $seguidos->fetch_assoc()): ?>
                            <a class="usuario-item" href="perfil.php?id=<?= $seg['id'] ?? 0 ?>">
                                <img src="<?= !empty($seg['avatar']) ? "../uploads/avatars/" . htmlspecialchars($seg['avatar']) : "../public/img/avatar_default.png" ?>" alt="Avatar">
                                <span><?= htmlspecialchars($seg['usuario'] ?? '') ?></span>
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
        </div>
    </div>
</div>
<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
</body>
</html>