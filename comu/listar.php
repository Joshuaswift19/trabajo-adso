<?php
session_start();
include '../conexion.php';


$sql = "SELECT * FROM comunidad ORDER BY id DESC";
$stmt = $pdo->query($sql);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
try {
    $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id'] ?? 0]);
    $foto_perfil = $stmt->fetchColumn();
    // Añadir ruta absoluta si existe
    $foto_perfil = $foto_perfil ? '/assets/uploads/user-photo/' . basename($foto_perfil) : '';
} catch (PDOException $e) {
    $foto_perfil = '';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Administrar Comentarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <script src="/assets/js/dashboard.js"></script>
    <link rel="shortcut icon" href="/assets/img/logo.png" type="png" />
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container-fluid">
            <!-- Logo y Título -->
            <div class="d-flex align-items-center">
                <img src="/assets/img/logo.png" alt="Logo" class="logo-img">
                <a class="navbar-brand fw-bold fs-4" href="/dashboard-admin.php">Adso</a>
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
                        <a href="/dashboard-admin.php" class="sidebar-item">
                            <i class="bi bi-grid"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="listar.php" class="sidebar-item">
                            <i class="bi bi-envelope"></i>
                            <span>Mensajes</span>
                        </a>
                    </div>
                    <div class="sidebar-footer">
                        <div class="sidebar-items">
                            <a href="/settings.php" class="sidebar-item">
                                <i class="bi bi-gear"></i>
                                <span>Configuración</span>
                            </a>
                            <a href="/logout.php" class="sidebar-item logout">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                        <div class="sidebar-separator"></div>
                        <div class="sidebar-profile">
                            <div class="profile-icon">
                                <?php if ($foto_perfil): ?>
                                    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" style="width: 2rem; height: 2rem; border-radius: 50%; object-fit: cover; margin-right: 0.5rem;">
                                <?php else: ?>
                                    <i class="fa-solid fa-user-circle fa-2x me-2"></i>
                                <?php endif; ?>
                            </div>
                            <div class="profile-info">
                                <span class="fw-bold d-block profile-name"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                                <span class="text-muted profile-email"><?= htmlspecialchars($_SESSION['usuario_correo']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backdrop -->
            <div class="backdrop" id="backdrop"></div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="mb-4">Listado de Comentarios</h2>


        <?php if (isset($_GET['respondido']) && $_GET['respondido'] === 'true'): ?>
            <div class="alert alert-success">Respuesta guardada correctamente.</div>
        <?php endif; ?>

        <?php if (count($comentarios) > 0): ?>
            <?php foreach ($comentarios as $row): ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">comunidad ID: <?= htmlspecialchars($row['id']) ?></h5>
                        <p class="card-text"><strong>Contenido:</strong> <?= htmlspecialchars($row['contenido']) ?></p>
                        <p class="card-text"><strong>ID Usuario:</strong> <?= htmlspecialchars($row['idusuario']) ?></p>
                        <p class="card-text"><strong>ID Publicación:</strong> <?= htmlspecialchars($row['idpublicacion']) ?></p>

                        <?php if (!empty($row['respuesta'])): ?>
                            <div class="alert alert-success mt-2">
                                <strong>Respuesta del administrador:</strong><br>
                                <?= nl2br(htmlspecialchars($row['respuesta'])) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="eliminar.php" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este comentario?');">
                            <input type="hidden" name="idcomentario" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>

                        <form method="post" action="./responder.php" class="mt-3">
                            <input type="hidden" name="idcomentario" value="<?= $row['id'] ?>">
                            <div class="mb-2">
                                <textarea class="form-control" name="respuesta" placeholder="Escribe una respuesta..." rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Responder</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No hay comentarios.</div>
        <?php endif; ?>
    </div>

    <footer class="custom-footer">
        <div class="container">
            <!-- Sección de tres columnas -->
            <div class="row justify-content-between mb-4 flex-column flex-lg-row text-center text-lg-start">
                <!-- Columna 1: Productos -->
                <div class="col-lg-3 col-12 mb-4 mb-lg-0">
                    <h3 class="footer-heading">PRODUCTOS</h3>
                    <ul class="list-unstyled">
                        <li><a href="/#" class="footer-link">Diseño Web</a></li>
                        <li><a href="/#" class="footer-link">Desarrollo Web</a></li>
                        <li><a href="/#" class="footer-link">E-commerce</a></li>
                        <li><a href="/#" class="footer-link">Gestión de Contenidos</a></li>
                        <li><a href="/#" class="footer-link">Aplicaciones Móviles</a></li>
                    </ul>
                </div>

                <!-- Columna 2: Recursos -->
                <div class="col-lg-3 col-12 mb-4 mb-lg-0">
                    <h3 class="footer-heading">RECURSOS</h3>
                    <ul class="list-unstyled">
                        <li><a href="/#" class="footer-link">Blog</a></li>
                        <li><a href="/#" class="footer-link">Estudios de Caso</a></li>
                        <li><a href="/#" class="footer-link">Documentos Técnicos</a></li>
                        <li><a href="/#" class="footer-link">Webinars</a></li>
                        <li><a href="/#" class="footer-link">E-books</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Sobre Nosotros -->
                <div class="col-lg-3 col-12 mb-4 mb-lg-0">
                    <h3 class="footer-heading">SOBRE NOSOTROS</h3>
                    <ul class="list-unstyled">
                        <li><a href="/#" class="footer-link">Nuestro Equipo</a></li>
                        <li><a href="/#" class="footer-link">Carreras</a></li>
                        <li><a href="/#" class="footer-link">Contáctanos</a></li>
                        <li><a href="/#" class="footer-link">Política de Privacidad</a></li>
                        <li><a href="/#" class="footer-link">Términos de Servicio</a></li>
                    </ul>
                </div>
            </div>

            <!-- Divisor -->
            <hr class="my-4" />

            <!-- Sección inferior -->
            <div class="row flex-column flex-lg-row align-items-center align-items-lg-start">
                <!-- Logo y Derechos de Autor -->
                <div class="col-lg-6 col-12 mb-4 mb-lg-0 text-center text-lg-start">
                    <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                        <img src="/assets/img/logo.png" alt="Logo" class="footer-logo me-2" />
                        <span class="footer-text">© 2025 Adso, Inc</span>
                    </div>
                </div>

                <!-- Redes Sociales -->
                <div class="col-lg-6 col-12 text-center text-lg-end">
                    <div class="d-flex justify-content-center justify-content-lg-end gap-3">
                        <a href="/#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="/#" class="social-link"><i class="bi bi-linkedin"></i></a>
                        <a href="/#" class="social-link"><i class="bi bi-facebook"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>