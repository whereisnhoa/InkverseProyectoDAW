<?php
/**
 * Página de inicio de sesión
 */

// Incluir el header
require_once(__DIR__ . '/../includes/header.php');

// Si el usuario ya está logueado, redirigir al perfil
if (isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/usuario/perfil.php");
    exit();
}
?>

<div class="container">
    <h2>Iniciar sesión</h2>
    
    <form action="<?php echo BASE_URL; ?>/controllers/procesar-login.php" method="POST" class="form">
        <!-- Token CSRF para seguridad -->
        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
        
        <div class="form-group">
            <label for="usuario">Nombre de usuario o email:</label>
            <input type="text" id="usuario" name="usuario" required>
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn">Entrar</button>
    </form>
    
    <p class="text-center mt-3">
        ¿No tienes cuenta? <a href="<?php echo BASE_URL; ?>/public/register.php">Regístrate aquí</a>
    </p>
</div>

<?php
// Incluir el footer
require_once(__DIR__ . '/../includes/footer.php');
?>