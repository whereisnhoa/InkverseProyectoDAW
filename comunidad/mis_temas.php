<?php
require_once('../includes/config.php');
require_once('../includes/conexion.php');
require_once('../includes/funciones.php');
include_once('../includes/languages/es.php'); // Cambia según idioma
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ".BASE_URL."/public/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT tf.id, tf.titulo, cf.nombre AS categoria, tf.ultima_actividad
        FROM temas_foro tf
        JOIN categorias_foro cf ON tf.categoria_id = cf.id
        WHERE tf.usuario_id = ?
        ORDER BY tf.ultima_actividad DESC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">

<style>
.mis-temas-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 75vh;
    padding: 60px 0 60px 0;
    background: none;
}
.mis-temas-card {
    background: #fff;
    border-radius: 32px;
    box-shadow: 0 8px 44px #d0abf040, 0 2px 6px #fbefff44;
    padding: 48px 44px 38px 44px;
    max-width: 900px;
    width: 100%;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.mis-temas-title {
    font-size: 2.3rem;
    font-weight: 900;
    background: linear-gradient(90deg, #a16ae8 10%, #fcb7ff 100%);
    color: transparent;
    background-clip: text;
    -webkit-background-clip: text;
    margin-bottom: 32px;
    text-align: center;
    text-shadow: 0 2px 16px #e6dbfa44;
    display: flex;
    align-items: center;
    gap: 16px;
}
.mis-temas-table-block {
    width: 100%;
    margin-top: 12px;
}
.mis-temas-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 1.11rem;
    background: none;
    border-radius: 14px;
    overflow: hidden;
}
.mis-temas-table thead tr {
    background: linear-gradient(90deg, #f4e2ff 0%, #fbefff 100%);
}
.mis-temas-table th, .mis-temas-table td {
    padding: 18px 16px;
    text-align: center;
}
.mis-temas-table th {
    color: #6e38b1;
    font-size: 1.07rem;
    font-weight: 800;
    letter-spacing: .5px;
    border-bottom: 2px solid #dabfff;
    background: transparent;
}
.mis-temas-table td {
    border-bottom: 1px solid #f6ebff;
    background: transparent;
    vertical-align: middle;
    transition: background .18s;
}
.mis-temas-table tr:last-child td {
    border-bottom: none;
}
.mis-temas-table tbody tr:hover td {
    background: linear-gradient(90deg, #f4e2ff22 0%, #fbefff22 100%);
}
.mis-temas-link {
    color: #a16ae8;
    font-weight: 700;
    text-decoration: none;
    transition: color .13s;
    font-size: 1.05rem;
    word-break: break-word;
}
.mis-temas-link:hover {
    color: #6e38b1;
    text-decoration: underline;
}
@media (max-width: 700px) {
    .mis-temas-card { padding: 20px 2vw; }
    .mis-temas-title { font-size: 1.4rem; }
    .mis-temas-table th, .mis-temas-table td { font-size: 0.94rem; padding: 12px 4px; }
}
</style>

<div class="mis-temas-container">
    <div class="mis-temas-card">
        <h1 class="mis-temas-title"><i class="fas fa-user-edit"></i> <?php echo $TEXTOS['foros_mis_temas']; ?></h1>

        <div class="mis-temas-table-block">
            <table class="mis-temas-table">
                <thead>
                    <tr>
                        <th><?php echo $TEXTOS['foros_col_tema']; ?></th>
                        <th><?php echo $TEXTOS['foros_col_categoria']; ?></th>
                        <th><?php echo $TEXTOS['foros_col_actividad']; ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($tema = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a class="mis-temas-link" href="tema.php?id=<?php echo $tema['id']; ?>">
                                    <?php echo htmlspecialchars($tema['titulo']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($tema['categoria']); ?></td>
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