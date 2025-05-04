
<?php 

    include '../includes/header.php'; 

?>

<h2>Iniciar sesión</h2>

<form action="/controllers/procesar-login.php" method="POST">

  <label for="usuario">Nombre de usuario o email:</label><br>
  <input type="text" name="usuario" required><br><br>

  <label for="password">Contraseña:</label><br>
  <input type="password" name="password" required><br><br>

  <button type="submit">Entrar</button>

</form>

<?php 

    include '../includes/footer.php'; 
    
?>