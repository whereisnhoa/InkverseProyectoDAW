
<?php
include '../includes/header.php';
include_once('../includes/funciones.php');

// Verificar si hay un usuario logueado
$usuario_logueado = isset($_SESSION['usuario_id']);

// Obtener ID de la obra
$arte_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($arte_id <= 0) {
    redirigirConMensaje(BASE_URL . '/comunidad/arte.php', 'Obra no especificada correctamente.', 'error');
    exit();
}

// Obtener datos de la obra
$sql = "SELECT ga.*, ca.nombre as categoria_nombre, ca.icono as categoria_icono, 
        u.usuario as autor_nombre, u.id as autor_id
        FROM galeria_arte ga
        JOIN categorias_arte ca ON ga.categoria_id = ca.id
        JOIN usuarios u ON ga.usuario_id = u.id
        WHERE ga.id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $arte_id);
$stmt->execute();
$result = $stmt->get_result();
$arte = $result->fetch_assoc();

if (!$arte) {
    redirigirConMensaje(BASE_URL . '/comunidad/arte.php', 'Obra no encontrada.', 'error');
    exit();
}

// Verificar restricción de edad para contenido adulto
if ($arte['publico_adulto'] && (!$usuario_logueado || (isset($_SESSION['edad']) && $_SESSION['edad'] < 18))) {
    redirigirConMensaje(BASE_URL . '/comunidad/arte.php', 'Esta obra contiene contenido para adultos. Debes ser mayor de 18 años para verla.', 'error');
    exit();
}

// Verificar si el usuario actual ha dado me gusta
$ha_dado_like = false;
if ($usuario_logueado) {
    $sql_like = "SELECT id FROM me_gusta_arte WHERE arte_id = ? AND usuario_id = ?";
    $stmt_like = $conexion->prepare($sql_like);
    $stmt_like->bind_param("ii", $arte_id, $_SESSION['usuario_id']);
    $stmt_like->execute();
    $result_like = $stmt_like->get_result();
    $ha_dado_like = $result_like->num_rows > 0;
}

// Obtener comentarios
$sql_comentarios = "SELECT c.*, u.usuario as nombre_usuario
                   FROM comentarios_arte c
                   JOIN usuarios u ON c.usuario_id = u.id
                   WHERE c.arte_id = ?
                   ORDER BY c.fecha_comentario DESC";
$stmt_comentarios = $conexion->prepare($sql_comentarios);
$stmt_comentarios->bind_param("i", $arte_id);
$stmt_comentarios->execute();
$result_comentarios = $stmt_comentarios->get_result();
?>

<div class="container">
    <div class="arte-detalle">
        <div class="arte-header">
            <h1><?php echo htmlspecialchars($arte['titulo']); ?></h1>
            
            <div class="arte-meta">
                <span class="arte-autor">
                    <i class="fas fa-user"></i> Por: 
                    <a href="<?php echo BASE_URL; ?>/usuario/perfil.php?id=<?php echo $arte['autor_id']; ?>">
                        <?php echo htmlspecialchars($arte['autor_nombre']); ?>
                    </a>
                </span>
                
                <span class="arte-categoria">
                    <i class="fas <?php echo htmlspecialchars($arte['categoria_icono']); ?>"></i>
                    <?php echo htmlspecialchars($arte['categoria_nombre']); ?>
                </span>
                
                <span class="arte-fecha">
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo date('d/m/Y', strtotime($arte['fecha_subida'])); ?>
                </span>
                
                <?php if ($arte['publico_adulto']): ?>
                    <span class="badge badge-adulto">+18</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="arte-imagen-container">
            <img src="<?php echo htmlspecialchars($arte['imagen']); ?>" alt="<?php echo htmlspecialchars($arte['titulo']); ?>" class="arte-imagen-completa">
        </div>
        
        <?php if (!empty($arte['descripcion'])): ?>
            <div class="arte-descripcion">
                <h3>Descripción</h3>
                <p><?php echo nl2br(htmlspecialchars($arte['descripcion'])); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="arte-acciones">
            <?php if ($usuario_logueado): ?>
                <form action="<?php echo BASE_URL; ?>/controllers/procesar-like-arte.php" method="POST" class="like-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                    <input type="hidden" name="arte_id" value="<?php echo $arte_id; ?>">
                    <input type="hidden" name="accion" value="<?php echo $ha_dado_like ? 'quitar' : 'dar'; ?>">
                    
                    <button type="submit" class="btn <?php echo $ha_dado_like ? 'btn-liked' : ''; ?>">
                        <i class="fas <?php echo $ha_dado_like ? 'fa-heart' : 'fa-heart'; ?>"></i>
                        <?php echo $ha_dado_like ? 'Me gusta (' . $arte['me_gusta'] . ')' : 'Me gusta (' . $arte['me_gusta'] . ')'; ?>
                    </button>
                </form>
                
                <?php if ($_SESSION['usuario_id'] == $arte['autor_id']): ?>
                    <a href="<?php echo BASE_URL; ?>/comunidad/editar_arte.php?id=<?php echo $arte_id; ?>" class="btn btn-outline">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    
                    <form action="<?php echo BASE_URL; ?>/controllers/procesar-eliminar-arte.php" method="POST" class="eliminar-form" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta obra? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        <input type="hidden" name="arte_id" value="<?php echo $arte_id; ?>">
                        
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="like-count">
                    <i class="fas fa-heart"></i> <?php echo $arte['me_gusta']; ?> me gusta
                </div>
            <?php endif; ?>
            
            <a href="<?php echo BASE_URL; ?>/comunidad/arte.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver a la galería
            </a>
        </div>
        
        <!-- Sección de comentarios -->
        <div class="comentarios-seccion">
            <h3>Comentarios (<?php echo $result_comentarios->num_rows; ?>)</h3>
            
            <?php if ($usuario_logueado): ?>
                <form action="<?php echo BASE_URL; ?>/controllers/procesar-comentario-arte.php" method="POST" class="comentario-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                    <input type="hidden" name="arte_id" value="<?php echo $arte_id; ?>">
                    
                    <div class="form-group">
                        <textarea name="comentario" rows="3" placeholder="Escribe un comentario..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-comment"></i> Comentar
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="comentarios-lista">
                <?php if ($result_comentarios->num_rows > 0): ?>
                    <?php while ($comentario = $result_comentarios->fetch_assoc()): ?>
                        <div class="comentario-item">
                            <div class="comentario-header">
                                <span class="comentario-autor">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($comentario['nombre_usuario']); ?>
                                </span>
                                <span class="comentario-fecha">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($comentario['fecha_comentario'])); ?>
                                </span>
                            </div>
                            <div class="comentario-contenido">
                                <?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-comentarios">No hay comentarios aún. ¡Sé el primero en comentar!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
