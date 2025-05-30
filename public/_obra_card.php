
<?php
if (!isset($obra)) return;
?>
<div class="obra-card-advanced" data-aos="fade-up">
    <div class="obra-portada">
        <?php if (!empty($obra['portada'])): ?>
            <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>"
                 alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
        <?php else: ?>
            <div class="portada-placeholder">
                <i class="fas <?php echo $tipo == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
            </div>
        <?php endif; ?>
    </div>
    <div class="obra-info">
        <h3>
            <a href="<?php echo BASE_URL . '/public/' . ($tipo == 'libro' ? 'libro' : 'comic') . '.php?id=' . $obra['id']; ?>">
                <?php echo htmlspecialchars($obra['titulo']); ?>
            </a>
        </h3>
        <div class="obra-meta">
            <span class="meta-autor"><i class="fas fa-user"></i> <?php echo htmlspecialchars($obra['usuario']); ?></span>
            <span class="meta-fecha"><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($obra['fecha_subida'])); ?></span>
            <?php if (!empty($obra['valoracion'])): ?>
                <span class="meta-rating"><i class="fas fa-star"></i> <?php echo number_format($obra['valoracion'], 1); ?></span>
            <?php endif; ?>
        </div>
        <p class="obra-descripcion">
            <?php
            if (!empty($obra['descripcion'])) {
                echo htmlspecialchars(mb_strimwidth($obra['descripcion'], 0, 150, '...'));
            } else {
                echo 'Sin descripción';
            }
            ?>
        </p>
        <div class="obra-actions">
            <a href="<?php echo BASE_URL . '/public/' . ($tipo == 'libro' ? 'libro' : 'comic') . '.php?id=' . $obra['id']; ?>"
               class="btn btn-outline">
                <i class="fas fa-eye"></i> Ver obra
            </a>
            <?php if (!empty($obra['archivo'])): ?>
                <a href="<?php echo BASE_URL . '/uploads/obras/' . htmlspecialchars($obra['archivo']); ?>"
                   class="btn btn-outline" download>
                    <i class="fas fa-download"></i> Descargar
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>