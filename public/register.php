<?php
// Incluir configuración para obtener la ruta base
require_once(__DIR__ . '/../includes/config.php');

// Incluir header con ruta absoluta
include_once(BASE_PATH . '/includes/header.php');
?>

<div class="container">
    <h2>Registrar nuevo usuario</h2>

    <!-- formulario de registro de usuario -->
    <form action="<?php echo BASE_URL; ?>/controllers/procesar-registro.php" method="POST">

        <div class="form-group">
            <label for="nombre">Nombre completo:</label>
            <input type="text" name="nombre" id="nombre" required>
        </div>

        <div class="form-group">
            <label for="usuario">Nombre de usuario:</label>
            <input type="text" name="usuario" id="usuario" required>
        </div>

        <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="fecha_nacimiento">Fecha de nacimiento:</label>
            <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required>
        </div>

        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-group checkbox-group">
            <label class="checkbox-inline">
                <input type="checkbox" name="acepta_terminos" id="acepta_terminos" required>
                Acepto los <a href="<?php echo BASE_URL; ?>/public/terminos.php" target="_blank">Términos y Condiciones</a> y la <a href="<?php echo BASE_URL; ?>/public/privacidad.php" target="_blank">Política de Privacidad</a>
            </label>
        </div>

        <button type="submit" class="btn">Registrarse</button>
    </form>

    <div class="mt-3">
        <p>¿Ya tienes una cuenta? <a href="<?php echo BASE_URL; ?>/public/login.php">Iniciar sesión</a></p>
    </div>
</div>

<?php include_once(BASE_PATH . '/includes/footer.php'); ?>

