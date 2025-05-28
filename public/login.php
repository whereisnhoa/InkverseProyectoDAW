
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
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/auth.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<body class="auth-bg">
<div class="auth-container">
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

<?php 
if (file_exists($footer_file)) {
    include_once($footer_file);
} else {
    echo '</body></html>';
}
?>
