<?php
require_once('../includes/config.php');
require_once('../includes/conexion.php');
require_once('../includes/funciones.php');

// Traducción
$idioma = $_SESSION['idioma'] ?? 'es';
$archivo_idioma = __DIR__ . "/../includes/languages/{$idioma}.php";
if (!file_exists($archivo_idioma)) $archivo_idioma = __DIR__ . "/../includes/languages/es.php";
require_once($archivo_idioma);

include '../includes/header.php';

$usuario_logueado = isset($_SESSION['usuario_id']);

// Categorías foro
$sql_categorias = "SELECT cf.*, 
                  (SELECT COUNT(*) FROM temas_foro WHERE categoria_id = cf.id) as total_temas,
                  (SELECT COUNT(*) FROM temas_foro tf JOIN respuestas_foro rf ON tf.id = rf.tema_id WHERE tf.categoria_id = cf.id) as total_respuestas
                  FROM categorias_foro cf
                  ORDER BY cf.id";
$result_categorias = $conexion->query($sql_categorias);

// Estadísticas globales
$total_categorias = 0;
$total_temas = 0;
$total_respuestas = 0;
$categorias_array = [];
if ($result_categorias && $result_categorias->num_rows > 0) {
    foreach ($result_categorias as $cat) {
        $total_categorias++;
        $total_temas += (int)$cat['total_temas'];
        $total_respuestas += (int)$cat['total_respuestas'];
        $categorias_array[] = $cat;
    }
}

// Temas recientes
$sql_temas_recientes = "SELECT tf.*, cf.nombre as categoria_nombre, u.usuario as autor_nombre,
                        (SELECT COUNT(*) FROM respuestas_foro WHERE tema_id = tf.id) as total_respuestas,
                        (SELECT MAX(fecha) FROM respuestas_foro WHERE tema_id = tf.id) as ultima_respuesta
                        FROM temas_foro tf
                        JOIN categorias_foro cf ON tf.categoria_id = cf.id
                        JOIN usuarios u ON tf.usuario_id = u.id
                        ORDER BY tf.ultima_actividad DESC
                        LIMIT 5";
$result_temas_recientes = $conexion->query($sql_temas_recientes);

?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>../css/comunidad.css">

