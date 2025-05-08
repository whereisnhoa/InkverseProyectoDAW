
<?php

require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// obtener término de búsqueda
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// si no hay término de búsqueda, mostrar formulario de búsqueda avanzada
if (empty($busqueda)) 
{

?>

<div class="container">

    <h1>Búsqueda Avanzada</h1>
    
    <form action="<?php echo BASE_URL; ?>/public/buscar.php" method="GET" class="search-form">

        <div class="form-group">
            <label for="q">Palabras clave:</label>
            <input type="text" id="q" name="q" class="form-control" placeholder="Título, autor, descripción...">
        </div>
        
        <div class="form-group">
            <label>Tipo de contenido:</label>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="tipo[]" value="libro" checked> Libros
                </label>
                <label>
                    <input type="checkbox" name="tipo[]" value="comic" checked> Cómics/Manga
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="categoria">Categoría:</label>
            <select name="categoria" id="categoria" class="form-control">
                <option value="">Todas las categorías</option>
                <?php
                $sql = "SELECT id, nombre FROM categorias ORDER BY nombre";
                $result = $conexion->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nombre']) . '</option>';
                    }
                }
                ?>
            </select>

        </div>
        
        <div class="form-group">

            <label>Contenido para adultos:</label>

            <div class="radio-group">

                <label>
                    <input type="radio" name="adulto" value="todos" checked> Mostrar todo
                </label>

                <label>
                    <input type="radio" name="adulto" value="no"> Solo contenido general
                </label>

                <label>
                    <input type="radio" name="adulto" value="si"> Solo contenido para adultos
                </label>

            </div>

        </div>
        
        <button type="submit" class="btn">Buscar</button>

    </form>

</div>

