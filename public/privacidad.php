<?php
/**
 * Página de Política de Privacidad
 */
require_once(__DIR__ . '/../includes/header.php');
?>
<div class="container legal-page">

    <div class="legal-header">
        <h1>Política de Privacidad</h1>
        <p class="legal-updated">Última actualización: <?php echo date('d/m/Y'); ?></p>
    </div>

    <nav class="legal-nav">
        <ol>
            <li><a href="#introduccion">1. Introducción</a></li>
            <li><a href="#datos">2. Datos que Recopilamos</a></li>
            <li><a href="#recopilacion">3. Cómo Recopilamos Datos</a></li>
            <li><a href="#uso">4. Cómo Usamos Sus Datos</a></li>
            <li><a href="#divulgacion">5. Divulgación de Datos</a></li>
            <li><a href="#seguridad">6. Seguridad</a></li>
            <li><a href="#retencion">7. Retención de Datos</a></li>
            <li><a href="#derechos">8. Sus Derechos</a></li>
            <li><a href="#cookies">9. Cookies</a></li>
            <li><a href="#cambios">10. Cambios a esta Política</a></li>
        </ol>
    </nav>

    <div class="legal-content">
        <section class="legal-section" id="introduccion">
            <h2>1. Introducción</h2>
            <p>
                Respetamos su privacidad y nos comprometemos a proteger sus datos personales. Esta política explica cómo recogemos, usamos y protegemos su información.
            </p>
        </section>

        <section class="legal-section" id="datos">
            <h2>2. Datos que Recopilamos</h2>

            <ul class="legal-list">
                <li><strong>Identidad:</strong> nombre, usuario, identificador.</li>
                <li><strong>Contacto:</strong> correo electrónico, teléfono.</li>
                <li><strong>Técnicos:</strong> IP, navegador, dispositivo, sistema operativo.</li>
                <li><strong>Perfil:</strong> intereses, preferencias, comentarios.</li>
                <li><strong>Uso:</strong> páginas visitadas, acciones realizadas.</li>
            </ul>

        </section>

        <section class="legal-section" id="recopilacion">
            <h2>3. Cómo Recopilamos Sus Datos</h2>

            <ul class="legal-list">
                <li><strong>Directamente:</strong> cuando se registra o rellena formularios.</li>
                <li><strong>Automáticamente:</strong> mediante cookies y tecnologías similares al navegar.</li>
                <li><strong>Terceros:</strong> como Google Analytics o redes sociales.</li>
            </ul>

        </section>

        <section class="legal-section" id="uso">
            <h2>4. Cómo Usamos Sus Datos</h2>

            <ul class="legal-list">
                <li>Para crear y gestionar su cuenta.</li>
                <li>Para personalizar su experiencia y mostrar contenido relevante.</li>
                <li>Para mejorar nuestra plataforma y prevenir fraudes.</li>
                <li>Para enviarle comunicaciones importantes.</li>
            </ul>

        </section>

        <section class="legal-section" id="divulgacion">
            <h2>5. Divulgación de Sus Datos</h2>

            <ul class="legal-list">
                <li>Proveedores de servicios de hosting, TI y soporte.</li>
                <li>Asesores legales, auditores y autoridades.</li>
                <li>Empresas en caso de fusión o adquisición.</li>
            </ul>

            <p>Requerimos a todos los terceros que respeten la confidencialidad y protejan sus datos.</p>
        </section>

        <section class="legal-section" id="seguridad">
            <h2>6. Seguridad de Datos</h2>
            <p>
                Adoptamos medidas técnicas y organizativas para proteger su información contra pérdida, uso indebido o acceso no autorizado.
            </p>
        </section>

        <section class="legal-section" id="retencion">
            <h2>7. Retención de Datos</h2>
            <p>
                Conservamos sus datos solo el tiempo necesario para cumplir las finalidades para las que se recogieron, o mientras la ley lo exija.
            </p>
        </section>

        <section class="legal-section" id="derechos">
            <h2>8. Sus Derechos</h2>

            <ul class="legal-list">
                <li>Acceder, corregir o eliminar sus datos.</li>
                <li>Oponerse o restringir su tratamiento.</li>
                <li>Solicitar portabilidad o retirar consentimiento.</li>
            </ul>

        </section>

        <section class="legal-section" id="cookies">
            <h2>9. Cookies</h2>
            <p>
                Usamos cookies para distinguirle de otros usuarios y mejorar su experiencia. Más información en nuestra <a href="cookies.php">Política de Cookies</a>.
            </p>
        </section>

        <section class="legal-section" id="cambios">
            <h2>10. Cambios a esta Política</h2>
            <p>
                Nos reservamos el derecho de modificar esta política. Los cambios se publicarán en esta página. Revísela periódicamente.
            </p>
        </section>

    </div>

</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>