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

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Manejar eliminación de libro deseado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);

    error_log('POST data received: ' . print_r($data, true));

    if (!isset($data['action']) || $data['action'] !== 'remove' || !isset($data['libro_id'])) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos o acción no válida']);
        exit;
    }

    $libro_id = (int)$data['libro_id'];
    if ($libro_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de libro inválido']);
        exit;
    }

    try {
        // Verificar si el libro está en deseados
        $query = "SELECT COUNT(*) FROM deseado WHERE usuario_id = :usuario_id AND documento_id = :libro_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':libro_id', $libro_id, PDO::PARAM_INT);
        $stmt->execute();
        $exists = $stmt->fetchColumn();

        if ($exists == 0) {
            echo json_encode(['success' => false, 'message' => 'El libro no está en la lista de deseados']);
            exit;
        }

        // Eliminar el libro
        $query = "DELETE FROM deseado WHERE usuario_id = :usuario_id AND documento_id = :libro_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':libro_id', $libro_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Libro eliminado de deseados']);
    } catch (Exception $e) {
        error_log("Error al eliminar libro deseado: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
    exit;
}

// Obtener los libros deseados del usuario
try {
    $query = "SELECT d.id AS deseado_id, doc.id AS documento_id, doc.titulo, doc.autor, doc.descripcion, doc.imagen_url 
              FROM deseado d
              JOIN documento doc ON d.documento_id = doc.id
              WHERE d.usuario_id = :usuario_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $librosDeseados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener libros deseados: " . $e->getMessage());
    die("Error al cargar la lista de deseados. Por favor, intenta de nuevo.");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de libros deseados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="shortcut icon" href="assets/img/logo.png" type="png" />
</head>

<body>
    <?php include 'navbar.php'; ?>
    <main class="container py-4">
        <h2 class="mb-4">Lista de libros deseados</h2>
        <div class="list-group" id="deseadosList" style="<?php echo count($librosDeseados) === 0 ? 'display: none;' : ''; ?>">
            <?php foreach ($librosDeseados as $libro): ?>
                <div class="list-group-item list-group-item-action d-flex align-items-center p-3"
                    data-id="<?php echo htmlspecialchars($libro['documento_id']); ?>">
                    <a href="libro.php?id=<?php echo htmlspecialchars($libro['documento_id']); ?>">
                        <img src="<?php echo htmlspecialchars($libro['imagen_url']); ?>" class="me-3" alt="Imagen del libro" style="width: 180px; height: 225px; object-fit: cover;">
                    </a>
                    <div class="flex-grow-1">
                        <h5 class="mb-1">
                            <a href="libro.php?id=<?php echo htmlspecialchars($libro['documento_id']); ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($libro['titulo']); ?>
                            </a>
                        </h5>
                        <p class="text-muted mb-1">Por <?php echo htmlspecialchars($libro['autor']); ?></p>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($libro['descripcion']); ?></p>
                        <button class="btn btn-outline-danger btn-sm remove-deseado"
                            data-id="<?php echo htmlspecialchars($libro['documento_id']); ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmDeleteModal">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="text-center text-muted py-4" id="deseadosEmpty" style="<?php echo count($librosDeseados) === 0 ? '' : 'display: none;'; ?>">
            No hay libros seleccionados.
        </p>
        <!-- Modal de confirmación -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar eliminación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que quieres eliminar este libro de tu lista de deseados?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteButton">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const removeButtons = document.querySelectorAll('.remove-deseado');
            const confirmDeleteButton = document.getElementById('confirmDeleteButton');
            const deseadosList = document.getElementById('deseadosList');
            const deseadosEmpty = document.getElementById('deseadosEmpty');
            let currentLibroId = null;

            // Manejar eliminación
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    currentLibroId = parseInt(this.getAttribute('data-id'));
                    console.log('Libro ID seleccionado para eliminar:', currentLibroId);
                });
            });

            confirmDeleteButton.addEventListener('click', function() {
                if (!currentLibroId) {
                    alert('Error: No se seleccionó ningún libro para eliminar.');
                    return;
                }

                console.log('Enviando solicitud para eliminar libro ID:', currentLibroId);

                fetch('deseados.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'remove',
                            libro_id: currentLibroId
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Respuesta del servidor:', data);
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
                            modal.hide();

                            // Actualizar la lista localmente
                            const libroElement = document.querySelector(`.list-group-item[data-id="${currentLibroId}"]`);
                            if (libroElement) {
                                libroElement.remove();
                            }

                            // Mostrar mensaje si la lista está vacía
                            if (deseadosList && deseadosList.children.length === 0) {
                                deseadosList.style.display = 'none';
                                if (deseadosEmpty) {
                                    deseadosEmpty.style.display = 'block';
                                }
                            }

                            // Notificar a otras pestañas
                            localStorage.setItem('deseadoRemoved', JSON.stringify({
                                libroId: currentLibroId,
                                timestamp: Date.now()
                            }));
                        } else {
                            alert('Error: ' + (data.message || 'No se pudo eliminar el libro.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error en fetch:', error);
                        alert('Error al procesar la solicitud: ' + error.message);
                    });
            });

            // Escuchar actualizaciones de deseados desde otras pestañas
            window.addEventListener('storage', (event) => {
                if (event.key === 'deseadoAdded') {
                    const data = JSON.parse(event.newValue);
                    const libroId = data.libroId;

                    // Obtener datos del libro desde el servidor
                    fetch(`libro.php?id=${libroId}`)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const title = doc.querySelector('.card-title')?.textContent || 'Libro desconocido';
                            const author = doc.querySelector('.card-text.text-muted')?.textContent.replace('Por ', '') || 'Autor desconocido';
                            const description = doc.querySelector('.card-text:not(.text-muted)')?.textContent || 'Sin descripción';
                            const image = doc.querySelector('.img-fluid')?.src || '/Uploads/img/default.png';

                            // Agregar el libro a la lista
                            if (deseadosList) {
                                if (deseadosEmpty) {
                                    deseadosEmpty.style.display = 'none';
                                }
                                deseadosList.style.display = 'block';

                                const libroElement = document.createElement('div');
                                libroElement.className = 'list-group-item list-group-item-action d-flex align-items-center p-3';
                                libroElement.dataset.id = libroId;
                                libroElement.innerHTML = `
                                    <a href="libro.php?id=${libroId}">
                                        <img src="${image}" class="me-3" alt="Imagen del libro" style="width: 180px; height: 225px; object-fit: cover;">
                                    </a>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">
                                            <a href="libro.php?id=${libroId}" class="text-decoration-none text-dark">${title}</a>
                                        </h5>
                                        <p class="text-muted mb-1">Por ${author}</p>
                                        <p class="text-muted mb-2">${description}</p>
                                        <button class="btn btn-outline-danger btn-sm remove-deseado" 
                                                data-id="${libroId}" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#confirmDeleteModal">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                `;
                                deseadosList.appendChild(libroElement);

                                // Reasignar eventos al nuevo botón
                                const newButton = libroElement.querySelector('.remove-deseado');
                                newButton.addEventListener('click', function() {
                                    currentLibroId = parseInt(this.getAttribute('data-id'));
                                    console.log('Libro ID seleccionado para eliminar:', currentLibroId);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error al obtener datos del libro:', error);
                        });
                }
            });
        });
    </script>
</body>

</html>