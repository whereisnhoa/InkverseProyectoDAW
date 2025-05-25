
<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/conexion.php');

header('Content-Type: application/json');

// Obtener tipo de obra
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';

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

try {
    $obra_aleatoria = obtenerObraAleatoria($conexion, $tipo);
    
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
    
} catch (Exception $e) {
    error_log("Error en contenido-aleatorio.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error del servidor'
    ]);
}
?>