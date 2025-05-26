
<?php
/**
 * Página de Contacto
 * Formulario de contacto con información de la plataforma
 */

require_once(__DIR__ . '/../includes/header.php');

// Procesar formulario si se envía
$mensaje_enviado = false;
$error_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $asunto = trim($_POST['asunto'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $tipo_consulta = $_POST['tipo_consulta'] ?? '';
    
    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
        $error_mensaje = 'Por favor, completa todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_mensaje = 'Por favor, ingresa un email válido.';
    } else {
        $mensaje_enviado = true;
        
        // Limpiar variables después del envío exitoso
        $nombre = $email = $asunto = $mensaje = $tipo_consulta = '';
    }
}
?>

<!-- Hero Section -->
<div class="contacto-hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1><i class="fas fa-envelope"></i> Contacto</h1>
                <p class="hero-description">¿Tienes alguna pregunta, sugerencia o necesitas ayuda? Estoy aquí para ayudarte</p>
            </div>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>100% Confidencial</span>
                </div>
            </div>

        </div>

    </div>

</div>

<div class="container">
    <div class="contacto-content">
        <!-- Formulario de contacto -->
        <div class="contacto-form-section">
            <div class="form-header">
                <h2><i class="fas fa-paper-plane"></i> Envía un mensaje</h2>
                <p>Completa el formulario y me pondré en contacto contigo lo antes posible</p>
            </div>
            
            <?php if ($mensaje_enviado): ?>
                <div class="alert alert-success" data-aos="fade-in">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <h4>¡Mensaje enviado correctamente!</h4>
                        <p>Gracias por contactarme. He recibido tu mensaje y te responderé en un plazo máximo de 24 horas.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_mensaje)): ?>
                <div class="alert alert-error" data-aos="fade-in">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <h4>Error al enviar el mensaje</h4>
                        <p><?php echo htmlspecialchars($error_mensaje); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="contacto-form" data-aos="fade-up">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre completo *</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?php echo htmlspecialchars($nombre ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo electrónico *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_consulta">Tipo de consulta</label>
                        <select id="tipo_consulta" name="tipo_consulta">
                            <option value="">Selecciona una opción</option>
                            <option value="soporte_tecnico" <?php echo ($tipo_consulta ?? '') === 'soporte_tecnico' ? 'selected' : ''; ?>>
                                Soporte técnico
                            </option>
                            <option value="sugerencia" <?php echo ($tipo_consulta ?? '') === 'sugerencia' ? 'selected' : ''; ?>>
                                Sugerencia o mejora
                            </option>
                            <option value="contenido" <?php echo ($tipo_consulta ?? '') === 'contenido' ? 'selected' : ''; ?>>
                                Problema con contenido
                            </option>
                            <option value="cuenta" <?php echo ($tipo_consulta ?? '') === 'cuenta' ? 'selected' : ''; ?>>
                                Problema con mi cuenta
                            </option>
                            <option value="colaboracion" <?php echo ($tipo_consulta ?? '') === 'colaboracion' ? 'selected' : ''; ?>>
                                Colaboración o partnership
                            </option>
                            <option value="otro" <?php echo ($tipo_consulta ?? '') === 'otro' ? 'selected' : ''; ?>>
                                Otro
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="asunto">Asunto *</label>
                        <input type="text" id="asunto" name="asunto" 
                               value="<?php echo htmlspecialchars($asunto ?? ''); ?>" 
                               placeholder="Describe brevemente tu consulta"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="mensaje">Mensaje *</label>
                    <textarea id="mensaje" name="mensaje" rows="6" 
                              placeholder="Describe detalladamente tu consulta, problema o sugerencia..."
                              required><?php echo htmlspecialchars($mensaje ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i>
                        Enviar mensaje
                    </button>
                    
                    <p class="form-note">
                        <i class="fas fa-info-circle"></i>
                        Al enviar este formulario, aceptas tus datos se procesen para responder a tu consulta.
                    </p>
                </div>
            </form>
        </div>
        
        <!-- Información de contacto -->
        <div class="contacto-info-section">
            <div class="info-card" data-aos="fade-up">
                <div class="info-header">
                    <h3><i class="fas fa-question-circle"></i> Preguntas Frecuentes</h3>
                </div>
                
                <div class="faq-list">
                    <div class="faq-item">
                        <h4>¿Cómo puedo subir mi obra?</h4>
                        <p>Regístrate en la plataforma y utiliza la opción "Subir obra" en el menú principal. Asegúrate de seguir nuestras pautas de contenido.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>¿Puedo descargar las obras?</h4>
                        <p>Esto depende de los permisos que haya establecido cada autor. Algunas obras permiten descarga y otras solo lectura online.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>¿Cómo reporto contenido inapropiado?</h4>
                        <p>Utiliza el botón de reporte en cada obra o contacta con nosotros directamente a través de este formulario.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>¿La plataforma es gratuita?</h4>
                        <p>Sí, nuestra plataforma es completamente gratuita tanto para lectores como para autores que deseen compartir su contenido.</p>
                    </div>
                </div>
                
            </div>
            
            <div class="info-card" data-aos="fade-up" data-aos-delay="100">
                <div class="info-header">
                    <h3><i class="fas fa-headset"></i> Otros medios de contacto</h3>
                </div>
                
                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="method-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="method-info">
                            <h4>Email directo</h4>
                            <p>inkverse@gmail.com</p>
                            <span class="method-note">Respuesta en 24-48h</span>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <i class="fab fa-twitter"></i>
                        </div>
                        <div class="method-info">
                            <h4>Twitter</h4>
                            <p>@inkverse</p>
                            <span class="method-note">Respuesta rápida</span>
                        </div>
                    </div>
                    
                    <div class="contact-method">
                        <div class="method-icon">
                            <i class="fab fa-discord"></i>
                        </div>
                        <div class="method-info">
                            <h4>Discord</h4>
                            <p>Servidor de la comunidad</p>
                            <span class="method-note">Chat en tiempo real</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="info-card" data-aos="fade-up" data-aos-delay="200">
                <div class="info-header">
                    <h3><i class="fas fa-clock"></i> Horarios de atención</h3>
                </div>
                
                <div class="schedule-info">
                    <div class="schedule-item">
                        <span class="day">Lunes - Viernes</span>
                        <span class="time">9:00 - 18:00</span>
                    </div>
                    <div class="schedule-item">
                        <span class="day">Sábados</span>
                        <span class="time">10:00 - 14:00</span>
                    </div>
                    <div class="schedule-item">
                        <span class="day">Domingos</span>
                        <span class="time">Cerrado</span>
                    </div>
                </div>
                
                <p class="schedule-note">
                    <i class="fas fa-info-circle"></i>
                    Los mensajes recibidos fuera del horario de atención serán respondidos el siguiente día hábil.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Estilos CSS -->
<style>
/* Hero Section */
.contacto-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.contacto-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="contact" width="60" height="60" patternUnits="userSpaceOnUse"><circle cx="30" cy="30" r="2" fill="rgba(255,255,255,0.1)"/><path d="M15,15 L45,15 L45,45 L15,45 Z" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23contact)"/></svg>');
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
    font-size: 20px;
    margin-bottom: 40px;
    opacity: 0.9;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
}

