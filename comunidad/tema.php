
<?php
include '../includes/header.php';
include_once('../includes/funciones.php');

// Verificar si hay un usuario logueado
$usuario_logueado = isset($_SESSION['usuario_id']);

// Obtener ID del tema
$tema_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($tema_id <= 0) {
    redirigirConMensaje(BASE_URL . '/comunidad/foros.php', 'Tema no especificado correctamente.', 'error');
    exit();
}

// Incrementar contador de vistas
$sql_vistas = "UPDATE temas_foro SET vistas = vistas + 1 WHERE id = ?";
$stmt_vistas = $conexion->prepare($sql_vistas);
$stmt_vistas->bind_param("i", $tema_id);
$stmt_vistas->execute();

// Obtener información del tema
$sql_tema = "SELECT tf.*, cf.nombre as categoria_nombre, cf.id as categoria_id, 
            u.usuario as autor_nombre, u.id as autor_id
            FROM temas_foro tf
            JOIN categorias_foro cf ON tf.categoria_id = cf.id
            JOIN usuarios u ON tf.usuario_id = u.id
            WHERE tf.id = ?";
$stmt_tema = $conexion->prepare($sql_tema);
$stmt_tema->bind_param("i", $tema_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();
$tema = $result_tema->fetch_assoc();

if (!$tema) {
    redirigirConMensaje(BASE_URL . '/comunidad/foros.php', 'Tema no encontrado.', 'error');
    exit();
}

// Configurar paginación para respuestas
$respuestas_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $respuestas_por_pagina;

// Obtener total de respuestas para la paginación
$sql_total = "SELECT COUNT(*) as total FROM respuestas_foro WHERE tema_id = ?";
$stmt_total = $conexion->prepare($sql_total);
$stmt_total->bind_param("i", $tema_id);
$stmt_total->execute();
$total_respuestas = $stmt_total->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_respuestas / $respuestas_por_pagina);

// Obtener respuestas del tema
$sql_respuestas = "SELECT rf.*, u.usuario as autor_nombre, u.id as autor_id
                  FROM respuestas_foro rf
                  JOIN usuarios u ON rf.usuario_id = u.id
                  WHERE rf.tema_id = ?
                  ORDER BY rf.fecha_creacion ASC
                  LIMIT ?, ?";
$stmt_respuestas = $conexion->prepare($sql_respuestas);
$stmt_respuestas->bind_param("iii", $tema_id, $offset, $respuestas_por_pagina);
$stmt_respuestas->execute();
$result_respuestas = $stmt_respuestas->get_result();

// Verificar si el usuario actual es el autor del tema o un administrador
$es_autor = $usuario_logueado && $_SESSION['usuario_id'] == $tema['autor_id'];
$es_admin = $usuario_logueado && isset($_SESSION['es_admin']) && $_SESSION['es_admin'];
$puede_editar = $es_autor || $es_admin;
?>

