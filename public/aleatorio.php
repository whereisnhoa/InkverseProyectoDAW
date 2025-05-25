
<?php
// Incluir configuración y archivos necesarios
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/conexion.php');
require_once(__DIR__ . '/../includes/funciones.php');

// Obtener tipo de obra (si se especifica)
$tipo_obra = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';

// Función para obtener obra aleatoria
function obtenerObraAleatoria($conexion, $tipo = 'todos') {
    $obras = [];
    
    // Obtener libro aleatorio
    if ($tipo === 'todos' || $tipo === 'libro') {
        $sql_libro = "SELECT l.*, c.nombre as categoria_nombre, 'libro' as tipo_obra,
                      (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = l.id AND f.tipo_contenido = 'libro') as total_favoritos
                      FROM libros l 
                      LEFT JOIN categorias c ON l.categoria_id = c.id 
                      WHERE 1=1";
        
        // Filtrar contenido adulto si es necesario
        if (!isset($_SESSION['usuario_id'])) {
            $sql_libro .= " AND l.publico_adulto = 0";
        }
        
        $sql_libro .= " ORDER BY RAND() LIMIT 1";
        $result_libro = $conexion->query($sql_libro);
        
        if ($result_libro && $result_libro->num_rows > 0) {
            $obras[] = $result_libro->fetch_assoc();
        }
    }
    
    // Obtener cómic aleatorio
    if ($tipo === 'todos' || $tipo === 'comic') {
        $sql_comic = "SELECT c.*, 'comic' as tipo_obra, '' as categoria_nombre,
                      (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = c.id AND f.tipo_contenido = 'comic') as total_favoritos
                      FROM comics c 
                      WHERE 1=1";
        
        // Filtrar contenido adulto si es necesario
        if (!isset($_SESSION['usuario_id'])) {
            $sql_comic .= " AND c.publico_adulto = 0";
        }
        
        $sql_comic .= " ORDER BY RAND() LIMIT 1";
        $result_comic = $conexion->query($sql_comic);
        
        if ($result_comic && $result_comic->num_rows > 0) {
            $obras[] = $result_comic->fetch_assoc();
        }
    }
    
    // Seleccionar una obra aleatoria de las disponibles
    if (!empty($obras)) {
        return $obras[array_rand($obras)];
    }
    
    return null;
}

// Obtener obra aleatoria
$obra_aleatoria = obtenerObraAleatoria($conexion, $tipo_obra);

// Si es una petición AJAX, devolver JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if ($obra_aleatoria) {
        // Preparar datos para JSON
        $obra_data = [
            'id' => $obra_aleatoria['id'],
            'titulo' => $obra_aleatoria['titulo'],
            'autor' => $obra_aleatoria['autor'],
            'descripcion' => $obra_aleatoria['descripcion'] ?? '',
            'portada' => !empty($obra_aleatoria['portada']) ? 
                        BASE_URL . '/uploads/portadas/' . $obra_aleatoria['portada'] : 
                        BASE_URL . '/assets/images/placeholder-book.png',
            'categoria' => $obra_aleatoria['categoria_nombre'] ?? 'Sin categoría',
            'tipo' => $obra_aleatoria['tipo_obra'],
            'publico_adulto' => (bool)$obra_aleatoria['publico_adulto'],
            'fecha_subida' => date('d/m/Y', strtotime($obra_aleatoria['fecha_subida'])),
            'total_favoritos' => $obra_aleatoria['total_favoritos']
        ];
        
        echo json_encode([
            'success' => true,
            'obra' => $obra_data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'mensaje' => 'No se encontraron obras disponibles'
        ]);
    }
    exit();
}

// Si no es AJAX, mostrar página completa
include_once(__DIR__ . '/../includes/header.php');
?>

