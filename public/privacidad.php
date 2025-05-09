
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
    
    <div class="legal-content">
        <section class="legal-section">
            <h2>1. Introducción</h2>
            <p>Respetamos su privacidad y nos comprometemos a proteger sus datos personales. Esta política de privacidad le informará sobre cómo cuidamos sus datos personales cuando visita nuestra plataforma y le informará sobre sus derechos de privacidad y cómo la ley le protege.</p>
        </section>
        
        <section class="legal-section">
            <h2>2. Datos que recopilamos</h2>
            <p>Podemos recopilar, usar, almacenar y transferir diferentes tipos de datos personales sobre usted, que hemos agrupado de la siguiente manera:</p>
            <ul class="legal-list">
                <li><strong>Datos de identidad:</strong> incluye nombre, apellido, nombre de usuario o identificador similar.</li>
                <li><strong>Datos de contacto:</strong> incluye dirección de correo electrónico y números de teléfono.</li>
                <li><strong>Datos técnicos:</strong> incluye dirección de protocolo de Internet (IP), datos de inicio de sesión, tipo y versión del navegador, configuración de zona horaria y ubicación, tipos y versiones de complementos del navegador, sistema operativo y plataforma.</li>
                <li><strong>Datos de perfil:</strong> incluye su nombre de usuario y contraseña, sus intereses, preferencias, comentarios y respuestas a encuestas.</li>
                <li><strong>Datos de uso:</strong> incluye información sobre cómo utiliza nuestra plataforma y servicios.</li>
            </ul>
        </section>
        
        <section class="legal-section">
            <h2>3. Cómo recopilamos sus datos</h2>
            <p>Utilizamos diferentes métodos para recopilar datos de y sobre usted, incluyendo:</p>
            <ul class="legal-list">
                <li><strong>Interacción directa:</strong> Puede proporcionarnos sus datos de identidad, contacto y perfil completando formularios o comunicándose con nosotros.</li>
                <li><strong>Tecnologías automatizadas:</strong> A medida que interactúa con nuestra plataforma, podemos recopilar automáticamente datos técnicos sobre su equipo, acciones de navegación y patrones.</li>
                <li><strong>Terceros:</strong> Podemos recibir datos personales sobre usted de varios terceros, como proveedores de análisis o redes sociales.</li>
            </ul>
        </section>
        
        <section class="legal-section">
            <h2>4. Cómo utilizamos sus datos</h2>
            <p>Solo utilizaremos sus datos personales cuando la ley nos lo permita. Más comúnmente, utilizaremos sus datos personales en las siguientes circunstancias:</p>
            <ul class="legal-list">
                <li>Para registrar su cuenta y gestionar nuestra relación con usted.</li>
                <li>Para proporcionarle contenido personalizado y recomendaciones.</li>
                <li>Para mejorar nuestra plataforma, productos/servicios y experiencia del usuario.</li>
                <li>Para comunicarnos con usted, incluyendo notificaciones sobre cambios en nuestros términos o políticas.</li>
                <li>Para administrar y proteger nuestro negocio y esta plataforma.</li>
            </ul>
        </section>
        
        <section class="legal-section">
            <h2>5. Divulgación de sus datos personales</h2>
            <p>Podemos compartir sus datos personales con las partes establecidas a continuación:</p>
            <ul class="legal-list">
                <li>Proveedores de servicios que nos proporcionan servicios de TI y administración de sistemas.</li>
                <li>Asesores profesionales, incluidos abogados, banqueros, auditores y aseguradores.</li>
                <li>Autoridades fiscales, reguladoras y otras autoridades.</li>
                <li>Terceros a quienes podemos elegir vender, transferir o fusionar partes de nuestro negocio o nuestros activos.</li>
            </ul>
            <p>Exigimos a todos los terceros que respeten la seguridad de sus datos personales y los traten de acuerdo con la ley.</p>
        </section>
        
        <section class="legal-section">
            <h2>6. Seguridad de datos</h2>
            <p>Hemos implementado medidas de seguridad apropiadas para evitar que sus datos personales se pierdan, usen o accedan accidentalmente de manera no autorizada, se alteren o divulguen. Además, limitamos el acceso a sus datos personales a aquellos empleados, agentes, contratistas y otros terceros que tienen una necesidad comercial de conocerlos.</p>
        </section>
        
        <section class="legal-section">
            <h2>7. Retención de datos</h2>
            <p>Solo conservaremos sus datos personales durante el tiempo necesario para cumplir con los fines para los que los recopilamos, incluyendo para satisfacer cualquier requisito legal, contable o de informes.</p>
        </section>
        
        <section class="legal-section">
            <h2>8. Sus derechos legales</h2>
            <p>Bajo ciertas circunstancias, usted tiene derechos bajo las leyes de protección de datos en relación con sus datos personales, incluyendo el derecho a:</p>
            <ul class="legal-list">
                <li>Solicitar acceso a sus datos personales.</li>
                <li>Solicitar la corrección de sus datos personales.</li>
                <li>Solicitar la eliminación de sus datos personales.</li>
                <li>Oponerse al procesamiento de sus datos personales.</li>
                <li>Solicitar la restricción del procesamiento de sus datos personales.</li>
                <li>Solicitar la transferencia de sus datos personales.</li>
                <li>Retirar el consentimiento.</li>
            </ul>
        </section>
        
        <section class="legal-section">
            <h2>9. Cookies</h2>
            <p>Nuestra plataforma utiliza cookies para distinguirlo de otros usuarios. Esto nos ayuda a proporcionarle una buena experiencia cuando navega por nuestra plataforma y también nos permite mejorarla. Para obtener información detallada sobre las cookies que utilizamos, consulte nuestra <a href="<?php echo BASE_URL; ?>/public/cookies.php">Política de Cookies</a>.</p>
        </section>
        
        <section class="legal-section">
            <h2>10. Cambios a esta política de privacidad</h2>
            <p>Cualquier cambio que podamos hacer a nuestra política de privacidad en el futuro se publicará en esta página. Por favor, revise esta página con frecuencia para ver cualquier actualización o cambio en nuestra política de privacidad.</p>
        </section>
        
    </div>
</div>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
