<?php
// Iniciar sesión
session_start();

// Incluir configuración y archivos necesarios
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/funciones.php');
require_once(__DIR__ . '/../includes/conexion.php');

// Verificar que el usuario esté logueado
verificarLogin();

$usuario_id = $_SESSION['usuario_id'];

// Configurar filtros
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';
$filtro_categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'reciente';

// Obtener libros favoritos
$sql_libros = "SELECT l.*, c.nombre as categoria_nombre, f.fecha_agregado,
               (SELECT COUNT(*) FROM favoritos f2 WHERE f2.contenido_id = l.id AND f2.tipo_contenido = 'libro') as total_favoritos
               FROM libros l 
               JOIN favoritos f ON f.contenido_id = l.id 
               LEFT JOIN categorias c ON l.categoria_id = c.id 
               WHERE f.usuario_id = ? AND f.tipo_contenido = 'libro'";

$params_libros = [$usuario_id];
$param_types_libros = "i";

// Aplicar filtros para libros
if ($filtro_tipo === 'libro' || $filtro_tipo === 'todos') {
    if ($filtro_categoria > 0) {
        $sql_libros .= " AND l.categoria_id = ?";
        $params_libros[] = $filtro_categoria;
        $param_types_libros .= "i";
    }
    
    // Aplicar orden
    switch ($orden) {
        case 'titulo':
            $sql_libros .= " ORDER BY l.titulo ASC";
            break;
        case 'autor':
            $sql_libros .= " ORDER BY l.autor ASC";
            break;
        case 'popular':
            $sql_libros .= " ORDER BY total_favoritos DESC";
            break;
        default:
            $sql_libros .= " ORDER BY f.fecha_agregado DESC";
    }
}

// Obtener cómics favoritos
$sql_comics = "SELECT c.*, f.fecha_agregado,
               (SELECT COUNT(*) FROM favoritos f2 WHERE f2.contenido_id = c.id AND f2.tipo_contenido = 'comic') as total_favoritos
               FROM comics c 
               JOIN favoritos f ON f.contenido_id = c.id 
               WHERE f.usuario_id = ? AND f.tipo_contenido = 'comic'";

$params_comics = [$usuario_id];
$param_types_comics = "i";

// Aplicar orden para cómics
switch ($orden) {
    case 'titulo':
        $sql_comics .= " ORDER BY c.titulo ASC";
        break;
    case 'autor':
        $sql_comics .= " ORDER BY c.autor ASC";
        break;
    case 'popular':
        $sql_comics .= " ORDER BY total_favoritos DESC";
        break;
    default:
        $sql_comics .= " ORDER BY f.fecha_agregado DESC";
}

// Ejecutar consultas según el filtro de tipo
$libros_favoritos = null;
$comics_favoritos = null;

if ($filtro_tipo === 'todos' || $filtro_tipo === 'libro') {
    $stmt_libros = $conexion->prepare($sql_libros);
    $stmt_libros->bind_param($param_types_libros, ...$params_libros);
    $stmt_libros->execute();
    $libros_favoritos = $stmt_libros->get_result();
}

if ($filtro_tipo === 'todos' || $filtro_tipo === 'comic') {
    $stmt_comics = $conexion->prepare($sql_comics);
    $stmt_comics->bind_param($param_types_comics, ...$params_comics);
    $stmt_comics->execute();
    $comics_favoritos = $stmt_comics->get_result();
}

// Obtener categorías para el filtro (solo de libros)
$sql_categorias = "SELECT DISTINCT c.id, c.nombre 
                   FROM categorias c 
                   JOIN libros l ON c.id = l.categoria_id 
                   JOIN favoritos f ON l.id = f.contenido_id 
                   WHERE f.usuario_id = ? AND f.tipo_contenido = 'libro'
                   ORDER BY c.nombre";
$stmt_cat = $conexion->prepare($sql_categorias);
$stmt_cat->bind_param("i", $usuario_id);
$stmt_cat->execute();
$categorias = $stmt_cat->get_result();

// Contar total de favoritos
$total_favoritos = 0;
if ($libros_favoritos) $total_favoritos += $libros_favoritos->num_rows;
if ($comics_favoritos) $total_favoritos += $comics_favoritos->num_rows;

// Incluir header
include_once(__DIR__ . '/../includes/header.php');
?>

