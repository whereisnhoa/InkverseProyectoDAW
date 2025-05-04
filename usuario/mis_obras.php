
<?php

include '../includes/funciones.php';
verificarLogin();
session_start();
include '../includes/conexion.php';
include '../includes/header.php';

$autor_id = $_SESSION['usuario_id'];

$sql = "SELECT id, titulo, descripcion, tipo, genero, es_adulto, fecha_creacion 
        FROM obras 
        WHERE autor_id = ? 
        ORDER BY fecha_creacion DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $autor_id);
$stmt->execute();
$resultado = $stmt->get_result();

?>

<h2>Mis obras publicadas</h2>

<?php if ($resultado->num_rows > 0): ?>

    <?php while ($obra = $resultado->fetch_assoc()): ?>

        <div>

            <h3><?= htmlspecialchars($obra['titulo']) ?></h3>
            <p><strong>Tipo:</strong> <?= $obra['tipo'] ?> | <strong>Género:</strong> <?= $obra['genero'] ?></p>
            <p><?= nl2br(htmlspecialchars($obra['descripcion'])) ?></p>
            <p>Publicada el: <?= $obra['fecha_creacion'] ?></p>
            <?php if ($obra['es_adulto']): ?>
                <p style="color: red;">Contenido para adultos</p>
            <?php endif; ?>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <p>No has publicado ninguna obra aún.</p>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>
