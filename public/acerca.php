<?php
/**
 * Página Acerca de InkVerse (versión proyecto, no empresa)
 * Información y valores del proyecto InkVerse
 */
require_once(__DIR__ . '/../includes/header.php');
include_once(__DIR__ . '/../includes/conexion.php');

// Estadísticas dummy (puedes cambiarlas a reales si quieres)
$stats = [
    'obras' => 5,
    'autores' => 1,
    'lectores' => 5,
];
?>

<!-- Sección superior simple -->
<section class="about-top-simple">
    <div class="container about-top-content">
        <div class="about-icon-title">
            <span class="about-icon"><i class="fas fa-info-circle"></i></span>
            <h1><span>Acerca de</span> <span class="about-inkverse">InkVerse</span></h1>
        </div>
        <p class="about-subtitle">
            Donde la <span class="about-gradient">creatividad</span> y la <span class="about-gradient">comunidad</span> conectan.<br>
            Un proyecto de plataforma para lectores, autores y artistas.
        </p>
        <div class="about-stats-row">
            <div class="about-stat">
                <span class="about-stat-num"><?php echo $stats['obras']; ?></span>
                <span class="about-stat-label">Obras publicadas</span>
            </div>
            <div class="about-stat">
                <span class="about-stat-num"><?php echo $stats['autores']; ?></span>
                <span class="about-stat-label">Autores activos</span>
            </div>
            <div class="about-stat">
                <span class="about-stat-num"><?php echo $stats['lectores']; ?></span>
                <span class="about-stat-label">Lectores registrados</span>
            </div>
        </div>
    </div>
</section>

