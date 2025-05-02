<?php
session_start();
require_once 'conexion.php';

// Tiempo máximo de inactividad en segundos (10 minutos = 600 segundos)
$tiempo_maximo_inactividad = 600;

// Verificar si existe la última actividad
if (isset($_SESSION['ultima_actividad'])) {
  $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];
  if ($tiempo_inactivo > $tiempo_maximo_inactividad) {
    // Destruir la sesión y redirigir al usuario a index.php
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
  }
}

// Actualizar el tiempo de la última actividad
$_SESSION['ultima_actividad'] = time();

// No redirigir si el usuario no está autenticado, para permitir ver libros
$usuario_id = isset($_SESSION['usuario_id']) && is_numeric($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;

// Configuración de paginación
$librosPorPagina = 20;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $librosPorPagina;

// Obtener parámetros de búsqueda y categoría
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$tipoBusqueda = isset($_GET['tipo']) ? trim($_GET['tipo']) : 'titulo'; // Por defecto busca en títulos
$categoriaFiltro = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';

// Manejar solicitudes POST para agregar o quitar libros de deseados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['libro_id'], $data['action'])) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    if ($usuario_id === null) {
        echo json_encode(['success' => false, 'message' => 'Por favor, inicia sesión para agregar libros a tus deseados']);
        exit;
    }

    $libro_id = (int)$data['libro_id'];
    $action = $data['action'];

    try {
        if ($action === 'add') {
            $query = "INSERT INTO deseado (usuario_id, documento_id) VALUES (:usuario_id, :libro_id)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':libro_id', $libro_id, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Libro agregado a deseados']);
        } elseif ($action === 'remove') {
            $query = "DELETE FROM deseado WHERE usuario_id = :usuario_id AND documento_id = :libro_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':libro_id', $libro_id, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Libro eliminado de deseados']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Construir la consulta SQL con filtros
$whereClauses = [];
$params = [];
if ($searchQuery) {
    if ($tipoBusqueda === 'autor') {
        $whereClauses[] = "autor ILIKE :searchQuery";
    } else {
        $whereClauses[] = "titulo ILIKE :searchQuery";
    }
    $params[':searchQuery'] = '%' . $searchQuery . '%';
}
if ($categoriaFiltro) {
    $whereClauses[] = "categoria = :categoria";
    $params[':categoria'] = $categoriaFiltro;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Obtener el total de libros para paginación
try {
    $query = "SELECT COUNT(*) FROM documento $whereSql";
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalLibros = $stmt->fetchColumn();
    $totalPaginas = ceil($totalLibros / $librosPorPagina);
} catch (Exception $e) {
    die("Error al contar libros: " . $e->getMessage());
}

// Obtener libros de la base de datos con paginación
try {
    $query = "SELECT id, titulo, autor, descripcion, categoria, imagen_url 
              FROM documento 
              $whereSql 
              ORDER BY id DESC 
              LIMIT :limite OFFSET :offset";
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limite', $librosPorPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error al obtener libros: " . $e->getMessage());
}

// Obtener los libros deseados del usuario (solo si está autenticado)
$deseados = [];
if ($usuario_id !== null) {
    try {
        $query = "SELECT documento_id FROM deseado WHERE usuario_id = :usuario_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $deseados = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        die("Error al obtener libros deseados: " . $e->getMessage());
    }
}

// Mapeo de categorías
$categorias = [
    'programacion' => 'Programación',
    'desarrollo_web' => 'Desarrollo Web',
    'desarrollo_aplicaciones' => 'Desarrollo de Aplicaciones',
    'ciberseguridad' => 'Ciberseguridad',
    'bases_datos' => 'Bases de Datos',
    'inteligencia_artificial' => 'IA/Machine Learning',
    'cloud_computing' => 'Cloud Computing',
    'devops_automatizacion' => 'DevOps y Automatización',
    'diseno_ux_ui' => 'Diseño UX/UI'
];

// El HTML y JavaScript se mantienen en tu archivo existente
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="shortcut icon" href="assets/img/logo.png" type="image/png">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include 'navbar.php'; ?>
    <main class="container py-3 flex-grow-1">
        <div class="mb-3">
            <h2 class="results-counter-large">Explorar Libros</h2>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 controles">
            <div class="d-flex gap-2 align-items-center">
                <button class="btn btn-outline-dark filter-btn" type="button" id="toggleFilters">
                    <i class="bi bi-filter me-1"></i> Filtrar
                </button>
                <div class="select-container">
                    <select class="form-select sort-select" id="sortSelect" style="width: auto;">
                        <option value="recientes">Más recientes</option>
                        <option value="titulo_asc">Título A-Z</option>
                        <option value="titulo_desc">Título Z-A</option>
                    </select>
                </div>
            </div>
            <span class="results-counter-right" id="resultsCounter"><?php echo $totalLibros; ?> resultados</span>
        </div>
        <div class="explorar-contenedor">
            <div class="sidebar-filtros" id="sidebarFiltros">
                <div class="p-3">
                    <h5 class="mb-3">Filtros</h5>
                    <div class="mb-4 filtro-seccion">
                        <h6 class="mb-2">Categorías</h6>
                        <?php foreach ($categorias as $key => $value): ?>
                            <div class="form-check">
                                <input class="form-check-input filter-checkbox" type="checkbox" value="<?php echo $key; ?>" id="<?php echo $key; ?>" <?php echo $categoriaFiltro === $key ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="<?php echo $key; ?>"><?php echo $value; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="libros-contenedor" id="booksContainer">
                <?php if (empty($libros)): ?>
                    <p class="text-center text-muted py-4">No se encontraron libros.</p>
                <?php else: ?>
                    <?php foreach ($libros as $libro): ?>
                        <a href="libro.php?id=<?php echo htmlspecialchars($libro['id']); ?>"
                            class="book-item list-group-item list-group-item-action d-flex align-items-center p-3"
                            data-id="<?php echo htmlspecialchars($libro['id']); ?>"
                            data-title="<?php echo htmlspecialchars($libro['titulo']); ?>"
                            data-author="<?php echo htmlspecialchars($libro['autor']); ?>"
                            data-description="<?php echo htmlspecialchars($libro['descripcion']); ?>"
                            data-category="<?php echo htmlspecialchars($libro['categoria']); ?>"
                            data-image="<?php echo htmlspecialchars($libro['imagen_url']); ?>">
                            <img src="<?php echo htmlspecialchars($libro['imagen_url'] ?: 'assets/images/default-book.jpg'); ?>" class="me-3" alt="<?php echo htmlspecialchars($libro['titulo']); ?>" style="width: 180px; height: 225px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($libro['titulo']); ?></h5>
                                    <?php if ($usuario_id): ?>
                                        <i class="toggle-favorite <?php echo in_array($libro['id'], $deseados) ? 'fas' : 'far'; ?> fa-heart"
                                            data-id="<?php echo htmlspecialchars($libro['id']); ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted mb-1">Por <?php echo htmlspecialchars($libro['autor']); ?></p>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($libro['descripcion']); ?></p>
                                <span class="badge bg-dark"><?php echo htmlspecialchars($categorias[$libro['categoria']] ?? $libro['categoria']); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <nav aria-label="Paginación" class="mt-4">
            <ul class="pagination justify-content-center" id="pagination">
                <?php if ($paginaActual > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $paginaActual - 1; ?><?php echo $searchQuery ? '&q=' . urlencode($searchQuery) . '&tipo=' . urlencode($tipoBusqueda) : ''; ?><?php echo $categoriaFiltro ? '&categoria=' . urlencode($categoriaFiltro) : ''; ?>">Anterior</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <a class="page-link" href="#">Anterior</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?php echo $i === $paginaActual ? 'active' : ''; ?>">
                        <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo $searchQuery ? '&q=' . urlencode($searchQuery) . '&tipo=' . urlencode($tipoBusqueda) : ''; ?><?php echo $categoriaFiltro ? '&categoria=' . urlencode($categoriaFiltro) : ''; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($paginaActual < $totalPaginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?php echo $paginaActual + 1; ?><?php echo $searchQuery ? '&q=' . urlencode($searchQuery) . '&tipo=' . urlencode($tipoBusqueda) : ''; ?><?php echo $categoriaFiltro ? '&categoria=' . urlencode($categoriaFiltro) : ''; ?>">Siguiente</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <a class="page-link" href="#">Siguiente</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </main>
    <?php include 'footer.php'; ?>
    <script>
        const deseados = <?php echo json_encode($deseados); ?>;
        const categoriaFiltro = <?php echo json_encode($categoriaFiltro); ?>;
        // Escuchar actualizaciones de deseados desde otras pestañas
        window.addEventListener('storage', (event) => {
            if (event.key === 'deseadoRemoved') {
                const data = JSON.parse(event.newValue);
                const libroId = data.libroId;
                const icon = document.querySelector(`.toggle-favorite[data-id="${libroId}"]`);
                if (icon) {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    const index = deseados.indexOf(libroId);
                    if (index !== -1) {
                        deseados.splice(index, 1);
                    }
                }
            }
        });
    </script>
    <script src="assets/js/filter.js"></script>
    <script src="assets/js/search.js"></script>
</body>

</html>