<?php

} else {
    // preparar la búsqueda
    $tipos = isset($_GET['tipo']) ? $_GET['tipo'] : ['libro', 'comic'];
    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
    $adulto = isset($_GET['adulto']) ? $_GET['adulto'] : 'todos';
    
    // construir consulta para libros
    $sql_libros = "SELECT 'libro' as tipo, l.id, l.titulo, l.autor, l.descripcion, l.portada, l.publico_adulto, 
                   c.nombre as categoria, l.fecha_subida
                   FROM libros l
                   LEFT JOIN categorias c ON l.categoria_id = c.id
                   WHERE (l.titulo LIKE ? OR l.autor LIKE ? OR l.descripcion LIKE ?)";
    
    // filtrar por categoría si se especificó
    if (!empty($categoria)) {
        $sql_libros .= " AND l.categoria_id = ?";
    }
    
    // filtrar por contenido adulto
    if ($adulto == 'no') {
        $sql_libros .= " AND l.publico_adulto = 0";
    } elseif ($adulto == 'si') {
        $sql_libros .= " AND l.publico_adulto = 1";
    }
    
    // construir consulta para cómics
    $sql_comics = "SELECT 'comic' as tipo, c.id, c.titulo, c.autor, c.descripcion, c.portada, c.publico_adulto, 
                   NULL as categoria, c.fecha_subida
                   FROM comics c
                   WHERE (c.titulo LIKE ? OR c.autor LIKE ? OR c.descripcion LIKE ?)";
    
    // filtrar por contenido adulto
    if ($adulto == 'no') {
        $sql_comics .= " AND c.publico_adulto = 0";
    } elseif ($adulto == 'si') {
        $sql_comics .= " AND c.publico_adulto = 1";
    }
    
    // combinar resultados si se buscan ambos tipos
    $sql = "";
    $params = [];
    $types = "";
    
    if (in_array('libro', $tipos) && in_array('comic', $tipos)) {
        $sql = "($sql_libros) UNION ($sql_comics) ORDER BY fecha_subida DESC";
        $busqueda_param = "%$busqueda%";
        
        // parámetros para libros
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $types .= "sss";
        
        if (!empty($categoria)) {
            $params[] = $categoria;
            $types .= "i";
        }
        
        // parámetros para cómics
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $types .= "sss";

    } elseif (in_array('libro', $tipos)) {
        $sql = $sql_libros . " ORDER BY fecha_subida DESC";
        $busqueda_param = "%$busqueda%";
        
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $types .= "sss";
        
        if (!empty($categoria)) {
            $params[] = $categoria;
            $types .= "i";
        }

    } elseif (in_array('comic', $tipos)) {
        $sql = $sql_comics . " ORDER BY fecha_subida DESC";
        $busqueda_param = "%$busqueda%";
        
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $types .= "sss";
    }
    
    // ejecutar la consulta
    $stmt = $conexion->prepare($sql);
    
    if (!empty($params)) 
    {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $resultados = $stmt->get_result();
    $total = $resultados->num_rows;
?>

<div class="container">

    <h1>Resultados de búsqueda</h1>

    <p class="search-summary">
        Se encontraron <strong><?php echo $total; ?></strong> resultados para "<strong><?php echo htmlspecialchars($busqueda); ?></strong>"
    </p>
    
    <!-- filtros rápidos -->
    <div class="search-filters">

        <form action="<?php echo BASE_URL; ?>/public/buscar.php" method="GET" class="flex gap-2 items-center">
            <input type="hidden" name="q" value="<?php echo htmlspecialchars($busqueda); ?>">
            
            <div class="filter-group">

                <label>Tipo:</label>

                <div class="flex gap-1">

                    <label class="checkbox-inline">
                        <input type="checkbox" name="tipo[]" value="libro" <?php echo in_array('libro', $tipos) ? 'checked' : ''; ?>> Libros
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="tipo[]" value="comic" <?php echo in_array('comic', $tipos) ? 'checked' : ''; ?>> Cómics
                    </label>

                </div>

            </div>
            
            <div class="filter-group">

                <label for="categoria-filter">Categoría:</label>

                <select name="categoria" id="categoria-filter">
                    <option value="">Todas</option>
                    <?php
                    $sql = "SELECT id, nombre FROM categorias ORDER BY nombre";
                    $result = $conexion->query($sql);
                    
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $selected = ($categoria == $row['id']) ? 'selected' : '';
                            echo '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['nombre']) . '</option>';
                        }
                    }
                    ?>
                </select>

            </div>
            
            <button type="submit" class="btn btn-sm">Filtrar</button>

            <a href="<?php echo BASE_URL; ?>/public/buscar.php" class="btn btn-outline btn-sm">Búsqueda avanzada</a>

        </form>

    </div>
    
    <?php if ($total > 0): ?>

        <div class="obra-grid mt-4">

            <?php while ($obra = $resultados->fetch_assoc()): ?>

                <div class="obra-card">

                    <div class="obra-card-header">

                        <h3><?php echo htmlspecialchars($obra['titulo']); ?></h3>

                        <div class="flex gap-1 mt-1">

                            <?php if ($obra['tipo'] == 'libro' && !empty($obra['categoria'])): ?>
                                <span class="badge badge-categoria"><?php echo htmlspecialchars($obra['categoria']); ?></span>
                            <?php endif; ?>

                            <span class="badge badge-tipo"><?php echo $obra['tipo'] == 'libro' ? 'Libro' : 'Cómic'; ?></span>
                            <?php if ($obra['publico_adulto']): ?>
                                <span class="badge badge-adulto">+18</span>
                            <?php endif; ?>

                        </div>

                    </div>

                    <div class="obra-card-body">

                        <?php if (!empty($obra['portada'])): ?>
                            <img src="<?php echo htmlspecialchars($obra['portada']); ?>" alt="Portada" class="obra-portada">
                        <?php endif; ?>

                        <p class="obra-autor">Por: <?php echo htmlspecialchars($obra['autor']); ?></p>
                        <p class="obra-descripcion"><?php echo substr(htmlspecialchars($obra['descripcion']), 0, 150) . '...'; ?></p>

                    </div>

                    <div class="obra-card-footer">
                        <span class="obra-fecha"><?php echo date('d/m/Y', strtotime($obra['fecha_subida'])); ?></span>
                        <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra['id']; ?>&tipo=<?php echo $obra['tipo']; ?>" class="btn btn-outline">Ver más</a>
                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="no-results">

            <i class="fas fa-search fa-3x"></i>
            <h2>No se encontraron resultados</h2>
            <p>Intenta con otros términos de búsqueda o filtros diferentes.</p>

        </div>

    <?php endif; ?>

</div>

<?php
}

include_once(__DIR__ . '/../includes/footer.php');
?>