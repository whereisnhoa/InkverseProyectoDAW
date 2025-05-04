
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Proyecto</title>

  <link rel="stylesheet" href="/css/estilo.css">

  <!-- Añadir Font Awesome para iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>
  
  <header>
   
    <div class="header-container">
      <a href="/public/index.php" class="logo">Proyecto Literario</a>
      
      <nav>

        <a href="/public/index.php"><i class="fas fa-home"></i> Inicio</a>
        <?php if (isset($_SESSION['usuario'])): ?>
          <a href="/usuario/perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
          <a href="/usuario/mis_obras.php"><i class="fas fa-book"></i> Mis Obras</a>
          <a href="/usuario/favoritos.php"><i class="fas fa-heart"></i> Favoritos</a>
          <a href="/usuario/nueva_obra.php"><i class="fas fa-plus-circle"></i> Nueva Obra</a>
          <a href="/public/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        <?php else: ?>
          <a href="/public/login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
          <a href="/public/register.php"><i class="fas fa-user-plus"></i> Registrarse</a>
        <?php endif; ?>
        
      </nav>

    </div>

  </header>

  <main>

    <?php
    // Mostrar mensajes de alerta si existen
    if (isset($_SESSION['mensaje'])) {
      $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
      echo '<div class="alerta alerta-' . $tipo . '">' . $_SESSION['mensaje'] . '</div>';
      unset($_SESSION['mensaje']);
      unset($_SESSION['tipo_mensaje']);
    }
    ?>
