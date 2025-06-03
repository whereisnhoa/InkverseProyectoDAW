<?php
// Iniciar sesión
session_start();

// Determinar la ruta base del proyecto
$document_root = $_SERVER['DOCUMENT_ROOT'];
$current_file = str_replace('\\', '/', __FILE__);
$document_root = str_replace('\\', '/', $document_root);

// Encontrar la posición de 'usuario' en la ruta actual
$usuario_pos = strrpos($current_file, '/usuario/');
if ($usuario_pos !== false) {
    $project_path = substr($current_file, 0, $usuario_pos);
} else {
    // Fallback: asumir que estamos en la carpeta usuario
    $project_path = dirname(dirname(__FILE__));
}

// Calcular la ruta relativa para BASE_URL
$relative_path = str_replace($document_root, '', $project_path);

// Definir BASE_URL si no está definido
if (!defined('BASE_URL')) {
    define('BASE_URL', $relative_path);
}

// Selección de idioma
$idioma = $_SESSION['idioma'] ?? 'es';
$archivo_idioma = $project_path . "/includes/languages/{$idioma}.php";
if (!file_exists($archivo_idioma)) {
    $archivo_idioma = $project_path . "/includes/languages/es.php"; // fallback
}
require_once($archivo_idioma);

require_once($project_path . '/includes/funciones.php');
require_once($project_path . '/includes/conexion.php');
require_once($project_path . '/includes/header.php');

// Verificar que el usuario esté logueado
verificarLogin();

// Obtener ID del usuario
$usuario_id = $_SESSION['usuario_id'];

// Obtener seguidos
$sql = "SELECT u.id, u.usuario, u.avatar, u.biografia,
        (SELECT COUNT(*) FROM seguidores WHERE usuario_id = u.id) AS seguidos,
        (SELECT COUNT(*) FROM seguidores WHERE seguido_id = u.id) AS seguidores,
        (SELECT COUNT(*) FROM seguidores WHERE seguido_id = ? AND usuario_id = u.id) AS te_sigue
        FROM seguidores s
        JOIN usuarios u ON s.seguido_id = u.id
        WHERE s.usuario_id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();
$seguidos = $stmt->get_result();
$total_seguidos = $seguidos->num_rows;
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-user-friends"></i> <?= htmlspecialchars($TEXTOS['usuarios_que_sigo'] ?? 'Usuarios que Sigo') ?></h1>
            <p><?= htmlspecialchars(str_replace("{n}", $total_seguidos, $TEXTOS['sigues_a_n_usuarios'] ?? "Sigues a {$total_seguidos} usuarios")) ?></p>
        </div>
        <div class="card-body">
            <?php if ($total_seguidos > 0): ?>
                <div class="user-grid">
                    <?php while ($seguido = $seguidos->fetch_assoc()): ?>
                        <div class="user-card">
                            <div class="user-avatar">
                                <img src="<?php echo !empty($seguido['avatar']) ? BASE_URL . '/uploads/avatars/' . $seguido['avatar'] : BASE_URL . '/img/default-avatar.png'; ?>"
                                     alt="<?= htmlspecialchars(str_replace("{usuario}", htmlspecialchars($seguido['usuario']), $TEXTOS["avatar_de"] ?? 'Avatar de usuario')) ?>">
                            </div>
                            <div class="user-info">
                                <h3><a href="<?php echo BASE_URL; ?>/public/perfil.php?id=<?php echo $seguido['id']; ?>"><?php echo htmlspecialchars($seguido['usuario']); ?></a></h3>
                                <div class="user-stats">
                                    <span><strong><?php echo $seguido['seguidores']; ?></strong> <?= htmlspecialchars($TEXTOS["seguidores"] ?? 'seguidores') ?></span>
                                    <span><strong><?php echo $seguido['seguidos']; ?></strong> <?= htmlspecialchars($TEXTOS["seguidos"] ?? 'seguidos') ?></span>
                                </div>
                                <p class="user-bio"><?php echo !empty($seguido['biografia']) ? htmlspecialchars(substr($seguido['biografia'], 0, 100)) . (strlen($seguido['biografia']) > 100 ? '...' : '') : htmlspecialchars($TEXTOS["sin_biografia"] ?? 'Sin biografía'); ?></p>
                                <?php if ($seguido['te_sigue']): ?>
                                    <span class="follows-you"><?= htmlspecialchars($TEXTOS["te_sigue"] ?? "Te sigue") ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="user-actions">
                                <form action="<?php echo BASE_URL; ?>/controllers/dejar-seguir.php" method="POST">
                                    <input type="hidden" name="usuario_id" value="<?php echo $seguido['id']; ?>">
                                    <button type="submit" class="btn btn-outline btn-sm"><?= htmlspecialchars($TEXTOS["dejar_de_seguir"] ?? 'Dejar de seguir') ?></button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-friends fa-4x"></i>
                    <h2><?= htmlspecialchars($TEXTOS["aun_no_sigues_usuarios"] ?? 'Aún no sigues a ningún usuario') ?></h2>
                    <p><?= htmlspecialchars($TEXTOS["mensaje_no_sigues_usuarios"] ?? 'Cuando sigas a alguien, aparecerá aquí.') ?></p>
                    <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn"><?= htmlspecialchars($TEXTOS["explorar_usuarios"] ?? 'Explorar usuarios') ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.container {
    max-width: 900px;
    margin: 30px auto;
    padding: 0 20px;
}
.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}
.card-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    background-color: #f8f9fa;
}
.card-header h1 {
    margin: 0 0 10px 0;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
}
.card-header h1 i {
    color: #4361ee;
}
.card-header p {
    margin: 0;
    color: #666;
}
.card-body {
    padding: 20px;
}
.user-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}
.user-card {
    display: flex;
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 8px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.user-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.user-avatar {
    margin-right: 20px;
}
.user-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}
.user-info {
    flex: 1;
}
.user-info h3 {
    margin: 0 0 5px 0;
    font-size: 1.2rem;
}
.user-info h3 a {
    color: #333;
    text-decoration: none;
    transition: color 0.2s;
}
.user-info h3 a:hover {
    color: #4361ee;
}
.user-stats {
    display: flex;
    gap: 15px;
    margin-bottom: 10px;
    font-size: 0.9rem;
    color: #666;
}
.user-bio {
    margin: 0 0 10px 0;
    font-size: 0.95rem;
    color: #555;
}
.follow-date {
    margin: 0;
    font-size: 0.85rem;
    color: #888;
}
.follows-you {
    display: inline-block;
    background-color: #f0f4ff;
    color: #4361ee;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
    margin-top: 5px;
}
.user-actions {
    display: flex;
    align-items: center;
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
.btn-sm {
    padding: 6px 12px;
    font-size: 0.85rem;
}
.empty-state {
    text-align: center;
    padding: 40px 20px;
}
.empty-state i {
    color: #ddd;
    margin-bottom: 20px;
}
.empty-state h2 {
    margin: 0 0 10px 0;
    font-size: 1.5rem;
    color: #555;
}
.empty-state p {
    margin: 0 0 20px 0;
    color: #777;
}
@media (max-width: 768px) {
    .user-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .user-avatar {
        margin-right: 0;
        margin-bottom: 15px;
    }
    .user-stats {
        justify-content: center;
    }
    .user-actions {
        margin-top: 15px;
        justify-content: center;
    }
}
</style>

<?php require_once($project_path . '/includes/footer.php'); ?>