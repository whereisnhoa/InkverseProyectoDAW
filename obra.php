
<?php

session_start();

include '../includes/conexion.php';
include '../includes/header.php';

// mostrar obras no adultas por defecto
$filtro_adulto = "WHERE es_adulto = 0";

// si está logueado y tiene edad >= 18, se quita el filtro
if (isset($_SESSION['edad']) && $_SESSION['edad'] >= 18) 
{
    $filtro_adulto = ""; // puede ver todo
}

// consulta de obras según el filtro
$sql = "SELECT titulo, descripcion, tipo, genero, es_adulto FROM obras $filtro_adulto ORDER BY fecha_creacion DESC";
$resultado = $conexion->query($sql);

?>

<h2>Obras disponibles</h2>

<?php if ($resultado->num_rows > 0): ?>

    <?php while ($obra = $resultado->fetch_assoc()): ?>

        <div>

            <h3><?= htmlspecialchars($obra['titulo']) ?></h3>
            <p><strong>Tipo:</strong> <?= $obra['tipo'] ?> | <strong>Género:</strong> <?= $obra['genero'] ?></p>
            <p><?= nl2br(htmlspecialchars($obra['descripcion'])) ?></p>

            <?php if ($obra['es_adulto']): ?>
                <p style="color: red;">Contenido para adultos</p>
            <?php endif; ?>

        </div>

    <?php endwhile; ?>

<?php else: ?>

    <p>No hay obras disponibles.</p>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>
