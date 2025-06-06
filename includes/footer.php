    </main>
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section footer-about">
                    <h3>InkVerse</h3>
                    <div class="footer-logo">
                        <img src="<?= BASE_URL; ?>/includes/img/inkverse3.jpg" alt="Logo">
                    </div>
                    <p><?= htmlspecialchars($TEXTOS['footer_slogan']) ?></p>
                    <div class="social-links">
                        <a href="https://discord.com/" aria-label="Discord" title="Discord" target="_blank" rel="noopener"><i class="fab fa-discord"></i></a>
                        <a href="https://github.com/whereisnhoa/proyecto" aria-label="Guthub" title="Github" target="_blank" rel="noopener"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3><?= htmlspecialchars($TEXTOS['explorar_seccion']) ?></h3>
                    <ul>
                        <li><a href="<?= BASE_URL; ?>/public/index.php"><?= htmlspecialchars($TEXTOS['inicio']) ?></a></li>
                        <li><a href="<?= BASE_URL; ?>/public/explorar.php"><?= htmlspecialchars($TEXTOS['explorar']) ?></a></li>
                        <li><a href="<?= BASE_URL; ?>/public/literatura.php"><?= htmlspecialchars($TEXTOS['literatura']) ?></a></li>
                        <li><a href="<?= BASE_URL; ?>/public/comics.php"><?= htmlspecialchars($TEXTOS['comics_manga']) ?></a></li>
                        <li><a href="<?= BASE_URL; ?>/public/categorias.php"><?= htmlspecialchars($TEXTOS['categorias']) ?></a></li>
                        <li><a href="<?= BASE_URL; ?>/public/buscar.php"><?= htmlspecialchars($TEXTOS['busqueda_avanzada']) ?></a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3><?= htmlspecialchars($TEXTOS['cuenta']) ?></h3>
                    <ul>
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <li><a href="<?= BASE_URL; ?>/usuario/perfil.php"><?= htmlspecialchars($TEXTOS['mi_perfil']) ?></a></li>
                            <li><a href="<?= BASE_URL; ?>/usuario/mis_obras.php"><?= htmlspecialchars($TEXTOS['mis_obras']) ?></a></li>
                            <li><a href="<?= BASE_URL; ?>/usuario/favoritos.php"><?= htmlspecialchars($TEXTOS['favoritos']) ?></a></li>
                            <li><a href="<?= BASE_URL; ?>/usuario/nueva_obra.php"><?= htmlspecialchars($TEXTOS['publicar_obra']) ?></a></li>
                            <li><a href="<?= BASE_URL; ?>/public/logout.php"><?= htmlspecialchars($TEXTOS['cerrar_sesion']) ?></a></li>
                        <?php else: ?>
                            <li><a href="<?= BASE_URL; ?>/public/login.php"><?= htmlspecialchars($TEXTOS['iniciar_sesion']) ?></a></li>
                            <li><a href="<?= BASE_URL; ?>/public/register.php"><?= htmlspecialchars($TEXTOS['registrarse']) ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3><?= htmlspecialchars($TEXTOS['comunidad']) ?></h3>
                    <ul>
                        <li><a href="<?= BASE_URL; ?>/comunidad/foros.php"><?= htmlspecialchars($TEXTOS['foros_discusion']) ?></a></li>
                        <li><a href="<?= BASE_URL; ?>/comunidad/grupos.php"><?= htmlspecialchars($TEXTOS['grupos_lectura']) ?></a></li>
                        <li><a href="<?= BASE_URL; ?>/comunidad/eventos.php"><?= htmlspecialchars($TEXTOS['eventos']) ?></a></li>
                        <li><a href="<?= BASE_URL; ?>/comunidad/arte.php"><?= htmlspecialchars($TEXTOS['galeria_arte']) ?></a></li>
                        <li><a href="<?= BASE_URL; ?>/comunidad/noticias.php"><?= htmlspecialchars($TEXTOS['noticias']) ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>InkVerse &copy; <?= date('Y'); ?> - <?= htmlspecialchars($TEXTOS['todos_derechos']) ?></p>
                <div class="footer-links">
                    <a href="<?= BASE_URL; ?>/public/acerca.php"><?= htmlspecialchars($TEXTOS['footer_acerca']) ?></a>
                    <a href="<?= BASE_URL; ?>/public/terminos.php"><?= htmlspecialchars($TEXTOS['footer_terminos']) ?></a>
                    <a href="<?= BASE_URL; ?>/public/privacidad.php"><?= htmlspecialchars($TEXTOS['footer_privacidad']) ?></a>
                    <a href="<?= BASE_URL; ?>/public/cookies.php"><?= htmlspecialchars($TEXTOS['footer_cookies']) ?></a>
                    <a href="<?= BASE_URL; ?>/public/contacto.php"><?= htmlspecialchars($TEXTOS['footer_contacto']) ?></a>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="<?= BASE_URL; ?>../js/script.js"></script>
    <?php include_once(__DIR__ . '/cookies-banner.php'); ?>
</body>
</html>