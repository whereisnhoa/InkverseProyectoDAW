
<?php
include '../includes/header.php';
include_once('../includes/funciones.php');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    redirigirConMensaje(BASE_URL . '/public/login.php', 'Debes iniciar sesión para ver tus notificaciones.', 'error');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Configurar paginación
$notificaciones_por_pagina = 20;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $notificaciones_por_pagina;

// Obtener total de notificaciones para la paginación
$sql_total = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ?";
$stmt_total = $conexion->prepare($sql_total);
$stmt_total->bind_param("i", $usuario_id);
$stmt_total->execute();
$total_notificaciones = $stmt_total->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_notificaciones / $notificaciones_por_pagina);

// Obtener notificaciones paginadas
$sql = "SELECT * FROM notificaciones 
        WHERE usuario_id = ? 
        ORDER BY fecha_creacion DESC 
        LIMIT ?, ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iii", $usuario_id, $offset, $notificaciones_por_pagina);
$stmt->execute();
$notificaciones = $stmt->get_result();
?>

<div class="container">
    <h1>Mis Notificaciones</h1>
    
    <?php if ($total_notificaciones > 0): ?>
        <div class="notification-actions">
            <a href="<?php echo BASE_URL; ?>/controllers/procesar-notificaciones.php?accion=marcar_todas_leidas" class="btn btn-sm">
                <i class="fas fa-check-double"></i> Marcar todas como leídas
            </a>
        </div>
        
        <div class="notification-list-full">
            <?php while ($notificacion = $notificaciones->fetch_assoc()): ?>
                <div class="notification-item <?php echo $notificacion['leida'] ? '' : 'unread'; ?>">
                    <div class="notification-icon">
                        <?php
                        $icon = 'bell';
                        switch ($notificacion['tipo']) {
                            case 'comentario':
                                $icon = 'comment';
                                break;
                            case 'like':
                                $icon = 'heart';
                                break;
                            case 'respuesta_foro':
                                $icon = 'reply';
                                break;
                            case 'nueva_obra':
                                $icon = 'book';
                                break;
                            case 'sistema':
                                $icon = 'info-circle';
                                break;
                        }
                        ?>
                        <i class="fas fa-<?php echo $icon; ?>"></i>
                    </div>
                    <div class="notification-content">
                        <p><?php echo htmlspecialchars($notificacion['mensaje']); ?></p>
                        <div class="notification-meta">
                            <span class="notification-time">
                                <?php echo date('d/m/Y H:i', strtotime($notificacion['fecha_creacion'])); ?>
                                (<?php echo time_elapsed_string($notificacion['fecha_creacion']); ?>)
                            </span>
                            
                            <div class="notification-actions">
                                <?php if (!$notificacion['leida']): ?>
                                    <a href="<?php echo BASE_URL; ?>/controllers/procesar-notificaciones.php?accion=marcar_leida&id=<?php echo $notificacion['id']; ?>" class="btn btn-sm">
                                        <i class="fas fa-check"></i> Marcar como leída
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($notificacion['url'])): ?>
                                    <a href="<?php echo $notificacion['url']; ?>" class="btn btn-sm">
                                        <i class="fas fa-external-link-alt"></i> Ver
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Paginación -->
        <?php if ($total_paginas > 1): ?>
            <div class="paginacion">
                <?php if ($pagina_actual > 1): ?>
                    <a href="<?php echo BASE_URL; ?>/usuario/notificaciones.php?pagina=<?php echo $pagina_actual - 1; ?>" class="btn-pagina">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>
                
                <div class="paginas-numeros">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="<?php echo BASE_URL; ?>/usuario/notificaciones.php?pagina=<?php echo $i; ?>" 
                           class="btn-pagina <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                
                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="<?php echo BASE_URL; ?>/usuario/notificaciones.php?pagina=<?php echo $pagina_actual + 1; ?>" class="btn-pagina">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="notification-empty">
            <i class="fas fa-bell-slash fa-3x"></i>
            <h3>No tienes notificaciones</h3>
            <p>Cuando recibas notificaciones, aparecerán aquí.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
