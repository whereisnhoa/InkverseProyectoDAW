
<?php
include '../includes/header.php';
include_once('../includes/funciones.php');

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['es_admin']) || !$_SESSION['es_admin']) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'No tienes permisos para acceder a esta página.', 'error');
    exit();
}

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || !verificarTokenCSRF($_POST['csrf_token'])) {
        redirigirConMensaje(BASE_URL . '/admin/enviar-notificacion.php', 'Error de validación. Por favor, intenta nuevamente.', 'error');
        exit();
    }
    
    $mensaje = limpiarDato($_POST['mensaje']);
    $url = limpiarDato($_POST['url']);
    $tipo_envio = $_POST['tipo_envio'];
    $usuarios_seleccionados = isset($_POST['usuarios']) ? $_POST['usuarios'] : [];
    
    if (empty($mensaje)) {
        redirigirConMensaje(BASE_URL . '/admin/enviar-notificacion.php', 'El mensaje no puede estar vacío.', 'error');
        exit();
    }
    
    // Enviar notificación según el tipo de envío
    if ($tipo_envio === 'todos') {
        // Obtener todos los usuarios
        $sql = "SELECT id FROM usuarios";
        $result = $conexion->query($sql);
        
        $contador = 0;
        while ($usuario = $result->fetch_assoc()) {
            if (crearNotificacion($usuario['id'], 'sistema', $mensaje, $url)) {
                $contador++;
            }
        }
        
        redirigirConMensaje(BASE_URL . '/admin/enviar-notificacion.php', "Notificación enviada a {$contador} usuarios.", 'exito');
    } elseif ($tipo_envio === 'seleccionados' && !empty($usuarios_seleccionados)) {
        $contador = 0;
        foreach ($usuarios_seleccionados as $usuario_id) {
            if (crearNotificacion($usuario_id, 'sistema', $mensaje, $url)) {
                $contador++;
            }
        }
        
        redirigirConMensaje(BASE_URL . '/admin/enviar-notificacion.php', "Notificación enviada a {$contador} usuarios seleccionados.", 'exito');
    } else {
        redirigirConMensaje(BASE_URL . '/admin/enviar-notificacion.php', 'No se seleccionaron usuarios para enviar la notificación.', 'error');
    }
}

// Obtener lista de usuarios para el formulario
$sql_usuarios = "SELECT id, usuario, email FROM usuarios ORDER BY usuario";
$result_usuarios = $conexion->query($sql_usuarios);
?>

<div class="container">
    <h1>Enviar Notificación del Sistema</h1>
    
    <form action="<?php echo BASE_URL; ?>/admin/enviar-notificacion.php" method="POST" class="form">
        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
        
        <div class="form-group">
            <label for="mensaje">Mensaje:</label>
            <textarea id="mensaje" name="mensaje" rows="4" required></textarea>
            <small>Escribe el mensaje que se enviará a los usuarios.</small>
        </div>
        
        <div class="form-group">
            <label for="url">URL (opcional):</label>
            <input type="text" id="url" name="url" placeholder="https://...">
            <small>Si deseas que la notificación enlace a una página específica.</small>
        </div>
        
        <div class="form-group">
            <label>Tipo de envío:</label>
            <div class="radio-group">
                <label class="radio-inline">
                    <input type="radio" name="tipo_envio" value="todos" checked> Enviar a todos los usuarios
                </label>
                <label class="radio-inline">
                    <input type="radio" name="tipo_envio" value="seleccionados"> Enviar a usuarios seleccionados
                </label>
            </div>
        </div>
        
        <div class="form-group" id="usuarios-container" style="display: none;">
            <label>Seleccionar usuarios:</label>
            <div class="usuarios-lista">
                <?php if ($result_usuarios && $result_usuarios->num_rows > 0): ?>
                    <?php while ($usuario = $result_usuarios->fetch_assoc()): ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="usuarios[]" value="<?php echo $usuario['id']; ?>">
                            <?php echo htmlspecialchars($usuario['usuario']); ?> (<?php echo htmlspecialchars($usuario['email']); ?>)
                        </label>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No hay usuarios disponibles.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <button type="submit" class="btn">Enviar Notificación</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoEnvioRadios = document.querySelectorAll('input[name="tipo_envio"]');
    const usuariosContainer = document.getElementById('usuarios-container');
    
    tipoEnvioRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'seleccionados') {
                usuariosContainer.style.display = 'block';
            } else {
                usuariosContainer.style.display = 'none';
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
