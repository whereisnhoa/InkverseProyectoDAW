
<!-- header -->
<?php include '../includes/header.php'; ?>

<h2>Registrar nuevo usuario</h2>

<!-- formulario de registro de usuario -->
<form action="../controllers/procesar-registro.php" method="POST">

  <label for="nombre">Nombre completo:</label><br>
  <input type="text" name="nombre" required><br><br>

  <label for="usuario">Nombre de usuario:</label><br>
  <input type="text" name="usuario" required><br><br>

  <label for="email">Correo electrónico:</label><br>
  <input type="email" name="email" required><br><br>

  <label for="fecha_nacimiento">Fecha de nacimiento:</label><br>
  <input type="date" name="fecha_nacimiento" required><br><br>

  <label for="password">Contraseña:</label><br>
  <input type="password" name="password" required><br><br>

  <button type="submit">Registrarse</button>

</form>

<!-- footer -->
<?php include '../includes/footer.php'; ?>
