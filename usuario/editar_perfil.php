<?php
// Iniciar sesión
session_start();

// Incluir configuración primero
require_once(__DIR__ . '/../includes/config.php');

// Incluir archivos necesarios
require_once(__DIR__ . '/../includes/funciones.php');
require_once(__DIR__ . '/../includes/conexion.php');

// Verificar que el usuario esté logueado
verificarLogin();

// Obtener información del usuario
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Incluir header
include_once(__DIR__ . '/../includes/header.php');
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/usuario.css">

<div class="editar-perfil-container">
    <div class="editar-perfil-card">
        <div class="card-header">
            <h1><i class="fas fa-user-edit"></i> Editar Perfil</h1>
            <a href="<?php echo BASE_URL; ?>/usuario/perfil.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al perfil
            </a>
        </div>
        
        <div class="card-body">
            <?php mostrarMensaje(); ?>
            
            <form action="<?php echo BASE_URL; ?>/controllers/actualizar-perfil.php" method="POST" class="form-editar-perfil">
                <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario">Nombre de usuario *</label>
                        <input type="text" id="usuario" name="usuario" class="form-control" 
                               value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                        <small>Este será tu nombre público en la plataforma</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo electrónico *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" 
                               value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" 
                               value="<?php echo $usuario['fecha_nacimiento'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="biografia">Biografía</label>
                    <textarea id="biografia" name="biografia" class="form-control" rows="4" 
                              placeholder="Cuéntanos algo sobre ti..."><?php echo htmlspecialchars($usuario['biografia'] ?? ''); ?></textarea>
                    <small>Máximo 500 caracteres</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sitio_web">Sitio web</label>
                        <input type="url" id="sitio_web" name="sitio_web" class="form-control" 
                               value="<?php echo htmlspecialchars($usuario['sitio_web'] ?? ''); ?>" 
                               placeholder="https://ejemplo.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="ubicacion">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" class="form-control" 
                               value="<?php echo htmlspecialchars($usuario['ubicacion'] ?? ''); ?>" 
                               placeholder="Ciudad, País">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Privacidad del perfil</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="privacidad" value="publico" 
                                   <?php echo (!isset($usuario['privacidad']) || $usuario['privacidad'] == 'publico') ? 'checked' : ''; ?>>
                            <span class="radio-custom"></span>
                            <div class="radio-content">
                                <strong>Público</strong>
                                <small>Todos pueden ver tu perfil y obras</small>
                            </div>
                        </label>
                        
                        <label class="radio-option">
                            <input type="radio" name="privacidad" value="privado" 
                                   <?php echo (isset($usuario['privacidad']) && $usuario['privacidad'] == 'privado') ? 'checked' : ''; ?>>
                            <span class="radio-custom"></span>
                            <div class="radio-content">
                                <strong>Privado</strong>
                                <small>Solo tus seguidores pueden ver tu perfil</small>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar cambios
                    </button>
                    <a href="<?php echo BASE_URL; ?>/usuario/perfil.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.editar-perfil-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 0 20px;
}

.editar-perfil-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    padding: 25px 30px;
    border-bottom: 1px solid #eee;
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h1 {
    margin: 0;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

.btn-volver {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-volver:hover {
    background: rgba(255,255,255,0.3);
    color: white;
}

.card-body {
    padding: 30px;
}

.form-editar-perfil {
    max-width: 100%;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
    background: #fff;
}

.form-control:focus {
    border-color: #4361ee;
    outline: none;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.form-control::placeholder {
    color: #999;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.radio-group {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.radio-option {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.radio-option:hover {
    border-color: #4361ee;
    background: #f8f9ff;
}

.radio-option input[type="radio"] {
    display: none;
}

.radio-custom {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 50%;
    position: relative;
    flex-shrink: 0;
    margin-top: 2px;
}

.radio-option input[type="radio"]:checked + .radio-custom {
    border-color: #4361ee;
}

.radio-option input[type="radio"]:checked + .radio-custom::after {
    content: '';
    width: 10px;
    height: 10px;
    background: #4361ee;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.radio-content {
    flex: 1;
}

.radio-content strong {
    display: block;
    color: #333;
    margin-bottom: 4px;
}

.radio-content small {
    color: #666;
    font-size: 12px;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-primary {
    background: #4361ee;
    color: white;
}

.btn-primary:hover {
    background: #3a56d4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.btn-outline {
    background: transparent;
    border: 2px solid #e1e5e9;
    color: #666;
}

.btn-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
}

.alerta {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alerta-exito {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alerta-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alerta-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

@media (max-width: 768px) {
    .editar-perfil-container {
        margin: 20px auto;
        padding: 0 15px;
    }
    
    .card-header {
        padding: 20px;
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        justify-content: center;
    }
}
</style>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
