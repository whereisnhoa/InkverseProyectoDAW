
<?php
// Archivo de depuración para identificar problemas
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Depuración del Proyecto</h1>";

// Verificar la estructura de archivos
echo "<h2>Verificación de Archivos Críticos</h2>";
$archivos_criticos = [
    'includes/config.php',
    'includes/conexion.php',
    'includes/header.php',
    'includes/footer.php',
    'includes/funciones.php',
    'public/index.php'
];

foreach ($archivos_criticos as $archivo) {
    if (file_exists(__DIR__ . '/' . $archivo)) {
        echo "<p style='color:green'>✓ El archivo {$archivo} existe</p>";
        
        // Verificar si el archivo tiene errores de sintaxis
        $contenido = file_get_contents(__DIR__ . '/' . $archivo);
        try {
            eval('?>' . $contenido);
            echo "<p style='color:green'>  ✓ El archivo {$archivo} no tiene errores de sintaxis</p>";
        } catch (ParseError $e) {
            echo "<p style='color:red'>  ✗ Error de sintaxis en {$archivo}: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:red'>✗ El archivo {$archivo} NO existe</p>";
    }
}

// Verificar la configuración
echo "<h2>Configuración</h2>";
if (file_exists(__DIR__ . '/includes/config.php')) {
    require_once(__DIR__ . '/includes/config.php');
    echo "<p>BASE_PATH: " . BASE_PATH . "</p>";
    echo "<p>BASE_URL: " . BASE_URL . "</p>";
    
    // Verificar si las rutas son correctas
    if (is_dir(BASE_PATH)) {
        echo "<p style='color:green'>✓ La ruta BASE_PATH es válida</p>";
    } else {
        echo "<p style='color:red'>✗ La ruta BASE_PATH NO es válida</p>";
    }
}

// Verificar la conexión a la base de datos
echo "<h2>Conexión a la Base de Datos</h2>";
if (file_exists(__DIR__ . '/includes/conexion.php')) {
    try {
        require_once(__DIR__ . '/includes/conexion.php');
        if (isset($conexion) && $conexion instanceof mysqli) {
            echo "<p style='color:green'>✓ Conexión a la base de datos establecida correctamente</p>";
            
            // Verificar si podemos hacer una consulta simple
            $result = $conexion->query("SELECT 1");
            if ($result) {
                echo "<p style='color:green'>✓ Consulta de prueba ejecutada correctamente</p>";
            } else {
                echo "<p style='color:red'>✗ Error al ejecutar consulta de prueba: " . $conexion->error . "</p>";
            }
        } else {
            echo "<p style='color:red'>✗ No se pudo establecer la conexión a la base de datos</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error al cargar conexion.php: " . $e->getMessage() . "</p>";
    }
}

// Verificar la sesión
echo "<h2>Sesión</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "<p style='color:green'>✓ Sesión iniciada correctamente</p>";
} else {
    echo "<p style='color:green'>✓ La sesión ya estaba iniciada</p>";
}

// Mostrar variables de sesión
echo "<h3>Variables de Sesión</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Información del servidor
echo "<h2>Información del Servidor</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Prueba de inclusión del header
echo "<h2>Prueba de Inclusión del Header</h2>";
try {
    ob_start();
    require_once(__DIR__ . '/includes/header.php');
    $header_output = ob_get_clean();
    echo "<p style='color:green'>✓ Header incluido correctamente</p>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color:red'>✗ Error al incluir el header: " . $e->getMessage() . "</p>";
}

echo "<p>Fin de la depuración</p>";
?>
