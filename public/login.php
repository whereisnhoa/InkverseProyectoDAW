
<?php
// Iniciar sesión
session_start();

// Determinar la ruta base del proyecto de forma más robusta
$document_root = $_SERVER['DOCUMENT_ROOT'];
$current_file = str_replace('\\', '/', __FILE__);
$document_root = str_replace('\\', '/', $document_root);

// Encontrar la posición de 'public' en la ruta actual
$public_pos = strrpos($current_file, '/public/');
if ($public_pos !== false) {
    $project_path = substr($current_file, 0, $public_pos);
} else {
    // Fallback: asumir que estamos en la carpeta public
    $project_path = dirname(dirname(__FILE__));
}

// Calcular la ruta relativa para BASE_URL
$relative_path = str_replace($document_root, '', $project_path);

// Definir BASE_URL si no está definido
if (!defined('BASE_URL')) {
    define('BASE_URL', $relative_path);
}

// Verificar que los archivos existan antes de incluirlos
$header_file = $project_path . '/includes/header.php';
$footer_file = $project_path . '/includes/footer.php';

if (file_exists($header_file)) {
    include_once($header_file);
} else {
    // Header básico si no se encuentra el archivo
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Iniciar Sesión - Proyecto</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            .password-input-container { position: relative; display: flex; align-items: center; }
            .password-input-container input { padding-right: 45px; }
            .password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; font-size: 16px; padding: 5px; border-radius: 3px; transition: color 0.2s, background-color 0.2s; }
            .password-toggle:hover { color: #333; background-color: #f0f0f0; }
            .password-toggle:focus { outline: 2px solid #007bff; outline-offset: 2px; }
            .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; }
            .btn:hover { background: #0056b3; }
            .mt-3 { margin-top: 20px; }
            a { color: #007bff; text-decoration: none; }
            a:hover { text-decoration: underline; }
            .text-center { text-align: center; }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>';
}
?>

<div class="container">
    <h2>Iniciar sesión</h2>

    <form action="<?php echo BASE_URL; ?>/controllers/procesar-login.php" method="POST" id="loginForm">

        <div class="form-group">
            <label for="usuario">Nombre de usuario o email:</label>
            <input type="text" name="usuario" id="usuario" required>
        </div>

        <div class="form-group">
            <label for="password">Contraseña:</label>
            <div class="password-input-container">
                <input type="password" name="password" id="password" required>
                <button type="button" class="password-toggle" id="togglePassword" aria-label="Mostrar contraseña">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn">Entrar</button>
    </form>

    <div class="mt-3 text-center">
        <p>¿No tienes una cuenta? <a href="<?php echo BASE_URL; ?>/public/register.php">Registrarse</a></p>
        <p><a href="<?php echo BASE_URL; ?>/public/recuperar-password.php">¿Olvidaste tu contraseña?</a></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    
    // Función para alternar visibilidad de contraseña
    function togglePasswordVisibility() {
        const icon = togglePassword.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            togglePassword.setAttribute('aria-label', 'Ocultar contraseña');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            togglePassword.setAttribute('aria-label', 'Mostrar contraseña');
        }
    }
    
    // Event listener para el botón de mostrar/ocultar contraseña
    togglePassword.addEventListener('click', function() {
        togglePasswordVisibility();
    });
    
    // Opcional: Permitir alternar con Enter cuando el botón tiene foco
    togglePassword.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            togglePasswordVisibility();
        }
    });
});
</script>

<?php 
if (file_exists($footer_file)) {
    include_once($footer_file);
} else {
    echo '</body></html>';
}
?>
