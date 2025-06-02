<?php
session_start();
include_once(__DIR__ . '/../includes/conexion.php');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// PROCESO POST ANTES DE HEADER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['obra_id'], $_POST['estado'], $_POST['obra_tipo'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $obra_id = (int)$_POST['obra_id'];
    $estado = $_POST['estado'];
    $obra_tipo = $_POST['obra_tipo'];
    $sql = "INSERT INTO biblioteca (usuario_id, obra_id, estado, tipo)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE estado = VALUES(estado)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiss", $usuario_id, $obra_id, $estado, $obra_tipo);
    $stmt->execute();
    header('Location: biblioteca.php?estado=' . $estado . '&exito=1');
    exit;
}

require_once(__DIR__ . '/../includes/header.php');

$estados = [
    'todos' => 'Todos',
    'leyendo' => 'Leyendo',
    'planeado' => 'Planeado',
    'terminado' => 'Terminado',
    'espera' => 'En espera',
    'abandonado' => 'Abandonado'
];

$estado_actual = $_GET['estado'] ?? 'todos';

// Traer las obras de la biblioteca del usuario (libros y comics)
$where_estado = $estado_actual !== 'todos' ? "AND b.estado = ?" : '';
$sql = "
    SELECT b.*, 
        CASE 
            WHEN b.tipo='libro' THEN l.titulo
            WHEN b.tipo='comic' THEN co.titulo
        END as titulo,
        CASE 
            WHEN b.tipo='libro' THEN l.portada
            WHEN b.tipo='comic' THEN co.portada
        END as portada,
        CASE 
            WHEN b.tipo='libro' THEN u1.usuario
            WHEN b.tipo='comic' THEN u2.usuario
        END as autor,
        CASE 
            WHEN b.tipo='libro' THEN l.descripcion
            WHEN b.tipo='comic' THEN co.descripcion
        END as descripcion,
        b.tipo
    FROM biblioteca b
    LEFT JOIN libros l ON b.tipo = 'libro' AND b.obra_id = l.id
    LEFT JOIN usuarios u1 ON l.usuario_id = u1.id
    LEFT JOIN comics co ON b.tipo = 'comic' AND b.obra_id = co.id
    LEFT JOIN usuarios u2 ON co.usuario_id = u2.id
    WHERE b.usuario_id = ? $where_estado
    ORDER BY titulo ASC
";
$stmt = $conexion->prepare($sql);
if ($where_estado) {
    $stmt->bind_param("is", $_SESSION['usuario_id'], $estado_actual);
} else {
    $stmt->bind_param("i", $_SESSION['usuario_id']);
}
$stmt->execute();
$result = $stmt->get_result();
$obras = [];
while ($row = $result->fetch_assoc()) $obras[] = $row;

$total_guardadas = count($obras);
$total_libros = 0;
$total_comics = 0;
foreach ($obras as $obra) {
    if ($obra['tipo'] === 'libro') $total_libros++;
    if ($obra['tipo'] === 'comic') $total_comics++;
}
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/usuario.css">