<div class="container">
    <div class="favoritos-container">
        <!-- Header de la página -->
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-heart"></i> Mis Favoritos</h1>
                <p>Todas las obras que has guardado como favoritas</p>
            </div>
            <div class="page-stats">
                <span class="stat-item">
                    <i class="fas fa-bookmark"></i>
                    <?php echo $total_favoritos; ?> obras guardadas
                </span>
            </div>
        </div>

        <!-- Filtros y ordenación -->
        <div class="filtros-container">
            <form method="GET" class="filtros-form">
                <div class="filtro-grupo">
                    <label for="tipo">Tipo:</label>
                    <select name="tipo" id="tipo">
                        <option value="todos" <?php echo $filtro_tipo === 'todos' ? 'selected' : ''; ?>>Todos</option>
                        <option value="libro" <?php echo $filtro_tipo === 'libro' ? 'selected' : ''; ?>>Libros</option>
                        <option value="comic" <?php echo $filtro_tipo === 'comic' ? 'selected' : ''; ?>>Cómics</option>
                    </select>
                </div>

                <div class="filtro-grupo">
                    <label for="categoria">Categoría:</label>
                    <select name="categoria" id="categoria">
                        <option value="0">Todas las categorías</option>
                        <?php 
                        $categorias->data_seek(0);
                        while ($cat = $categorias->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filtro-grupo">
                    <label for="orden">Ordenar por:</label>
                    <select name="orden" id="orden">
                        <option value="reciente" <?php echo $orden === 'reciente' ? 'selected' : ''; ?>>Más recientes</option>
                        <option value="titulo" <?php echo $orden === 'titulo' ? 'selected' : ''; ?>>Título A-Z</option>
                        <option value="autor" <?php echo $orden === 'autor' ? 'selected' : ''; ?>>Autor A-Z</option>
                        <option value="popular" <?php echo $orden === 'popular' ? 'selected' : ''; ?>>Más populares</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Aplicar filtros
                </button>
            </form>
        </div>

        <!-- Lista de obras favoritas -->
        <?php if ($total_favoritos > 0): ?>
            <div class="obras-grid">
                
                <!-- Mostrar libros favoritos -->
                <?php if ($libros_favoritos && $libros_favoritos->num_rows > 0): ?>
                    <?php while ($libro = $libros_favoritos->fetch_assoc()): ?>
                        <div class="obra-card favorita" data-obra-id="<?php echo $libro['id']; ?>" data-tipo="libro">
                            <div class="obra-portada">
                                <?php if (!empty($libro['portada'])): ?>
                                    <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($libro['portada']); ?>" 
                                         alt="Portada de <?php echo htmlspecialchars($libro['titulo']); ?>">
                                <?php else: ?>
                                    <div class="portada-placeholder">
                                        <i class="fas fa-book"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Botón para quitar de favoritos -->
                                <button class="btn-quitar-favorito" data-obra-id="<?php echo $libro['id']; ?>" data-tipo="libro" title="Quitar de favoritos">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>

                            <div class="obra-info">
                                <div class="obra-header">
                                    <h3><?php echo htmlspecialchars($libro['titulo']); ?></h3>
                                    <div class="obra-badges">
                                        <span class="badge badge-tipo">Libro</span>
                                        <?php if (!empty($libro['categoria_nombre'])): ?>
                                            <span class="badge badge-categoria"><?php echo htmlspecialchars($libro['categoria_nombre']); ?></span>
                                        <?php endif; ?>
                                        <?php if ($libro['publico_adulto']): ?>
                                            <span class="badge badge-adulto">+18</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <p class="obra-autor">
                                    <i class="fas fa-user"></i>
                                    Por: <?php echo htmlspecialchars($libro['autor']); ?>
                                </p>

                                <?php if (!empty($libro['descripcion'])): ?>
                                    <p class="obra-descripcion">
                                        <?php echo substr(htmlspecialchars($libro['descripcion']), 0, 120) . '...'; ?>
                                    </p>
                                <?php endif; ?>

                                <div class="obra-stats">
                                    <span><i class="fas fa-heart"></i> <?php echo $libro['total_favoritos']; ?></span>
                                    <span><i class="fas fa-calendar-plus"></i> Guardado: <?php echo date('d/m/Y', strtotime($libro['fecha_guardado'])); ?></span>
                                </div>

                                <div class="obra-acciones">
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $libro['id']; ?>&tipo=libro" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Ver obra
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <!-- Mostrar cómics favoritos -->
                <?php if ($comics_favoritos && $comics_favoritos->num_rows > 0): ?>
                    <?php while ($comic = $comics_favoritos->fetch_assoc()): ?>
                        <div class="obra-card favorita" data-obra-id="<?php echo $comic['id']; ?>" data-tipo="comic">
                            <div class="obra-portada">
                                <?php if (!empty($comic['portada'])): ?>
                                    <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($comic['portada']); ?>" 
                                         alt="Portada de <?php echo htmlspecialchars($comic['titulo']); ?>">
                                <?php else: ?>
                                    <div class="portada-placeholder">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Botón para quitar de favoritos -->
                                <button class="btn-quitar-favorito" data-obra-id="<?php echo $comic['id']; ?>" data-tipo="comic" title="Quitar de favoritos">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>

                            <div class="obra-info">
                                <div class="obra-header">
                                    <h3><?php echo htmlspecialchars($comic['titulo']); ?></h3>
                                    <div class="obra-badges">
                                        <span class="badge badge-tipo">Cómic</span>
                                        <?php if ($comic['publico_adulto']): ?>
                                            <span class="badge badge-adulto">+18</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <p class="obra-autor">
                                    <i class="fas fa-user"></i>
                                    Por: <?php echo htmlspecialchars($comic['autor']); ?>
                                </p>

                                <?php if (!empty($comic['descripcion'])): ?>
                                    <p class="obra-descripcion">
                                        <?php echo substr(htmlspecialchars($comic['descripcion']), 0, 120) . '...'; ?>
                                    </p>
                                <?php endif; ?>

                                <div class="obra-stats">
                                    <span><i class="fas fa-heart"></i> <?php echo $comic['total_favoritos']; ?></span>
                                    <span><i class="fas fa-calendar-plus"></i> Guardado: <?php echo date('d/m/Y', strtotime($comic['fecha_guardado'])); ?></span>
                                </div>

                                <div class="obra-acciones">
                                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $comic['id']; ?>&tipo=comic" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Ver obra
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                
            </div>
        <?php else: ?>
            <div class="no-favoritos">
                <div class="no-favoritos-icon">
                    <i class="fas fa-heart-broken"></i>
                </div>
                <h3>No tienes obras favoritas</h3>
                <p>Cuando encuentres obras que te gusten, puedes guardarlas como favoritas haciendo clic en el corazón.</p>
                <div class="no-favoritos-acciones">
                    <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-primary">
                        <i class="fas fa-compass"></i> Explorar obras
                    </a>
                    <a href="<?php echo BASE_URL; ?>/public/buscar.php" class="btn btn-outline">
                        <i class="fas fa-search"></i> Buscar obras
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Estilos CSS -->
<style>
.favoritos-container {
    max-width: 1200px;
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
    color: #e74c3c;
}

.page-title p {
    margin: 5px 0 0 0;
    color: #666;
}

.page-stats {
    display: flex;
    gap: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-weight: 500;
}

.stat-item i {
    color: #4361ee;
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

.obras-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}

.obra-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.obra-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.obra-portada {
    height: 200px;
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.obra-portada img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.portada-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.8);
    font-size: 48px;
}

