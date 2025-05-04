<?php

session_start();
include '../includes/conexion.php';

$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$tipo = $_POST['tipo'];
$genero = $_POST['genero'];
$es_adulto = isset($_POST['es_adulto']) ? 1 : 0;
$autor = $_SESSION['usuario']; // guardar el nombre de usuario como autor

// determinar en qué tabla insertar según el tipo
if ($tipo === 'libro') 
{
    // Obtener la categoría basada en el género
    $sql_categoria = "SELECT id FROM categorias WHERE nombre = ? LIMIT 1";
    $stmt_categoria = $conexion->prepare($sql_categoria);
    $stmt_categoria->bind_param("s", $genero);
    $stmt_categoria->execute();
    $result_categoria = $stmt_categoria->get_result();
    
    if ($result_categoria->num_rows > 0) {
        $categoria = $result_categoria->fetch_assoc();
        $categoria_id = $categoria['id'];
    } else {
        // Si no existe la categoría, usar NULL o una categoría por defecto
        $categoria_id = NULL;
    }
    
    $sql = "INSERT INTO libros (titulo, autor, descripcion, categoria_id, publico_adulto)
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssis", $titulo, $autor, $descripcion, $categoria_id, $es_adulto);

} else {
    // Es un cómic
    $sql = "INSERT INTO comics (titulo, autor, descripcion, publico_adulto)
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssi", $titulo, $autor, $descripcion, $es_adulto);
}

// subir la obra
if ($stmt->execute()) 
{
    echo "Obra subida correctamente.";
    header("Location: ../usuario/mis_obras.php");
} else {
    echo "Error al guardar la obra: " . $stmt->error;
}

?>