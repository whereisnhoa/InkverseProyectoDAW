
<?php
/**
 * Página Acerca de Nosotros
 * Información sobre la plataforma InkVerse
 */

require_once(__DIR__ . '/../includes/header.php');
?>

<!-- Hero Section -->
<div class="about-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1><i class="fas fa-info-circle"></i> Acerca de InkVerse</h1>
                <p class="hero-description">Conectando lectores, autores y creadores en una comunidad global de literatura y arte.</p>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Obras publicadas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">1</div>
                    <div class="stat-label">Autores activos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">1</div>
                    <div class="stat-label">Lectores registrados</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Historia -->
    <section class="about-section" data-aos="fade-up">
        <div class="section-content">
            <div class="section-text">
                <h2><i class="fas fa-book-open"></i> Historia</h2>
                <p>InkVerse se desarrolló a principios de 2025 como un proyecto para crear un espacio donde la literatura y el arte tuvieran lugar, la plataforma será como el hogar digital de creadores y lectores de todo el mundo.</p>
                
                <p>Hoy, InkVerse es mucho más que una plataforma de publicación. Tiene una comunidad vibrante donde se descubren nuevos talentos, se forjan amistades duraderas y se celebra la diversidad de voces que enriquecen el mundo literario.</p>
            </div>
            
            <div class="section-image">
                <div class="image-placeholder">
                    <i class="fas fa-users"></i>
                    <span>Comunidad</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Nuestra Misión -->
    <section class="about-section reverse" data-aos="fade-up">
        <div class="section-content">
            <div class="section-image">
                <div class="image-placeholder">
                    <i class="fas fa-target"></i>
                    <span>Misión</span>
                </div>
            </div>
            
            <div class="section-text">
                <h2><i class="fas fa-bullseye"></i>Misión</h2>
                <p>Permitir el acceso a la literatura y el arte de la misma, proporcionando herramientas y oportunidades para que cualquier persona pueda compartir sus historias con el mundo.</p>
                
                <div class="mission-points">
                    <div class="mission-point">
                        <i class="fas fa-globe"></i>
                        <div>
                            <h4>Acceso Global</h4>
                            <p>Eliminar las barreras geográficas y económicas que limitan el acceso a la literatura de calidad.</p>
                        </div>
                    </div>
                    
                    <div class="mission-point">
                        <i class="fas fa-hands-helping"></i>
                        <div>
                            <h4>Apoyo a Creadores</h4>
                            <p>Proporcionar herramientas y recursos para que los autores puedan desarrollar y promocionar su trabajo.</p>
                        </div>
                    </div>
                    
                    <div class="mission-point">
                        <i class="fas fa-heart"></i>
                        <div>
                            <h4>Comunidad Inclusiva</h4>
                            <p>Fomentar un ambiente seguro y acogedor donde todas las voces sean valoradas y respetadas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nuestros Valores -->
    <section class="values-section" data-aos="fade-up">
        <div class="section-header">
            <h2><i class="fas fa-gem"></i>Valores</h2>
            <p>Los principios que guían cada decisión</p>
        </div>
        
        <div class="values-grid">
            <div class="value-card" data-aos="fade-up" data-aos-delay="100">
                <div class="value-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3>Creatividad</h3>
                <p>Celebrar la originalidad y la innovación en todas sus formas, desde la narrativa experimental hasta el arte visual revolucionario.</p>
            </div>
            
            <div class="value-card" data-aos="fade-up" data-aos-delay="200">
                <div class="value-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h3>Equidad</h3>
                <p>Creer que todos merecen la oportunidad de ser escuchados, independientemente de su origen, experiencia o recursos.</p>
            </div>
            
            <div class="value-card" data-aos="fade-up" data-aos-delay="300">
                <div class="value-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Integridad</h3>
                <p>Mantener los más altos estándares éticos en todo lo que hacemos, protegiendo tanto a creadores como a lectores.</p>
            </div>
            
            <div class="value-card" data-aos="fade-up" data-aos-delay="400">
                <div class="value-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3>Innovación</h3>
                <p>Buscar nuevas formas de mejorar la experiencia de lectura y creación en el mundo digital.</p>
            </div>
        </div>
    </section>

    <!-- Nuestro Equipo -->
    <section class="team-section" data-aos="fade-up">
        <div class="section-header">
            <h2><i class="fas fa-users"></i>Equipo</h2>
        </div>
        
        <div class="team-grid">
            <div class="team-member" data-aos="fade-up" data-aos-delay="100">
                <div class="member-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h3>Ainhoa Lobato</h3>
                <p class="member-role">Desarrolladora y Diseñadora</p>
                <p class="member-bio">Sin mucha experiencia, pero desarrollando y diseñando este proyecto, Inkverse.</p>
                <div class="member-social">
                    <a href="https://github.com/whereisnhoa" target="_blank"><i class="fab fa-github"></i> </a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Futuras mejoras -->
    <section class="achievements-section" data-aos="fade-up">
        <div class="section-header">
            <h2><i class="fas fa-trophy"></i> Futuras mejoras</h2>
        </div>
        
        <div class="achievements-timeline">
            <div class="timeline-item" data-aos="fade-right">
                <div class="timeline-content">
                    <h3>Implementación de idiomas</h3>
                    <p>Hasta ahora solo hay unos pocos, una futura mejora sería añadir más lenguajes.</p>
                </div>
            </div>
            
            <div class="timeline-item" data-aos="fade-left">
                <div class="timeline-content">
                    <h3>Chat entre usuarios</h3>
                    <p>Habilitar un chat entre usuarios para mejorar su comunicación.</p>
                </div>
            </div>
            
        </div>
    </section>

    <!-- Únete a Nosotros -->
    <section class="join-section" data-aos="fade-up">
        <div class="join-content">
            <h2><i class="fas fa-handshake"></i> Únete</h2>
            <p>InkVerse es más que una plataforma, es una comunidad. Ya seas escritor, ilustrador, lector o simplemente alguien que ama las buenas historias, hay un lugar para ti aquí.</p>
            
            <div class="join-actions">
                <a href="<?php echo BASE_URL; ?>/public/register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus"></i> Únete como Creador
                </a>
                <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-compass"></i> Explora Contenido
                </a>
                <a href="<?php echo BASE_URL; ?>/public/contacto.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-envelope"></i> Contáctanos
                </a>
            </div>
        </div>
    </section>
