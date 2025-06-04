<?php
require_once('../includes/config.php');
require_once('../includes/conexion.php');
require_once('../includes/funciones.php');
// Carga el idioma según tu lógica, aquí ejemplo en español:
include_once('../includes/languages/es.php');
session_start();

$tema_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($tema_id < 1) {
    header('Location: ' . BASE_URL . '/comunidad/');
    exit;
}

// Obtener datos del tema, categoría, autor y avatar
$sql = "SELECT tf.*, cf.nombre AS categoria, u.usuario AS autor, u.avatar AS autor_avatar
        FROM temas_foro tf
        JOIN categorias_foro cf ON tf.categoria_id = cf.id
        JOIN usuarios u ON tf.usuario_id = u.id
        WHERE tf.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $tema_id);
$stmt->execute();
$result = $stmt->get_result();
$tema = $result->fetch_assoc();
$stmt->close();

if (!$tema) {
    include '../includes/header.php';
    echo '<div class="foro-container"><div class="tema-noexiste">'.$TEXTOS['foro_tema_no_encontrado'].'</div></div>';
    include '../includes/footer.php';
    exit;
}

// Obtener respuestas (incluyendo avatar, likes y anidamiento)
$sql_resp = "SELECT rf.*, u.usuario as autor, u.avatar as autor_avatar,
                (SELECT COUNT(*) FROM respuestas_likes rl WHERE rl.respuesta_id = rf.id) as likes
             FROM respuestas_foro rf
             JOIN usuarios u ON rf.usuario_id = u.id
             WHERE rf.tema_id = ?
             ORDER BY rf.fecha ASC";
$stmt = $conexion->prepare($sql_resp);
$stmt->bind_param("i", $tema_id);
$stmt->execute();
$result_resp = $stmt->get_result();
$respuestas = [];
foreach ($result_resp as $row) {
    $parent = $row['respuesta_a'] ?? null;
    if ($parent) {
        $respuestas['hijos'][$parent][] = $row;
    } else {
        $respuestas['raiz'][] = $row;
    }
}
$stmt->close();

// Saber si el usuario dio like a cada respuesta
$likes_usuario = [];
if (isset($_SESSION['usuario_id'])) {
    $sql_likes = "SELECT respuesta_id FROM respuestas_likes WHERE usuario_id = ?";
    $stmt = $conexion->prepare($sql_likes);
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result_likes = $stmt->get_result();
    while ($like = $result_likes->fetch_assoc()) {
        $likes_usuario[$like['respuesta_id']] = true;
    }
    $stmt->close();
}

