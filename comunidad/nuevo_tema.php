<?php
require_once('../includes/config.php');
require_once('../includes/conexion.php');
require_once('../includes/funciones.php');
include_once('../includes/languages/es.php');
session_start();

// Si no está logueado, redirige
if(!isset($_SESSION['usuario_id'])){
    header("Location: ".BASE_URL."/public/login.php");
    exit;
}

// Obtener categorías para el select
$sql = "SELECT id, nombre FROM categorias_foro ORDER BY nombre ASC";
$categorias = $conexion->query($sql);

$cat_preseleccionada = isset($_GET['cat']) ? intval($_GET['cat']) : 0;

// Mensaje de error
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$error = '';
if ($msg === 'datos') $error = $TEXTOS['foros_error_datos'];
if ($msg === 'error') $error = $TEXTOS['foros_error_publicar'];
if ($msg === 'csrf') $error = $TEXTOS['foros_error_csrf'];

include '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">

<style>
.nuevo-tema-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 75vh;
    padding: 56px 0 60px 0;
}
.nuevo-tema-card {
    background: #fff;
    border-radius: 32px;
    box-shadow: 0 8px 44px #d0abf040, 0 2px 6px #fbefff44;
    padding: 48px 42px 38px 42px;
    max-width: 520px;
    width: 100%;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.nuevo-tema-title {
    font-size: 2.2rem;
    font-weight: 900;
    background: linear-gradient(90deg, #a16ae8 10%, #fcb7ff 100%);
    color: transparent;
    background-clip: text;
    -webkit-background-clip: text;
    margin-bottom: 24px;
    text-align: center;
    text-shadow: 0 2px 16px #e6dbfa44;
}
.nuevo-tema-form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 18px;
    align-items: center;
}
.nuevo-tema-label {
    font-weight: 700;
    color: #6e38b1;
    margin-bottom: 4px;
    text-align: left;
    width: 100%;
}
.nuevo-tema-select, .nuevo-tema-input, .nuevo-tema-textarea {
    width: 100%;
    border-radius: 12px;
    border: 1.8px solid #e8d7fb;
    padding: 12px;
    font-size: 1rem;
    margin-bottom: 0;
    background: #f8f4ff;
    transition: border .15s;
}
.nuevo-tema-select:focus, .nuevo-tema-input:focus, .nuevo-tema-textarea:focus {
    outline: none;
    border: 2px solid #a16ae8;
    background: #fff;
}
.nuevo-tema-textarea {
    min-height: 110px;
    resize: vertical;
}
.nuevo-tema-btn {
    margin-top: 16px;
    background: linear-gradient(90deg, #a16ae8 0%, #fcb7ff 100%);
    color: #fff;
    font-weight: 800;
    font-size: 1.15rem;
    border: none;
    border-radius: 16px;
    padding: 14px 38px;
    cursor: pointer;
    box-shadow: 0 2px 14px #d1b3fc55;
    transition: filter .15s, transform .15s;
    display: flex;
    align-items: center;
    gap: 10px;
}
.nuevo-tema-btn:hover {
    filter: brightness(1.08);
    transform: scale(1.03);
}
.nuevo-tema-error {
    color: #b7375b;
    background: #fff0f3;
    border: 1px solid #fcb7ff;
    border-radius: 8px;
    padding: 8px 16px;
    margin-bottom: 10px;
    width: 100%;
    text-align: center;
    font-weight: 600;
}
@media (max-width: 700px) {
    .nuevo-tema-card { padding: 20px 2vw; }
    .nuevo-tema-title { font-size: 1.4rem; }
    .nuevo-tema-btn { padding: 10px 15px; font-size: 1rem; }
}
</style>

<div class="nuevo-tema-container">
    <div class="nuevo-tema-card">
        <h1 class="nuevo-tema-title"><?php echo $TEXTOS['foros_nuevo_tema']; ?></h1>

        <?php if ($error): ?>
            <div class="nuevo-tema-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form class="nuevo-tema-form" action="../controllers/procesar-nuevo-tema.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
            <label class="nuevo-tema-label" for="categoria"><?php echo $TEXTOS['foros_categoria']; ?></label>
            <select class="nuevo-tema-select" name="categoria" id="categoria" required>
                <option value=""><?php echo $TEXTOS['foros_select_categoria']; ?></option>
                <?php while($cat = $categorias->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php if ($cat_preseleccionada == $cat['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label class="nuevo-tema-label" for="titulo"><?php echo $TEXTOS['foros_titulo_tema']; ?></label>
            <input class="nuevo-tema-input" type="text" id="titulo" name="titulo" maxlength="100" required>

            <label class="nuevo-tema-label" for="mensaje"><?php echo $TEXTOS['foros_mensaje']; ?></label>
            <textarea class="nuevo-tema-textarea" id="mensaje" name="mensaje" maxlength="1000" required></textarea>

            <button class="nuevo-tema-btn" type="submit">
                <i class="fas fa-plus"></i> <?php echo $TEXTOS['foros_nuevo_tema']; ?>
            </button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>