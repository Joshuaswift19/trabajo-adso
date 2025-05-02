<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error fatal: ' . $error['message']]);
        exit;
    }
});

session_start();

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=web', 'postgres', '1');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    error_log('Error de conexión: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $action === 'get') {
    header('Content-Type: application/json');

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log('Inicio de acción add');
        error_log('POST: ' . json_encode($_POST));
        error_log('FILES: ' . json_encode($_FILES));
        try {
            $imageDir = 'Uploads/img/';
            $fileDir = 'Uploads/documents/';
            if (!is_dir($imageDir)) mkdir($imageDir, 0777, true);
            if (!is_dir($fileDir)) mkdir($fileDir, 0777, true);

            $imagenUrl = null;
            if (!isset($_FILES['imagen']) || !is_array($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
                throw new Exception('Debe proporcionar una imagen');
            }
            switch ($_FILES['imagen']['error']) {
                case UPLOAD_ERR_OK:
                    if ($_FILES['imagen']['size'] > 3 * 1024 * 1024) throw new Exception('Imagen excede 3 MB');
                    $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($_FILES['imagen']['type'], $allowedImageTypes)) throw new Exception('Solo JPG, PNG o GIF');
                    $imageName = uniqid('img_') . '_' . basename($_FILES['imagen']['name']);
                    $imagePath = $imageDir . $imageName;
                    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $imagePath)) throw new Exception('Error al mover imagen');
                    $imagenUrl = '/' . $imagePath;
                    break;
                default:
                    $errorCodes = [
                        UPLOAD_ERR_INI_SIZE => 'Imagen excede tamaño máximo del servidor',
                        UPLOAD_ERR_FORM_SIZE => 'Imagen excede tamaño máximo del formulario',
                        UPLOAD_ERR_PARTIAL => 'Imagen subida parcialmente',
                        UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
                        UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir imagen',
                        UPLOAD_ERR_EXTENSION => 'Extensión PHP detuvo la subida'
                    ];
                    $errorMsg = $errorCodes[$_FILES['imagen']['error']] ?? 'Error desconocido';
                    throw new Exception('Error en imagen: ' . $errorMsg . ' (Código: ' . $_FILES['imagen']['error'] . ')');
            }

            $enlace = null;
            if (isset($_FILES['enlace_file']) && is_array($_FILES['enlace_file']) && $_FILES['enlace_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                switch ($_FILES['enlace_file']['error']) {
                    case UPLOAD_ERR_OK:
                        if ($_FILES['enlace_file']['size'] > 15 * 1024 * 1024) throw new Exception('Archivo excede 15 MB');
                        $allowedFileTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
                        if (!in_array($_FILES['enlace_file']['type'], $allowedFileTypes)) throw new Exception('Solo PDF, DOC, DOCX o TXT');
                        $fileName = uniqid('file_') . '_' . basename($_FILES['enlace_file']['name']);
                        $filePath = $fileDir . $fileName;
                        if (!move_uploaded_file($_FILES['enlace_file']['tmp_name'], $filePath)) throw new Exception('Error al mover archivo');
                        $enlace = '/' . $filePath;
                        break;
                    default:
                        $errorCodes = [
                            UPLOAD_ERR_INI_SIZE => 'Archivo excede tamaño máximo del servidor',
                            UPLOAD_ERR_FORM_SIZE => 'Archivo excede tamaño máximo del formulario',
                            UPLOAD_ERR_PARTIAL => 'Archivo subido parcialmente',
                            UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
                            UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir archivo',
                            UPLOAD_ERR_EXTENSION => 'Extensión PHP detuvo la subida'
                        ];
                        $errorMsg = $errorCodes[$_FILES['enlace_file']['error']] ?? 'Error desconocido';
                        throw new Exception('Error en archivo: ' . $errorMsg . ' (Código: ' . $_FILES['enlace_file']['error'] . ')');
                }
            } elseif (!empty($_POST['enlace_url'])) {
                $enlace = filter_var($_POST['enlace_url'], FILTER_VALIDATE_URL) ? $_POST['enlace_url'] : null;
                if (!$enlace) throw new Exception('Enlace no válido');
            } else {
                throw new Exception('Debe proporcionar un archivo o enlace');
            }

            $titulo = trim($_POST['titulo'] ?? '');
            $autor = trim($_POST['autor'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $publication_date = trim($_POST['publication_date'] ?? '');
            $idusuario = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

            if (empty($titulo)) throw new Exception('Título obligatorio');
            if (empty($autor)) throw new Exception('Autor obligatorio');
            if (empty($descripcion)) throw new Exception('Descripción obligatoria');
            if (empty($categoria)) throw new Exception('Categoría obligatoria');
            if (empty($publication_date)) throw new Exception('Fecha de publicación obligatoria');

            $stmt = $pdo->prepare('INSERT INTO documento (titulo, autor, descripcion, categoria, publication_date, imagen_url, enlace, idusuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$titulo, $autor, $descripcion, $categoria, $publication_date, $imagenUrl, $enlace, $idusuario]);
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Documento agregado']);
        } catch (Exception $e) {
            ob_clean();
            error_log('Error al agregar: ' . $e->getMessage() . ' | POST: ' . json_encode($_POST) . ' | FILES: ' . json_encode($_FILES));
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) throw new Exception('ID inválido');
            $titulo = trim($_POST['titulo'] ?? '');
            $autor = trim($_POST['autor'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $publication_date = trim($_POST['publication_date'] ?? '');
            if (empty($titulo)) throw new Exception('Título obligatorio');
            if (empty($autor)) throw new Exception('Autor obligatorio');
            if (empty($descripcion)) throw new Exception('Descripción obligatoria');
            if (empty($categoria)) throw new Exception('Categoría obligatoria');
            if (empty($publication_date)) throw new Exception('Fecha de publicación obligatoria');
            $stmt = $pdo->prepare('UPDATE documento SET titulo = ?, autor = ?, descripcion = ?, categoria = ?, publication_date = ? WHERE id = ?');
            $stmt->execute([$titulo, $autor, $descripcion, $categoria, $publication_date, $id]);
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Documento actualizado']);
        } catch (Exception $e) {
            ob_clean();
            error_log('Error al actualizar: ' . $e->getMessage() . ' | POST: ' . json_encode($_POST));
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) throw new Exception('ID inválido');
            $stmt = $pdo->prepare('SELECT imagen_url, enlace FROM documento WHERE id = ?');
            $stmt->execute([$id]);
            $documento = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($documento) {
                if ($documento['imagen_url'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $documento['imagen_url'])) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . $documento['imagen_url']);
                }
                if ($documento['enlace'] && !str_starts_with($documento['enlace'], 'http') && file_exists($_SERVER['DOCUMENT_ROOT'] . $documento['enlace'])) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . $documento['enlace']);
                }
                $stmt = $pdo->prepare('DELETE FROM documento WHERE id = ?');
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Documento eliminado']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Documento no encontrado']);
            }
        } catch (Exception $e) {
            error_log('Error al eliminar: ' . $e->getMessage());
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'get') {
        try {
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'titulo';
            $reverse = isset($_GET['reverse']) && $_GET['reverse'] == '1';
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $allowedSortFields = ['id', 'titulo', 'autor', 'categoria'];
            $sort = in_array($sort, $allowedSortFields) ? $sort : 'titulo';
            $query = 'SELECT * FROM documento';
            $params = [];
            if ($id > 0) {
                $query .= ' WHERE id = ?';
                $params = [$id];
            } elseif ($search) {
                $query .= ' WHERE titulo ILIKE ? OR autor ILIKE ? OR categoria ILIKE ? OR descripcion ILIKE ?';
                $searchParam = "%$search%";
                $params = [$searchParam, $searchParam, $searchParam, $searchParam];
            }
            $query .= ' ORDER BY ' . $sort . ($reverse ? ' DESC' : ' ASC');
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($documentos);
        } catch (Exception $e) {
            error_log('Error al obtener documentos: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

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

try {
    $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id'] ?? 0]);
    $foto_perfil = $stmt->fetchColumn() ?: '';
} catch (PDOException $e) {
    $foto_perfil = '';
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="shortcut icon" href="assets/img/logo.png" type="png" />
    <script src="assets/js/dashboard.js"></script>
</head>

<body>

    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container-fluid">
            <!-- Logo y Título -->
            <div class="d-flex align-items-center">
                <img src="assets/img/logo.png" alt="Logo" class="logo-img">
                <a class="navbar-brand fw-bold fs-4" href="dashboard-admin.php">Adso</a>
            </div>

            <!-- Botón de hamburguesa -->
            <button class="custom-hamburger" id="hamburgerButton">
                <div></div>
                <div></div>
                <div></div>
            </button>

            <!-- Sidebar -->
            <div class="custom-sidebar" id="customSidebar">
                <div class="sidebar-header">
                    <h3>Adso</h3>
                </div>
                <div class="sidebar-content">
                    <div class="sidebar-items">
                        <a href="dashboard-admin.php" class="sidebar-item">
                            <i class="bi bi-grid"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="./comu/listar.php" class="sidebar-item">
                            <i class="bi bi-envelope"></i>
                            <span>Mensajes</span>
                        </a>
                    </div>
                    <div class="sidebar-footer">
                        <div class="sidebar-items">
                            <a href="settings.php" class="sidebar-item">
                                <i class="bi bi-gear"></i>
                                <span>Configuración</span>
                            </a>
                            <a href="logout.php" class="sidebar-item logout">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                        <div class="sidebar-separator"></div>
                        <div class="sidebar-profile">
                            <div class="profile-icon">
                                <?php if ($foto_perfil): ?>
                                    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="bi bi-person-circle"></i>
                                <?php endif; ?>
                            </div>
                            <div class="profile-info">
                                <span class="fw-bold d-block profile-name"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                                <span class="text-muted profile-email"><?php echo htmlspecialchars($_SESSION['usuario_correo']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backdrop -->
            <div class="backdrop" id="backdrop"></div>
        </div>
    </nav>

    <div class="custom-container">
        <div class="custom-card">
            <div class="card-body">

                <div class="d-flex flex-wrap justify-content-end align-items-center mb-2">
                    <div class="total-books">
                        <i class="bi bi-book text-primary me-1" style="font-size: 1rem;"></i>
                        <span class="text-muted fw-medium">Total Books:</span>
                        <span class="total-books-value" id="totalBooksValue">0</span>
                        <span class="total-books-prev" id="totalBooksPrev">from 0</span>
                    </div>
                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="bi bi-plus"></i> Agregar Libro
                    </button>
                    <button class="btn btn-outline-secondary me-2" id="toggleSortBtn">
                        <i class="bi bi-sort-alpha-down"></i>
                    </button>
                    <select class="form-select me-2" style="max-width: 200px;" id="sortSelect">
                        <option value="" disabled selected>Ordenar por: </option>
                        <option value="titulo">Título</option>
                        <option value="autor">Autor</option>
                        <option value="genero">Género</option>
                        <option value="fecha">Fecha</option>
                        <option value="id">Más recientes</option>
                    </select>
                    <div class="input-group" style="max-width: 225px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search here...">
                    </div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th><i class="bi bi-image me-1"></i>Imagen</th>
                                <th><i class="bi bi-book me-1"></i>Título</th>
                                <th><i class="bi bi-person me-1"></i>Autor</th>
                                <th><i class="bi bi-tag me-1"></i>Género</th>
                                <th><i class="bi bi-card-text me-1"></i>Descripción</th>
                                <th><i class="bi bi-calendar me-1"></i>Fecha</th>
                                <th><i class="bi bi-link-45deg me-1"></i>Enlace</th>
                                <th><i class="bi bi-gear me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="customerTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center w-100">
                        <span class="badge-grass me-4"><i class="bi bi-book"></i></span>
                        <div>
                            <h5 class="modal-title" id="addCustomerModalLabel">Agrega un nuevo libro</h5>
                            <p class="text-muted mb-0">Llena el formulario con información del libro</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCustomerForm">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Imagen</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-image"></i></span>
                                <button type="button" class="image-input-button form-control" id="imageInput" onclick="openUploadModal()">Selecciona una imagen</button>
                                <span class="clear-button-icon" id="imageClearIcon" onclick="clearImageSelection()">✖</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-book"></i></span>
                                <input type="text" name="titulo" class="form-control" placeholder="Nombre del libro" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Autor</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="autor" class="form-control" placeholder="Nombre del autor" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <textarea name="descripcion" id="descripcion" class="form-control" cols="50" rows="3" placeholder="Descripción del libro"></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Género</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <select class="form-select form-book form-control me-2" name="categoria" id="genreSelect" required>
                                    <option value="" disabled selected>Seleccione un género</option>
                                    <option value="programacion">Programación</option>
                                    <option value="desarrollo_web">Desarrollo Web</option>
                                    <option value="desarrollo_aplicaciones">Desarrollo de Aplicaciones</option>
                                    <option value="ciberseguridad">Ciberseguridad</option>
                                    <option value="bases_datos">Bases de Datos</option>
                                    <option value="inteligencia_artificial">IA/Machine Learning</option>
                                    <option value="cloud_computing">Cloud Computing</option>
                                    <option value="devops_automatizacion">DevOps y Automatización</option>
                                    <option value="diseno_ux_ui">Diseño UX/UI</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Enlace</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link"></i></span>
                                <button type="button" class="image-input-button form-control" id="linkInput" onclick="openLinkModal()">Seleccione un archivo o enlace</button>
                                <span class="clear-button-icon" id="linkClearIcon" onclick="clearLinkSelection()">✖</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de publicación</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <input type="date" name="publication_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Agregar Libro</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para subir imagen -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Subir Imagen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="upload-container" id="uploadContainer">
                        <button class="btn select-button" onclick="document.getElementById('imageFileInput').click()">Seleccionar Imagen</button>
                        <input type="file" id="imageFileInput" accept="image/*" style="display: none;">
                        <p>Arrastra y suelta una imagen aquí o haz clic para seleccionar una imagen</p>
                    </div>
                    <div class="selected-files" id="imageSelectedFiles"></div>
                    <input type="file" id="formImageInput" name="imagen" style="display: none;">
                </div>
                <div class="modal-footer">
                    <button class="btn upload-button text-white" onclick="uploadImage()">Subir</button>
                    <button class="btn clear-button text-white" onclick="clearImageModalSelection()">Limpiar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para seleccionar archivo o enlace -->
    <div class="modal fade" id="linkModal" tabindex="-1" aria-labelledby="linkModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="linkModalLabel">Seleccionar Archivo o Enlace</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="link-container mb-3" id="linkFileContainer">
                        <h6>Subir Archivo</h6>
                        <button class="btn select-button" onclick="document.getElementById('linkFileInput').click()">Seleccionar Archivo</button>
                        <input type="file" id="linkFileInput" accept=".pdf,.doc,.docx,.txt" style="display: none;">
                        <p>Arrastra y suelta un archivo (PDF, DOCX, etc.) aquí o haz clic para seleccionar</p>
                    </div>
                    <div class="selected-files" id="linkFileSelectedFiles"></div>
                    <input type="file" id="formLinkFileInput" name="enlace_file" style="display: none;">
                    <input type="url" id="formLinkUrlInput" name="enlace_url" style="display: none;">
                    <div class="mb-3">
                        <h6>Ingresar Enlace</h6>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link"></i></span>
                            <input type="url" id="linkUrlInput" class="form-control" placeholder="https://ejemplo.com">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn upload-button text-white" onclick="submitLink()">Aceptar</button>
                    <button class="btn clear-button text-white" onclick="clearLinkModalSelection()">Limpiar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center w-100">
                        <span class="badge-grass me-3"><i class="bi bi-pencil-square"></i></span>
                        <div>
                            <h5 class="modal-title" id="editCustomerModalLabel">Editar Libro</h5>
                            <p class="text-muted mb-0">Edita la información del libro</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCustomerForm">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id">
                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-book"></i></span>
                                <input type="text" name="titulo" class="form-control" placeholder="Nombre del libro" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Autor</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="autor" class="form-control" placeholder="Nombre del autor" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción del libro"></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Género</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <select class="form-select form-book form-control me-2" name="categoria" required>
                                    <option value="" disabled selected>Seleccione un género</option>
                                    <option value="programacion">Programación</option>
                                    <option value="desarrollo_web">Desarrollo Web</option>
                                    <option value="desarrollo_aplicaciones">Desarrollo de Aplicaciones</option>
                                    <option value="ciberseguridad">Ciberseguridad</option>
                                    <option value="bases_datos">Bases de Datos</option>
                                    <option value="inteligencia_artificial">IA/Machine Learning</option>
                                    <option value="cloud_computing">Cloud Computing</option>
                                    <option value="devops_automatizacion">DevOps y Automatización</option>
                                    <option value="diseno_ux_ui">Diseño UX/UI</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de publicación</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                <input type="date" name="publication_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Actualizar Libro</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/upload.js"></script>
</body>

</html>