// Procesamiento de nueva respuesta
$respuesta_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenido']) && isset($_SESSION['usuario_id'])) {
    $contenido = trim($_POST['contenido']);
    $respuesta_a = isset($_POST['respuesta_a']) ? intval($_POST['respuesta_a']) : null;
    if ($contenido !== '') {
        $sql_new = "INSERT INTO respuestas_foro (tema_id, usuario_id, contenido, fecha, respuesta_a) VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $conexion->prepare($sql_new);
        $stmt->bind_param("iisi", $tema_id, $_SESSION['usuario_id'], $contenido, $respuesta_a);
        $stmt->execute();
        $stmt->close();

        // Notificación al autor de la respuesta original si es respuesta anidada
        if ($respuesta_a) {
            $sql_owner = "SELECT usuario_id FROM respuestas_foro WHERE id = ?";
            $stmt_owner = $conexion->prepare($sql_owner);
            $stmt_owner->bind_param("i", $respuesta_a);
            $stmt_owner->execute();
            $stmt_owner->bind_result($dueño_id);
            $stmt_owner->fetch();
            $stmt_owner->close();
            if ($dueño_id != $_SESSION['usuario_id']) {
                crearNotificacion($dueño_id, 'respuesta_foro', $TEXTOS['foro_han_respondido_a_tu_comentario'], null);
            }
        } else {
            if ($tema['usuario_id'] != $_SESSION['usuario_id']) {
                crearNotificacion($tema['usuario_id'], 'respuesta_foro', $TEXTOS['foro_han_respondido_a_tu_tema'], null);
            }
        }

        $sql_upd = "UPDATE temas_foro SET ultima_actividad = NOW() WHERE id = ?";
        $stmt = $conexion->prepare($sql_upd);
        $stmt->bind_param("i", $tema_id);
        $stmt->execute();
        $stmt->close();

        header('Location: ' . BASE_URL . '/comunidad/tema.php?id=' . $tema_id);
        exit;
    } else {
        $respuesta_error = $TEXTOS['foro_respuesta_vacia'];
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/comunidad.css">

<div class="foro-container">
    <div class="tema-block">
        <div class="tema-titulo">
            <h1><?php echo htmlspecialchars($tema['titulo'] ?? ''); ?></h1>
        </div>
        <div class="tema-meta">
            <span><i class="fas fa-layer-group"></i> <b><?php echo $TEXTOS['foro_categoria']; ?>:</b> <?php echo htmlspecialchars($tema['categoria'] ?? ''); ?></span>
            <span><i class="fas fa-user"></i> <b><?php echo $TEXTOS['foro_autor']; ?>:</b> <?php echo htmlspecialchars($tema['autor'] ?? ''); ?></span>
            <span><i class="fas fa-clock"></i> <b><?php echo $TEXTOS['foro_publicado']; ?>:</b> <?php echo isset($tema['fecha']) ? date('d/m/Y H:i', strtotime($tema['fecha'])) : ''; ?></span>
        </div>
        <div class="tema-descripcion">
            <?php echo nl2br(htmlspecialchars($tema['descripcion'] ?? '')); ?>
        </div>
    </div>

    <div class="tema-respuestas-block">
        <h2><i class="fas fa-reply"></i> <?php echo $TEXTOS['foro_respuestas']; ?></h2>
        <?php
        function mostrar_respuestas($lista, $hijos, $nivel = 0, $likes_usuario = [], $TEXTOS) {
            foreach ($lista as $respuesta) {
                $avatar = !empty($respuesta['autor_avatar']) ? BASE_URL . '/uploads/avatars/' . $respuesta['autor_avatar'] : BASE_URL . '/img/default_avatar.png';
                $usuarioYaDioLike = isset($likes_usuario[$respuesta['id']]);
                ?>
                <div class="tema-respuesta-item<?php if($nivel) echo ' respuesta-anidada'; ?>" id="respuesta-<?php echo $respuesta['id']; ?>">
                    <div class="respuesta-usuario">
                        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="avatar" class="respuesta-avatar">
                        <span class="respuesta-autor"><?php echo htmlspecialchars($respuesta['autor']); ?></span>
                        <span class="respuesta-fecha">
                            <i class="fas fa-clock"></i>
                            <?php echo isset($respuesta['fecha']) ? date('d/m/Y H:i', strtotime($respuesta['fecha'])) : ''; ?>
                        </span>
                    </div>
                    <div class="respuesta-contenido">
                        <?php echo nl2br(htmlspecialchars($respuesta['contenido'] ?? '')); ?>
                    </div>
                    <div class="respuesta-acciones">
                        <button class="like-btn<?php if($usuarioYaDioLike) echo ' liked'; ?>" data-id="<?php echo $respuesta['id']; ?>">
                            <i class="fas fa-heart"></i>
                            <span class="like-count"><?php echo (int)($respuesta['likes'] ?? 0); ?></span>
                        </button>
                        <button class="responder-btn" data-id="<?php echo $respuesta['id']; ?>">
                            <i class="fas fa-reply"></i> <?php echo $TEXTOS['foro_responder']; ?>
                        </button>
                    </div>
                    <form class="respuesta-form-anidada" id="form-responder-<?php echo $respuesta['id']; ?>" method="post" style="display:none;">
                        <textarea name="contenido" rows="2" placeholder="<?php echo $TEXTOS['foro_escribe_respuesta']; ?>"></textarea>
                        <input type="hidden" name="respuesta_a" value="<?php echo $respuesta['id']; ?>">
                        <button type="submit" class="btn-mini-grad"><i class="fas fa-paper-plane"></i> <?php echo $TEXTOS['foro_enviar']; ?></button>
                    </form>
                </div>
                <?php
                if (isset($hijos[$respuesta['id']])) {
                    mostrar_respuestas($hijos[$respuesta['id']], $hijos, $nivel + 1, $likes_usuario, $TEXTOS);
                }
            }
        }
        if (!empty($respuestas['raiz'])) {
            mostrar_respuestas($respuestas['raiz'], $respuestas['hijos'] ?? [], 0, $likes_usuario, $TEXTOS);
        } else {
            echo '<div class="tema-sin-respuestas">'.$TEXTOS['foro_no_hay_respuestas'].'</div>';
        }
        ?>
    </div>

    <div class="tema-form-respuesta-block">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <form method="post" class="tema-form-respuesta">
                <?php if ($respuesta_error): ?>
                    <div class="form-error"><?php echo $respuesta_error; ?></div>
                <?php endif; ?>
                <label for="contenido"><b><?php echo $TEXTOS['foro_responder']; ?>:</b></label>
                <textarea id="contenido" name="contenido" rows="4" required placeholder="<?php echo $TEXTOS['foro_escribe_aqui_respuesta']; ?>"></textarea>
                <button type="submit" class="btn-primary-grad"><i class="fas fa-paper-plane"></i> <?php echo $TEXTOS['foro_enviar_respuesta']; ?></button>
            </form>
        <?php else: ?>
            <div class="tema-login-msg">
                <?php echo $TEXTOS['foro_debes_iniciar_sesion']; ?>
                <a href="<?php echo BASE_URL; ?>/public/login.php"><?php echo $TEXTOS['foro_enviar']; ?></a>
                <?php // Puedes mejorar esto con un enlace separado si lo prefieres ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.responder-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        let id = this.getAttribute('data-id');
        let form = document.getElementById('form-responder-' + id);
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    });
});
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const respuestaId = this.getAttribute('data-id');
        fetch('like_respuesta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'respuesta_id=' + encodeURIComponent(respuestaId)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.querySelector('.like-count').textContent = data.likes;
                this.classList.toggle('liked', data.liked);
            }
        });
    });
});
</script>

