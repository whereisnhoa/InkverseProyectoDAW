<?php
/**
 * Configuración global del proyecto - Versión mejorada
 */

// Función helper para definir constantes de forma segura
function definir_constante($nombre, $valor) {
    if (!defined($nombre)) {
        define($nombre, $valor);
    }
}

// Ruta base del proyecto
$document_root = $_SERVER['DOCUMENT_ROOT'];
$project_folder = '/cozitas/proyecto';

definir_constante('BASE_PATH', $document_root . $project_folder);
definir_constante('BASE_URL', $project_folder);

// Configuración de base de datos
definir_constante('DB_HOST', 'localhost');
definir_constante('DB_USER', 'root');
definir_constante('DB_PASS', '');
definir_constante('DB_NAME', 'proyecto_db');

// Configuración adicional
definir_constante('DB_CHARSET', 'utf8mb4');
definir_constante('SITE_NAME', 'InkVerse');

// Idioma
$idioma = $_GET['lang'] ?? 'es';

// Cargar archivo de idioma
if (!isset($TEXTOS)) {
    $ruta_idioma = __DIR__ . "/languages/$idioma.php";
    if (file_exists($ruta_idioma)) {
        $TEXTOS = include $ruta_idioma;
    } else {
        // Fallback a español si no existe el archivo
        $ruta_es = __DIR__ . "/languages/es.php";
        if (file_exists($ruta_es)) {
            $TEXTOS = include $ruta_es;
        } else {
            // Textos básicos si no hay archivos de idioma
            $TEXTOS = [
                'inicio' => 'Inicio',
                'explorar' => 'Explorar',
                'categorias' => 'Categorías',
                'comunidad' => 'Comunidad',
                'iniciar_sesion' => 'Iniciar Sesión',
                'registrarse' => 'Registrarse'
            ];
        }
    }
}

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de errores (solo en desarrollo)
if (!defined('PRODUCTION') || !PRODUCTION) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
?>