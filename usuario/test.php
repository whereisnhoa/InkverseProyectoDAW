<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/login.php");
    exit;
}
include '../includes/conexion.php';
include '../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/usuario.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/test.css">

<div class="test-container">
    <span class="test-icon"><i class="fas fa-question-circle"></i></span>
    <h2><?= htmlspecialchars($TEXTOS['test_titulo']) ?></h2>
    <p><?= htmlspecialchars($TEXTOS['test_intro']) ?></p>
    <form id="genreTestForm" autocomplete="off">
        <?php
        // Opciones por orden: fantasia, romance, scifi, historico, sliceoflife, terror, aventuras, humor
        $generos = ['fantasia','romance','scifi','historico','sliceoflife','terror','aventuras','humor'];
        for($i=1;$i<=8;$i++): ?>
            <div class="test-question">
                <p><strong><?= htmlspecialchars($TEXTOS["test_pregunta$i"]) ?></strong></p>
                <?php for($op=1;$op<=8;$op++): ?>
                    <?php if(isset($TEXTOS["test_pregunta{$i}_op$op"])): ?>
                        <label>
                            <input type="radio" name="q<?= $i ?>" value="<?= $generos[$op-1] ?>" required>
                            <?= htmlspecialchars($TEXTOS["test_pregunta{$i}_op$op"]) ?>
                        </label>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endfor; ?>
        <button type="submit" class="obra-link" style="margin-top:20px;"><?= htmlspecialchars($TEXTOS['test_btn_enviar']) ?></button>
    </form>
    <div id="testResult" style="display:none; margin-top:32px;"></div>
</div>
<script>
document.getElementById('genreTestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let generos = ['fantasia','romance','scifi','historico','sliceoflife','terror','aventuras','humor'];
    let total = {fantasia:0, romance:0, scifi:0, historico:0, sliceoflife:0, terror:0, aventuras:0, humor:0};
    for(let i=1;i<=8;i++){
        let val = document.querySelector('input[name="q'+i+'"]:checked').value;
        total[val]++;
    }
    // Obtener el género con más puntos
    let favorito = generos[0], max = total[generos[0]];
    for(let g of generos){
        if(total[g] > max){ max=total[g]; favorito=g; }
    }
    // Mensajes resultado
    let nombres = {
        fantasia: "<?= htmlspecialchars($TEXTOS['test_genero_fantasia']) ?>",
        romance: "<?= htmlspecialchars($TEXTOS['test_genero_romance']) ?>",
        terror: "<?= htmlspecialchars($TEXTOS['test_genero_terror']) ?>",
        scifi: "<?= htmlspecialchars($TEXTOS['test_genero_scifi']) ?>",
        aventuras: "<?= htmlspecialchars($TEXTOS['test_genero_aventuras']) ?>",
        historico: "<?= htmlspecialchars($TEXTOS['test_genero_historico']) ?>",
        humor: "<?= htmlspecialchars($TEXTOS['test_genero_humor']) ?>",
        sliceoflife: "<?= htmlspecialchars($TEXTOS['test_genero_sliceoflife']) ?>"
    };
    let desc = {
        fantasia: "<?= htmlspecialchars($TEXTOS['test_genero_fantasia_desc']) ?>",
        romance: "<?= htmlspecialchars($TEXTOS['test_genero_romance_desc']) ?>",
        terror: "<?= htmlspecialchars($TEXTOS['test_genero_terror_desc']) ?>",
        scifi: "<?= htmlspecialchars($TEXTOS['test_genero_scifi_desc']) ?>",
        aventuras: "<?= htmlspecialchars($TEXTOS['test_genero_aventuras_desc']) ?>",
        historico: "<?= htmlspecialchars($TEXTOS['test_genero_historico_desc']) ?>",
        humor: "<?= htmlspecialchars($TEXTOS['test_genero_humor_desc']) ?>",
        sliceoflife: "<?= htmlspecialchars($TEXTOS['test_genero_sliceoflife_desc']) ?>"
    };
    let resultado = `<h3>${nombres[favorito]}</h3>
    <p>${desc[favorito]}</p>
    <div style='margin:18px 0;'><i class="fas fa-star" style="color:#f7b801;font-size:2rem;"></i></div>
    <p><?= htmlspecialchars($TEXTOS['test_resultado_intro']) ?></p>
    <p><?= htmlspecialchars($TEXTOS['test_resultado_recomendacion']) ?></p>
    <div style="margin-top:18px;">
        <a href="../public/buscar.php?q=${encodeURIComponent(nombres[favorito])}" class="obra-link">
            <?= htmlspecialchars($TEXTOS['explorar_seccion'] ?? 'Explorar') ?>: ${nombres[favorito]}
        </a>
    </div>`;
    document.getElementById('testResult').innerHTML = resultado;
    document.getElementById('testResult').style.display = 'block';
    document.getElementById('genreTestForm').style.display = 'none';

    // Guardar en servidor (opcional)
    fetch('guardar_test_resultado.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `genero=${encodeURIComponent(favorito)}`
    });
});
</script>
<?php include '../includes/footer.php'; ?>