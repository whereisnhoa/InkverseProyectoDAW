<?php
session_start();
include '../includes/conexion.php';

function mostrarMensaje($tipo, $mensaje, $obra_id, $tipo_obra) {
    // $tipo: "success" o "error"
    $clase = $tipo === 'success' ? 'success-message' : 'error-message';
    $btn = '<a class="btn" href="../usuario/subir_capitulo.php?tipo=' . htmlspecialchars($tipo_obra) . '&obra_id=' . intval($obra_id) . '">Subir otro capítulo</a>';
    $btn2 = '<a class="btn btn-outline" href="../usuario/mis_obras.php">Volver a mis obras</a>';
    echo <<<HTML
    <div class="$clase">$mensaje</div>
    <div style="text-align:center; margin-top:18px;">
        $btn
        $btn2
    </div>
    HTML;
}

// 1. Seguridad: solo usuarios logueados
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$tipo_obra = $_POST['tipo'];
$obra_id = intval($_POST['obra_id']);
$numero = intval($_POST['numero']);
$titulo = trim($_POST['titulo']);
$modo_capitulo = $_POST['modo_capitulo'];

// Inicializa variables
$archivo = null;
$contenido = null;
$tipo_archivo = null;
$num_paginas = null;

// Según el modo, guarda el archivo o el texto
if ($modo_capitulo === 'texto') {
    $contenido = $_POST['contenido'];
    $tipo_archivo = 'texto';
} else if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
    $nombre = uniqid() . '_' . basename($_FILES['archivo']['name']);
    if ($modo_capitulo === 'pdf') {
        $destino = "../uploads/capitulos/";
        $tipo_archivo = 'pdf';
    } else {
        $destino = "../uploads/capitulos_img/";
        $tipo_archivo = 'imagen';
    }
    // Crea carpeta si no existe
    if (!is_dir($destino)) {
        mkdir($destino, 0777, true);
    }
    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $destino . $nombre)) {
        $archivo = $nombre;
        // Si quieres intentar contar páginas PDF o imágenes, pon aquí tu lógica
    } else {
        // Error al subir archivo
        echo '<style>
        .error-message {
            background: #ffd5d5; color: #a23333; padding: 15px 20px; border-radius: 7px;
            margin: 23px auto; max-width: 440px; text-align: center; font-weight: bold; font-size: 1.1em;
        }
        </style>';
        mostrarMensaje('error', "Error al subir el archivo al servidor.", $obra_id, $tipo_obra);
        exit;
    }
} else if ($modo_capitulo !== 'texto') {
    // No se subió archivo pero se seleccionó modo archivo
    echo '<style>
    .error-message {
        background: #ffd5d5; color: #a23333; padding: 15px 20px; border-radius: 7px;
        margin: 23px auto; max-width: 440px; text-align: center; font-weight: bold; font-size: 1.1em;
    }
    </style>';
    mostrarMensaje('error', "Debes seleccionar un archivo válido.", $obra_id, $tipo_obra);
    exit;
}

// Inserta en la tabla correspondiente
if ($tipo_obra === 'libro') {
    $sql = "INSERT INTO capitulos_libro (libro_id, numero, titulo, contenido, archivo, fecha_subida)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iisss", $obra_id, $numero, $titulo, $contenido, $archivo);
} else {
    // Para cómic, incluye tipo_archivo y num_paginas si están en la tabla
    $sql = "INSERT INTO capitulos_comic (comic_id, numero, titulo, contenido, archivo, tipo_archivo, num_paginas, fecha_subida)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iissssi", $obra_id, $numero, $titulo, $contenido, $archivo, $tipo_archivo, $num_paginas);
}

// Estilos para los mensajes
echo '<style>
.success-message {
    background: #e4faea; color: #23734e; padding: 15px 20px; border-radius: 7px;
    margin: 23px auto; max-width: 440px; text-align: center; font-weight: bold; font-size: 1.1em;
}
.error-message {
    background: #ffd5d5; color: #a23333; padding: 15px 20px; border-radius: 7px;
    margin: 23px auto; max-width: 440px; text-align: center; font-weight: bold; font-size: 1.1em;
}
.btn {
    background: #4361ee; color: #fff; border: none; padding: 11px 26px; border-radius: 5px;
    font-weight: 600; font-size: 15px; cursor: pointer; transition: background 0.2s, box-shadow 0.2s;
    margin-right: 10px; box-shadow: 0 2px 8px rgba(67,97,238,0.10); text-decoration:none; display:inline-block;
}
.btn-outline {
    background: #fff; color: #4361ee !important; border: 1.5px solid #4361ee; box-shadow: none;
}
.btn:hover, .btn-outline:hover {
    background: #3a56d4; color: #fff !important; border-color: #3a56d4;
}
</style>';

// Ejecuta y muestra mensaje correspondiente
if ($stmt->execute()) {
    mostrarMensaje('success', "¡Capítulo subido correctamente!", $obra_id, $tipo_obra);
} else {
    mostrarMensaje('error', "Error al guardar el capítulo: " . htmlspecialchars($stmt->error), $obra_id, $tipo_obra);
}
?>