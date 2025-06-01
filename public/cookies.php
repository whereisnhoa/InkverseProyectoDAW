
<?php
require_once(__DIR__ . '/../includes/header.php');
?>

<div class="container legal-page">

    <div class="legal-header">
        <h1>Política de Cookies</h1>
        <p class="legal-updated">Última actualización: <?php echo date('d/m/Y'); ?></p>
    </div>

    <nav class="legal-nav">

        <ol>
            <li><a href="#que-son">¿Qué son las cookies?</a></li>
            <li><a href="#uso">Cómo utilizamos las cookies</a></li>
            <li><a href="#tipos">Tipos de cookies que utilizamos</a></li>
            <li><a href="#control">Control de cookies</a></li>
            <li><a href="#terceros">Cookies de terceros</a></li>
            <li><a href="#contacto">Más información</a></li>
        </ol>
    </nav>

    <div class="legal-content">

        <section class="legal-section" id="que-son">
            <h2>¿Qué son las cookies?</h2>
            <p>Las cookies son pequeños archivos de texto que se almacenan en su dispositivo cuando visita un sitio web. Sirven para recordar sus preferencias y mejorar su experiencia de usuario.</p>
        </section>

        <section class="legal-section" id="uso">
            <h2>Cómo utilizamos las cookies</h2>
            <p>Utilizamos cookies para guardar su sesión, preferencias, analizar el uso del sitio y personalizar el contenido.</p>
        </section>

        <section class="legal-section" id="tipos">
            <h2>Tipos de cookies que utilizamos</h2>
            <h3>Cookies esenciales</h3>
            <p>Indispensables para el funcionamiento del sitio.</p>
            <h3>Cookies de rendimiento</h3>
            <p>Nos ayudan a analizar cómo se usa el sitio y a mejorar su funcionamiento.</p>
            <h3>Cookies de funcionalidad</h3>
            <p>Permiten personalizar la experiencia según sus preferencias.</p>
            <h3>Cookies de publicidad</h3>
            <p>Utilizadas para mostrar anuncios relevantes según sus intereses.</p>
        </section>

        <section class="legal-section" id="control">
            <h2>Control de cookies</h2>
            <p>Puede administrar o eliminar las cookies desde la configuración de su navegador. Si las desactiva, algunas partes del sitio podrían no funcionar correctamente.</p>
            <ul class="legal-list">
                <li><a href="https://support.google.com/chrome/answer/95647" target="_blank">Google Chrome</a></li>
                <li><a href="https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web-rastrear-preferencias" target="_blank">Mozilla Firefox</a></li>
                <li><a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" target="_blank">Safari</a></li>
                <li><a href="https://support.microsoft.com/es-es/help/17442/windows-internet-explorer-delete-manage-cookies" target="_blank">Internet Explorer</a></li>
                <li><a href="https://support.microsoft.com/es-es/help/4468242/microsoft-edge-browsing-data-and-privacy" target="_blank">Microsoft Edge</a></li>
            </ul>
        </section>

        <section class="legal-section" id="terceros">
            <h2>Cookies de terceros</h2>
            <p>Utilizamos servicios como Google Analytics para analizar el uso del sitio, y botones de redes sociales. Estos servicios pueden establecer cookies propias. Consulte sus políticas para más información.</p>
        </section>
    
    </div>

</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>