.hero-stats .stat-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
    font-weight: 500;
}

.hero-stats .stat-item i {
    font-size: 20px;
    opacity: 0.8;
}

/* Contenido principal */
.contacto-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 60px;
    margin: 60px 0;
}

/* Formulario */
.contacto-form-section {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
}

.form-header {
    margin-bottom: 40px;
    text-align: center;
}

.form-header h2 {
    font-size: 28px;
    color: #333;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.form-header h2 i {
    color: #4361ee;
}

.form-header p {
    color: #666;
    font-size: 16px;
    line-height: 1.6;
}

/* Alertas */
.alert {
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert i {
    font-size: 20px;
    margin-top: 2px;
}

.alert h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 600;
}

.alert p {
    margin: 0;
    font-size: 14px;
}

/* Formulario */
.contacto-form {
    max-width: none;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 15px;
    border: 2px solid #e1e5e9;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.form-actions {
    text-align: center;
    margin-top: 40px;
}

.form-note {
    margin-top: 20px;
    font-size: 14px;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

/* Información lateral */
.contacto-info-section {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.info-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: 1px solid #f0f0f0;
}

.info-header {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.info-header h3 {
    font-size: 20px;
    color: #333;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-header h3 i {
    color: #4361ee;
}

/* FAQ */
.faq-list {
    margin-bottom: 25px;
}

.faq-item {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.faq-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.faq-item h4 {
    font-size: 16px;
    color: #333;
    margin-bottom: 8px;
    font-weight: 600;
}

.faq-item p {
    color: #666;
    line-height: 1.6;
    margin: 0;
    font-size: 14px;
}

/* Métodos de contacto */
.contact-methods {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.contact-method {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.method-icon {
    width: 50px;
    height: 50px;
    background: #4361ee;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.method-info h4 {
    font-size: 16px;
    color: #333;
    margin: 0 0 5px 0;
    font-weight: 600;
}

.method-info p {
    color: #666;
    margin: 0 0 5px 0;
    font-size: 14px;
}

.method-note {
    font-size: 12px;
    color: #999;
    font-style: italic;
}

/* Horarios */
.schedule-info {
    margin-bottom: 20px;
}

.schedule-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.schedule-item:last-child {
    border-bottom: none;
}

.schedule-item .day {
    font-weight: 600;
    color: #333;
}

.schedule-item .time {
    color: #666;
    font-size: 14px;
}

.schedule-note {
    font-size: 14px;
    color: #666;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    line-height: 1.5;
    margin: 0;
}

/* Botones */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-align: center;
    justify-content: center;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 16px;
}

.btn-primary {
    background: #4361ee;
    color: white;
}

.btn-primary:hover {
    background: #3a56d4;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(67, 97, 238, 0.3);
}

.btn-outline {
    background: transparent;
    border: 2px solid #4361ee;
    color: #4361ee;
}

.btn-outline:hover {
    background: #4361ee;
    color: white;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .contacto-hero {
        padding: 60px 0;
    }
    
    .hero-text h1 {
        font-size: 36px;
    }
    
    .hero-description {
        font-size: 18px;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 20px;
    }
    
    .contacto-content {
        grid-template-columns: 1fr;
        gap: 40px;
        margin: 40px 0;
    }
    
    .contacto-form-section {
        padding: 30px 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .info-card {
        padding: 25px 20px;
    }
    
    .contact-method {
        flex-direction: column;
        text-align: center;
    }
    
    .method-icon {
        align-self: center;
    }
}

@media (max-width: 480px) {
    .hero-text h1 {
        font-size: 28px;
        flex-direction: column;
        gap: 10px;
    }
    
    .contacto-form-section {
        padding: 20px 15px;
    }
    
    .form-header h2 {
        font-size: 24px;
        flex-direction: column;
        gap: 10px;
    }
    
    .info-card {
        padding: 20px 15px;
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
    
    // Validación del formulario
    const form = document.querySelector('.contacto-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const email = document.getElementById('email').value.trim();
            const asunto = document.getElementById('asunto').value.trim();
            const mensaje = document.getElementById('mensaje').value.trim();
            
            if (!nombre || !email || !asunto || !mensaje) {
                e.preventDefault();
                alert('Por favor, completa todos los campos obligatorios.');
                return;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('Por favor, ingresa un email válido.');
                return;
            }
        });
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Auto-resize textarea
    const textarea = document.getElementById('mensaje');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>
