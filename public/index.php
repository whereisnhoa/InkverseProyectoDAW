<?php
/**
 * Página de inicio
 * Esta es la página principal del sitio
 */

// Incluir el header
require_once(__DIR__ . '/../includes/header.php');
?>

<div class="container">
    <h1>Bienvenido al Proyecto</h1>
    
    <p>En esta plataforma podrás leer y compartir contenido como libros, novelas, cómics y manga.</p>
    
    <p>Regístrate o inicia sesión para comenzar a explorar.</p>
    
    <?php if (!isset($_SESSION['usuario_id'])): ?>
    <div class="cta-buttons">
        <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn">Iniciar Sesión</a>
        <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-outline">Registrarse</a>
    </div>
    <?php endif; ?>
</div>

<?php
// Incluir el footer
require_once(__DIR__ . '/../includes/footer.php');
?>