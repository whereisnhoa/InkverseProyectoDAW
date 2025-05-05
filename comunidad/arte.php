
<?php
include '../includes/header.php';
?>

<div class="container">
    <h1>Galería de Arte</h1>
    <p class="mb-4">Explora ilustraciones, portadas y arte conceptual creado por nuestra comunidad.</p>
    
    <div class="alerta alerta-info">
        <p>La galería de arte está en desarrollo y estará disponible próximamente.</p>
    </div>
    
    <div class="arte-upload-section">
        <h2>Comparte tu arte</h2>
        <p>Cuando la galería esté disponible, podrás subir tus creaciones artísticas relacionadas con la literatura.</p>
        
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <div class="arte-upload-placeholder">
                <i class="fas fa-cloud-upload-alt fa-3x"></i>
                <p>Arrastra y suelta tus imágenes aquí o haz clic para seleccionar</p>
                <button class="btn" disabled>Subir arte (próximamente)</button>
            </div>
        <?php else: ?>
            <div class="text-center mt-3">
                <p>Necesitas iniciar sesión para compartir tu arte.</p>
                <a href="/public/login.php" class="btn">Iniciar sesión</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="arte-categorias mt-4">
        <h2>Categorías de arte</h2>
        
        <div class="arte-categorias-grid">
            <div class="arte-categoria-card">
                <div class="arte-categoria-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3>Portadas</h3>
                <p>Diseños de portadas para libros y cómics</p>
            </div>
            
            <div class="arte-categoria-card">
                <div class="arte-categoria-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h3>Personajes</h3>
                <p>Ilustraciones de personajes de obras literarias</p>
            </div>
            
            <div class="arte-categoria-card">
                <div class="arte-categoria-icon">
                    <i class="fas fa-map"></i>
                </div>
                <h3>Escenarios</h3>
                <p>Paisajes y escenarios de mundos literarios</p>
            </div>
            
            <div class="arte-categoria-card">
                <div class="arte-categoria-icon">
                    <i class="fas fa-paint-brush"></i>
                </div>
                <h3>Fan Art</h3>
                <p>Arte inspirado en obras populares</p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>