</div>

<!-- Estilos CSS -->
<style>
/* Hero Section */
.about-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 100px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.about-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="about" width="50" height="50" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="2" fill="rgba(255,255,255,0.1)"/><path d="M10,10 L40,10 L40,40 L10,40 Z" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23about)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-text h1 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.hero-description {
    font-size: 22px;
    margin-bottom: 50px;
    opacity: 0.9;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.hero-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 40px;
    max-width: 800px;
    margin: 0 auto;
}

.hero-stats .stat-item {
    text-align: center;
}

.stat-number {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 8px;
    display: block;
}

.stat-label {
    font-size: 16px;
    opacity: 0.9;
    font-weight: 500;
}

/* Secciones generales */
.about-section {
    margin: 80px 0;
}

.section-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.about-section.reverse .section-content {
    direction: rtl;
}

.about-section.reverse .section-text {
    direction: ltr;
}

.section-text h2 {
    font-size: 36px;
    color: #333;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.section-text h2 i {
    color: #4361ee;
}

.section-text p {
    font-size: 18px;
    line-height: 1.8;
    color: #555;
    margin-bottom: 20px;
}

.section-image {
    display: flex;
    justify-content: center;
    align-items: center;
}

.image-placeholder {
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 18px;
    font-weight: 600;
    border: 2px dashed #dee2e6;
}

.image-placeholder i {
    font-size: 80px;
    margin-bottom: 20px;
    color: #4361ee;
}

/* Puntos de misión */
.mission-points {
    margin-top: 30px;
}

.mission-point {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 25px;
}

.mission-point i {
    font-size: 24px;
    color: #4361ee;
    margin-top: 5px;
    flex-shrink: 0;
}

.mission-point h4 {
    font-size: 20px;
    color: #333;
    margin-bottom: 8px;
}

.mission-point p {
    color: #666;
    line-height: 1.6;
    margin: 0;
}

