
</main>

    <footer>
        <div class="footer-container">
            <div class="footer-section footer-about">
                <h3>Inkverse</h3>
                <div class="footer-logo">
                    <img src="<?php echo BASE_URL; ?>/includes/img/inkverse3.jpg" alt="Logo">
                </div>
                <p>Plataforma para compartir y descubrir obras literarias, cómics y manga. Un espacio para lectores y creadores.</p>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="Instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Discord" title="Discord"><i class="fab fa-discord"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Explorar</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/public/index.php">Inicio</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/explorar.php">Explorar</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/literatura.php">Literatura</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/comics.php">Cómics/Manga</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/categorias.php">Categorías</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/buscar.php">Búsqueda Avanzada</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Cuenta</h3>
                <ul>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li><a href="<?php echo BASE_URL; ?>/usuario/perfil.php">Mi Perfil</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/usuario/mis_obras.php">Mis Obras</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/usuario/favoritos.php">Favoritos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/usuario/nueva_obra.php">Publicar Obra</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>/public/login.php">Iniciar Sesión</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/public/register.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Comunidad</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/comunidad/foros.php">Foros de Discusión</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/comunidad/grupos.php">Grupos de Lectura</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/comunidad/eventos.php">Eventos</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/comunidad/arte.php">Galería de Arte</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/comunidad/noticias.php">Noticias</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Ayuda</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/public/contacto.php">Contacto</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/faq.php">Preguntas Frecuentes</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/terminos.php">Términos y Condiciones</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/privacidad.php">Política de Privacidad</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/cookies.php">Política de Cookies</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/public/acerca.php">Acerca de Nosotros</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-container">
                <p>Proyecto Ainhoa &copy; <?php echo date('Y'); ?> - Todos los derechos reservados</p>
                <div class="footer-legal">
                    <a href="<?php echo BASE_URL; ?>/public/terminos.php">Términos</a>
                    <a href="<?php echo BASE_URL; ?>/public/privacidad.php">Privacidad</a>
                    <a href="<?php echo BASE_URL; ?>/public/cookies.php">Cookies</a>
                    <a href="<?php echo BASE_URL; ?>/public/contacto.php">Contacto</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts al final del documento para mejorar el rendimiento -->
    <script src="<?php echo BASE_URL; ?>/js/script.js"></script>
</body>
</html>
