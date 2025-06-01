
<!-- Banner de cookies moderno -->
<div id="cookie-banner" class="cookie-banner" style="display:none;">
    <div class="cookie-banner-content">
        <div class="cookie-banner-text">
            <strong>Usamos cookies</strong> para mejorar tu experiencia y analizar el uso del sitio. 
            Consulta nuestra <Política href="<?php echo BASE_URL; ?>/public/cookies.php" target="_blank">Política de Cookies</a>.
        </div>
        <div class="cookie-banner-actions">
            <button id="cookie-accept" class="cookie-btn cookie-btn-accept">Aceptar</button>
            <button id="cookie-reject" class="cookie-btn cookie-btn-reject">Rechazar</button>
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
    if(!getCookie("cookie_consent")){
        document.getElementById("cookie-banner").style.display = "block";
    }
    document.getElementById("cookie-accept").onclick = function(){
        setCookie("cookie_consent", "accepted", 365);
        document.getElementById("cookie-banner").style.display = "none";
        // Aquí puedes ejecutar código de scripts de terceros si las cookies fueron aceptadas
    };
    document.getElementById("cookie-reject").onclick = function(){
        setCookie("cookie_consent", "rejected", 365);
        document.getElementById("cookie-banner").style.display = "none";
        // Aquí puedes desactivar scripts de terceros si no se aceptan cookies
    };
});
</script>