<div class="foro-container">

    <!-- Bloque de título y estadísticas -->
    <div class="foro-titulo-block">
        <div class="foro-titulo-destacado">
            <i class="fas fa-comments"></i>
            <?php echo $TEXTOS['foros_titulo'] ?? 'Foros de Discusión'; ?>
        </div>
        <div class="foro-descripcion">
            <?php echo $TEXTOS['foros_intro'] ?? 'Participa en conversaciones sobre literatura, comparte opiniones y conecta con otros lectores.'; ?>
        </div>
        <div class="foro-stats">
            <span><i class="fas fa-layer-group"></i> 
                <?php echo $total_categorias; ?> <?php echo $TEXTOS['foros_categorias'] ?? 'Categorías'; ?>
            </span>
            <span><i class="fas fa-comments"></i>
                <?php echo $total_temas; ?> <?php echo $TEXTOS['foros_temas'] ?? 'Temas'; ?>
            </span>
            <span><i class="fas fa-reply"></i>
                <?php echo $total_respuestas; ?> <?php echo $TEXTOS['foros_respuestas'] ?? 'Respuestas'; ?>
            </span>
        </div>
    </div>

    <!-- REGLAS DEL FORO DESPLEGABLE DEBAJO DEL TÍTULO -->
    <div class="foro-reglas-toggle" id="foro-reglas-toggle">
        <div class="foro-reglas-header" onclick="toggleReglas()">
            <i class="fas fa-gavel"></i>
            <?php echo $TEXTOS['foros_reglas'] ?? 'Reglas del foro'; ?>
            <span class="arrow" id="arrow-reglas"><i class="fas fa-chevron-down"></i></span>
        </div>
        <div class="foro-reglas-lista" id="foro-reglas-lista">
            <ol>
                <li><strong><?php echo $TEXTOS['foros_regla1_titulo'] ?? 'Respeto mutuo:'; ?></strong> <?php echo $TEXTOS['foros_regla1'] ?? ''; ?></li>
                <li><strong><?php echo $TEXTOS['foros_regla2_titulo'] ?? 'Contenido apropiado:'; ?></strong> <?php echo $TEXTOS['foros_regla2'] ?? ''; ?></li>
                <li><strong><?php echo $TEXTOS['foros_regla3_titulo'] ?? 'Propiedad intelectual:'; ?></strong> <?php echo $TEXTOS['foros_regla3'] ?? ''; ?></li>
                <li><strong><?php echo $TEXTOS['foros_regla4_titulo'] ?? 'No spam:'; ?></strong> <?php echo $TEXTOS['foros_regla4'] ?? ''; ?></li>
                <li><strong><?php echo $TEXTOS['foros_regla5_titulo'] ?? 'Temas relevantes:'; ?></strong> <?php echo $TEXTOS['foros_regla5'] ?? ''; ?></li>
            </ol>
            <p style="margin-top:1em;"><em><?php echo $TEXTOS['foros_regla_final'] ?? ''; ?></em></p>
        </div>
    </div>
    <script>
    function toggleReglas() {
      var lista = document.getElementById('foro-reglas-lista');
      var arrow = document.getElementById('arrow-reglas');
      if (lista.hasAttribute('hidden')) {
        lista.removeAttribute('hidden');
        arrow.style.transform = 'rotate(0deg)';
      } else {
        lista.setAttribute('hidden', 'hidden');
        arrow.style.transform = 'rotate(-90deg)';
      }
    }
    if(window.innerWidth < 700) toggleReglas();
    </script>

    <?php if ($usuario_logueado): ?>
        <div class="foro-acciones" style="justify-content:center; margin-bottom:2.3em;">
            <a href="<?php echo BASE_URL; ?>/comunidad/nuevo_tema.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> <?php echo $TEXTOS['foros_nuevo_tema'] ?? 'Crear nuevo tema'; ?>
            </a>
            <a href="<?php echo BASE_URL; ?>/comunidad/mis_temas.php" class="btn btn-outline">
                <i class="fas fa-list"></i> <?php echo $TEXTOS['foros_mis_temas'] ?? 'Mis temas'; ?>
            </a>
        </div>
    <?php else: ?>
        <div class="foro-login-alert">
            <p><?php echo $TEXTOS['foros_login_msg'] ?? 'Necesitas iniciar sesión para participar en los foros.'; ?></p>
            <a href="<?php echo BASE_URL; ?>/public/login.php" class="btn btn-primary"><?php echo $TEXTOS['foros_login_btn'] ?? 'Iniciar sesión'; ?></a>
        </div>
    <?php endif; ?>

    <!-- Temas recientes -->
    <section class="foro-seccion">
        <h2><i class="fas fa-fire"></i> <?php echo $TEXTOS['foros_temas_recientes'] ?? 'Temas recientes'; ?></h2>
        <div class="temas-lista">
            <?php if ($result_temas_recientes && $result_temas_recientes->num_rows > 0): ?>
                <div class="temas-header">
                    <div><?php echo $TEXTOS['foros_col_tema'] ?? 'Tema'; ?></div>
                    <div><?php echo $TEXTOS['foros_col_categoria'] ?? 'Categoría'; ?></div>
                    <div><?php echo $TEXTOS['foros_col_autor'] ?? 'Autor'; ?></div>
                    <div><?php echo $TEXTOS['foros_col_respuestas'] ?? 'Respuestas'; ?></div>
                    <div><?php echo $TEXTOS['foros_col_actividad'] ?? 'Última actividad'; ?></div>
                </div>
                <?php while ($tema = $result_temas_recientes->fetch_assoc()): ?>
                    <div class="tema-item">
                        <div class="tema-titulo">
                            <a href="<?php echo BASE_URL; ?>/comunidad/tema.php?id=<?php echo $tema['id']; ?>">
                                <?php
                                    $estado = $tema['estado'] ?? '';
                                    if ($estado == 'destacado') {
                                        echo '<i class="fas fa-star tema-estado-ico tema-destacado" title="' . ($TEXTOS['foros_destacado'] ?? 'Tema destacado') . '"></i>';
                                    } elseif ($estado == 'cerrado') {
                                        echo '<i class="fas fa-lock tema-estado-ico tema-cerrado" title="' . ($TEXTOS['foros_cerrado'] ?? 'Tema cerrado') . '"></i>';
                                    } else {
                                        echo '<i class="fas fa-comments tema-estado-ico tema-normal"></i>';
                                    }
                                ?>
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
                        <div>
                            <span class="tema-respuestas" title="<?php echo $TEXTOS['foros_respuestas'] ?? 'Respuestas'; ?>">
                                <i class="fas fa-reply"></i> <?php echo (int)$tema['total_respuestas']; ?>
                            </span>
                            <span class="tema-vistas" title="<?php echo $TEXTOS['foros_vistas'] ?? 'Vistas'; ?>">
                                <i class="fas fa-eye"></i> <?php echo isset($tema['vistas']) ? (int)$tema['vistas'] : 0; ?>
                            </span>
                        </div>
                        <div class="tema-actividad">
                            <?php
                            $fecha_actividad = !empty($tema['ultima_respuesta']) ? $tema['ultima_respuesta'] : ($tema['fecha'] ?? '');
                            if ($fecha_actividad) {
                                echo date('d/m/Y H:i', strtotime($fecha_actividad));
                            } else {
                                echo '-';
                            }
                            ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-temas"><?php echo $TEXTOS['foros_no_temas_recientes'] ?? 'No hay temas recientes.'; ?></p>
            <?php endif; ?>
        </div>
        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>/comunidad/todos_temas.php" class="btn btn-outline">
                <?php echo $TEXTOS['foros_ver_todos'] ?? 'Ver todos los temas'; ?>
            </a>
        </div>
    </section>
    
    <!-- Categorías de foro -->
    <div class="categorias-section-title">
    <i class="fas fa-layer-group"></i> <?php echo $TEXTOS['foros_categorias'] ?? 'Categorías'; ?>
    </div>
    
    <div class="categorias-foro-grid">
        <?php if (!empty($categorias_array)): ?>
            <?php foreach ($categorias_array as $categoria): ?>
                <div class="categoria-foro-card">
                    <span class="categoria-foro-icon">
                        <i class="fas <?php echo htmlspecialchars($categoria['icono']); ?>"></i>
                    </span>
                    <div class="categoria-foro-content">
                        <h3>
                            <a href="<?php echo BASE_URL; ?>/comunidad/categoria_foro.php?id=<?php echo $categoria['id']; ?>">
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </a>
                        </h3>
                        <p><?php echo htmlspecialchars($categoria['descripcion']); ?></p>
                        <div class="categoria-foro-stats">
                            <span title="<?php echo $TEXTOS['foros_temas'] ?? 'Temas'; ?>">
                                <i class="fas fa-comments"></i> <?php echo (int)$categoria['total_temas']; ?>
                            </span>
                            <span title="<?php echo $TEXTOS['foros_respuestas'] ?? 'Respuestas'; ?>">
                                <i class="fas fa-reply"></i> <?php echo (int)$categoria['total_respuestas']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-categorias"><?php echo $TEXTOS['foros_no_categorias'] ?? 'No hay categorías disponibles.'; ?></p>
        <?php endif; ?>
    </div>

<?php include '../includes/footer.php'; ?>