<div class="biblio-hero-bg-azul py-5 mb-5">
    <div class="container text-center">
        <h1 class="display-2 fw-bold biblio-hero-titulo mb-3">
            <i class="fas fa-bookmark biblio-azul-icon"></i> Biblioteca
        </h1>
        <p class="biblio-hero-desc mb-4">Tus libros y cómics favoritos, organizados y siempre a tu alcance.</p>
        <div class="row justify-content-center g-4 mb-2">
            <div class="col-12 col-sm-4 col-md-3">
                <div class="biblio-stat-card biblio-stat-total">
                    <i class="fas fa-bookmark"></i>
                    <div class="biblio-stat-title">Guardadas</div>
                    <div class="biblio-stat-value"><?php echo $total_guardadas; ?></div>
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-3">
                <div class="biblio-stat-card biblio-stat-libro">
                    <i class="fas fa-book"></i>
                    <div class="biblio-stat-title">Libros</div>
                    <div class="biblio-stat-value"><?php echo $total_libros; ?></div>
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-3">
                <div class="biblio-stat-card biblio-stat-comic">
                    <i class="fas fa-book-open"></i>
                    <div class="biblio-stat-title">Cómics</div>
                    <div class="biblio-stat-value"><?php echo $total_comics; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container biblio-container">
    
    <div class="biblio-tabs mb-4">
        <?php foreach ($estados as $key => $label): ?>
            <a href="?estado=<?php echo $key; ?>"
               class="biblio-tab<?php echo $estado_actual == $key ? ' active' : ''; ?>">
               <?php echo $label; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="biblio-table-wrapper">
        <table class="biblio-table align-middle">
            <thead>
                <tr>
                    <th>Portada</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($obras) == 0): ?>
                <tr><td colspan="6" class="text-center text-muted">No hay obras en esta categoría.</td></tr>
            <?php endif; ?>
            <?php foreach ($obras as $obra): ?>
                <tr>
                    <td>
                        <?php if (!empty($obra['portada'])): ?>
                            <img class="biblio-cover-img" src="<?php echo BASE_URL.'/uploads/portadas/'.$obra['portada']; ?>" alt="">
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="biblio-title"><?php echo htmlspecialchars($obra['titulo']); ?></div>
                    </td>
                    <td>
                        <div class="biblio-autor"><?php echo htmlspecialchars($obra['autor']); ?></div>
                    </td>
                    <td>
                        <span class="biblio-type-badge <?php echo $obra['tipo']; ?>">
                            <?php echo strtoupper($obra['tipo']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="biblio-state-badge biblio-<?php echo $obra['estado']; ?>">
                            <?php echo $estados[$obra['estado']]; ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php
                            if ($obra['tipo'] === 'libro') {
                                echo BASE_URL.'/public/obra.php?id='.$obra['obra_id'].'&tipo=libro';
                            } else {
                                echo BASE_URL.'/public/obra.php?id='.$obra['obra_id'].'&tipo=comic';
                            }
                        ?>" 
                           class="biblio-action-link" title="Ver">
                           <i class="fas fa-eye biblio-action-ico"></i>Ver
                        </a>
                        <a href="javascript:void(0);" 
                           class="biblio-action-link" 
                           onclick="abrirBiblioModalDesdeBiblioteca('<?php echo $obra['obra_id']; ?>','<?php echo htmlspecialchars($obra['titulo'],ENT_QUOTES); ?>','<?php echo $obra['tipo']; ?>')" 
                           title="Cambiar estado">
                            <i class="fas fa-pen-to-square"></i> Cambiar estado
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Modal para la biblioteca -->
<div class="modal fade biblio-modal" id="biblioModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?php echo BASE_URL; ?>/usuario/biblioteca.php">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-bookmark"></i> Añadir a biblioteca</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <input type="hidden" id="biblioObraId" name="obra_id">
          <input type="hidden" id="biblioObraTipo" name="obra_tipo">
          <p id="biblioObraTitulo" class="mb-3 fw-bold"></p>
          <div class="biblio-state-list d-flex flex-wrap justify-content-center">
            <?php foreach ($estados as $key => $label): ?>
              <button type="button" class="btn biblio-state-btn" data-value="<?php echo $key; ?>"><?php echo $label; ?></button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="estado" id="estadoBiblioInput" value="">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-gradient" disabled id="biblioSubmit">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function abrirBiblioModalDesdeBiblioteca(id, titulo, tipo){
    document.getElementById('biblioObraId').value = id;
    document.getElementById('biblioObraTitulo').innerText = titulo;
    document.getElementById('biblioObraTipo').value = tipo;
    document.getElementById('estadoBiblioInput').value = '';
    document.getElementById('biblioSubmit').disabled = true;
    document.querySelectorAll('.biblio-state-btn').forEach(function(b){
        b.classList.remove('selected');
    });
    new bootstrap.Modal(document.getElementById('biblioModal')).show();
}
document.querySelectorAll('.biblio-state-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.querySelectorAll('.biblio-state-btn').forEach(function(b){
            b.classList.remove('selected');
        });
        btn.classList.add('selected');
        document.getElementById('estadoBiblioInput').value = btn.getAttribute('data-value');
        document.getElementById('biblioSubmit').disabled = false;
    });
});
</script>

<?php include '../includes/footer.php'; ?>