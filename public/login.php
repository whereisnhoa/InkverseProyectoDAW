<?php include '../includes/header.php'; ?>

<h1 class="text-center">Iniciar Sesión</h1>

<form action="/controllers/procesar-login.php" method="POST">
  <div class="form-group">
    <label for="usuario">Nombre de usuario o email:</label>
    <input type="text" id="usuario" name="usuario" required>
  </div>
  
  <div class="form-group">
    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required>
  </div>
  
  <div class="flex flex-between items-center">
    <a href="#">¿Olvidaste tu contraseña?</a>
    <button type="submit">Iniciar Sesión</button>
  </div>
  
  <div class="text-center mt-3">
    <p>¿No tienes una cuenta? <a href="/public/register.php">Regístrate aquí</a></p>
  </div>
</form>

<?php include '../includes/footer.php'; ?>