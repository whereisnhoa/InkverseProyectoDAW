
<?php include '../includes/header.php'; ?>

<div class="text-center mb-4">
  <h1>Bienvenido a Proyecto Literario</h1>
  <p class="mb-3">Tu plataforma para descubrir, leer y compartir libros, novelas, cómics y manga.</p>
  
  <?php if (!isset($_SESSION['usuario'])): ?>
    <div class="flex flex-center gap-2 mt-3">
      <a href="/public/login.php" class="btn">Iniciar Sesión</a>
      <a href="/public/register.php" class="btn btn-outline">Registrarse</a>
    </div>
  <?php endif; ?>
</div>

<div class="obra-grid mt-4">
  <!-- Ejemplo de tarjeta de obra -->
  <div class="obra-card">
    <div class="obra-card-header">
      <h3>Título de la Obra</h3>
      <div class="flex gap-1 mt-1">
        <span class="badge badge-categoria">Fantasía</span>
      </div>
    </div>
    <div class="obra-card-body">
      <p>Descripción breve de la obra que aparecerá en la tarjeta...</p>
    </div>
    <div class="obra-card-footer">
      <span>Por: Autor</span>
      <a href="/public/obra.php?id=1&tipo=libro" class="btn btn-outline">Leer más</a>
    </div>
  </div>
  
</div>

<?php include '../includes/footer.php'; ?>