<div class="container">
    <div class="aleatorio-container">
        <!-- Header de la página -->
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-random"></i> Obra Aleatoria</h1>
                <p>Descubre algo nuevo y sorprendente</p>
            </div>
            <div class="page-actions">
                <button id="nueva-obra-aleatoria" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Nueva obra aleatoria
                </button>
                <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-outline">
                    <i class="fas fa-compass"></i> Explorar más
                </a>
            </div>
        </div>

        <!-- Filtros de tipo -->
        <div class="filtros-aleatorio">
            <div class="filtro-buttons">
                <button class="btn-filtro <?php echo $tipo_obra === 'todos' ? 'active' : ''; ?>" 
                        data-tipo="todos">
                    <i class="fas fa-globe"></i> Todos
                </button>
                <button class="btn-filtro <?php echo $tipo_obra === 'libro' ? 'active' : ''; ?>" 
                        data-tipo="libro">
                    <i class="fas fa-book"></i> Libros
                </button>
                <button class="btn-filtro <?php echo $tipo_obra === 'comic' ? 'active' : ''; ?>" 
                        data-tipo="comic">
                    <i class="fas fa-book-open"></i> Cómics
                </button>
            </div>
        </div>

        <!-- Contenedor de obra aleatoria -->
        <div id="obra-aleatoria-container">
            <?php if ($obra_aleatoria): ?>
                <div class="obra-aleatoria-card">
                    <div class="obra-portada-grande">
                        <?php if (!empty($obra_aleatoria['portada'])): ?>
                            <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra_aleatoria['portada']); ?>" 
                                 alt="Portada de <?php echo htmlspecialchars($obra_aleatoria['titulo']); ?>">
                        <?php else: ?>
                            <div class="portada-placeholder-grande">
                                <i class="fas fa-<?php echo $obra_aleatoria['tipo_obra'] === 'libro' ? 'book' : 'book-open'; ?>"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Botón de favorito -->
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <button class="btn-favorito-grande" 
                                    data-obra-id="<?php echo $obra_aleatoria['id']; ?>" 
                                    data-tipo="<?php echo $obra_aleatoria['tipo_obra']; ?>"
                                    title="Añadir a favoritos">
                                <i class="fas fa-heart"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="obra-info-grande">
                        <div class="obra-header-grande">
                            <h2><?php echo htmlspecialchars($obra_aleatoria['titulo']); ?></h2>
                            <div class="obra-badges-grande">
                                <span class="badge badge-tipo-grande">
                                    <?php echo $obra_aleatoria['tipo_obra'] === 'libro' ? 'Libro' : 'Cómic'; ?>
                                </span>
                                <?php if (!empty($obra_aleatoria['categoria_nombre'])): ?>
                                    <span class="badge badge-categoria-grande">
                                        <?php echo htmlspecialchars($obra_aleatoria['categoria_nombre']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($obra_aleatoria['publico_adulto']): ?>
                                    <span class="badge badge-adulto-grande">+18</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <p class="obra-autor-grande">
                            <i class="fas fa-user"></i>
                            Por: <?php echo htmlspecialchars($obra_aleatoria['autor']); ?>
                        </p>

                        <?php if (!empty($obra_aleatoria['descripcion'])): ?>
                            <div class="obra-descripcion-grande">
                                <p><?php echo nl2br(htmlspecialchars($obra_aleatoria['descripcion'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="obra-stats-grande">
                            <div class="stat-item">
                                <i class="fas fa-heart"></i>
                                <span><?php echo $obra_aleatoria['total_favoritos']; ?> favoritos</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-calendar"></i>
                                <span>Subida: <?php echo date('d/m/Y', strtotime($obra_aleatoria['fecha_subida'])); ?></span>
                            </div>
                        </div>

                        <div class="obra-acciones-grande">
                            <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra_aleatoria['id']; ?>&tipo=<?php echo $obra_aleatoria['tipo_obra']; ?>" 
                               class="btn btn-primary btn-lg">
                                <i class="fas fa-eye"></i> Ver obra completa
                            </a>
                            <button id="compartir-obra" class="btn btn-outline btn-lg" 
                                    data-titulo="<?php echo htmlspecialchars($obra_aleatoria['titulo']); ?>"
                                    data-url="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra_aleatoria['id']; ?>&tipo=<?php echo $obra_aleatoria['tipo_obra']; ?>">
                                <i class="fas fa-share"></i> Compartir
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-obra-aleatoria">
                    <div class="no-obra-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No hay obras disponibles</h3>
                    <p>No se encontraron obras que cumplan con los criterios seleccionados.</p>
                    <div class="no-obra-acciones">
                        <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-primary">
                            <i class="fas fa-compass"></i> Explorar obras
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Obras relacionadas -->
        <?php if ($obra_aleatoria): ?>
            <section class="obras-relacionadas">
                <div class="section-header">
                    <h3><i class="fas fa-lightbulb"></i> Más obras que podrían gustarte</h3>
                </div>
                
                <div class="obras-relacionadas-grid" id="obras-relacionadas-container">
                    <!-- Se cargarán dinámicamente -->
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<!-- Estilos CSS -->
<style>
.aleatorio-container {
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
    color: #f39c12;
}

.page-title p {
    margin: 5px 0 0 0;
    color: #666;
}

.page-actions {
    display: flex;
    gap: 10px;
}

.filtros-aleatorio {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 30px;
    text-align: center;
}

.filtro-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-filtro {
    padding: 12px 24px;
    border: 2px solid #e1e5e9;
    border-radius: 25px;
    background: white;
    color: #666;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-filtro:hover {
    border-color: #4361ee;
    color: #4361ee;
    transform: translateY(-2px);
}

.btn-filtro.active {
    background: #4361ee;
    border-color: #4361ee;
    color: white;
}

.obra-aleatoria-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 0;
    margin-bottom: 40px;
}

.obra-portada-grande {
    height: 400px;
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.obra-portada-grande img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.portada-placeholder-grande {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.8);
    font-size: 80px;
}

.btn-favorito-grande {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    color: #333;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 20px;
}

.btn-favorito-grande:hover {
    background: #e74c3c;
    color: white;
    transform: scale(1.1);
}

.obra-info-grande {
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.obra-header-grande h2 {
    margin: 0 0 15px 0;
    font-size: 32px;
    color: #333;
    line-height: 1.2;
}

.obra-badges-grande {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.badge-tipo-grande,
.badge-categoria-grande,
.badge-adulto-grande {
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-tipo-grande {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-categoria-grande {
    background: #f3e5f5;
    color: #7b1fa2;
}

.badge-adulto-grande {
    background: #ffebee;
    color: #d32f2f;
}

.obra-autor-grande {
    color: #666;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 500;
}

.obra-descripcion-grande {
    margin: 20px 0;
    color: #555;
    line-height: 1.6;
    font-size: 16px;
}

.obra-stats-grande {
    display: flex;
    gap: 30px;
    margin: 25px 0;
    padding: 20px 0;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 14px;
}

.stat-item i {
    color: #4361ee;
    font-size: 16px;
}

.obra-acciones-grande {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 16px;
    font-weight: 600;
}

.no-obra-aleatoria {
    text-align: center;
    padding: 80px 20px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.no-obra-icon {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 30px;
}

.no-obra-aleatoria h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 24px;
}

.no-obra-aleatoria p {
    color: #666;
    margin-bottom: 30px;
    font-size: 16px;
}

.obras-relacionadas {
    margin-top: 50px;
}

.section-header {
    margin-bottom: 25px;
}

.section-header h3 {
    margin: 0;
    color: #333;
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h3 i {
    color: #f39c12;
}

.obras-relacionadas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
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
    transform: translateY(-1px);
}

.btn-outline {
    background: transparent;
    border: 2px solid #4361ee;
    color: #4361ee;
}

.btn-outline:hover {
    background: #4361ee;
    color: white;
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
    
    .obra-aleatoria-card {
        grid-template-columns: 1fr;
    }
    
    .obra-portada-grande {
        height: 250px;
    }
    
    .obra-info-grande {
        padding: 30px 20px;
    }
    
    .obra-header-grande h2 {
        font-size: 24px;
    }
    
    .obra-acciones-grande {
        flex-direction: column;
    }
    
    .obra-stats-grande {
        flex-direction: column;
        gap: 15px;
    }
    
    .filtro-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-filtro {
        width: 100%;
        max-width: 200px;
        justify-content: center;
    }
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Botón para nueva obra aleatoria
    const btnNuevaObra = document.getElementById('nueva-obra-aleatoria');
    if (btnNuevaObra) {
        btnNuevaObra.addEventListener('click', function() {
            const tipoActivo = document.querySelector('.btn-filtro.active').dataset.tipo;
            cargarObraAleatoria(tipoActivo);
        });
    }
    
    // Filtros de tipo
    document.querySelectorAll('.btn-filtro').forEach(button => {
        button.addEventListener('click', function() {
            // Actualizar botones activos
            document.querySelectorAll('.btn-filtro').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Cargar nueva obra
            const tipo = this.dataset.tipo;
            cargarObraAleatoria(tipo);
        });
    });
    
    // Botón de favorito
    const btnFavorito = document.querySelector('.btn-favorito-grande');
    if (btnFavorito) {
        btnFavorito.addEventListener('click', function() {
            const obraId = this.dataset.obraId;
            const tipo = this.dataset.tipo;
            window.toggleFavorito(obraId, tipo, this);
        });
    }
    
    // Botón de compartir
    const btnCompartir = document.getElementById('compartir-obra');
    if (btnCompartir) {
        btnCompartir.addEventListener('click', function() {
            const titulo = this.dataset.titulo;
            const url = this.dataset.url;
            
            if (navigator.share) {
                navigator.share({
                    title: titulo,
                    text: `Mira esta obra: ${titulo}`,
                    url: url
                });
            } else {
                // Fallback: copiar al portapapeles
                navigator.clipboard.writeText(url).then(() => {
                    window.showAlert('Enlace copiado al portapapeles', 'success');
                });
            }
        });
    }
    
    // Cargar obras relacionadas si hay una obra mostrada
    if (document.querySelector('.obra-aleatoria-card')) {
        cargarObrasRelacionadas();
    }
});

function cargarObraAleatoria(tipo) {
    const container = document.getElementById('obra-aleatoria-container');
    
    // Mostrar spinner
    container.innerHTML = '<div class="text-center"><div class="spinner"></div><p>Buscando una obra aleatoria...</p></div>';
    
    // Hacer petición AJAX
    fetch(`<?php echo BASE_URL; ?>/public/aleatorio.php?tipo=${tipo}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.obra) {
            mostrarObraAleatoria(data.obra);
            cargarObrasRelacionadas();
        } else {
            container.innerHTML = `
                <div class="no-obra-aleatoria">
                    <div class="no-obra-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No hay obras disponibles</h3>
                    <p>No se encontraron obras del tipo seleccionado.</p>
                    <div class="no-obra-acciones">
                        <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-primary">
                            <i class="fas fa-compass"></i> Explorar obras
                        </a>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = `
            <div class="no-obra-aleatoria">
                <div class="no-obra-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3>Error al cargar</h3>
                <p>Hubo un problema al cargar la obra aleatoria.</p>
                <button onclick="location.reload()" class="btn btn-primary">
                    <i class="fas fa-refresh"></i> Intentar de nuevo
                </button>
            </div>
        `;
    });
}

function mostrarObraAleatoria(obra) {
    const container = document.getElementById('obra-aleatoria-container');
    const adultBadge = obra.publico_adulto ? '<span class="badge badge-adulto-grande">+18</span>' : '';
    const categoriaBadge = obra.categoria !== 'Sin categoría' ? 
        `<span class="badge badge-categoria-grande">${obra.categoria}</span>` : '';
    
    container.innerHTML = `
        <div class="obra-aleatoria-card">
            <div class="obra-portada-grande">
                <img src="${obra.portada}" alt="${obra.titulo}">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                <button class="btn-favorito-grande" 
                        data-obra-id="${obra.id}" 
                        data-tipo="${obra.tipo}"
                        title="Añadir a favoritos">
                    <i class="fas fa-heart"></i>
                </button>
                <?php endif; ?>
            </div>
            <div class="obra-info-grande">
                <div class="obra-header-grande">
                    <h2>${obra.titulo}</h2>
                    <div class="obra-badges-grande">
                        <span class="badge badge-tipo-grande">
                            ${obra.tipo === 'libro' ? 'Libro' : 'Cómic'}
                        </span>
                        ${categoriaBadge}
                        ${adultBadge}
                    </div>
                </div>
                <p class="obra-autor-grande">
                    <i class="fas fa-user"></i>
                    Por: ${obra.autor}
                </p>
                ${obra.descripcion ? `
                <div class="obra-descripcion-grande">
                    <p>${obra.descripcion}</p>
                </div>
                ` : ''}
                <div class="obra-stats-grande">
                    <div class="stat-item">
                        <i class="fas fa-heart"></i>
                        <span>${obra.total_favoritos} favoritos</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calendar"></i>
                        <span>Subida: ${obra.fecha_subida}</span>
                    </div>
                </div>
                <div class="obra-acciones-grande">
                    <a href="<?php echo BASE_URL; ?>/public/obra.php?id=${obra.id}&tipo=${obra.tipo}" 
                       class="btn btn-primary btn-lg">
                        <i class="fas fa-eye"></i> Ver obra completa
                    </a>
                    <button class="btn btn-outline btn-lg" 
                            onclick="compartirObra('${obra.titulo}', '<?php echo BASE_URL; ?>/public/obra.php?id=${obra.id}&tipo=${obra.tipo}')">
                        <i class="fas fa-share"></i> Compartir
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Reinicializar eventos
    const btnFavorito = container.querySelector('.btn-favorito-grande');
    if (btnFavorito) {
        btnFavorito.addEventListener('click', function() {
            const obraId = this.dataset.obraId;
            const tipo = this.dataset.tipo;
            window.toggleFavorito(obraId, tipo, this);
        });
    }
}

function compartirObra(titulo, url) {
    if (navigator.share) {
        navigator.share({
            title: titulo,
            text: `Mira esta obra: ${titulo}`,
            url: url
        });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            window.showAlert('Enlace copiado al portapapeles', 'success');
        });
    }
}

function cargarObrasRelacionadas() {
    const container = document.getElementById('obras-relacionadas-container');
    if (!container) return;
    
    // Aquí podrías implementar la carga de obras relacionadas
    // Por ahora, simplemente ocultamos la sección
    const section = document.querySelector('.obras-relacionadas');
    if (section) {
        section.style.display = 'none';
    }
}

// Añadir estilos para el spinner
const style = document.createElement('style');
style.textContent = `
    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #4361ee;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 20px auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>