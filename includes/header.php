
<?php
/**
 * Header de la página
 * Este archivo contiene la cabecera HTML y la navegación principal
 */

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

// Incluir archivos de configuración y funciones
if (!defined('BASE_PATH')) 
{
    require_once(__DIR__ . '/config.php');
}
require_once(__DIR__ . '/conexion.php');
require_once(__DIR__ . '/funciones.php');
?>

<!DOCTYPE html>
<html lang="es">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <title>Proyecto Ainhoa</title>
  
  <link rel="stylesheet" href="/css/estilo.css">
  
  <!-- Font Awesome para iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <!-- Script para el modo oscuro -->
  <script src="/js/dark-mode.js"></script>
  
  <!-- Script para el header -->
  <script src="/js/header.js" defer></script>

</head>

<body>

  <header>

    <div class="header-container">
      <!-- Logo -->
      <a href="/public/index.php" class="logo">

        <i class="fas fa-book-open"></i>
        <span>InkVerse</span>

      </a>
      
      <!-- Navegación principal -->
      <nav class="main-nav">
        <!-- Categorías -->
        <div class="dropdown">

          <a href="#" class="dropdown-toggle">
            <i class="fas fa-list"></i> Categorías <i class="fas fa-chevron-down"></i>
          </a>

          <div class="dropdown-menu">

            <?php

            // obtener categorías de la base de datos
            include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/conexion.php';
            $sql = "SELECT nombre FROM categorias ORDER BY nombre";
            $result = $conexion->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<a href="/public/categoria.php?nombre=' . urlencode($row['nombre']) . '">' . htmlspecialchars($row['nombre']) . '</a>';
                }
            } else {
                echo '<a href="#">No hay categorías</a>';
            }
            ?>
            <div class="dropdown-divider"></div>
            <a href="/public/categorias.php">Ver todas</a>
          </div>
        </div>
        
        <!-- Comunidad (Dropdown) -->
        <div class="dropdown">
          <a href="#" class="dropdown-toggle">
            <i class="fas fa-users"></i> Comunidad <i class="fas fa-chevron-down"></i>
          </a>
          <div class="dropdown-menu">
            <a href="/comunidad/foros.php"><i class="fas fa-comments"></i> Foros</a>
            <a href="/comunidad/arte.php"><i class="fas fa-palette"></i> Galería de Arte</a>
            <a href="/comunidad/eventos.php"><i class="fas fa-calendar-alt"></i> Eventos</a>
            <a href="/comunidad/retos.php"><i class="fas fa-trophy"></i> Retos Literarios</a>
          </div>
        </div>
        
        <!-- Enlace a Explorar -->
        <a href="/public/explorar.php">
          <i class="fas fa-compass"></i> Explorar
        </a>

      </nav>
      
      <!-- Buscador -->
      <div class="search-container">

        <form action="/public/buscar.php" method="GET">

          <input type="text" name="q" placeholder="Buscar obras, autores..." class="search-input">
          <button type="submit" class="search-button">
            <i class="fas fa-search"></i>
          </button>

        </form>

      </div>
      
      <!-- Iconos de acción -->
      <div class="action-icons">

        <!-- Modo oscuro -->
        <button id="theme-toggle" class="icon-button" title="Cambiar a modo oscuro">
          <i id="theme-icon" class="fas fa-moon"></i>
        </button>
        
        <!-- Favoritos -->
        <a href="<?php echo isset($_SESSION['usuario_id']) ? '/usuario/favoritos.php' : '/public/login.php'; ?>" class="icon-button" title="Mis favoritos">
          <i class="fas fa-heart"></i>
          <?php if (isset($_SESSION['usuario_id'])): ?>
            <?php

            // Contar favoritos
            $usuario_id = $_SESSION['usuario_id'];
            $sql = "SELECT COUNT(*) as total FROM favoritos WHERE usuario_id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['total'];
            
            if ($count > 0):
            ?>

            <span class="badge-count"><?php echo $count; ?></span>
            <?php endif; ?>
          <?php endif; ?>
        </a>
        
        <!-- Usuario -->
        <div class="dropdown user-dropdown">

          <a href="#" class="dropdown-toggle icon-button" title="<?php echo isset($_SESSION['usuario']) ? 'Mi perfil' : 'Iniciar sesión'; ?>">
            <i class="fas fa-user-circle"></i>
          </a>

          <div class="dropdown-menu dropdown-menu-right">

            <?php if (isset($_SESSION['usuario'])): ?>
              <div class="dropdown-header">
                <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>
              </div>

              <a href="/usuario/perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
              <a href="/usuario/mis_obras.php"><i class="fas fa-book"></i> Mis Obras</a>
              <a href="/usuario/nueva_obra.php"><i class="fas fa-plus-circle"></i> Nueva Obra</a>
              <div class="dropdown-divider"></div>
              <a href="/public/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>

            <?php else: ?>
              <a href="/public/login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
              <a href="/public/register.php"><i class="fas fa-user-plus"></i> Registrarse</a>

            <?php endif; ?>

          </div>

        </div>
        
        <!-- botón de menú móvil -->
        <button class="mobile-menu-toggle icon-button" aria-label="Menú">
          <i class="fas fa-bars"></i>
        </button>

      </div>

    </div>
    
    <!-- menú móvil (aparece en pantallas pequeñas) -->
    <div class="mobile-menu">

      <div class="search-container-mobile">

        <form action="/public/buscar.php" method="GET">

          <input type="text" name="q" placeholder="Buscar obras, autores..." class="search-input">

          <button type="submit" class="search-button">
            <i class="fas fa-search"></i>
          </button>

        </form>

      </div>
      
      <nav class="mobile-nav">

        <a href="/public/index.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="/public/categorias.php"><i class="fas fa-list"></i> Categorías</a>
        <a href="/comunidad/foros.php"><i class="fas fa-users"></i> Comunidad</a>
        <a href="/public/explorar.php"><i class="fas fa-compass"></i> Explorar</a>
        
        <?php if (isset($_SESSION['usuario'])): ?>
          <div class="dropdown-divider"></div>
          <a href="/usuario/perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
          <a href="/usuario/favoritos.php"><i class="fas fa-heart"></i> Mis Favoritos</a>
          <a href="/usuario/mis_obras.php"><i class="fas fa-book"></i> Mis Obras</a>
          <a href="/usuario/nueva_obra.php"><i class="fas fa-plus-circle"></i> Nueva Obra</a>
          <div class="dropdown-divider"></div>
          <a href="/public/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        
        <?php else: ?>
          <div class="dropdown-divider"></div>
          <a href="/public/login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
          <a href="/public/register.php"><i class="fas fa-user-plus"></i> Registrarse</a>

        <?php endif; ?>

      </nav>

    </div>

  </header>
  
  <main>

    <?php
    // mostrar mensajes de alerta si existen
    if (isset($_SESSION['mensaje'])) 
    {
      $tipo = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : 'info';
      echo '<div class="alerta alerta-' . $tipo . '">' . $_SESSION['mensaje'] . '</div>';
      unset($_SESSION['mensaje']);
      unset($_SESSION['tipo_mensaje']);
    }
    ?>