<div class="container">

    <!-- Historia -->
    <section class="about-section" data-aos="fade-up">
        <div class="section-content">
            <div class="section-text">
                <p>
                    <strong>InkVerse</strong> es un proyecto académico nacido en 2025, creado como ejercicio práctico de desarrollo web, diseño y creatividad. No somos una empresa real ni una plataforma en producción, sino un espacio experimental donde se exploran ideas de comunidad, lectura y escritura digital.
                </p>
                <p>
                    La meta fue imaginar cómo podría funcionar una red social creativa para lectores y autores, aprendiendo tecnologías modernas y buenas prácticas de diseño. El espíritu detrás del proyecto es la experimentación, el aprendizaje y el amor por los libros, cómics y el arte narrativo.
                </p>
            </div>
            <div class="section-image">
                <div class="image-placeholder">
                    <img src="<?= BASE_URL; ?>/includes/img/inkverse3.jpg" alt="logo">
                </div>
            </div>
        </div>
    </section>

    <!-- Nuestros Valores -->
    <section class="values-section" data-aos="fade-up">
        <div class="section-header">
            <h2><i class="fas fa-gem"></i> Valores del Proyecto</h2>
            <p>Principios que guían el desarrollo y el diseño de InkVerse.</p>
        </div>
        <div class="values-grid">
            <div class="value-card" data-aos="fade-up" data-aos-delay="100">
                <div class="value-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3>Creatividad</h3>
                <p>Fomentar la originalidad y la experimentación tanto en el diseño como en la funcionalidad.</p>
            </div>
            <div class="value-card" data-aos="fade-up" data-aos-delay="200">
                <div class="value-icon">
                    <i class="fas fa-code"></i>
                </div>
                <h3>Aprendizaje</h3>
                <p>Priorizar el proceso de aprendizaje y la mejora continua en cada parte del proyecto.</p>
            </div>
            <div class="value-card" data-aos="fade-up" data-aos-delay="300">
                <div class="value-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h3>Accesibilidad</h3>
                <p>Diseñar pensando en la diversidad de usuarios y dispositivos.</p>
            </div>
            
        </div>
    </section>

    <!-- Nuestro Equipo -->
    <section class="team-section" data-aos="fade-up">
        <div class="section-header">
            <h2><i class="fas fa-users"></i> Quién está detrás</h2>
        </div>
        <div class="team-grid">
            <div class="team-member" data-aos="fade-up" data-aos-delay="100">
                <div class="member-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h3>Ainhoa Lobato</h3>
                <p class="member-role">Desarrolladora & Diseñadora</p>
                <p class="member-bio">Aprendiendo y experimentando con tecnologías web mientras desarrolla InkVerse para su TFG.</p>
                <div class="member-social">
                    <a href="https://github.com/whereisnhoa" target="_blank"><i class="fab fa-github"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Futuras mejoras -->
    <section class="achievements-section" data-aos="fade-up">
        <div class="section-header">
            <h2><i class="fas fa-trophy"></i> Ideas y Mejoras Futuras</h2>
            <p>El proyecto está en constante evolución y abierto a nuevas ideas.</p>
        </div>
        <div class="achievements-timeline" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(270px, 1fr)); gap: 30px;">
            <div class="timeline-content card-futura-mejora" data-aos="fade-up" data-aos-delay="50">
                <h3><i class="fas fa-language"></i> Más idiomas</h3>
                <p>Añadir más variedad de lenguajes de traducción a la página web, ya que solo dispone de cuatro lenguajes: español, inglés, catalán y francés.</p>
            </div>
            <div class="timeline-content card-futura-mejora" data-aos="fade-up" data-aos-delay="100">
                <h3><i class="fas fa-comments"></i> Mensajería interna</h3>
                <p>Mensajería interna entre usuarios. Actualmente solo pueden comentar y responderse, pero no existe un sistema de mensajería privada.</p>
            </div>
            <div class="timeline-content card-futura-mejora" data-aos="fade-up" data-aos-delay="150">
                <h3><i class="fas fa-desktop"></i> App multiplataforma</h3>
                <p>Hacer de la página web una aplicación de escritorio, móvil y para otros dispositivos, que no sea únicamente esta página web.</p>
            </div>
            <div class="timeline-content card-futura-mejora" data-aos="fade-up" data-aos-delay="200">
                <h3><i class="fas fa-envelope-open-text"></i> Verificación real</h3>
                <p>Mejorar el método de envío de verificación del correo, usando un método real que envíe mensajes de verificación a los usuarios, no una simulación.</p>
            </div>
            <div class="timeline-content card-futura-mejora" data-aos="fade-up" data-aos-delay="250">
                <h3><i class="fas fa-sign-in-alt"></i> Más métodos de acceso</h3>
                <p>Agregar más métodos de inicio de sesión y registro, no solo por correo electrónico.</p>
            </div>
            <div class="timeline-content card-futura-mejora" data-aos="fade-up" data-aos-delay="300">
                <h3><i class="fas fa-coins"></i> Pago a autores</h3>
                <p>Pago a autores por sus obras y su valoración o descargas.</p>
            </div>
            <div class="timeline-content card-futura-mejora" data-aos="fade-up" data-aos-delay="350">
                <h3><i class="fas fa-crown"></i> Plan premium</h3>
                <p>Crear un plan premium para la página.</p>
            </div>
            <div class="timeline-content card-futura-mejora" data-aos="fade-up" data-aos-delay="400">
                <h3><i class="fab fa-laravel"></i> Mejorar el backend</h3>
                <p>Uso de frameworks como Laravel o React para mejorar el funcionamiento del backend.</p>
            </div>
            <div class="timeline-content card-futura-mejora" data-aos="fade-up" data-aos-delay="450">
                <h3><i class="fas fa-rocket"></i> Despliegue</h3>
                <p>Despliegue de la página.</p>
            </div>
        </div>
    </section>

    <!-- Únete / Participa -->
    <section class="join-section join-section-fancy" data-aos="fade-up">
        <div class="join-content-fancy">
            <div class="join-title-row">
                <span class="join-icon"><i class="fas fa-handshake"></i></span>
                <h2>Participa</h2>
            </div>
        <hr><br>
            <div class="join-actions-fancy">
                <a href="https://github.com/whereisnhoa/proyecto" class="btn-join btn-join-github" target="_blank">
                    <i class="fab fa-github"></i> Ver código en GitHub
                </a>
                <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn-join btn-join-outline">
                    <i class="fas fa-compass"></i> Explorar el proyecto
                </a>
                <a href="<?php echo BASE_URL; ?>/public/contacto.php" class="btn-join btn-join-outline">
                    <i class="fas fa-envelope"></i> Contacto
                </a>
            </div>
        </div>
    </section>
</div>

