<?php
session_start();
require_once 'conexion.php';

// Obtener ID del libro desde la URL
$libro_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = isset($_SESSION['usuario_id']) && is_numeric($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
$user_role = isset($_SESSION['usuario_rol']) && is_numeric($_SESSION['usuario_rol']) ? (int)$_SESSION['usuario_rol'] : null;

// Debug temporal
if (!$user_id) {
    error_log("Sesión no encontrada en libro.php: " . var_export($_SESSION, true));
}

// Obtener detalles del libro
try {
    $query = "SELECT id, titulo, autor, descripcion, categoria, imagen_url, publication_date, enlace 
              FROM documento 
              WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $libro_id, PDO::PARAM_INT);
    $stmt->execute();
    $libro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$libro) {
        header('Location: explorar.php');
        exit;
    }
} catch (Exception $e) {
    die("Error al obtener libro: " . $e->getMessage());
}

// Manejar envío de calificación y comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id !== null) {
    if (isset($_POST['action']) && $_POST['action'] === 'delete_comment' && isset($_POST['comment_id'])) {
        $comment_id = (int)$_POST['comment_id'];
        try {
            // Verificar si el usuario es el autor del comentario o admin (rol = 2)
            $query = "SELECT idusuario FROM comentario WHERE id = :comment_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
            $stmt->execute();
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($comment && ($comment['idusuario'] == $user_id || $user_role == 2)) {
                $pdo->beginTransaction();
                // Eliminar comentario
                $query = "DELETE FROM comentario WHERE id = :comment_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
                $stmt->execute();
                // Eliminar calificación asociada
                $query = "DELETE FROM calificacion WHERE usuario_id = :usuario_id AND documento_id = :documento_id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':usuario_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':documento_id', $libro_id, PDO::PARAM_INT);
                $stmt->execute();
                $pdo->commit();
                $_SESSION['mensaje'] = "Comentario y calificación eliminados exitosamente.";
            } else {
                $error = "No tienes permiso para eliminar este comentario.";
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al eliminar comentario: " . $e->getMessage();
            error_log("Error al eliminar comentario: " . $e->getMessage());
        }
    } elseif (isset($_POST['puntuacion']) && isset($_POST['comentario'])) {
        $puntuacion = (int)$_POST['puntuacion'];
        $comentario = trim($_POST['comentario']);

        if ($puntuacion >= 1 && $puntuacion <= 5 && $comentario !== '') {
            try {
                $pdo->beginTransaction();
                // Guardar calificación
                $query = "INSERT INTO calificacion (usuario_id, documento_id, puntuacion) 
                          VALUES (:usuario_id, :documento_id, :puntuacion)
                          ON CONFLICT (usuario_id, documento_id) 
                          DO UPDATE SET puntuacion = :puntuacion";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':usuario_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':documento_id', $libro_id, PDO::PARAM_INT);
                $stmt->bindParam(':puntuacion', $puntuacion, PDO::PARAM_INT);
                $stmt->execute();

                // Guardar comentario
                $query = "INSERT INTO comentario (contenido, idusuario, documento_id, fecha) 
                          VALUES (:contenido, :idusuario, :documento_id, NOW())";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':contenido', $comentario, PDO::PARAM_STR);
                $stmt->bindParam(':idusuario', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':documento_id', $libro_id, PDO::PARAM_INT);
                $stmt->execute();

                $pdo->commit();
                $_SESSION['mensaje'] = "Comentario y calificación guardados exitosamente.";
                error_log("Comentario guardado: usuario_id=$user_id, documento_id=$libro_id, contenido=$comentario");
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Error al guardar: " . $e->getMessage();
                error_log("Error al guardar comentario: " . $e->getMessage());
            }
        } else {
            $error = "Debes proporcionar una puntuación y un comentario.";
        }
    }

    // Redirigir con PRG para evitar duplicación en historial
    header("Location: libro.php?id=$libro_id", true, 303);
    exit;
}

