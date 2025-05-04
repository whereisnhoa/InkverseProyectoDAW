
<?php

include '/includes/funciones.php';
verificarLogin();
session_start();
include '/includes/conexion.php';
include '/includes/header.php';

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT obras.* FROM obras 
        JOIN favoritos ON favoritos.obra_id = obras.id 
        WHERE favoritos.usuario_id = ?
        ORDER BY obras.fecha_creacion DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<h2>Mis obras favoritas</h2>

<?php if ($resultado->num_rows > 0): ?>
    <?php while ($obra = $resultado->fetch_assoc()): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <h3><?= htmlspecialchars($obra['titulo']) ?></h3>
            <p><strong>Tipo:</strong> <?= $obra['tipo'] ?> | <strong>Género:</strong> <?= $obra['genero'] ?></p>
            <a href="/public/obra.php?id=<?= $obra['id'] ?>">Ver obra</a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No has añadido obras a favoritos aún.</p>
<?php endif; ?>

<?php include '/includes/footer.php'; ?>
