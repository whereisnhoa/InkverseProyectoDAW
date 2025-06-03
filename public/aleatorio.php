
<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/conexion.php');
require_once(__DIR__ . '/../includes/funciones.php');

// Cargar idioma
$idioma = $_SESSION['idioma'] ?? 'es';
$archivo_idioma = __DIR__ . "/../includes/languages/{$idioma}.php";
if (!file_exists($archivo_idioma)) $archivo_idioma = __DIR__ . "/../includes/languages/es.php";
require_once($archivo_idioma);

$tipo_obra = $_GET['tipo'] ?? 'todos';

function obtenerObraAleatoria($conexion, $tipo = 'todos') {
    $obras = [];
    $sqls = [];

    if ($tipo === 'libro' || $tipo === 'todos') {
        $sql = "SELECT l.*, c.nombre as categoria_nombre, 'libro' as tipo_obra,
                      (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = l.id AND f.tipo_contenido = 'libro') as total_favoritos
                FROM libros l 
                LEFT JOIN categorias c ON l.categoria_id = c.id
                WHERE 1=1";
        if (!isset($_SESSION['usuario_id'])) $sql .= " AND l.publico_adulto = 0";
        $sqls[] = $sql;
    }
    if ($tipo === 'comic' || $tipo === 'todos') {
        $sql = "SELECT c.*, 'comic' as tipo_obra,
                      (SELECT nombre FROM categorias WHERE id = c.categoria_id) as categoria_nombre,
                      (SELECT COUNT(*) FROM favoritos f WHERE f.contenido_id = c.id AND f.tipo_contenido = 'comic') as total_favoritos
                FROM comics c
                WHERE 1=1";
        if (!isset($_SESSION['usuario_id'])) $sql .= " AND c.publico_adulto = 0";
        $sqls[] = $sql;
    }

    foreach ($sqls as $sql) {
        $res = $conexion->query($sql);
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $obras[] = $row;
            }
        }
    }

    if (!empty($obras)) return $obras[array_rand($obras)];
    return null;
}

$obra_aleatoria = obtenerObraAleatoria($conexion, $tipo_obra);

// AJAX response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    if ($obra_aleatoria) {
        $obra_data = [
            'id' => $obra_aleatoria['id'],
            'titulo' => $obra_aleatoria['titulo'] ?? '',
            'autor' => $obra_aleatoria['autor'] ?? $TEXTOS['autor_desconocido'] ?? 'Autor desconocido',
            'descripcion' => $obra_aleatoria['descripcion'] ?? '',
            'portada' => !empty($obra_aleatoria['portada']) ? 
                BASE_URL . '/uploads/portadas/' . $obra_aleatoria['portada'] : 
                BASE_URL . '/assets/images/placeholder-book.png',
            'categoria' => $obra_aleatoria['categoria_nombre'] ?? $TEXTOS['sin_categoria'] ?? 'Sin categoría',
            'tipo' => $obra_aleatoria['tipo_obra'],
            'publico_adulto' => (bool)($obra_aleatoria['publico_adulto'] ?? 0),
            'fecha_subida' => isset($obra_aleatoria['fecha_subida']) && $obra_aleatoria['fecha_subida'] ? date('d/m/Y', strtotime($obra_aleatoria['fecha_subida'])) : '',
            'total_favoritos' => $obra_aleatoria['total_favoritos'] ?? 0
        ];
        echo json_encode(['success' => true, 'obra' => $obra_data]);
    } else {
        echo json_encode(['success' => false, 'mensaje' => $TEXTOS['no_hay_obras'] ?? 'No hay obras disponibles']);
    }
    exit;
}

include_once(__DIR__ . '/../includes/header.php');
?>

