
<?php
/**
 * Página de Términos y Condiciones
 */
require_once(__DIR__ . '/../includes/header.php');
?>

<div class="container legal-page">

    <div class="legal-header">
        <h1>Términos y Condiciones</h1>
        <p class="legal-updated">Última actualización: <?php echo date('d/m/Y'); ?></p>
    </div>

    <nav class="legal-nav">
        <ol>
            <li><a href="#introduccion">1. Introducción</a></li>
            <li><a href="#definiciones">2. Definiciones</a></li>
            <li><a href="#registro">3. Registro de Cuenta</a></li>
            <li><a href="#conducta">4. Contenido y Conducta del Usuario</a></li>
            <li><a href="#propiedad">5. Derechos de Propiedad Intelectual</a></li>
            <li><a href="#adultos">6. Contenido para Adultos</a></li>
            <li><a href="#enlaces">7. Enlaces a Otros Sitios Web</a></li>
            <li><a href="#terminacion">8. Terminación</a></li>
            <li><a href="#limite">9. Limitación de Responsabilidad</a></li>
            <li><a href="#cambios">10. Cambios a los Términos</a></li>
            <li><a href="#ley">11. Ley Aplicable</a></li>
        </ol>
    </nav>

    <div class="legal-content">

        <section class="legal-section" id="introduccion">
            <h2>1. Introducción</h2>
            <p>
                Bienvenido a nuestra plataforma de literatura y cómics. Al acceder o utilizar nuestro sitio web, aplicaciones o servicios ("la Plataforma"), usted acepta cumplir con estos Términos y Condiciones. Si no está de acuerdo, por favor no utilice la Plataforma.
            </p>
        </section>

        <section class="legal-section" id="definiciones">
            <h2>2. Definiciones</h2>

            <ul class="legal-list">
                <li><strong>Usuario:</strong> cualquier persona que acceda o utilice la Plataforma.</li>
                <li><strong>Contenido:</strong> textos, gráficos, logotipos, imágenes, clips de audio, descargas digitales, compilaciones de datos y software.</li>
                <li><strong>Contenido del Usuario:</strong> cualquier contenido que los usuarios publiquen, carguen o transmitan a través de la Plataforma.</li>
                <li><strong>Nosotros/Nuestro:</strong> la entidad propietaria y operadora de la Plataforma.</li>
            </ul>

        </section>

        <section class="legal-section" id="registro">
            <h2>3. Registro de Cuenta</h2>
            <p>
                Para acceder a ciertas funciones, debe crear una cuenta. Usted se compromete a:
            </p>
            <ul class="legal-list">
                <li>Proporcionar información veraz y actualizada.</li>
                <li>Mantener la confidencialidad de su contraseña y notificar cualquier uso no autorizado.</li>
                <li>Ser responsable de toda actividad en su cuenta.</li>
            </ul>
            <p>Nos reservamos el derecho de suspender cuentas con información incorrecta o sospecha de uso indebido.</p>
        </section>

        <section class="legal-section" id="conducta">
            <h2>4. Contenido y Conducta del Usuario</h2>
            <p>
                Usted es responsable de todo contenido que publique. Al hacerlo, garantiza que tiene los derechos necesarios y que su contenido no infringe derechos de terceros, no es ilegal ni ofensivo.
            </p>

            <ul class="legal-list">
                <li>No publicar contenido ilegal, ofensivo o que viole derechos de terceros.</li>
                <li>No acosar, amenazar, discriminar o suplantar a otros usuarios.</li>
                <li>No distribuir spam ni realizar actividades fraudulentas.</li>
            </ul>

            <p>Nos reservamos el derecho de eliminar contenido o cuentas que incumplan estas reglas.</p>
        </section>

        <section class="legal-section" id="propiedad">
            <h2>5. Derechos de Propiedad Intelectual</h2>
            <p>
                Todo el contenido original de la Plataforma nos pertenece. Los usuarios conservan derechos sobre su propio contenido, pero nos otorgan una licencia mundial, no exclusiva, para mostrarlo y promover la Plataforma.
            </p>
        </section>

        <section class="legal-section" id="adultos">
            <h2>6. Contenido para Adultos</h2>
            <p>
                Puede haber contenido para mayores de edad. Al acceder, declara tener al menos 18 años. No debe permitir el acceso a menores y debe cumplir las leyes aplicables.
            </p>
        </section>

        <section class="legal-section" id="enlaces">
            <h2>7. Enlaces a Otros Sitios Web</h2>
            <p>
                Podemos mostrar enlaces a sitios externos. No somos responsables de su contenido ni políticas. Revise siempre las políticas de esos sitios antes de usarlos.
            </p>
        </section>

        <section class="legal-section" id="terminacion">
            <h2>8. Terminación</h2>
            <p>
                Podemos suspender o cerrar su cuenta en cualquier momento si viola estos términos o por razones de seguridad. Puede cerrar su cuenta notificándonos.
            </p>
        </section>

        <section class="legal-section" id="limite">
            <h2>9. Limitación de Responsabilidad</h2>
            <p>
                No seremos responsables por daños indirectos, incidentales o consecuentes derivados del uso de la Plataforma. El uso es bajo su propio riesgo.
            </p>
        </section>

        <section class="legal-section" id="cambios">
            <h2>10. Cambios a los Términos y Condiciones</h2>
            <p>
                Podemos modificar estos términos en cualquier momento. Se notificará cualquier cambio importante. El uso continuado implica aceptación de los nuevos términos.
            </p>
        </section>

        <section class="legal-section" id="ley">
            <h2>11. Ley Aplicable</h2>
            <p>
                Estos términos se rigen por las leyes de España y cualquier disputa será resuelta en los tribunales de Madrid, España.
            </p>
        </section>

    </div>

    <div class="legal-footer">
        <p>Al utilizar nuestra Plataforma, usted reconoce que ha leído, entendido y acepta estos Términos y Condiciones.</p>
    </div>

</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>