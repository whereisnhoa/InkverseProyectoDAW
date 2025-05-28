
<?php
include '../includes/header.php';
include_once('../includes/funciones.php');

// Verificar si hay un usuario logueado
$usuario_logueado = isset($_SESSION['usuario_id']);

// Obtener categorías de foro
$sql_categorias = "SELECT cf.*, 
                  (SELECT COUNT(*) FROM temas_foro WHERE categoria_id = cf.id) as total_temas,
                  (SELECT COUNT(*) FROM temas_foro tf JOIN respuestas_foro rf ON tf.id = rf.tema_id WHERE tf.categoria_id = cf.id) as total_respuestas
                  FROM categorias_foro cf
                  ORDER BY cf.orden";
$result_categorias = $conexion->query($sql_categorias);

// Obtener temas recientes
$sql_temas_recientes = "SELECT tf.*, cf.nombre as categoria_nombre, u.usuario as autor_nombre,
                        (SELECT COUNT(*) FROM respuestas_foro WHERE tema_id = tf.id) as total_respuestas,
                        (SELECT MAX(fecha_creacion) FROM respuestas_foro WHERE tema_id = tf.id) as ultima_respuesta
                        FROM temas_foro tf
                        JOIN categorias_foro cf ON tf.categoria_id = cf.id
                        JOIN usuarios u ON tf.usuario_id = u.id
                        ORDER BY tf.ultima_actividad DESC
                        LIMIT 5";
$result_temas_recientes = $conexion->query($sql_temas_recientes);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">

<div class="container">
    <h1>Foros de Discusión</h1>
    <p class="mb-4">Participa en conversaciones sobre literatura, comparte opiniones y conecta con otros lectores.</p>
    
    <?php if ($usuario_logueado): ?>
        <div class="foro-acciones mb-4">
            <a href="<?php echo BASE_URL; ?>/comunidad/nuevo_tema.php" class="btn">
                <i class="fas fa-plus-circle"></i> Crear nuevo tema
            </a>
            
            <a href="<?php echo BASE_URL; ?>/comunidad/mis_temas.php" class="btn btn-outline">
                <i class="fas fa-list"></i> Mis temas
            </a>
        </div>
    <?php else: ?>
        <div class="text-center mt-3 mb-4">
            <p>Necesitas iniciar sesión para participar en los foros.</p>
            <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn">Iniciar sesión</a>
        </div>
    <?php endif; ?>
    
    <!-- Temas recientes -->
    <div class="foro-seccion">
        <h2>Temas recientes</h2>
        
        <div class="temas-lista">
            <?php if ($result_temas_recientes && $result_temas_recientes->num_rows > 0): ?>
                <div class="temas-header">
                    <div class="tema-titulo">Tema</div>
                    <div class="tema-categoria">Categoría</div>
                    <div class="tema-autor">Autor</div>
                    <div class="tema-stats">Respuestas</div>
                    <div class="tema-actividad">Última actividad</div>
                </div>
                
                <?php while ($tema = $result_temas_recientes->fetch_assoc()): ?>
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
                        <div class="tema-categoria">
                            <a href="<?php echo BASE_URL; ?>/comunidad/categoria_foro.php?id=<?php echo $tema['categoria_id']; ?>">
                                <?php echo htmlspecialchars($tema['categoria_nombre']); ?>
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
            <?php else: ?>
                <p class="no-temas">No hay temas recientes.</p>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>/comunidad/todos_temas.php" class="btn btn-outline">
                Ver todos los temas
            </a>
        </div>
    </div>
    
    <!-- Categorías de foro -->
    <div class="foro-seccion mt-5">
        <h2>Categorías</h2>
        
        <div class="categorias-foro">
            <?php if ($result_categorias && $result_categorias->num_rows > 0): ?>
                <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
                    <div class="categoria-foro-card">
                        <div class="categoria-foro-icon">
                            <i class="fas <?php echo htmlspecialchars($categoria['icono']); ?>"></i>
                        </div>
                        <div class="categoria-foro-content">
                            <h3>
                                <a href="<?php echo BASE_URL; ?>/comunidad/categoria_foro.php?id=<?php echo $categoria['id']; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </a>
                            </h3>
                            <p><?php echo htmlspecialchars($categoria['descripcion']); ?></p>
                        </div>
                        <div class="categoria-foro-stats">
                            <span title="Temas"><i class="fas fa-comments"></i> <?php echo $categoria['total_temas']; ?></span>
                            <span title="Respuestas"><i class="fas fa-reply"></i> <?php echo $categoria['total_respuestas']; ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-categorias">No hay categorías disponibles.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Reglas del foro -->
    <div class="foro-seccion mt-5">
        <h2>Reglas del foro</h2>
        
        <div class="reglas-foro">
            <ol>
                <li><strong>Respeto mutuo:</strong> Trata a todos los miembros con respeto. No se tolerará el acoso, insultos o comportamiento agresivo.</li>
                <li><strong>Contenido apropiado:</strong> No publiques contenido ilegal, ofensivo o inapropiado. El contenido para adultos debe estar correctamente etiquetado.</li>
                <li><strong>Propiedad intelectual:</strong> Respeta los derechos de autor. No publiques contenido protegido sin permiso.</li>
                <li><strong>No spam:</strong> Evita publicaciones repetitivas o promociones no solicitadas.</li>
                <li><strong>Temas relevantes:</strong> Mantén las discusiones relacionadas con literatura, cómics y temas afines.</li>
            </ol>
            
            <p>El incumplimiento de estas reglas puede resultar en la eliminación del contenido o la suspensión de la cuenta.</p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
