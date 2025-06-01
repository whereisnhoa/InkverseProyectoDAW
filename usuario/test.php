<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: public/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test de géneros | InkVerse</title>
    <link rel="stylesheet" href="../css/usuario.css">
    <style>
        .test-container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 28px rgba(67,97,238,0.09);
            padding: 40px 30px 30px 30px;
            text-align: center;
        }
        .test-container h2 {
            color: #4361ee;
            margin-bottom: 18px;
        }
        .test-placeholder {
            color: #666;
            margin-top: 16px;
            font-size: 1.12rem;
            background: #f8faff;
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 1px 7px #4361ee11;
            display: inline-block;
        }
        .test-icon {
            color: #4cc9f0;
            font-size: 2.5rem;
            margin-bottom: 12px;
            display: block;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="test-container">
    <span class="test-icon"><i class="fas fa-question-circle"></i></span>
    <h2>Test: ¿Cuál es tu género favorito?</h2>
    <div class="test-placeholder">
        ¡Muy pronto podrás hacer un test para descubrir tu género favorito de <b>literatura</b> o <b>cómic</b>!<br>
        <small>(Próximamente disponible)</small>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>