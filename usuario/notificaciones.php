<?php
// Iniciar sesión
session_start();

// Incluir configuración y archivos necesarios
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/funciones.php');
require_once(__DIR__ . '/../includes/conexion.php');

// Cargar idioma
require_once(__DIR__ . '/../includes/idioma.php');

// Verificar que el usuario esté logueado
verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

// Configurar filtros
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todas';
$filtro_leidas = isset($_GET['leidas']) ? $_GET['leidas'] : 'todas';

// Configurar paginación
$notificaciones_por_pagina = 15;
$pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_actual - 1) * $notificaciones_por_pagina;

// Construir consulta con filtros
$sql_where = "WHERE usuario_id = ?";
$params = [$usuario_id];
$param_types = "i";

if ($filtro_tipo !== 'todas') {
    $sql_where .= " AND tipo = ?";
    $params[] = $filtro_tipo;
    $param_types .= "s";
}

if ($filtro_leidas === 'leidas') {
    $sql_where .= " AND leida = 1";
} elseif ($filtro_leidas === 'no_leidas') {
    $sql_where .= " AND leida = 0";
}

// Obtener total de notificaciones para la paginación
$sql_total = "SELECT COUNT(*) as total FROM notificaciones $sql_where";
$stmt_total = $conexion->prepare($sql_total);
$stmt_total->bind_param($param_types, ...$params);
$stmt_total->execute();
$total_notificaciones = $stmt_total->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_notificaciones / $notificaciones_por_pagina);

// Obtener notificaciones paginadas
$sql = "SELECT * FROM notificaciones 
        $sql_where 
        ORDER BY fecha DESC 
        LIMIT ?, ?";
$params[] = $offset;
$params[] = $notificaciones_por_pagina;
$param_types .= "ii";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$notificaciones = $stmt->get_result();

// Obtener estadísticas
$sql_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN leido = 0 THEN 1 ELSE 0 END) as no_leidas,
                SUM(CASE WHEN tipo = 'comentario' THEN 1 ELSE 0 END) as comentarios,
                SUM(CASE WHEN tipo = 'like' THEN 1 ELSE 0 END) as likes,
                SUM(CASE WHEN tipo = 'nueva_obra' THEN 1 ELSE 0 END) as nuevas_obras
              FROM notificaciones WHERE usuario_id = ?";