.btn-quitar-favorito {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(231, 76, 60, 0.9);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    opacity: 0.8;
}

.btn-quitar-favorito:hover {
    opacity: 1;
    transform: scale(1.1);
    background: #e74c3c;
}

.obra-info {
    padding: 20px;
}

.obra-header {
    margin-bottom: 15px;
}

.obra-header h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #333;
    line-height: 1.3;
}

.obra-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
}

.badge-tipo {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-categoria {
    background: #f3e5f5;
    color: #7b1fa2;
}

.badge-adulto {
    background: #ffebee;
    color: #d32f2f;
}

.obra-autor {
    color: #666;
    margin: 10px 0;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
}

.obra-descripcion {
    color: #555;
    line-height: 1.5;
    margin: 12px 0;
    font-size: 14px;
}

.obra-stats {
    display: flex;
    gap: 15px;
    margin: 15px 0;
    font-size: 13px;
    color: #777;
}

.obra-stats span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.obra-acciones {
    display: flex;
    gap: 10px;
    margin-top: 15px;
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

.no-favoritos {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.no-favoritos-icon {
    font-size: 64px;
    color: #ddd;
    margin-bottom: 20px;
}

.no-favoritos h3 {
    color: #333;
    margin-bottom: 10px;
}

.no-favoritos p {
    color: #666;
    margin-bottom: 30px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.no-favoritos-acciones {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .filtros-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filtro-grupo {
        width: 100%;
    }
    
    .obras-grid {
        grid-template-columns: 1fr;
    }
    
    .obra-acciones {
        flex-direction: column;
    }
    
    .no-favoritos-acciones {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<!-- JavaScript para funcionalidad -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar botones de quitar favorito
    document.querySelectorAll('.btn-quitar-favorito').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const obraId = this.dataset.obraId;
            const tipo = this.dataset.tipo;
            
            if (confirm('¿Estás seguro de que quieres quitar esta obra de tus favoritos?')) {
                quitarDeFavoritos(obraId, tipo);
            }
        });
    });
    
    // Función para quitar de favoritos
    function quitarDeFavoritos(obraId, tipo) {
        fetch('<?php echo BASE_URL; ?>/controllers/procesar-favoritos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `accion=quitar&obra_id=${obraId}&tipo=${tipo}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remover la tarjeta de la vista
                const card = document.querySelector(`[data-obra-id="${obraId}"][data-tipo="${tipo}"]`);
                if (card) {
                    card.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => {
                        card.remove();
                        
                        // Si no quedan obras, mostrar mensaje
                        if (document.querySelectorAll('.obra-card').length === 0) {
                            location.reload();
                        }
                    }, 300);
                }
                
                // Mostrar mensaje de éxito
                mostrarMensaje('Obra quitada de favoritos', 'success');
            } else {
                mostrarMensaje('Error al quitar de favoritos', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error de conexión', 'error');
        });
    }
    
    // Función para mostrar mensajes
    function mostrarMensaje(mensaje, tipo) {
        const alerta = document.createElement('div');
        alerta.className = `alerta alerta-${tipo}`;
        alerta.textContent = mensaje;
        alerta.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 12px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        `;
        
        if (tipo === 'success') {
            alerta.style.background = '#27ae60';
        } else {
            alerta.style.background = '#e74c3c';
        }
        
        document.body.appendChild(alerta);
        
        setTimeout(() => {
            alerta.remove();
        }, 3000);
    }
});

// Animaciones CSS
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