<div class="container">
    <div class="tema-header">
        <div class="tema-breadcrumb">
            <a href="<?php echo BASE_URL; ?>/comunidad/foros.php">Foros</a> &raquo;
            <a href="<?php echo BASE_URL; ?>/comunidad/categoria_foro.php?id=<?php echo $tema['categoria_id']; ?>">
                <?php echo htmlspecialchars($tema['categoria_nombre']); ?>
            </a> &raquo;
            <span><?php echo htmlspecialchars($tema['titulo']); ?></span>
        </div>
        
        <h1>
            <?php if ($tema['estado'] == 'destacado'): ?>
                <i class="fas fa-star tema-destacado" title="Tema destacado"></i>
            <?php elseif ($tema['estado'] == 'cerrado'): ?>
                <i class="fas fa-lock tema-cerrado" title="Tema cerrado"></i>
            <?php endif; ?>
            <?php echo htmlspecialchars($tema['titulo']); ?>
        </h1>
        
        <div class="tema-meta">
            <span class="tema-autor">
                <i class="fas fa-user"></i> Por: 
                <a href="<?php echo BASE_URL; ?>/usuario/perfil.php?id=<?php echo $tema['autor_id']; ?>">
                    <?php echo htmlspecialchars($tema['autor_nombre']); ?>
                </a>
            </span>
            
            <span class="tema-fecha">
                <i class="fas fa-calendar-alt"></i>
                <?php echo date('d/m/Y H:i', strtotime($tema['fecha_creacion'])); ?>
            </span>
            
            <span class="tema-stats">
                <i class="fas fa-eye"></i> <?php echo $tema['vistas']; ?> vistas
                <i class="fas fa-reply ml-2"></i> <?php echo $total_respuestas; ?> respuestas
            </span>
        </div>
        
        <?php if ($puede_editar): ?>
            <div class="tema-acciones">
                <?php if ($es_autor || $es_admin): ?>
                    <a href="<?php echo BASE_URL; ?>/comunidad/editar_tema.php?id=<?php echo $tema_id; ?>" class="btn btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                <?php endif; ?>
                
                <?php if ($es_admin): ?>
                    <form action="<?php echo BASE_URL; ?>/controllers/procesar-estado-tema.php" method="POST" class="inline-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        <input type="hidden" name="tema_id" value="<?php echo $tema_id; ?>">
                        
                        <?php if ($tema['estado'] == 'destacado'): ?>
                            <input type="hidden" name="accion" value="quitar_destacado">
                            <button type="submit" class="btn btn-sm">
                                <i class="fas fa-star"></i> Quitar destacado
                            </button>
                        <?php else: ?>
                            <input type="hidden" name="accion" value="destacar">
                            <button type="submit" class="btn btn-sm">
                                <i class="fas fa-star"></i> Destacar
                            </button>
                        <?php endif; ?>
                    </form>
                    
                    <form action="<?php echo BASE_URL; ?>/controllers/procesar-estado-tema.php" method="POST" class="inline-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        <input type="hidden" name="tema_id" value="<?php echo $tema_id; ?>">
                        
                        <?php if ($tema['estado'] == 'cerrado'): ?>
                            <input type="hidden" name="accion" value="abrir">
                            <button type="submit" class="btn btn-sm">
                                <i class="fas fa-lock-open"></i> Abrir tema
                            </button>
                        <?php else: ?>
                            <input type="hidden" name="accion" value="cerrar">
                            <button type="submit" class="btn btn-sm">
                                <i class="fas fa-lock"></i> Cerrar tema
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
                
                <?php if ($es_autor || $es_admin): ?>
                    <form action="<?php echo BASE_URL; ?>/controllers/procesar-eliminar-tema.php" method="POST" class="inline-form" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este tema? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                        <input type="hidden" name="tema_id" value="<?php echo $tema_id; ?>">
                        
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Contenido del tema -->
    <div class="tema-contenido">
        <div class="post-mensaje">
            <?php echo nl2br(htmlspecialchars($tema['contenido'])); ?>
        </div>
    </div>
    
    <!-- Respuestas -->
    <div class="respuestas-seccion">
        <h2>Respuestas (<?php echo $total_respuestas; ?>)</h2>
        
        <?php if ($result_respuestas && $result_respuestas->num_rows > 0): ?>
            <div class="respuestas-lista">
                <?php while ($respuesta = $result_respuestas->fetch_assoc()): ?>
                    <div class="respuesta-item" id="respuesta-<?php echo $respuesta['id']; ?>">
                        <div class="respuesta-header">
                            <div class="respuesta-autor">
                                <i class="fas fa-user"></i>
                                <a href="<?php echo BASE_URL; ?>/usuario/perfil.php?id=<?php echo $respuesta['autor_id']; ?>">
                                    <?php echo htmlspecialchars($respuesta['autor_nombre']); ?>
                                </a>
                            </div>
                            <div class="respuesta-fecha">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('d/m/Y H:i', strtotime($respuesta['fecha_creacion'])); ?>
                                
                                <?php if ($respuesta['fecha_edicion']): ?>
                                    <span class="respuesta-editada" title="Editado el <?php echo date('d/m/Y H:i', strtotime($respuesta['fecha_edicion'])); ?>">
                                        (editado)
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="respuesta-contenido">
                            <?php if ($respuesta['es_solucion']): ?>
                                <div class="respuesta-solucion">
                                    <i class="fas fa-check-circle"></i> Marcada como solución
                                </div>
                            <?php endif; ?>
                            
                            <div class="post-mensaje">
                                <?php echo nl2br(htmlspecialchars($respuesta['contenido'])); ?>
                            </div>
                        </div>
                        
                        <?php if ($usuario_logueado && ($_SESSION['usuario_id'] == $respuesta['autor_id'] || $es_admin)): ?>
                            <div class="respuesta-acciones">
                                <a href="<?php echo BASE_URL; ?>/comunidad/editar_respuesta.php?id=<?php echo $respuesta['id']; ?>" class="btn btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                
                                <?php if ($es_autor || $es_admin): ?>
                                    <form action="<?php echo BASE_URL; ?>/controllers/procesar-solucion.php" method="POST" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                                        <input type="hidden" name="respuesta_id" value="<?php echo $respuesta['id']; ?>">
                                        <input type="hidden" name="tema_id" value="<?php echo $tema_id; ?>">
                                        
                                        <?php if ($respuesta['es_solucion']): ?>
                                            <input type="hidden" name="accion" value="quitar_solucion">
                                            <button type="submit" class="btn btn-sm">
                                                <i class="fas fa-times-circle"></i> Quitar solución
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="accion" value="marcar_solucion">
                                            <button type="submit" class="btn btn-sm">
                                                <i class="fas fa-check-circle"></i> Marcar como solución
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                                
                                <form action="<?php echo BASE_URL; ?>/controllers/procesar-eliminar-respuesta.php" method="POST" class="inline-form" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta respuesta? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                                    <input type="hidden" name="respuesta_id" value="<?php echo $respuesta['id']; ?>">
                                    <input type="hidden" name="tema_id" value="<?php echo $tema_id; ?>">
                                    
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <div class="paginacion">
                    <?php if ($pagina_actual > 1): ?>
                        <a href="<?php echo BASE_URL; ?>/comunidad/tema.php?id=<?php echo $tema_id; ?>&pagina=<?php echo $pagina_actual - 1; ?>" class="btn-pagina">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <div class="paginas-numeros">
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <a href="<?php echo BASE_URL; ?>/comunidad/tema.php?id=<?php echo $tema_id; ?>&pagina=<?php echo $i; ?>" 
                               class="btn-pagina <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="<?php echo BASE_URL; ?>/comunidad/tema.php?id=<?php echo $tema_id; ?>&pagina=<?php echo $pagina_actual + 1; ?>" class="btn-pagina">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <p class="no-respuestas">No hay respuestas aún. ¡Sé el primero en responder!</p>
        <?php endif; ?>
    </div>
    
    <!-- Formulario de respuesta -->
    <?php if ($usuario_logueado && $tema['estado'] != 'cerrado'): ?>
        <div class="responder-seccion">
            <h3>Responder al tema</h3>
            
            <form action="<?php echo BASE_URL; ?>/controllers/procesar-respuesta.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                <input type="hidden" name="tema_id" value="<?php echo $tema_id; ?>">
                
                <div class="form-group">
                    <textarea name="contenido" rows="5" placeholder="Escribe tu respuesta..." required></textarea>
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-reply"></i> Responder
                </button>
            </form>
        </div>
    <?php elseif ($tema['estado'] == 'cerrado'): ?>
        <div class="tema-cerrado-mensaje">
            <i class="fas fa-lock"></i>
            <p>Este tema está cerrado y no se permiten nuevas respuestas.</p>
        </div>
    <?php else: ?>
        <div class="login-para-responder">
            <p>Necesitas <a href="<?php echo BASE_URL; ?>/public/login.php">iniciar sesión</a> para responder a este tema.</p>
        </div>
    <?php endif; ?>
    
    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>/comunidad/categoria_foro.php?id=<?php echo $tema['categoria_id']; ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Volver a la categoría
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