// Obtener calificación promedio
try {
    $query = "SELECT AVG(puntuacion)::numeric(3,1) as promedio, COUNT(*) as total 
              FROM calificacion 
              WHERE documento_id = :documento_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':documento_id', $libro_id, PDO::PARAM_INT);
    $stmt->execute();
    $calificacion = $stmt->fetch(PDO::FETCH_ASSOC);
    $promedio = $calificacion['promedio'] ?: 0;
    $total_calificaciones = $calificacion['total'];
} catch (Exception $e) {
    $promedio = 0;
    $total_calificaciones = 0;
}

// Obtener comentarios
try {
    $query = "SELECT c.id, c.contenido AS comentario, c.fecha, c.idusuario, u.nombre, u.foto_perfil 
              FROM comentario c 
              JOIN usuarios u ON c.idusuario = u.id 
              WHERE c.documento_id = :documento_id 
              ORDER BY c.fecha DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':documento_id', $libro_id, PDO::PARAM_INT);
    $stmt->execute();
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Comentarios recuperados: " . count($comentarios));
} catch (Exception $e) {
    $comentarios = [];
    error_log("Error al obtener comentarios: " . $e->getMessage());
}

// Obtener libros relacionados
try {
    $query = "SELECT id, titulo 
              FROM documento 
              WHERE categoria = :categoria AND id != :id 
              LIMIT 3";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':categoria', $libro['categoria'], PDO::PARAM_STR);
    $stmt->bindParam(':id', $libro_id, PDO::PARAM_INT);
    $stmt->execute();
    $relacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $relacionados = [];
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

try {
    $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id'] ?? 0]);
    $foto_perfil = $stmt->fetchColumn();
    $foto_perfil = $foto_perfil ? '/assets/uploads/user-photo/' . basename($foto_perfil) : '';
} catch (PDOException $e) {
    $foto_perfil = '';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($libro['titulo']); ?> - Detalles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="/assets/img/logo.png" type="png" />
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <main class="container py-4 mt-4">
        <div class="row">
            <!-- Columna izquierda -->
            <div class="col-md-3">
                <img src="<?php echo htmlspecialchars($libro['imagen_url'] ?: '/assets/images/default-book.jpg'); ?>" alt="Imagen de <?php echo htmlspecialchars($libro['titulo']); ?>" class="img-fluid rounded mb-3 image-book">
                <p><strong>Año de publicación:</strong> <?php echo htmlspecialchars($libro['publication_date'] ?: 'Desconocido'); ?></p>
                <?php if (!empty($libro['enlace']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $libro['enlace'])): ?>
                    <?php
                    $filename = basename($libro['enlace']);
                    $clean_filename = preg_replace('/^file_[a-f0-9]+_/', '', $filename);
                    ?>
                    <a href="<?php echo htmlspecialchars($libro['enlace']); ?>" class="btn btn-primary w-100" download="<?php echo htmlspecialchars($clean_filename); ?>">Descargar Archivo</a>
                <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>No disponible</button>
                <?php endif; ?>
            </div>

            <!-- Columna principal -->
            <div class="col-md-6">
                <h1 class="mb-3"><?php echo htmlspecialchars($libro['titulo']); ?></h1>
                <p><strong>Autor:</strong> <?php echo htmlspecialchars($libro['autor']); ?></p>
                <p class="text-muted"><?php echo htmlspecialchars($libro['descripcion'] ?: 'Sin descripción disponible.'); ?></p>
                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($categorias[$libro['categoria']] ?? $libro['categoria']); ?></p>
            </div>

            <!-- Columna derecha -->
            <div class="col-md-3">
                <h5>Libros relacionados</h5>
                <ul class="list-group">
                    <?php if ($relacionados): ?>
                        <?php foreach ($relacionados as $rel): ?>
                            <li class="list-group-item">
                                <a href="libro.php?id=<?php echo $rel['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($rel['titulo']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">No hay libros relacionados.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Sección de calificación y comentarios -->
        <div class="mt-5">
            <h4>Calificación y Comentarios</h4>
            <div class="d-flex align-items-center mb-3">
                <div class="rating">
                    <?php
                    $full_stars = floor($promedio);
                    $half_star = ($promedio - $full_stars) >= 0.5 ? 1 : 0;
                    $empty_stars = 5 - $full_stars - $half_star;
                    for ($i = 0; $i < $full_stars; $i++): ?>
                        <i class="fa fa-star text-warning"></i>
                    <?php endfor; ?>
                    <?php if ($half_star): ?>
                        <i class="fa fa-star-half-alt text-warning"></i>
                    <?php endif; ?>
                    <?php for ($i = 0; $i < $empty_stars; $i++): ?>
                        <i class="fa fa-star-o text-muted"></i>
                    <?php endfor; ?>
                </div>
                <span class="ms-2"><?php echo number_format($promedio, 1); ?>/5 (<?php echo $total_calificaciones; ?> votos)</span>
            </div>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['mensaje']);
                                                    unset($_SESSION['mensaje']); ?></div>
            <?php endif; ?>
            <?php if ($user_id): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="puntuacion" class="form-label">Tu puntuación:</label>
                        <div id="puntuacion" class="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa fa-star text-muted" data-value="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                            <input type="hidden" name="puntuacion" id="puntuacion_value">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Deja tu comentario:</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3" placeholder="Escribe tu comentario aquí..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </form>
            <?php else: ?>
                <p><a href="login.php">Inicia sesión</a> para dejar una calificación o comentario.</p>
            <?php endif; ?>
            <hr>
            <h5>Comentarios:</h5>
            <div class="mb-3">
                <?php if ($comentarios): ?>
                    <?php foreach ($comentarios as $c): ?>
                        <div class="d-flex align-items-start mb-3">
                            <?php
                            // Asegurar ruta absoluta para la foto de perfil del comentario
                            $foto_comentario = $c['foto_perfil'] ? '/assets/uploads/user-photo/' . basename($c['foto_perfil']) : '';
                            ?>
                            <?php if ($foto_comentario): ?>
                                <img src="<?php echo htmlspecialchars($foto_comentario); ?>" alt="Foto de perfil" style="width: 2rem; height: 2rem; border-radius: 50%; object-fit: cover; margin-right: 0.5rem;">
                            <?php else: ?>
                                <i class="fa-solid fa-user-circle fa-2x me-2"></i>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <p class="mb-0"><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></p>
                                    <?php if ($user_id && ($c['idusuario'] == $user_id || $user_role == 2)): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete_comment">
                                            <input type="hidden" name="comment_id" value="<?php echo $c['id']; ?>">
                                            <button type="submit" class="btn btn-link text-danger p-0 ms-2" onclick="return confirm('¿Estás seguro de eliminar este comentario?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <p class="mb-1"><small class="text-muted"><?php echo date('Y-m-d', strtotime($c['fecha'])); ?></small></p>
                                <p><?php echo htmlspecialchars($c['comentario']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay comentarios aún.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para manejar la puntuación por estrellas
        document.querySelectorAll('#puntuacion .fa-star').forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                document.getElementById('puntuacion_value').value = value;
                document.querySelectorAll('#puntuacion .fa-star').forEach(s => {
                    s.classList.remove('text-warning');
                    s.classList.add('text-muted');
                });
                for (let i = 0; i < value; i++) {
                    document.querySelectorAll('#puntuacion .fa-star')[i].classList.remove('text-muted');
                    document.querySelectorAll('#puntuacion .fa-star')[i].classList.add('text-warning');
                }
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            let mensaje = document.querySelector(".alert");
            if (mensaje) {
                let tiempo = mensaje.classList.contains("alert-danger") ? 5000 : 4000;
                setTimeout(() => {
                    mensaje.style.display = "none";
                }, tiempo);
            }
        });
    </script>
</body>
</html>