<div class="container-aleatorio">
    <div class="aleatorio-header">
        <h1><i class="fas fa-random"></i> <?= htmlspecialchars($TEXTOS['obra_aleatoria']) ?></h1>
        <p><?= htmlspecialchars($TEXTOS['descubre_nuevo']) ?></p>
        <div class="aleatorio-actions">
            <button id="nueva-obra-aleatoria" class="btn-gradient">
                <i class="fas fa-sync-alt"></i> <?= htmlspecialchars($TEXTOS['nueva_obra_aleatoria']) ?>
            </button>
            <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn-outline">
                <i class="fas fa-compass"></i> <?= htmlspecialchars($TEXTOS['explorar_mas']) ?>
            </a>
        </div>
        <div class="aleatorio-filtros">
            <button class="btn-filtro <?= $tipo_obra === 'todos' ? 'active' : '' ?>" data-tipo="todos"><i class="fas fa-globe"></i> <?= htmlspecialchars($TEXTOS['todos']) ?></button>
            <button class="btn-filtro <?= $tipo_obra === 'libro' ? 'active' : '' ?>" data-tipo="libro"><i class="fas fa-book"></i> <?= htmlspecialchars($TEXTOS['libros']) ?></button>
            <button class="btn-filtro <?= $tipo_obra === 'comic' ? 'active' : '' ?>" data-tipo="comic"><i class="fas fa-book-open"></i> <?= htmlspecialchars($TEXTOS['comics']) ?></button>
        </div>
    </div>

    <div id="obra-aleatoria-container">
        <?php if ($obra_aleatoria): ?>
            <?php
            $obra = $obra_aleatoria;
            $obra['categoria'] = $obra['categoria_nombre'] ?? $TEXTOS['sin_categoria'] ?? 'Sin categoría';
            $obra['autor'] = $obra['autor'] ?? $TEXTOS['autor_desconocido'] ?? 'Autor desconocido';
            $obra['titulo'] = $obra['titulo'] ?? '';
            ?>
            <div class="obra-aleatoria-card">
                <div class="obra-portada-grande">
                    <?php if (!empty($obra['portada'])): ?>
                        <img src="<?php echo BASE_URL . '/uploads/portadas/' . htmlspecialchars($obra['portada']); ?>" 
                             alt="<?= htmlspecialchars($obra['titulo']) ?>">
                    <?php else: ?>
                        <div class="portada-placeholder-grande">
                            <i class="fas fa-<?php echo $obra['tipo_obra'] === 'libro' ? 'book' : 'book-open'; ?>"></i>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <button class="btn-favorito-grande" 
                                data-obra-id="<?php echo $obra['id']; ?>" 
                                data-tipo="<?php echo $obra['tipo_obra']; ?>"
                                title="<?= htmlspecialchars($TEXTOS['favoritos']) ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="obra-info-grande">
                    <div class="obra-header-grande">
                        <h2><?= htmlspecialchars($obra['titulo']) ?></h2>
                        <div class="obra-badges-grande">
                            <span class="badge badge-tipo-grande"><?= htmlspecialchars($obra['tipo_obra'] === 'libro' ? $TEXTOS['libro'] : $TEXTOS['comic']) ?></span>
                            <?php if (!empty($obra['categoria'])): ?>
                                <span class="badge badge-categoria-grande"><?= htmlspecialchars($obra['categoria']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($obra['publico_adulto'])): ?>
                                <span class="badge badge-adulto-grande"><?= htmlspecialchars($TEXTOS['adulto']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p class="obra-autor-grande">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars($TEXTOS['por']) ?> <?= htmlspecialchars($obra['autor']) ?>
                    </p>
                    <?php if (!empty($obra['descripcion'])): ?>
                        <div class="obra-descripcion-grande">
                            <p><?= nl2br(htmlspecialchars($obra['descripcion'])) ?></p>
                        </div>
                    <?php endif; ?>
                    <div class="obra-stats-grande">
                        <div class="stat-item">
                            <i class="fas fa-heart"></i>
                            <span><?= intval($obra['total_favoritos']) . ' ' . htmlspecialchars($TEXTOS['favoritos']) ?></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-calendar"></i>
                            <span><?= htmlspecialchars($TEXTOS['subida']) . ' ' . htmlspecialchars($obra['fecha_subida'] ?? '') ?></span>
                        </div>
                    </div>
                    <div class="obra-acciones-grande">
                        <a href="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra['id']; ?>&tipo=<?php echo $obra['tipo_obra']; ?>" 
                           class="btn-gradient btn-lg">
                            <i class="fas fa-eye"></i> <?= htmlspecialchars($TEXTOS['ver_obra_completa']) ?>
                        </a>
                        <button id="compartir-obra" class="btn-outline btn-lg" 
                                data-titulo="<?= htmlspecialchars($obra['titulo']) ?>"
                                data-url="<?php echo BASE_URL; ?>/public/obra.php?id=<?php echo $obra['id']; ?>&tipo=<?php echo $obra['tipo_obra']; ?>">
                            <i class="fas fa-share"></i> <?= htmlspecialchars($TEXTOS['compartir']) ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="no-obra-aleatoria">
                <div class="no-obra-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3><?= htmlspecialchars($TEXTOS['no_hay_obras']) ?></h3>
                <p><?= htmlspecialchars($TEXTOS['no_hay_obras_desc']) ?></p>
                <div class="no-obra-acciones">
                    <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn-gradient">
                        <i class="fas fa-compass"></i> <?= htmlspecialchars($TEXTOS['explorar_obras']) ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
body { background: #fafbfc; }
.container-aleatorio { max-width: 1150px; margin: 40px auto 0 auto; padding: 0 24px; }
.aleatorio-header h1 { font-size: 2.6rem; margin-bottom: 0; color: #23272f; }
.aleatorio-header p { color: #666; margin: 8px 0 24px 1px; font-size: 1.2rem; }
.aleatorio-actions { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 20px;}
.btn-gradient {
    background: linear-gradient(90deg, #7b2ff2 0%, #f357a8 100%);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 16px 32px;
    font-size: 1.15rem;
    font-weight: 600;
    box-shadow: 0 6px 24px rgba(123,47,242,0.10);
    transition: box-shadow 0.2s, transform 0.2s;
}
.btn-gradient:hover { transform: translateY(-2px) scale(1.04); box-shadow: 0 12px 40px rgba(123,47,242,0.12);}
.btn-outline {
    background: #fff;
    color: #7b2ff2;
    border: 2px solid #e2d8f7;
    border-radius: 12px;
    padding: 16px 32px;
    font-size: 1.15rem;
    font-weight: 600;
    transition: background 0.2s, color 0.2s;
}
.btn-outline:hover {
    background: linear-gradient(90deg, #7b2ff2 0%, #f357a8 100%);
    color: #fff;
}
.aleatorio-filtros { margin: 32px 0 28px 0; display: flex; gap: 18px; }
.btn-filtro {
    background: #fff;
    color: #a57ae7;
    border: 2px solid #e2d8f7;
    border-radius: 22px;
    padding: 9px 28px;
    font-weight: 600;
    font-size: 1.07rem;
    transition: background 0.2s, color 0.2s, border 0.2s;
}
.btn-filtro.active, .btn-filtro:focus {
    background: linear-gradient(90deg, #7b2ff2 0%, #f357a8 100%);
    color: #fff;
    border-color: #7b2ff2;
}
.btn-filtro:hover { color: #fff; background: #a57ae7; border-color: #a57ae7; }
.obra-aleatoria-card {
    background: #fff; border-radius: 22px; box-shadow: 0 6px 32px rgba(123,47,242,0.10);
    display: flex; flex-wrap: wrap; overflow: hidden; margin-bottom: 60px;
}
.obra-portada-grande {
    flex:0 0 340px; min-width: 260px; max-width: 390px; height: 390px;
    background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    display: flex; justify-content: center; align-items: center; position: relative;
}
.obra-portada-grande img { width: 100%; height: 100%; object-fit: cover; border-radius: 0 0 22px 22px; }
.portada-placeholder-grande { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.85); font-size: 110px;}
.btn-favorito-grande {
    position: absolute; top: 18px; right: 18px;
    width: 52px; height: 52px; border-radius: 50%; background: #fff; color: #f357a8; border: none;
    display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 23px; box-shadow: 0 2px 10px #f357a84d;
    transition: background .2s, color .2s, transform .2s;
}
.btn-favorito-grande:hover { background: #f357a8; color: #fff; transform: scale(1.12);}
.obra-info-grande { padding: 48px 38px; flex: 1 1 360px; display: flex; flex-direction: column; justify-content: center;}
.obra-header-grande h2 { margin: 0 0 15px 0; font-size: 2.3rem; color: #222; }
.obra-badges-grande { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;}
.badge-tipo-grande, .badge-categoria-grande, .badge-adulto-grande {
    padding: 8px 15px;
    border-radius: 15px;
    font-size: 15px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
}
.badge-tipo-grande { background: #e3f2fd; color: #7b2ff2;}
.badge-categoria-grande { background: #f3e5f5; color: #f357a8;}
.badge-adulto-grande { background: #ffebee; color: #d32f2f;}
.obra-autor-grande { color: #666; margin: 0 0 24px 0; display: flex; align-items: center; gap: 8px; font-size: 1.1rem; font-weight: 600;}
.obra-descripcion-grande { margin: 18px 0; color: #444; line-height: 1.55; font-size: 1.13rem;}
.obra-stats-grande { display: flex; gap: 30px; margin: 18px 0; padding: 12px 0; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0;}
.stat-item { display: flex; align-items: center; gap: 8px; color: #666; font-size: 1.04rem;}
.stat-item i { color: #7b2ff2; font-size: 1.1rem;}
.obra-acciones-grande { display: flex; gap: 18px; margin-top: 27px;}
.btn-lg { padding: 15px 30px; font-size: 1.13rem; font-weight: 700;}
.no-obra-aleatoria { text-align: center; padding: 80px 20px; background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);}
.no-obra-icon { font-size: 70px; color: #ddd; margin-bottom: 30px;}
.no-obra-aleatoria h3 { color: #333; margin-bottom: 15px; font-size: 2rem;}
.no-obra-aleatoria p { color: #666; margin-bottom: 30px; font-size: 1.15rem;}
@media (max-width: 900px) {
    .obra-aleatoria-card { flex-direction: column; }
    .obra-portada-grande { width: 100%; max-width: 100vw; border-radius: 22px 22px 0 0; }
    .obra-info-grande { padding: 32px 18px; }
    .aleatorio-header h1 { font-size: 2rem;}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnNuevaObra = document.getElementById('nueva-obra-aleatoria');
    const container = document.getElementById('obra-aleatoria-container');
    function cargarObraAleatoria(tipo) {
        container.innerHTML = '<div style="text-align:center"><div class="spinner"></div><p><?= addslashes($TEXTOS['buscando_obra']) ?></p></div>';
        fetch(`<?php echo BASE_URL; ?>/public/aleatorio.php?tipo=${tipo}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.obra) {
                container.innerHTML = renderObraCard(data.obra);
                addActionEvents();
            } else {
                container.innerHTML = `
                    <div class="no-obra-aleatoria">
                        <div class="no-obra-icon"><i class="fas fa-search"></i></div>
                        <h3><?= addslashes($TEXTOS['no_hay_obras']) ?></h3>
                        <p><?= addslashes($TEXTOS['no_hay_obras_tipo']) ?></p>
                        <div class="no-obra-acciones">
                            <a href="<?php echo BASE_URL; ?>/public/explorar.php" class="btn-gradient"><i class="fas fa-compass"></i> <?= addslashes($TEXTOS['explorar_obras']) ?></a>
                        </div>
                    </div>
                `;
            }
        })
        .catch(() => {
            container.innerHTML = `
                <div class="no-obra-aleatoria">
                    <div class="no-obra-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <h3><?= addslashes($TEXTOS['error_carga']) ?></h3>
                    <p><?= addslashes($TEXTOS['error_carga_desc']) ?></p>
                    <button onclick="location.reload()" class="btn-gradient"><i class="fas fa-refresh"></i> <?= addslashes($TEXTOS['intentar_de_nuevo']) ?></button>
                </div>
            `;
        });
    }

    function renderObraCard(obra) {
        let portada = obra.portada ? `<img src="${obra.portada}" alt="Portada de ${obra.titulo}">` : `<div class="portada-placeholder-grande"><i class="fas fa-${obra.tipo === 'libro' ? 'book' : 'book-open'}"></i></div>`;
        let badgeAdulto = obra.publico_adulto ? '<span class="badge badge-adulto-grande"><?= addslashes($TEXTOS['adulto']) ?></span>' : '';
        let badgeCategoria = obra.categoria && obra.categoria !== 'Sin categoría' ? `<span class="badge badge-categoria-grande">${obra.categoria}</span>` : '';
        let btnFavorito = <?php echo isset($_SESSION['usuario_id']) ? "true" : "false"; ?> ? `
            <button class="btn-favorito-grande" data-obra-id="${obra.id}" data-tipo="${obra.tipo}" title="<?= addslashes($TEXTOS['favoritos']) ?>">
                <i class="fas fa-heart"></i>
            </button>
        ` : '';
        return `
            <div class="obra-aleatoria-card">
                <div class="obra-portada-grande" style="flex:0 0 250px; min-width: 210px; max-width: 290px; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);position:relative;">
                    ${portada}
                    ${btnFavorito}
                </div>
                <div class="obra-info-grande" style="padding:30px;flex:1">
                    <div class="obra-header-grande">
                        <h2>${obra.titulo}</h2>
                        <div class="obra-badges-grande">
                            <span class="badge badge-tipo-grande">${obra.tipo === 'libro' ? '<?= addslashes($TEXTOS['libro']) ?>' : '<?= addslashes($TEXTOS['comic']) ?>'}</span>
                            ${badgeCategoria}
                            ${badgeAdulto}
                        </div>
                    </div>
                    <p class="obra-autor-grande"><i class="fas fa-user"></i> <?= addslashes($TEXTOS['por']) ?> ${obra.autor}</p>
                    ${obra.descripcion ? `<div class="obra-descripcion-grande"><p>${obra.descripcion}</p></div>` : ''}
                    <div class="obra-stats-grande">
                        <div class="stat-item"><i class="fas fa-heart"></i> <span>${obra.total_favoritos} <?= addslashes($TEXTOS['favoritos']) ?></span></div>
                        <div class="stat-item"><i class="fas fa-calendar"></i> <span><?= addslashes($TEXTOS['subida']) ?> ${obra.fecha_subida}</span></div>
                    </div>
                    <div class="obra-acciones-grande" style="margin-top:20px;">
                        <a href="<?php echo BASE_URL; ?>/public/obra.php?id=${obra.id}&tipo=${obra.tipo}" class="btn-gradient btn-lg">
                            <i class="fas fa-eye"></i> <?= addslashes($TEXTOS['ver_obra_completa']) ?>
                        </a>
                        <button class="btn-outline btn-lg" onclick="compartirObra('${obra.titulo}', '<?php echo BASE_URL; ?>/public/obra.php?id=${obra.id}&tipo=${obra.tipo}')">
                            <i class="fas fa-share"></i> <?= addslashes($TEXTOS['compartir']) ?>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function addActionEvents() {
        const btnFavorito = document.querySelector('.btn-favorito-grande');
        if (btnFavorito) {
            btnFavorito.addEventListener('click', function() {
                const obraId = this.dataset.obraId;
                const tipo = this.dataset.tipo;
                if (typeof window.toggleFavorito === 'function') {
                    window.toggleFavorito(obraId, tipo, this);
                }
            });
        }
        const btnCompartir = document.getElementById('compartir-obra');
        if (btnCompartir) {
            btnCompartir.addEventListener('click', function() {
                const titulo = this.dataset.titulo;
                const url = this.dataset.url;
                if (navigator.share) {
                    navigator.share({ title: titulo, text: titulo, url: url });
                } else {
                    navigator.clipboard.writeText(url).then(() => {
                        alert('<?= addslashes($TEXTOS['copiado_portapapeles']) ?>');
                    });
                }
            });
        }
        // Para los renderizados por AJAX:
        document.querySelectorAll('.btn-outline.btn-lg[onclick^="compartirObra"]').forEach(button => {
            button.addEventListener('click', function() {
                const url = this.getAttribute('onclick').match(/obra\.php\?id=([0-9]+)&tipo=([a-zA-Z]+)/);
                if (url) {
                    let obraId = url[1], tipoObra = url[2];
                    let urlFinal = "<?php echo BASE_URL; ?>/public/obra.php?id=" + obraId + "&tipo=" + tipoObra;
                    if (navigator.share) {
                        navigator.share({ title: document.querySelector('.obra-header-grande > h2').innerText, text: document.querySelector('.obra-header-grande > h2').innerText, url: urlFinal });
                    } else {
                        navigator.clipboard.writeText(urlFinal).then(() => {
                            alert('<?= addslashes($TEXTOS['copiado_portapapeles']) ?>');
                        });
                    }
                }
            });
        });
    }

    if (btnNuevaObra) {
        btnNuevaObra.addEventListener('click', function() {
            const tipoActivo = document.querySelector('.btn-filtro.active').dataset.tipo;
            cargarObraAleatoria(tipoActivo);
        });
    }

    document.querySelectorAll('.btn-filtro').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.btn-filtro').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            cargarObraAleatoria(this.dataset.tipo);
        });
    });

    window.compartirObra = function(titulo, url){
        if (navigator.share) {
            navigator.share({ title: titulo, text: `<?= addslashes($TEXTOS['ver_obra_completa']) ?>: ${titulo}`, url: url });
        } else {
            navigator.clipboard.writeText(url).then(() => {
                alert('<?= addslashes($TEXTOS['copiado_portapapeles']) ?>');
            });
        }
    };

    // Botón favorito
    window.toggleFavorito = function(obraId, tipo, btn) {
        fetch('<?php echo BASE_URL; ?>/controllers/procesar-favorito.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
            body: `accion=toggle&contenido_id=${obraId}&tipo_contenido=${tipo}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.agregado) {
                    btn.classList.add('activo');
                } else {
                    btn.classList.remove('activo');
                }
            } else {
                alert('Error al marcar como favorito');
            }
        })
        .catch(() => alert('Error al conectar con el servidor'));
    }

    addActionEvents();
});
</script>

<?php include_once(__DIR__ . '/../includes/footer.php'); ?>