$stmt_stats = $conexion->prepare($sql_stats);
$stmt_stats->bind_param("i", $usuario_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

// Incluir header
include_once(__DIR__ . '/../includes/header.php');
?>

<div class="container">
    <div class="notificaciones-container">
        <!-- Header de la página -->
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-bell"></i> <?php echo $TEXTOS['notificaciones_titulo']; ?></h1>
                <p><?php echo $TEXTOS['notificaciones_subtitulo']; ?></p>
            </div>
            <div class="page-actions">
                <?php if ($stats['no_leidas'] > 0): ?>
                    <a href="<?php echo BASE_URL; ?>/controllers/procesar-notificaciones.php?accion=marcar_todas_leidas" 
                       class="btn btn-primary" id="marcar-todas-leidas">
                        <i class="fas fa-check-double"></i> <?php echo $TEXTOS['marcar_todas_leidas']; ?>
                    </a>
                <?php endif; ?>
                <button class="btn btn-outline" id="btn-configuracion">
                    <i class="fas fa-cog"></i> <?php echo $TEXTOS['configuracion']; ?>
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-number"><?php echo $stats['total']; ?></span>
                    <span class="stat-label"><?php echo $TEXTOS['total']; ?></span>
                </div>
            </div>
            
            <div class="stat-card <?php echo $stats['no_leidas'] > 0 ? 'highlight' : ''; ?>">
                <div class="stat-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-number"><?php echo $stats['no_leidas']; ?></span>
                    <span class="stat-label"><?php echo $TEXTOS['sin_leer']; ?></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-number"><?php echo $stats['comentarios']; ?></span>
                    <span class="stat-label"><?php echo $TEXTOS['comentarios']; ?></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-number"><?php echo $stats['likes']; ?></span>
                    <span class="stat-label"><?php echo $TEXTOS['me_gusta']; ?></span>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-container">
            <form method="GET" class="filtros-form">
                <div class="filtro-grupo">
                    <label for="tipo"><?php echo $TEXTOS['tipo']; ?></label>
                    <select name="tipo" id="tipo">
                        <option value="todas" <?php echo $filtro_tipo === 'todas' ? 'selected' : ''; ?>><?php echo $TEXTOS['todas']; ?></option>
                        <option value="comentario" <?php echo $filtro_tipo === 'comentario' ? 'selected' : ''; ?>><?php echo $TEXTOS['comentario']; ?></option>
                        <option value="like" <?php echo $filtro_tipo === 'like' ? 'selected' : ''; ?>><?php echo $TEXTOS['like']; ?></option>
                        <option value="respuesta_foro" <?php echo $filtro_tipo === 'respuesta_foro' ? 'selected' : ''; ?>><?php echo $TEXTOS['respuesta_foro']; ?></option>
                        <option value="nueva_obra" <?php echo $filtro_tipo === 'nueva_obra' ? 'selected' : ''; ?>><?php echo $TEXTOS['nueva_obra']; ?></option>
                        <option value="sistema" <?php echo $filtro_tipo === 'sistema' ? 'selected' : ''; ?>><?php echo $TEXTOS['sistema']; ?></option>
                    </select>
                </div>

                <div class="filtro-grupo">
                    <label for="leidas"><?php echo $TEXTOS['estado']; ?></label>
                    <select name="leidas" id="leidas">
                        <option value="todas" <?php echo $filtro_leidas === 'todas' ? 'selected' : ''; ?>><?php echo $TEXTOS['todas']; ?></option>
                        <option value="no_leidas" <?php echo $filtro_leidas === 'no_leidas' ? 'selected' : ''; ?>><?php echo $TEXTOS['no_leidas']; ?></option>
                        <option value="leidas" <?php echo $filtro_leidas === 'leidas' ? 'selected' : ''; ?>><?php echo $TEXTOS['leidas']; ?></option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> <?php echo $TEXTOS['filtrar']; ?>
                </button>
                
                <a href="<?php echo BASE_URL; ?>/usuario/notificaciones.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> <?php echo $TEXTOS['limpiar']; ?>
                </a>
            </form>
        </div>

        <!-- Lista de notificaciones -->
        <?php if ($total_notificaciones > 0): ?>
            <div class="notificaciones-lista">
                <?php while ($notificacion = $notificaciones->fetch_assoc()): ?>
                    <div class="notificacion-item <?php echo $notificacion['leida'] ? 'leida' : 'no-leida'; ?>" 
                         data-notificacion-id="<?php echo $notificacion['id']; ?>">
                        
                        <div class="notificacion-icon">
                            <?php
                            $icon_class = 'bell';
                            $icon_color = '#4361ee';
                            switch ($notificacion['tipo']) {
                                case 'comentario':
                                    $icon_class = 'comment';
                                    $icon_color = '#27ae60';
                                    break;
                                case 'like':
                                    $icon_class = 'heart';
                                    $icon_color = '#e74c3c';
                                    break;
                                case 'respuesta_foro':
                                    $icon_class = 'reply';
                                    $icon_color = '#f39c12';
                                    break;
                                case 'nueva_obra':
                                    $icon_class = 'book';
                                    $icon_color = '#9b59b6';
                                    break;
                                case 'sistema':
                                    $icon_class = 'info-circle';
                                    $icon_color = '#3498db';
                                    break;
                            }
                            ?>
                            <i class="fas fa-<?php echo $icon_class; ?>" style="color: <?php echo $icon_color; ?>"></i>
                        </div>

                        <div class="notificacion-content">
                            <div class="notificacion-mensaje">
                                <?php echo htmlspecialchars($notificacion['mensaje']); ?>
                            </div>
                            <div class="notificacion-meta">
                                <span class="notificacion-tiempo">
                                    <i class="fas fa-clock"></i>
                                    <?php echo time_elapsed_string($notificacion['fecha']); ?>
                                </span>
                                <span class="notificacion-tipo">
                                    <?php
                                    $tipos = [
                                        'comentario' => $TEXTOS['comentario'],
                                        'like' => $TEXTOS['like'],
                                        'respuesta_foro' => $TEXTOS['respuesta_foro'],
                                        'nueva_obra' => $TEXTOS['nueva_obra'],
                                        'sistema' => $TEXTOS['sistema']
                                    ];
                                    echo $tipos[$notificacion['tipo']] ?? 'Notificación';
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="notificacion-acciones">
                            <?php if (!$notificacion['leida']): ?>
                                <button class="btn-accion btn-marcar-leida" 
                                        data-notificacion-id="<?php echo $notificacion['id']; ?>"
                                        title="<?php echo $TEXTOS['marcar_leida']; ?>">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if (!empty($notificacion['url'])): ?>
                                <a href="<?php echo htmlspecialchars($notificacion['url']); ?>" 
                                   class="btn-accion btn-ver" title="<?php echo $TEXTOS['ver']; ?>">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            <?php endif; ?>
                            
                            <button class="btn-accion btn-eliminar" 
                                    data-notificacion-id="<?php echo $notificacion['id']; ?>"
                                    title="<?php echo $TEXTOS['eliminar']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <div class="paginacion">
                    <?php if ($pagina_actual > 1): ?>
                        <a href="?pagina=<?php echo $pagina_actual - 1; ?>&tipo=<?php echo $filtro_tipo; ?>&leidas=<?php echo $filtro_leidas; ?>" 
                           class="btn-pagina">
                            <i class="fas fa-chevron-left"></i> <?php echo $TEXTOS['anterior']; ?>
                        </a>
                    <?php endif; ?>
                    
                    <div class="paginas-numeros">
                        <?php
                        $inicio = max(1, $pagina_actual - 2);
                        $fin = min($total_paginas, $pagina_actual + 2);
                        
                        if ($inicio > 1): ?>
                            <a href="?pagina=1&tipo=<?php echo $filtro_tipo; ?>&leidas=<?php echo $filtro_leidas; ?>" class="btn-pagina">1</a>
                            <?php if ($inicio > 2): ?>
                                <span class="pagina-separador">...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                            <a href="?pagina=<?php echo $i; ?>&tipo=<?php echo $filtro_tipo; ?>&leidas=<?php echo $filtro_leidas; ?>" 
                               class="btn-pagina <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($fin < $total_paginas): ?>
                            <?php if ($fin < $total_paginas - 1): ?>
                                <span class="pagina-separador">...</span>
                            <?php endif; ?>
                            <a href="?pagina=<?php echo $total_paginas; ?>&tipo=<?php echo $filtro_tipo; ?>&leidas=<?php echo $filtro_leidas; ?>" class="btn-pagina"><?php echo $total_paginas; ?></a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="?pagina=<?php echo $pagina_actual + 1; ?>&tipo=<?php echo $filtro_tipo; ?>&leidas=<?php echo $filtro_leidas; ?>" 
                           class="btn-pagina">
                            <?php echo $TEXTOS['siguiente']; ?> <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-notificaciones">
                <div class="no-notificaciones-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3><?php echo $TEXTOS['no_notificaciones']; ?></h3>
                <p><?php echo $TEXTOS['no_notificaciones_desc']; ?></p>
                <div class="no-notificaciones-acciones">
                    <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-primary">
                        <i class="fas fa-compass"></i> <?php echo $TEXTOS['explorar_contenido']; ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de configuración -->
<div id="modal-configuracion" class="modal">
    <div class="modal-contenido">
        <div class="modal-header">
            <h3><?php echo $TEXTOS['config_titulo']; ?></h3>
            <button class="btn-cerrar-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-configuracion">
                <div class="config-grupo">
                    <h4><?php echo $TEXTOS['tipos_notificaciones']; ?></h4>
                    <label class="checkbox-label">
                        <input type="checkbox" name="comentarios" checked>
                        <span class="checkmark"></span>
                        <?php echo $TEXTOS['noti_comentarios']; ?>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="likes" checked>
                        <span class="checkmark"></span>
                        <?php echo $TEXTOS['noti_likes']; ?>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="respuestas" checked>
                        <span class="checkmark"></span>
                        <?php echo $TEXTOS['noti_respuestas']; ?>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="nuevas_obras" checked>
                        <span class="checkmark"></span>
                        <?php echo $TEXTOS['noti_nuevas_obras']; ?>
                    </label>
                </div>
                
                <div class="config-grupo">
                    <h4><?php echo $TEXTOS['frecuencia_limpieza']; ?></h4>
                    <select name="limpieza">
                        <option value="nunca"><?php echo $TEXTOS['nunca_eliminar']; ?></option>
                        <option value="30"><?php echo $TEXTOS['eliminar_30']; ?></option>
                        <option value="60"><?php echo $TEXTOS['eliminar_60']; ?></option>
                        <option value="90"><?php echo $TEXTOS['eliminar_90']; ?></option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancelar-config"><?php echo $TEXTOS['cancelar']; ?></button>
            <button class="btn btn-primary" id="guardar-config"><?php echo $TEXTOS['guardar_cambios']; ?></button>
        </div>
    </div>
</div>

<style>

.notificaciones-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.page-title h1 {
    margin: 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-title h1 i {
    color: #4361ee;
}

.page-title p {
    margin: 5px 0 0 0;
    color: #666;
}

.page-actions {
    display: flex;
    gap: 10px;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.2s;
}

.stat-card.highlight {
    border-left: 4px solid #e74c3c;
    background: linear-gradient(135deg, #fff 0%, #ffebee 100%);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4361ee, #3a56d4);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.stat-content {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.stat-label {
    font-size: 14px;
    color: #666;
}

.filtros-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 30px;
}

.filtros-form {
    display: flex;
    gap: 20px;
    align-items: end;
    flex-wrap: wrap;
}

.filtro-grupo {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filtro-grupo label {
    font-weight: 500;
    color: #333;
    font-size: 14px;
}

.filtro-grupo select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    min-width: 150px;
}

.notificaciones-lista {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.notificacion-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s;
    position: relative;
}

.notificacion-item:last-child {
    border-bottom: none;
}

.notificacion-item.no-leida {
    background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);
    border-left: 4px solid #4361ee;
}

.notificacion-item:hover {
    background: #f8f9fa;
}

.notificacion-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.notificacion-content {
    flex: 1;
}

.notificacion-mensaje {
    color: #333;
    line-height: 1.5;
    margin-bottom: 8px;
}

.notificacion-meta {
    display: flex;
    gap: 15px;
    font-size: 13px;
    color: #666;
}

.notificacion-tiempo {
    display: flex;
    align-items: center;
    gap: 4px;
}

.notificacion-tipo {
    padding: 2px 8px;
    background: #f0f0f0;
    border-radius: 12px;
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 500;
}

.notificacion-acciones {
    display: flex;
    gap: 5px;
    flex-shrink: 0;
}

.btn-accion {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: #f0f0f0;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12px;
}

.btn-accion:hover {
    background: #e0e0e0;
}

.btn-marcar-leida:hover {
    background: #27ae60;
    color: white;
}

.btn-ver:hover {
    background: #4361ee;
    color: white;
}

.btn-eliminar:hover {
    background: #e74c3c;
    color: white;
}

.paginacion {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn-pagina {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    text-decoration: none;
    color: #666;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-pagina:hover {
    border-color: #4361ee;
    color: #4361ee;
}

.btn-pagina.active {
    background: #4361ee;
    color: white;
    border-color: #4361ee;
}

.paginas-numeros {
    display: flex;
    gap: 5px;
    align-items: center;
}

.pagina-separador {
    color: #999;
    padding: 0 5px;
}

.no-notificaciones {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.no-notificaciones-icon {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.no-notificaciones h3 {
    color: #333;
    margin-bottom: 10px;
}

.no-notificaciones p {
    color: #666;
    margin-bottom: 30px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn {
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #4361ee;
    color: white;
}

.btn-primary:hover {
    background: #3a56d4;
}

.btn-outline {
    background: transparent;
    border: 1px solid #ddd;
    color: #666;
}

.btn-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-contenido {
    background-color: #fff;
    margin: 5% auto;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.btn-cerrar-modal {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.modal-body {
    padding: 20px;
}

.config-grupo {
    margin-bottom: 25px;
}

.config-grupo h4 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 16px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 3px;
    position: relative;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
    background-color: #4361ee;
    border-color: #4361ee;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.config-grupo select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #eee;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .page-actions {
        width: 100%;
        justify-content: stretch;
    }
    
    .page-actions .btn {
        flex: 1;
        justify-content: center;
    }
    
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filtros-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filtro-grupo {
        width: 100%;
    }
    
    .notificacion-item {
        flex-direction: column;
        gap: 10px;
    }
    
    .notificacion-acciones {
        align-self: flex-end;
    }
    
    .paginacion {
        flex-direction: column;
        gap: 15px;
    }
    
    .paginas-numeros {
        order: 2;
    }
}

</style>

<!-- JavaScript y animaciones para la página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-marcar-leida').forEach(button => {
        button.addEventListener('click', function() {
            const notificacionId = this.dataset.notificacionId;
            marcarComoLeida(notificacionId);
        });
    });
    document.querySelectorAll('.btn-eliminar').forEach(button => {
        button.addEventListener('click', function() {
            const notificacionId = this.dataset.notificacionId;
            if (confirm('<?php echo $TEXTOS['confirmar_eliminar'] ?? "¿Estás seguro de que quieres eliminar esta notificación?"; ?>')) {
                eliminarNotificacion(notificacionId);
            }
        });
    });
    const modal = document.getElementById('modal-configuracion');
    const btnConfig = document.getElementById('btn-configuracion');
    const btnCerrar = document.querySelector('.btn-cerrar-modal');
    const btnCancelar = document.getElementById('cancelar-config');
    btnConfig.addEventListener('click', () => modal.style.display = 'block');
    btnCerrar.addEventListener('click', () => modal.style.display = 'none');
    btnCancelar.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });

    function marcarComoLeida(notificacionId) {
        fetch('<?php echo BASE_URL; ?>/controllers/procesar-notificaciones.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `accion=marcar_leida&id=${notificacionId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-notificacion-id="${notificacionId}"]`);
                item.classList.remove('no-leida');
                item.classList.add('leida');
                item.querySelector('.btn-marcar-leida').remove();
                mostrarMensaje('<?php echo $TEXTOS['marcada_leida']; ?>', 'success');
            }
        })
        .catch(error => console.error('Error:', error));
    }
    function eliminarNotificacion(notificacionId) {
        fetch('<?php echo BASE_URL; ?>/controllers/procesar-notificaciones.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `accion=eliminar&id=${notificacionId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-notificacion-id="${notificacionId}"]`);
                item.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => item.remove(), 300);
                mostrarMensaje('<?php echo $TEXTOS['eliminada']; ?>', 'success');
            }
        })
        .catch(error => console.error('Error:', error));
    }
    function mostrarMensaje(mensaje, tipo) {
        const alerta = document.createElement('div');
        alerta.className = `alerta alerta-${tipo}`;
        alerta.textContent = mensaje;
        alerta.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 1001;
            padding: 12px 20px; border-radius: 6px; color: white;
            background: ${tipo === 'success' ? '#27ae60' : '#e74c3c'};
            animation: slideIn 0.3s ease-out;
        `;
        document.body.appendChild(alerta);
        setTimeout(() => alerta.remove(), 3000);
    }
});
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.8); }
    }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(style);
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>