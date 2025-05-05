<?php
/**
 * Página de registro de usuarios
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
    <h2>Registrar nuevo usuario</h2>
    
    <form action="<?php echo BASE_URL; ?>/controllers/procesar-registro.php" method="POST" class="form">
        <!-- Token CSRF para seguridad -->
        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
        
        <div class="form-group">
            <label for="nombre">Nombre completo:</label>
            <input type="text" id="nombre" name="nombre" required>
        </div>
        
        <div class="form-group">
            <label for="usuario">Nombre de usuario:</label>
            <input type="text" id="usuario" name="usuario" required>
        </div>
        
        <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="fecha_nacimiento">Fecha de nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn">Registrarse</button>
    </form>
    
    <p class="text-center mt-3">
        ¿Ya tienes cuenta? <a href="<?php echo BASE_URL; ?>/public/login.php">Inicia sesión aquí</a>
    </p>
</div>

<?php
// Incluir el footer
require_once(__DIR__ . '/../includes/footer.php');
?>
