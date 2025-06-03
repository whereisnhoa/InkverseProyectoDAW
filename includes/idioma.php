
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$idioma = $_GET['lang'] ?? $_SESSION['lang'] ?? 'es';
$_SESSION['lang'] = $idioma;

// Carga el archivo de idioma correspondiente, o español por defecto
$lang_file = __DIR__ . "/languages/{$idioma}.php";
if (file_exists($lang_file)) {
    require $lang_file;
} else {
    require __DIR__ . "/languages/es.php";
}
?>