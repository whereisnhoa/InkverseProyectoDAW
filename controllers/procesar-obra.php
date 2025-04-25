
<?php

session_start();

include '../includes/conexion.php';

$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$tipo = $_POST['tipo'];
$genero = $_POST['genero'];
$es_adulto = isset($_POST['es_adulto']) ? 1 : 0;
$autor_id = $_SESSION['usuario_id'];

$sql = "INSERT INTO obras (titulo, descripcion, tipo, genero, es_adulto, autor_id)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssssii", $titulo, $descripcion, $tipo, $genero, $es_adulto, $autor_id);

if ($stmt->execute()) 
{
    echo "Obra subida correctamente.";
    header("Location: ../usuarios/mis-obras.php");

}else
    {
        echo "Error al guardar la obra: " . $stmt->error;
    }

?>