/* Sección de valores */
.values-section {
    margin: 100px 0;
    padding: 80px 0;
    background: #f8f9fa;
    border-radius: 20px;
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-header h2 {
    font-size: 36px;
    color: #333;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.section-header h2 i {
    color: #4361ee;
}

.section-header p {
    font-size: 18px;
    color: #666;
    max-width: 600px;
    margin: 0 auto;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
}

.value-card {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.value-card:hover {
    transform: translateY(-10px);
}

.value-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #4361ee, #3a56d4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    color: white;
    font-size: 32px;
}

.value-card h3 {
    font-size: 24px;
    color: #333;
    margin-bottom: 15px;
}

.value-card p {
    color: #666;
    line-height: 1.6;
}

/* Sección de equipo */
.team-section {
    margin: 100px 0;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
}

.team-member {
    background: white;
    padding: 40px 30px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.team-member:hover {
    transform: translateY(-10px);
}

.member-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #4361ee, #3a56d4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 40px;
}

.team-member h3 {
    font-size: 22px;
    color: #333;
    margin-bottom: 5px;
}

.member-role {
    color: #4361ee;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 16px;
}

.member-bio {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 14px;
}

.member-social {
    display: flex;
    justify-content: center;
    gap: 15px;
}

.member-social a {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: all 0.3s ease;
}

.member-social a:hover {
    background: #4361ee;
    color: white;
    transform: translateY(-2px);
}

/* Timeline de logros */
.achievements-section {
    margin: 100px 0;
}

.achievements-timeline {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
}

.achievements-timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #4361ee;
    transform: translateX(-50%);
}

.timeline-item {
    display: flex;
    align-items: center;
    margin-bottom: 60px;
    position: relative;
}

.timeline-item:nth-child(even) {
    flex-direction: row-reverse;
}

.timeline-date {
    width: 100px;
    height: 100px;
    background: #4361ee;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 18px;
    position: relative;
    z-index: 2;
}

.timeline-content {
    flex: 1;
    padding: 30px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    margin: 0 30px;
}

.timeline-content h3 {
    font-size: 22px;
    color: #333;
    margin-bottom: 10px;
}

.timeline-content p {
    color: #666;
    line-height: 1.6;
    margin: 0;
}

/* Sección únete */
.join-section {
    margin: 100px 0;
    padding: 80px 0;
    background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
    border-radius: 20px;
    text-align: center;
    color: white;
}

.join-content h2 {
    font-size: 36px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.join-content p {
    font-size: 18px;
    line-height: 1.8;
    margin-bottom: 40px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
    opacity: 0.9;
}

.join-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Botones */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-align: center;
    justify-content: center;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 18px;
}

.btn-primary {
    background: white;
    color: #4361ee;
}

.btn-primary:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255,255,255,0.3);
}

.btn-outline {
    background: transparent;
    border: 2px solid white;
    color: white;
}

.btn-outline:hover {
    background: white;
    color: #4361ee;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .about-hero {
        padding: 80px 0;
    }
    
    .hero-text h1 {
        font-size: 36px;
    }
    
    .hero-description {
        font-size: 18px;
    }
    
    .hero-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
    }
    
    .section-content {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }
    
    .about-section.reverse .section-content {
        direction: ltr;
    }
    
    .section-text h2 {
        font-size: 28px;
        justify-content: center;
    }
    
    .values-grid {
        grid-template-columns: 1fr;
    }
    
    .team-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
    
    .achievements-timeline::before {
        left: 30px;
    }
    
    .timeline-item {
        flex-direction: row !important;
        padding-left: 80px;
    }
    
    .timeline-item:nth-child(even) {
        flex-direction: row !important;
    }
    
    .timeline-date {
        position: absolute;
        left: 0;
        width: 60px;
        height: 60px;
        font-size: 14px;
    }
    
    .timeline-content {
        margin: 0;
    }
    
    .join-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .join-actions .btn {
        width: 100%;
        max-width: 300px;
    }
}

@media (max-width: 480px) {
    .hero-stats {
        grid-template-columns: 1fr;
    }
    
    .hero-text h1 {
        font-size: 28px;
        flex-direction: column;
        gap: 10px;
    }
    
    .section-text h2 {
        font-size: 24px;
        flex-direction: column;
        gap: 10px;
    }
    
    .values-section,
    .join-section {
        padding: 60px 20px;
    }
}
</style>

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar AOS
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 100
    });
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
