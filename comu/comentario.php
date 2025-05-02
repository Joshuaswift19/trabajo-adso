<?php
session_start();

$idusuario_actual = $_SESSION['usuario_id'] ?? null;

if (!$idusuario_actual) {
    header('Location: /login.php');
    exit;
}
try {
    $pdo = new PDO("pgsql:host=localhost;dbname=web", "postgres", "1");
    $sql = "
        SELECT c.contenido, c.respuesta, u.nombre, c.idusuario, c.idpublicacion, u.foto_perfil
        FROM comunidad c
        JOIN usuarios u ON c.idusuario = u.id
        ORDER BY c.id DESC
    ";
    $stmt = $pdo->query($sql);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}
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
    <title>Comentarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="shortcut icon" href="/assets/img/logo.png" type="png" />
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <img src="/assets/img/logo.png" alt="Logo" class="logo-img">
                <a class="navbar-brand fw-bold fs-4" href="/index.php">Adso</a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between align-items-center" id="navbarContent">
                <?php if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] == 1): ?>
                    <ul class="navbar-nav custom-nav-position me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="/index.php">Inicio</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="/explorar.php" id="explorarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Explorar
                            </a>
                            <div class="dropdown-menu custom-dropdown" aria-labelledby="explorarDropdown">
                                <ul class="list-unstyled mb-0">
                                    <li><a class="dropdown-item" href="/explorar.php">Todas las categorías</a></li>
                                    <li><a class="dropdown-item" href="/explorar.php?categoria=programacion">Programación</a></li>
                                    <li><a class="dropdown-item" href="/explorar.php?categoria=desarrollo_web">Desarrollo Web</a></li>
                                    <li><a class="dropdown-item" href="/explorar.php?categoria=desarrollo_aplicaciones">Desarrollo de Aplicaciones</a></li>
                                    <li><a class="dropdown-item" href="/explorar.php?categoria=ciberseguridad">Ciberseguridad</a></li>
                                    <li><a class="dropdown-item" href="/explorar.php?categoria=bases_datos">Bases de Datos</a></li>
                                    <li><a class="dropdown-item" href="/explorar.php?categoria=inteligencia_artificial">IA/Machine Learning</a></li>
                                    <li><a class="dropdown-item" href="/explorar.php?categoria=cloud_computing">Cloud Computing</a></li>
                                    <li><a class="dropdown-item" href="/explorar.php?categoria=devops_automatizacion">DevOps y Automatización</a></li>
                                    <li><a class="dropdown-item" href="/explorar.php?categoria=diseno_ux_ui">Diseño UX/UI</a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="comentario.php">Comunidad</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Contacto</a>
                        </li>
                    </ul>
                    <div class="custom-search-bar-wrapper d-flex align-items-center position-relative">
                        <form class="custom-search-bar" id="searchForm" action="explorar.php" method="GET">
                            <input type="search" name="q" placeholder="Buscar libros..." class="custom-search-input" id="searchInput" autocomplete="off">
                            <button type="submit" class="custom-search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                        <div class="search-suggestions" id="searchSuggestions"></div>
                    </div>
                    <a href="/deseados.php" class="nav-link ms-3 d-none d-lg-block" id="navHeart">
                        <i class="far fa-heart" style="font-size: 1.2rem;"></i>
                    </a>
                <?php endif; ?>
                <div class="user-menu">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary rounded-circle userMenuH" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php if ($foto_perfil): ?>
                                    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fa-solid fa-user" id="icon-nav"></i>
                                <?php endif; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="userMenu">
                                <li class="d-flex align-items-center mb-2">
                                    <?php if ($foto_perfil): ?>
                                        <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" style="width: 2rem; height: 2rem; border-radius: 50%; object-fit: cover; margin-right: 0.5rem;">
                                    <?php else: ?>
                                        <i class="fa-solid fa-user-circle fa-2x me-2"></i>
                                    <?php endif; ?>
                                    <div>
                                        <span class="fw-bold d-block" style="font-size: 1.1rem;"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                                        <small class="text-muted"><?php echo htmlspecialchars($_SESSION['usuario_correo']); ?></small>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/settings.php">Configuración</a></li>
                                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 2): ?>
                                    <li><a class="dropdown-item" href="/dashboard-admin.php">Dashboard</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="/logout.php">Cerrar sesión</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="d-flex gap-3">
                            <a class="btn btn-outline-primary" href="/login.php">Iniciar sesión</a>
                            <a class="btn btn-primary" href="/register.php">Registrarse</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido -->
    <div class="container py-5">
        <h2 class="mb-4 text-primary comentario-usuario"><i class="bi bi-chat-left-text"></i> Comentarios de los usuarios</h2>
        <?php foreach ($comentarios as $comentario): ?>
            <div class="card card-custom mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <?php
                        // Asegurar ruta absoluta para la foto de perfil del comentario
                        $foto_comentario = $comentario['foto_perfil'] ? '/assets/uploads/user-photo/' . basename($comentario['foto_perfil']) : '';
                        ?>
                        <?php if ($foto_comentario): ?>
                            <img src="<?php echo htmlspecialchars($foto_comentario); ?>" alt="Foto de perfil" style="width: 2rem; height: 2rem; border-radius: 50%; object-fit: cover; margin-right: 0.5rem;">
                        <?php else: ?>
                            <i class="fa-solid fa-user-circle fa-2x me-2"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($comentario['nombre']); ?>
                    </h5>
                    <p class="card-text alert alert-light"><?php echo nl2br(htmlspecialchars($comentario['contenido'])); ?></p>
                    <?php if (!empty($comentario['respuesta'])): ?>
                        <div class="respuesta-admin">
                            <strong>Respuesta del administrador:</strong><br>
                            <?php echo nl2br(htmlspecialchars($comentario['respuesta'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <hr class="my-5">
        <h3 class="mb-4 text-success"><i class="bi bi-pencil-square"></i> Agregar un nuevo comentario</h3>
        <form action="guardar.php" method="POST">
            <div class="mb-3">
                <textarea class="form-control shadow-sm" name="contenido" rows="4" required placeholder="Escribe tu comentario aquí referenciando tema"></textarea>
            </div>
            <input type="hidden" name="idusuario" value="<?php echo $idusuario_actual; ?>">
            <input type="hidden" name="idpublicacion" value="10">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-send"></i> Comentar
            </button>
        </form>
    </div>

    <!-- Footer -->
    <footer class="custom-footer">
        <div class="container">
            <div class="row justify-content-between mb-4 flex-column flex-lg-row text-center text-lg-start">
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
            <hr class="my-4">
            <div class="row flex-column flex-lg-row align-items-center align-items-lg-start">
                <div class="col-lg-6 col-12 mb-4 mb-lg-0 text-center text-lg-start">
                    <div class="d-flex align-items-center justify-content-center justify-content-lg-start">
                        <img src="/assets/img/logo.png" alt="Logo" class="footer-logo me-2">
                        <span class="footer-text">© 2025 Adso, Inc</span>
                    </div>
                </div>
                <div class="col-lg-6 col-12 text-center text-lg-end">
                    <div class="d-flex justify-content-center justify-content-lg-end gap-3">
                        <a href="/#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="/#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="/#" class="social-link"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>