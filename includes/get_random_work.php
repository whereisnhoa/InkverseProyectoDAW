<?php
// Incluir el archivo de conexión a la base de datos
// Asegúrate de que la ruta sea correcta desde este archivo (includes/get_random_work.php)
require_once(__DIR__ . '/conexion.php');
require_once(__DIR__ . '/config.php'); // Necesario para BASE_URL si no está ya en conexión.php

header('Content-Type: application/json'); // Indicar que la respuesta es JSON

$response = ['success' => false, 'message' => ''];
$type = $_GET['type'] ?? ''; // Obtener el tipo de obra (libro o comic) de la URL

if (!in_array($type, ['libro', 'comic'])) {
    $response['message'] = 'Tipo de obra no válido.';
    echo json_encode($response);
    exit;
}

try {
    // Consulta SQL para seleccionar una obra aleatoria del tipo especificado
    if ($type === 'libro') {
        $sql = "SELECT 
                    l.id, 
                    l.titulo, 
                    l.autor, 
                    l.descripcion, 
                    l.portada, 
                    c.nombre AS categoria,
                    'libro' AS tipo_obra
                FROM libros l
                LEFT JOIN categorias c ON l.categoria_id = c.id
                ORDER BY RAND() LIMIT 1"; // RAND() para aleatorio, LIMIT 1 para una sola obra
    } else { // type === 'comic'
        $sql = "SELECT 
                    id, 
                    titulo, 
                    autor, 
                    descripcion, 
                    portada,
                    NULL AS categoria, -- Los cómics no tienen categoría asociada directamente en tu esquema
                    'comic' AS tipo_obra
                FROM comics
                ORDER BY RAND() LIMIT 1";
    }

    $result = $conexion->query($sql);

    if ($result && $result->num_rows > 0) {
        $work = $result->fetch_assoc();
        $response['success'] = true;
        $response['work'] = $work;
    } else {
        $response['message'] = 'No se encontraron obras de este tipo.';
    }

} catch (Exception $e) {
    $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    // En un entorno de producción, no mostrar $e->getMessage() directamente
    // sino un mensaje genérico y registrar el error.
} finally {
    // Cerrar la conexión si se abrió aquí y no se maneja globalmente
    if (isset($conexion) && $conexion instanceof mysqli && !$conexion->connect_error) {
        $conexion->close();
    }
}

echo json_encode($response);
?>