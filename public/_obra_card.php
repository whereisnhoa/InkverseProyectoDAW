<?php
if (!isset($obra)) return;
$tipo = $tipo ?? 'libro';
?>
<div class="lit-obra-card mb-4">
    <div class="obra-img-wrapper">
        <?php if (!empty($obra['portada'])): ?>
            <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>"
                 alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
        <?php else: ?>
            <div class="obra-placeholder">
                <i class="fas <?php echo $tipo == 'libro' ? 'fa-book' : 'fa-book-open'; ?>"></i>
            </div>
        <?php endif; ?>
    </div>
    <div class="lit-obra-card-body d-flex flex-column">
        <div>
            <div class="lit-obra-card-title"><?php echo htmlspecialchars($obra['titulo']); ?></div>
            <div class="lit-obra-card-author"><i class="fas fa-user"></i> <?php echo htmlspecialchars($obra['autor'] ?? $obra['usuario'] ?? ''); ?></div>
            <?php if (!empty($obra['categoria'])): ?>
                <span class="lit-obra-card-category"><?php echo htmlspecialchars($obra['categoria']); ?></span>
            <?php endif; ?>
            <?php if (!empty($obra['publico_adulto'])): ?>
                <span class="badge-adulto">+18</span>
            <?php endif; ?>
            <div class="lit-obra-card-description">
                <?php
                if (!empty($obra['descripcion'])) {
                    echo htmlspecialchars(mb_strimwidth($obra['descripcion'], 0, 120, '...'));
                } else {
                    echo 'Sin descripción';
                }
                ?>
            </div>
        </div>
        <div class="lit-obra-card-stats mt-auto">
            <span><i class="fas fa-eye"></i> <?php echo number_format($obra['visitas'] ?? 0); ?></span>
            <span><i class="fas fa-heart"></i> <?php echo $obra['total_favoritos'] ?? 0; ?></span>
            <span><i class="fas fa-calendar"></i> <?php echo isset($obra['fecha_subida']) ? date('d/m/Y', strtotime($obra['fecha_subida'])) : ''; ?></span>
        </div>
        <div class="lit-obra-card-actions mt-2 d-flex gap-2">
            <a href="<?php echo BASE_URL . '/public/obra.php?id=' . $obra['id'] . '&tipo=' . $tipo; ?>" class="ver-btn-grad">
                <i class="fas fa-eye"></i>
            </a>
            <?php if (isset($_SESSION['usuario_id'])): ?>
            <button class="biblio-btn-icon"
                    title="Añadir a biblioteca"
                    data-obra-id="<?php echo $obra['id']; ?>"
                    data-obra-titulo="<?php echo htmlspecialchars($obra['titulo']); ?>"
                    data-obra-tipo="<?php echo $tipo; ?>"
                    onclick="abrirBiblioModal(this);">
                <i class="fas fa-bookmark"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>