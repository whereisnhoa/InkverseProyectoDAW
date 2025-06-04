<?php
require_once('../includes/config.php');
require_once('../includes/conexion.php');
require_once('../includes/funciones.php');
include_once('../includes/languages/es.php');

session_start();

// Obtener todos los temas
$sql = "SELECT tf.id, tf.titulo, cf.nombre AS categoria, u.usuario AS autor, tf.ultima_actividad
        FROM temas_foro tf
        JOIN categorias_foro cf ON tf.categoria_id = cf.id
        JOIN usuarios u ON tf.usuario_id = u.id
        ORDER BY tf.ultima_actividad DESC";
$result = $conexion->query($sql);

include '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">

<style>
.foro-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 48px 0 60px 0;
    background: linear-gradient(120deg, #f5efff 0%, #e3f0ff 100%);
    min-height: 100vh;
}

/* Centrado y gradiente animado para el título */
.foros-titulo-principal {
    font-size: 3rem;
    font-weight: 900;
    background: linear-gradient(87deg, #a16ae8 10%, #6e38b1 50%, #fcb7ff 100%);
    background-size: 220% auto;
    color: transparent;
    background-clip: text;
    -webkit-background-clip: text;
    animation: gradient-move 7s linear infinite alternate;
    margin: 0 0 32px 0;
    letter-spacing: -.5px;
    text-shadow: 0 4px 18px #d7c1fa30;
    text-align: center;
}
@keyframes gradient-move {
    to { background-position: 220% center; }
}

/* Botones centrados */
.foros-botones {
    margin-bottom: 38px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    width: 100%;
}

/* Botón crear tema */
.foros-boton-crear {
    background: linear-gradient(95deg, #a16ae8 30%, #fcb7ff 100%);
    color: #fff;
    font-weight: 700;
    font-size: 1.15rem;
    border: none;
    border-radius: 14px;
    padding: 15px 36px;
    cursor: pointer;
    box-shadow: 0 2px 16px #c8a8e966;
    transition: transform .13s, filter .18s;
    display: flex;
    align-items: center;
    gap: 13px;
    justify-content: center;
}
.foros-boton-crear:hover {
    filter: brightness(1.11);
    transform: scale(1.040);
    box-shadow: 0 4px 22px #c8a8e988;
}

/* Botón mis temas */
.foros-boton-mis {
    background: #fff;
    color: #a16ae8;
    border: 2px solid #a16ae8;
    padding: 15px 36px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 1.15rem;
    transition: background .15s, color .15s, box-shadow .15s;
    cursor: pointer;
    box-shadow: 0 2px 8px #e4d2f6;
    display: flex;
    align-items: center;
    justify-content: center;
}
.foros-boton-mis:hover {
    background: #f5ecff;
    color: #6e38b1;
    box-shadow: 0 4px 18px #e4d2f6;
}

/* Contenedor centrado para la tabla */
.foros-tabla-block {
    margin-top: 15px;
    background: #fff;
    border-radius: 22px;
    box-shadow: 0 7px 36px #a16ae835, 0 1.5px 4px #fcb7ff40;
    overflow-x: auto;
    border: 1.7px solid #f2d9ff;
    max-width: 1000px;
    width: 100%;
    display: flex;
    justify-content: center;
}

/* Tabla centrada y estilizada */
.foros-tabla {
    width: 100%;
    max-width: 950px;
    margin: 0 auto;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 1.12rem;
    background: none;
}
.foros-tabla thead tr {
    background: linear-gradient(90deg, #ede2ff 0%, #fbefff 100%);
}
.foros-tabla th, .foros-tabla td {
    padding: 20px 18px;
    text-align: center;
}
.foros-tabla th {
    color: #6e38b1;
    font-size: 1.08rem;
    font-weight: 900;
    letter-spacing: .4px;
    border-bottom: 2.5px solid #e8d7fb;
    background: transparent;
}
.foros-tabla td {
    border-bottom: 1px solid #f6ebff;
    background: transparent;
    vertical-align: middle;
    transition: background .18s;
}
.foros-tabla tr:last-child td {
    border-bottom: none;
}
.foros-tabla tbody tr:hover td {
    background: linear-gradient(90deg, #f4e2ff22 0%, #fbefff22 100%);
}

/* Enlace del tema */
.foros-tema-link {
    color: #a16ae8;
    font-weight: 700;
    text-decoration: none;
    transition: color .13s;
    font-size: 1.08rem;
    word-break: break-word;
}
.foros-tema-link:hover {
    color: #6e38b1;
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 1100px) {
    .foros-tabla-block {
        max-width: 99vw;
    }
    .foros-tabla {
        max-width: 99vw;
        font-size: 1rem;
    }
}
@media (max-width: 900px) {
    .foros-titulo-principal { font-size: 2.05rem; }
    .foros-tabla th, .foros-tabla td { padding: 13px 7px; font-size: 0.97rem; }
    .foros-boton-crear, .foros-boton-mis { padding: 11px 10px; font-size: 1rem; }
}
@media (max-width: 600px) {
    .foro-container {
        padding: 24px 0 60px 0;
    }
    .foros-titulo-principal { font-size: 1.3rem; }
    .foros-boton-crear, .foros-boton-mis { border-radius: 9px; padding: 7px 10px; }
    .foros-tabla-block { border-radius: 12px; }
    .foros-tabla th, .foros-tabla td { font-size: 0.93rem; }
}
</style>

<div class="foro-container">
    <h1 class="foros-titulo-principal"><?php echo $TEXTOS['foros_todos']; ?></h1>

    <div class="foros-botones">
        <a href="nuevo_tema.php" class="foros-boton-crear">
            <i class="fas fa-plus"></i> <?php echo $TEXTOS['foros_nuevo_tema']; ?>
        </a>
        <a href="mis_temas.php" class="foros-boton-mis">
            <?php echo $TEXTOS['foros_mis_temas']; ?>
        </a>
    </div>

    <div class="foros-tabla-block">
        <table class="foros-tabla">
            <thead>
                <tr>
                    <th><?php echo $TEXTOS['foros_col_tema']; ?></th>
                    <th><?php echo $TEXTOS['foros_col_categoria']; ?></th>
                    <th><?php echo $TEXTOS['foros_col_autor']; ?></th>
                    <th><?php echo $TEXTOS['foros_col_actividad']; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($tema = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <a class="foros-tema-link" href="tema.php?id=<?php echo $tema['id']; ?>">
                                    <?php echo htmlspecialchars($tema['titulo']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($tema['categoria']); ?></td>
                            <td><?php echo htmlspecialchars($tema['autor']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($tema['ultima_actividad'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4"><?php echo $TEXTOS['foros_no_temas']; ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>