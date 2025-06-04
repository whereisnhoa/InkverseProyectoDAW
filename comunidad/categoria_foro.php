<?php
require_once('../includes/config.php');
require_once('../includes/conexion.php');
require_once('../includes/funciones.php');
include_once('../includes/languages/es.php'); // Cambia según idioma
session_start();

$categoria_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($categoria_id < 1) {
    header('Location: ' . BASE_URL . '/comunidad/');
    exit;
}

// Obtener datos de la categoría
$sql_cat = "SELECT nombre, descripcion FROM categorias_foro WHERE id = ?";
$stmt = $conexion->prepare($sql_cat);
$stmt->bind_param("i", $categoria_id);
$stmt->execute();
$stmt->bind_result($nombre_categoria, $descripcion_categoria);
$stmt->fetch();
$stmt->close();

if (!$nombre_categoria) {
    include '../includes/header.php';
    echo '<div class="foro-container"><div class="tema-noexiste">'.$TEXTOS['foro_categoria_no_encontrada'].'</div></div>';
    include '../includes/footer.php';
    exit;
}

// Obtener temas de la categoría
$sql = "SELECT tf.id, tf.titulo, u.usuario AS autor, tf.ultima_actividad
        FROM temas_foro tf
        JOIN usuarios u ON tf.usuario_id = u.id
        WHERE tf.categoria_id = ?
        ORDER BY tf.ultima_actividad DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $categoria_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">

<style>
.foro-categoria-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 70vh;
    padding: 56px 0 60px 0;
}
.foro-categoria-card {
    background: #fff;
    border-radius: 32px;
    box-shadow: 0 8px 44px #d0abf040, 0 2px 6px #fbefff44;
    padding: 42px 38px 38px 38px;
    max-width: 900px;
    width: 100%;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.foro-categoria-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 16px;
}
.foro-categoria-icon {
    font-size: 2.2rem;
    color: #d1b3fc;
    margin-bottom: 8px;
    filter: drop-shadow(0 2px 8px #fcb7ff66);
}
.foro-categoria-nombre {
    font-size: 2.2rem;
    font-weight: 900;
    color: #d1b3fc;
    text-shadow: 0 2px 14px #e6dbfa44;
    margin-bottom: 0;
    text-align: center;
}
.foro-categoria-desc {
    font-size: 1.12rem;
    color: #8762b0;
    margin-bottom: 26px;
    text-align: center;
    max-width: 600px;
}
.foro-categoria-crear-btn {
    background: linear-gradient(90deg, #a16ae8 0%, #fcb7ff 100%);
    color: #fff;
    font-weight: 700;
    font-size: 1.09rem;
    border: none;
    border-radius: 16px;
    padding: 14px 36px;
    cursor: pointer;
    margin: 0 auto 32px auto;
    box-shadow: 0 2px 14px #d1b3fc55;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: filter .15s, transform .15s;
}
.foro-categoria-crear-btn:hover {
    filter: brightness(1.08);
    transform: scale(1.03);
}

/* Tabla */
.foro-categoria-tabla-block {
    width: 100%;
    margin-top: 4px;
}
.foro-categoria-tabla {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 1.09rem;
    background: none;
    border-radius: 14px;
    overflow: hidden;
}
.foro-categoria-tabla thead tr {
    background: linear-gradient(90deg, #f4e2ff 0%, #fbefff 100%);
}
.foro-categoria-tabla th, .foro-categoria-tabla td {
    padding: 18px 16px;
    text-align: center;
}
.foro-categoria-tabla th {
    color: #6e38b1;
    font-size: 1.03rem;
    font-weight: 800;
    letter-spacing: .5px;
    border-bottom: 2px solid #dabfff;
    background: transparent;
}
.foro-categoria-tabla td {
    border-bottom: 1px solid #f6ebff;
    background: transparent;
    vertical-align: middle;
    transition: background .18s;
}
.foro-categoria-tabla tr:last-child td {
    border-bottom: none;
}
.foro-categoria-tabla tbody tr:hover td {
    background: linear-gradient(90deg, #f4e2ff22 0%, #fbefff22 100%);
}
.foro-categoria-tema-link {
    color: #a16ae8;
    font-weight: 700;
    text-decoration: none;
    transition: color .13s;
    font-size: 1.07rem;
    word-break: break-word;
}
.foro-categoria-tema-link:hover {
    color: #6e38b1;
    text-decoration: underline;
}
@media (max-width: 700px) {
    .foro-categoria-card { padding: 20px 2vw; }
    .foro-categoria-nombre { font-size: 1.28rem; }
    .foro-categoria-tabla th, .foro-categoria-tabla td { font-size: 0.94rem; padding: 12px 4px; }
    .foro-categoria-crear-btn { padding: 10px 10px; font-size: .97rem; }
}
</style>

<div class="foro-categoria-container">
    <div class="foro-categoria-card">
        <div class="foro-categoria-header">
            <span class="foro-categoria-icon"><i class="fas fa-comments"></i></span>
            <h1 class="foro-categoria-nombre"><?php echo htmlspecialchars($nombre_categoria); ?></h1>
        </div>
        <div class="foro-categoria-desc">
            <?php echo htmlspecialchars($descripcion_categoria ? $descripcion_categoria : $TEXTOS['foro_categoria_default_desc']); ?>
        </div>
        <a href="nuevo_tema.php?cat=<?php echo $categoria_id; ?>" class="foro-categoria-crear-btn">
            <i class="fas fa-plus"></i> <?php echo $TEXTOS['foros_nuevo_tema']; ?>
        </a>
        <div class="foro-categoria-tabla-block">
            <table class="foro-categoria-tabla">
                <thead>
                    <tr>
                        <th><?php echo $TEXTOS['foros_col_tema']; ?></th>
                        <th><?php echo $TEXTOS['foros_col_autor']; ?></th>
                        <th><?php echo $TEXTOS['foros_col_actividad']; ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($tema = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a class="foro-categoria-tema-link" href="tema.php?id=<?php echo $tema['id']; ?>">
                                    <?php echo htmlspecialchars($tema['titulo']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($tema['autor']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($tema['ultima_actividad'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3"><?php echo $TEXTOS['foros_no_temas']; ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>