<style>
/* Tarjeta principal del tema */
.tema-block {
    background: #fff;
    border-radius: 1.5em;
    box-shadow: 0 4px 32px #a259e61b;
    padding: 2.3em 2.2em 1.7em 2.2em;
    margin-bottom: 2.5em;
    margin-top: 1.5em;
    position: relative;
}
.tema-titulo h1 {
    font-size: 2.2em;
    font-weight: 900;
    margin: 0 0 .7em 0;
    background: linear-gradient(90deg,#8c52ff 10%,#ff8cda 90%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text; text-fill-color: transparent;
    text-shadow: 0 4px 18px #a259e626;
    letter-spacing: .5px;
}
.tema-meta {
    font-size: 1.09em;
    color: #7b2ff2;
    margin-bottom: 1.3em;
    display: flex;
    flex-wrap: wrap;
    gap: 2.1em 1.2em;
    align-items: center;
    font-weight: 500;
}
.tema-meta span { display: flex; align-items: center; gap: .3em; }
.tema-meta i { opacity: .88; margin-right: .13em;}
.tema-descripcion {
    color: #4b277e;
    font-size: 1.17em;
    background: #f7f3fe;
    border-radius: .8em;
    padding: 1.1em 1.3em .9em 1.3em;
    margin-bottom: .2em;
    margin-top: .2em;
    line-height: 1.5;
    box-shadow: 0 1.5px 10px #8c52ff09;
    word-break: break-word;
}

/* Respuestas tipo Twitter */
.tema-respuestas-block { margin-bottom: 2.8em; }
.tema-respuestas-block h2 {
    font-size: 1.32em;
    font-weight: 800;
    color: #8c52ff;
    margin-top: 0;
    margin-bottom: 1.2em;
    display: flex;
    align-items: center;
    gap: .3em;
    text-shadow: 0 3px 12px #a259e629;
}
.tema-respuesta-item {
    background: #f9f6ff;
    border-radius: 1.1em;
    box-shadow: 0 1.5px 10px #a259e611;
    margin-bottom: 1.33em;
    padding: 1.2em 1.2em 1em 1.2em;
    transition: box-shadow .13s, background .13s;
    position: relative;
    border: 1.2px solid #f0e8ff;
}
.respuesta-anidada {
    margin-left: 3.2em;
    background: #fcf6ff;
}
.tema-respuesta-item:hover {
    box-shadow: 0 5px 24px #a259e61a;
    background: #f4eaff;
    border-color: #d7bfff;
}
.respuesta-usuario {
    display: flex;
    align-items: center;
    gap: 1.1em;
    margin-bottom: .55em;
}
.respuesta-avatar {
    width: 2.7em;
    height: 2.7em;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 2px 8px #a259e61a;
    border: 2.5px solid #ede3ff;
    background: #fff;
}
.respuesta-autor {
    font-weight: 800;
    color: #7b2ff2;
    font-size: 1.07em;
}
.respuesta-fecha {
    margin-left: auto;
    color: #a259e6;
    font-size: .99em;
    display: flex;
    align-items: center;
    gap: .27em;
    font-weight: 500;
    opacity: .8;
}
.respuesta-contenido {
    color: #3b196b;
    font-size: 1.11em;
    background: #fdfbff;
    border-radius: .7em;
    padding: .95em 1.15em;
    line-height: 1.5;
    word-break: break-word;
    box-shadow: 0 1px 5px #a259e60a;
    margin-bottom: .7em;
}
.respuesta-acciones {
    display: flex;
    align-items: center;
    gap: 1.3em;
    margin-bottom: .5em;
}
.like-btn, .responder-btn {
    background: none;
    border: none;
    color: #a259e6;
    font-size: 1.11em;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .28em;
    transition: color .14s;
    border-radius: .6em;
    padding: .2em .7em;
}
.like-btn.liked, .like-btn:hover {
    color: #d84bb2;
}
.like-count {
    font-weight: 800;
    font-size: 1.08em;
}
.btn-mini-grad {
    background: linear-gradient(90deg,#8c52ff 0,#ff8cda 100%);
    color: #fff;
    border: none;
    border-radius: 1.2em;
    padding: .45em 1.2em;
    font-size: 1em;
    font-weight: 700;
    margin-top: .4em;
    box-shadow: 0 1px 8px #a259e611;
    cursor: pointer;
}
.respuesta-form-anidada {
    margin-top: .8em;
}
.respuesta-form-anidada textarea {
    width: 100%;
    border-radius: .8em;
    border: 1.2px solid #e6d4fb;
    font-size: 1em;
    padding: .7em;
    margin-bottom: .5em;
    background: #fff;
    resize: vertical;
    transition: border .13s;
}
.respuesta-form-anidada textarea:focus { border: 1.2px solid #8c52ff; outline: none;}
/* MENSAJE VACÍO */
.tema-sin-respuestas {
    color: #ba7fc5;
    font-style: italic;
    font-size: 1.15em;
    padding: 1.3em 0;
    text-align: center;
    background: #f7f3fe;
    border-radius: .9em;
    margin-bottom: 1.2em;
}
/* FORMULARIO PRINCIPAL */
.tema-form-respuesta-block {
    background: #f7f3fe;
    border-radius: 1.2em;
    box-shadow: 0 2px 10px #a259e613;
    padding: 1.5em 1.2em 1.2em 1.2em;
    margin-bottom: 1em;
}
.tema-form-respuesta label {
    font-weight: 700;
    color: #7b2ff2;
    display: block;
    margin-bottom: .5em;
    font-size: 1.09em;
}
.tema-form-respuesta textarea {
    width: 100%;
    border-radius: 1em;
    border: 1.5px solid #e6d4fb;
    font-size: 1.13em;
    padding: 1em;
    margin-bottom: 1em;
    background: #fff;
    resize: vertical;
    transition: border .15s;
}
.tema-form-respuesta textarea:focus { border: 1.5px solid #8c52ff; outline: none;}
.btn-primary-grad {
    background: linear-gradient(90deg,#8c52ff 0,#ff8cda 100%);
    color: #fff;
    font-weight: 900;
    border: none;
    border-radius: 1.3em;
    padding: .68em 2.4em;
    font-size: 1.12em;
    box-shadow: 0 2px 14px #a259e618;
    cursor: pointer;
    transition: background .18s, box-shadow .15s;
    margin-top: .5em;
}
.btn-primary-grad:hover {
    background: linear-gradient(90deg,#7b2ff2 0,#c37fc5 100%);
    box-shadow: 0 4px 22px #8c52ff28;
}
.form-error {
    background: #ffd2e6;
    color: #f24481;
    border-radius: .6em;
    padding: .7em 1.2em;
    margin-bottom: 1em;
    font-weight: 700;
}
.tema-login-msg {
    text-align: center;
    color: #8c52ff;
    font-size: 1.14em;
    padding: 1.2em 0;
}
.tema-login-msg a { color: #8c52ff; text-decoration: underline;}
@media (max-width: 700px) {
    .tema-block, .tema-form-respuesta-block { padding: 1em .4em; }
    .tema-titulo h1 { font-size: 1.18em;}
    .respuesta-anidada { margin-left: 1.1em; }
    .respuesta-avatar { width: 2.1em; height: 2.1em; }
}
</style>

<?php include '../includes/footer.php'; ?>