<style>
/* Sección superior simple */
.about-top-simple {
    background: linear-gradient(100deg, #6a8dff 0%, #a57ff9 100%);
    color: #fff;
    padding: 60px 0 30px 0;
    text-align: center;
}
.about-top-content { max-width: 900px; margin: 0 auto; }
.about-icon-title { display: flex; align-items: center; justify-content: center; gap: 18px; margin-bottom: 18px; }
.about-icon { background: rgba(255,255,255,0.18); border-radius: 50%; padding: 15px; font-size: 30px; }
.about-inkverse { color: #ffe082; font-weight: 800; letter-spacing: 1px;}
.about-subtitle { font-size: 1.25rem; margin-bottom: 40px; }
.about-gradient { background: linear-gradient(90deg,#ffe082,#81d4fa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;}
.about-stats-row { display: flex; gap: 50px; justify-content: center; margin-top: 24px; flex-wrap: wrap;}
.about-stat { min-width: 120px; }
.about-stat-num { font-size: 2.2rem; font-weight: 700; display: block; margin-bottom: 7px;}
.about-stat-label { font-size: 1rem; opacity: 0.92; font-weight: 400; }
/* Secciones generales y cards */
.about-section { margin: 70px 0; }
.section-content { display: grid; grid-template-columns: 1fr 1fr; gap: 55px; align-items: center; }
.about-section.reverse .section-content { direction: rtl; }
.about-section.reverse .section-text { direction: ltr; }
.section-text h2 { font-size: 2rem; color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 13px;}
.section-text h2 i { color: #4361ee; }
.section-text p { font-size: 1rem; line-height: 1.8; color: #555; margin-bottom: 15px;}
.section-image { display: flex; justify-content: center; align-items: center;}
.image-placeholder { width: 220px; height: 220px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #6c757d; font-size: 1rem; font-weight: 600; border: 2px dashed #dee2e6;}
.image-placeholder i { font-size: 60px; margin-bottom: 17px; color: #4361ee; }
.mission-points { margin-top: 20px; }
.mission-point { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 16px;}
.mission-point i { font-size: 20px; color: #4361ee; margin-top: 3px; flex-shrink: 0;}
.mission-point h4 { font-size: 1rem; color: #333; margin-bottom: 4px;}
.mission-point p { color: #666; line-height: 1.6; margin: 0;}
.values-section { margin: 60px 0; padding: 55px 0; background: #f8f9fa; border-radius: 20px;}
.section-header { text-align: center; margin-bottom: 40px;}
.section-header h2 { font-size: 1.7rem; color: #333; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 10px;}
.section-header h2 i { color: #4361ee; }
.section-header p { font-size: 1rem; color: #666; max-width: 550px; margin: 0 auto;}
.values-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;}
.value-card { background: white; padding: 28px 20px; border-radius: 15px; text-align: center; box-shadow: 0 8px 30px rgba(0,0,0,0.08); transition: transform 0.3s;}
.value-card:hover { transform: translateY(-7px);}
.value-icon { width: 60px; height: 60px; background: linear-gradient(135deg, #4361ee, #3a56d4); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 17px; color: white; font-size: 25px;}
.value-card h3 { font-size: 1.1rem; color: #333; margin-bottom: 10px;}
.value-card p { color: #666; line-height: 1.5;}
.team-section { margin: 60px 0;}
.team-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 27px;}
.team-member { background: white; padding: 27px 18px; border-radius: 15px; text-align: center; box-shadow: 0 8px 30px rgba(0,0,0,0.08); transition: transform 0.3s;}
.team-member:hover { transform: translateY(-5px);}
.member-avatar { width: 65px; height: 65px; background: linear-gradient(135deg, #4361ee, #3a56d4); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: white; font-size: 30px;}
.team-member h3 { font-size: 1rem; color: #333; margin-bottom: 3px;}
.member-role { color: #4361ee; font-weight: 600; margin-bottom: 7px; font-size: .96rem;}
.member-bio { color: #666; line-height: 1.6; margin-bottom: 13px; font-size: .93rem;}
.member-social { display: flex; justify-content: center; gap: 9px;}
.member-social a { width: 32px; height: 32px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #666; transition: all 0.3s;}
.member-social a:hover { background: #4361ee; color: white; transform: translateY(-1px);}
.achievements-section { margin: 60px 0;}
.achievements-timeline { max-width: 700px; margin: 0 auto; position: relative;}
.card-futura-mejora {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 8px 30px rgba(105, 70, 255, 0.07);
    padding: 26px 22px 20px 22px;
    margin-bottom: 0;
    text-align: left;
    transition: transform 0.2s;
    border-left: 5px solid #7f53ff;
    min-height: 160px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}
.card-futura-mejora h3 {
    font-size: 1.17rem;
    color: #363657;
    font-weight: 700;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 9px;
}
.card-futura-mejora p {
    color: #666;
    font-size: 1rem;
    line-height: 1.5;
    margin: 0;
}
.card-futura-mejora i {
    color: #7f53ff;
    font-size: 1.2em;
}
/* Participa/Únete */
.join-section-fancy {
    background: #f7f8fa;
    border-radius: 0 0 42px 42px;
    margin-top: 60px;
    box-shadow: 0 8px 40px -16px #7f53ff22;
    padding: 52px 0 55px 0;
    position: relative;
}
.join-content-fancy {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 18px;
}
.join-title-row {
    display: flex;
    gap: 18px;
    align-items: center;
    margin-bottom: 10px;
}
.join-icon {
    background: linear-gradient(135deg, #7f53ff 0%, #5ad6ff 100%);
    color: #fff;
    border-radius: 18px;
    padding: 11px 17px 11px 13px;
    font-size: 2.1rem;
    box-shadow: 0 2px 10px #7f53ff22;
    display: flex;
    align-items: center;
}
.join-section-fancy h2 {
    font-size: 2.4rem;
    color: #2d2d50;
    font-weight: 800;
    margin: 0;
    letter-spacing: -1.2px;
}
.join-desc {
    font-size: 1.22rem;
    color: #39395c;
    margin-bottom: 38px;
    margin-top: 0;
    max-width: 900px;
}
.join-gradient-text {
    background: linear-gradient(90deg, #7f53ff 0%, #5ad6ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 600;
}
.join-actions-fancy {
    display: flex;
    gap: 22px;
    flex-wrap: wrap;
    margin-top: 10px;
}
.btn-join {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 18px 38px;
    border-radius: 16px;
    font-weight: 700;
    font-size: 1.15rem;
    text-decoration: none;
    transition: all 0.19s cubic-bezier(.44,2,.55,1);
    border: none;
    box-shadow: 0 2px 16px #7f53ff12;
    letter-spacing: 0.2px;
}
.btn-join-github {
    background: linear-gradient(90deg, #a259ff 0%, #377dff 100%);
    color: #fff;
    box-shadow: 0 4px 30px #a259ff44;
}
.btn-join-github:hover {
    background: linear-gradient(90deg, #377dff 0%, #a259ff 100%);
    color: #fff;
    transform: translateY(-2px) scale(1.04);
}
.btn-join-outline {
    background: #fff;
    color: #4b4b8f;
    border: 2.5px solid #e0e2f6;
    box-shadow: 0 1px 7px #7f53ff08;
}
.btn-join-outline:hover {
    background: #f3f5fa;
    color: #7f53ff;
    border-color: #7f53ff;
    transform: translateY(-2px) scale(1.04);
}
@media (max-width: 900px) {
    .about-top-simple { padding: 38px 0 15px 0;}
    .about-stats-row { gap: 26px; }
    .section-content { grid-template-columns: 1fr;}
    .about-section.reverse .section-content { direction: ltr;}
    .team-grid { grid-template-columns: 1fr;}
    .achievements-timeline { grid-template-columns: 1fr !important; }
    .join-section-fancy { border-radius: 0 0 22px 22px; padding: 40px 0 36px 0;}
    .join-section-fancy h2 { font-size: 1.55rem;}
    .join-actions-fancy { flex-direction: column; gap: 15px;}
    .btn-join { width: 100%; justify-content: center; font-size: 1rem; }
}
@media (max-width: 600px) {
    .about-top-content { padding: 0 10px;}
    .about-icon-title { flex-direction: column; gap: 8px;}
    .about-stats-row { flex-direction: column; gap: 10px;}
    .image-placeholder { width: 140px; height: 140px; font-size: .85rem;}
}
</style>

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 900,
        easing: 'ease-out-cubic',
        once: true,
        offset: 100
    });
});
</script>
<?php include_once(__DIR__ . '/../includes/footer.php'); ?>