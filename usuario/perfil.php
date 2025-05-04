
<?php

include '/includes/funciones.php';

verificarLogin(); // asegura que esté logueado

session_start();

include '/includes/header.php';

?>

<h2>Bienvenido, <?php echo $_SESSION['usuario']; ?></h2>


<a href="/public/logout.php">Cerrar sesión</a>

<?php include '/includes/footer.php'; ?>
