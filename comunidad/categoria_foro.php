
<?php
include '../includes/header.php';
include_once('../includes/funciones.php');

// Verificar si hay un usuario logueado
$usuario_logueado = isset($_SESSION['usuario_id']);

// Obtener ID de la categoría
$categoria_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($categoria_id <= 0) {
    redirigirConMensaje(BASE_URL . '/comunidad/foros.php', 'Categoría no especificada correctamente.', 'error');
    exit();
}

// Obtener información de la categoría
$sql_categoria = "SELECT * FROM categorias_foro WHERE id = ?";
$stmt_categoria = $conexion->prepare($sql_categoria);
$stmt_categoria->bind_param("i", $categoria_id);
$stmt_categoria->execute();
$result_categoria = $stmt_categoria->get_result();
$categoria = $result_categoria->fetch_assoc();

if (!$categoria) {
    redirigirConMensaje(BASE_URL . '/comunidad/foros.php', 'Categoría no encontrada.', 'error');
    exit();
}

// Configurar paginación
$temas_por_pagina = 15;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $temas_por_pagina;

// Obtener total de temas para la paginación
$sql_total = "SELECT COUNT(*) as total FROM temas_foro WHERE categoria_id = ?";
$stmt_total = $conexion->prepare($sql_total);
$stmt_total->bind_param("i", $categoria_id);
$stmt_total->execute();
$total_temas = $stmt_total->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_temas / $temas_por_pagina);

// Obtener temas de la categoría
$sql_temas = "SELECT tf.*, u.usuario as autor_nombre,
             (SELECT COUNT(*) FROM respuestas_foro WHERE tema_id = tf.id) as total_respuestas,
             (SELECT MAX(fecha_creacion) FROM respuestas_foro WHERE tema_id = tf.id) as ultima_respuesta
             FROM temas_foro tf
             JOIN usuarios u ON tf.usuario_id = u.id
             WHERE tf.categoria_id = ?
             ORDER BY 
                CASE WHEN tf.estado = 'destacado' THEN 0 ELSE 1 END,
                tf.ultima_actividad DESC
             LIMIT ?, ?";
$stmt_temas = $conexion->prepare($sql_temas);
$stmt_temas->bind_param("iii", $categoria_id, $offset, $temas_por_pagina);
$stmt_temas->execute();
$result_temas = $stmt_temas->get_result();
?>

<div class="container">
    <div class="categoria-foro-header">
        <h1>
            <i class="fas <?php echo htmlspecialchars($categoria['icono']); ?>"></i>
            <?php echo htmlspecialchars($categoria['nombre']); ?>
        </h1>
        <p><?php echo htmlspecialchars($categoria['descripcion']); ?></p>
    </div>
    
    <?php if ($usuario_logueado): ?>
        <div class="foro-acciones mb-4">
            <a href="<?php echo BASE_URL; ?>/comunidad/nuevo_tema.php?categoria=<?php echo $categoria_id; ?>" class="btn">
                <i class="fas fa-plus-circle"></i> Crear nuevo tema
            </a>
        </div>
    <?php endif; ?>
    
    <!-- Lista de temas -->
    <div class="temas-lista">
        <?php if ($result_temas && $result_temas->num_rows > 0): ?>
            <div class="temas-header">
                <div class="tema-titulo">Tema</div>
                <div class="tema-autor">Autor</div>
                <div class="tema-stats">Estadísticas</div>
                <div class="tema-actividad">Última actividad</div>
            </div>
            
            <?php while ($tema = $result_temas->fetch_assoc()): ?>
                <div class="tema-item">
                    <div class="tema-titulo">
                        <a href="<?php echo BASE_URL; ?>/comunidad/tema.php?id=<?php echo $tema['id']; ?>">
                            <?php if ($tema['estado'] == 'destacado'): ?>
                                <i class="fas fa-star tema-destacado" title="Tema destacado"></i>
                            <?php elseif ($tema['estado'] == 'cerrado'): ?>
                                <i class="fas fa-lock tema-cerrado" title="Tema cerrado"></i>
                            <?php else: ?>
                                <i class="fas fa-comments"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($tema['titulo']); ?>
                        </a>
                    </div>
                    <div class="tema-autor">
                        <?php echo htmlspecialchars($tema['autor_nombre']); ?>
                    </div>
                    <div class="tema-stats">
                        <span title="Respuestas"><i class="fas fa-reply"></i> <?php echo $tema['total_respuestas']; ?></span>
                        <span title="Vistas"><i class="fas fa-eye"></i> <?php echo $tema['vistas']; ?></span>
                    </div>
                    <div class="tema-actividad">
                        <?php 
                        $fecha_actividad = !empty($tema['ultima_respuesta']) ? $tema['ultima_respuesta'] : $tema['fecha_creacion'];
                        echo date('d/m/Y H:i', strtotime($fecha_actividad)); 
                        ?>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <div class="paginacion">
                    <?php if ($pagina_actual > 1): ?>
                        <a href="<?php echo BASE_URL; ?>/comunidad/categoria_foro.php?id=<?php echo $categoria_id; ?>&pagina=<?php echo $pagina_actual - 1; ?>" class="btn-pagina">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <div class="paginas-numeros">
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <a href="<?php echo BASE_URL; ?>/comunidad/categoria_foro.php?id=<?php echo $categoria_id; ?>&pagina=<?php echo $i; ?>" 
                               class="btn-pagina <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="<?php echo BASE_URL; ?>/comunidad/categoria_foro.php?id=<?php echo $categoria_id; ?>&pagina=<?php echo $pagina_actual + 1; ?>" class="btn-pagina">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-temas">
                <i class="fas fa-comments fa-3x"></i>
                <h3>No hay temas en esta categoría</h3>
                <p>Sé el primero en crear un tema de discusión.</p>
                
                <?php if ($usuario_logueado): ?>
                    <a href="<?php echo BASE_URL; ?>/comunidad/nuevo_tema.php?categoria=<?php echo $categoria_id; ?>" class="btn mt-3">
                        <i class="fas fa-plus-circle"></i> Crear nuevo tema
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn mt-3">
                        Iniciar sesión para crear un tema
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>/comunidad/foros.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Volver a los foros
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
