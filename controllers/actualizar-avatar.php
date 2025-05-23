<?php
// Verificar sesión y redirigir si no hay usuario logueado
require_once(__DIR__ . '/../includes/funciones.php');
verificarLogin();

// Incluir archivos necesarios
require_once(__DIR__ . '/../includes/conexion.php');

// Obtener ID del usuario actual
$usuario_id = $_SESSION['usuario_id'];

// Directorio para guardar avatares
$directorio_avatares = __DIR__ . '/../uploads/avatars/';

// Crear directorio si no existe
if (!file_exists($directorio_avatares)) {
    mkdir($directorio_avatares, 0755, true);
}

// Verificar si se ha enviado un archivo
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    // Obtener información del archivo
    $archivo_tmp = $_FILES['avatar']['tmp_name'];
    $archivo_nombre = $_FILES['avatar']['name'];
    $archivo_tamano = $_FILES['avatar']['size'];
    $archivo_tipo = $_FILES['avatar']['type'];
    
    // Verificar tipo de archivo
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($archivo_tipo, $tipos_permitidos)) {
        $_SESSION['error'] = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.";
        header("Location: " . BASE_URL . "/usuario/perfil.php");
        exit();
    }
    
    // Verificar tamaño (máximo 2MB)
    $tamano_maximo = 2 * 1024 * 1024; // 2MB en bytes
    if ($archivo_tamano > $tamano_maximo) {
        $_SESSION['error'] = "El archivo es demasiado grande. El tamaño máximo permitido es 2MB.";
        header("Location: " . BASE_URL . "/usuario/perfil.php");
        exit();
    }
    
    // Generar nombre único para el archivo
    $extension = pathinfo($archivo_nombre, PATHINFO_EXTENSION);
    $nombre_archivo = 'avatar_' . $usuario_id . '_' . time() . '.' . $extension;
    $ruta_destino = $directorio_avatares . $nombre_archivo;
    
    // Mover archivo al directorio de avatares
    if (move_uploaded_file($archivo_tmp, $ruta_destino)) {
        // Actualizar ruta del avatar en la base de datos
        $ruta_avatar = BASE_URL . '/uploads/avatars/' . $nombre_archivo;
        
        // Obtener avatar anterior para eliminarlo después
        $sql_avatar_anterior = "SELECT avatar FROM usuarios WHERE id = ?";
        $stmt_avatar = $conexion->prepare($sql_avatar_anterior);
        $stmt_avatar->bind_param("i", $usuario_id);
        $stmt_avatar->execute();
        $resultado = $stmt_avatar->get_result();
        $avatar_anterior = $resultado->fetch_assoc()['avatar'];
        
        // Actualizar en la base de datos
        $sql = "UPDATE usuarios SET avatar = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $ruta_avatar, $usuario_id);
        
        if ($stmt->execute()) {
            // Eliminar avatar anterior si existe y no es el predeterminado
            if (!empty($avatar_anterior) && strpos($avatar_anterior, 'default-avatar.png') === false) {
                $ruta_anterior = str_replace(BASE_URL, __DIR__ . '/..', $avatar_anterior);
                if (file_exists($ruta_anterior)) {
                    unlink($ruta_anterior);
                }
            }
            
            $_SESSION['exito'] = "Avatar actualizado correctamente.";
        } else {
            $_SESSION['error'] = "Error al actualizar el avatar en la base de datos.";
        }
    } else {
        $_SESSION['error'] = "Error al subir el archivo. Por favor, inténtalo de nuevo.";
    }
} else {
    $_SESSION['error'] = "No se ha seleccionado ningún archivo o ha ocurrido un error.";
}

// Redireccionar de vuelta al perfil
header("Location: " . BASE_URL . "/usuario/perfil.php");
exit();
