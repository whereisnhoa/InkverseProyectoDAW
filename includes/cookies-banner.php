<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$usuario_logueado = isset($_SESSION['usuario_id']);
require_once __DIR__ . '/idioma.php'; 
?>
<!-- Banner de cookies moderno -->
<style>
.cookie-banner {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    z-index: 2000;
    background: rgba(24, 25, 36, 0.97);
    color: #f0f4fa;
    box-shadow: 0 -4px 18px rgba(44,40,110,0.14);
    font-size: 1.09rem;
    padding: 0;
    border-top: 2.5px solid #4cc9f0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cookie-banner-content {
    max-width: 700px;
    margin: 16px auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;
    width: 100%;
    justify-content: space-between;
    padding: 0 18px;
}
.cookie-banner-text {
    flex: 1 1 320px;
    color: #f0f4fa;
}
.cookie-banner-text a {
    color: #4cc9f0;
    font-weight: 600;
    text-decoration: underline;
}
.cookie-banner-actions {
    display: flex;
    gap: 12px;
}
.cookie-btn {
    padding: 8px 24px;
    border-radius: 9px;
    border: none;
    font-size: 1.02em;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.16s, color 0.16s;
}
.cookie-btn-accept {
    background: #4361ee;
    color: #fff;
    border: 2px solid #4361ee;
}
.cookie-btn-accept:hover, .cookie-btn-accept:focus {
    background: #4cc9f0;
    border-color: #4cc9f0;
    color: #181b2d;
}
.cookie-btn-reject {
    background: transparent;
    color: #b4badb;
    border: 2px solid #35395d;
}
.cookie-btn-reject:hover, .cookie-btn-reject:focus {
    background: #35395d;
    color: #fff;
}
@media (max-width: 600px) {
    .cookie-banner-content { flex-direction: column; gap: 10px; padding: 0 5px;}
    .cookie-banner-text { font-size: 0.97em;}
    .cookie-btn { width: 100%; }
}
</style>
<div id="cookie-banner" class="cookie-banner" style="display:none;">
    <div class="cookie-banner-content">
        <div class="cookie-banner-text">
            <strong><?= htmlspecialchars($TEXTOS['cookies_titulo'] ?? 'Usamos cookies') ?></strong>
            <?= htmlspecialchars($TEXTOS['cookies_desc'] ?? 'para mejorar tu experiencia y analizar el uso del sitio.') ?>
            <a href="<?php echo BASE_URL; ?>/public/cookies.php" target="_blank"><?= htmlspecialchars($TEXTOS['cookies_politica'] ?? 'Política de Cookies') ?></a>.
        </div>
        <div class="cookie-banner-actions">
            <button id="cookie-accept" class="cookie-btn cookie-btn-accept"><?= htmlspecialchars($TEXTOS['cookies_aceptar'] ?? 'Aceptar') ?></button>
            <button id="cookie-reject" class="cookie-btn cookie-btn-reject"><?= htmlspecialchars($TEXTOS['cookies_rechazar'] ?? 'Rechazar') ?></button>
        </div>
    </div>
</div>
<script>
function setCookie(name, value, days) {
    var expires = "";
    if(days){
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/; SameSite=Lax";
}
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(";");
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==" ") c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
document.addEventListener("DOMContentLoaded", function(){
    var usuarioLogueado = <?php echo json_encode($usuario_logueado); ?>;
    var banner = document.getElementById("cookie-banner");
    var consentCookie = getCookie("cookie_consent");
    if(usuarioLogueado) {
        // Usuario logueado: mostrar solo si no hay cookie
        if(!consentCookie) banner.style.display = "flex";
    } else {
        // Usuario NO logueado: mostrar SIEMPRE (no guardar cookie, siempre mostrar aunque se acepte/rechace)
        banner.style.display = "flex";
    }
    document.getElementById("cookie-accept").onclick = function(){
        if(usuarioLogueado) {
            setCookie("cookie_consent", "accepted", 365);
        }
        banner.style.display = "none";
    };
    document.getElementById("cookie-reject").onclick = function(){
        if(usuarioLogueado) {
            setCookie("cookie_consent", "rejected", 365);
        }
        banner.style.display = "none";
    };
});
</script>