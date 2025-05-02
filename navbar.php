<?php
// Asegurarse de que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener foto de perfil
require_once 'conexion.php';
try {
    $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id'] ?? 0]);
    $foto_perfil = $stmt->fetchColumn() ?: '';
} catch (PDOException $e) {
    $foto_perfil = '';
}
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid">
        <!-- Logo y Título -->
        <div class="d-flex align-items-center">
            <img src="assets/img/logo.png" alt="Logo" class="logo-img">
            <a class="navbar-brand fw-bold fs-4" href="index.php">Adso</a>
        </div>

        <!-- Contenido del Navbar (Desktop) -->
        <div class="collapse navbar-collapse justify-content-between align-items-center" id="navbarContent">
            <?php if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] == 1): ?>
                <!-- Navbar para usuarios no autenticados y rol 1 -->
                <ul class="navbar-nav custom-nav-position me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="explorarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Explorar
                        </a>
                        <div class="dropdown-menu custom-dropdown" aria-labelledby="explorarDropdown">
                            <ul class="list-unstyled mb-0">
                                <li><a class="dropdown-item" href="explorar.php">Todas las categorías</a></li>
                                <li><a class="dropdown-item" href="explorar.php?categoria=programacion">Programación</a></li>
                                <li><a class="dropdown-item" href="explorar.php?categoria=desarrollo_web">Desarrollo Web</a></li>
                                <li><a class="dropdown-item" href="explorar.php?categoria=desarrollo_aplicaciones">Desarrollo de Aplicaciones</a></li>
                                <li><a class="dropdown-item" href="explorar.php?categoria=ciberseguridad">Ciberseguridad</a></li>
                                <li><a class="dropdown-item" href="explorar.php?categoria=bases_datos">Bases de Datos</a></li>
                                <li><a class="dropdown-item" href="explorar.php?categoria=inteligencia_artificial">IA/Machine Learning</a></li>
                                <li><a class="dropdown-item" href="explorar.php?categoria=cloud_computing">Cloud Computing</a></li>
                                <li><a class="dropdown-item" href="explorar.php?categoria=devops_automatizacion">DevOps y Automatización</a></li>
                                <li><a class="dropdown-item" href="explorar.php?categoria=diseno_ux_ui">Diseño UX/UI</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comu/comentario.php">Comunidad</a>
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
                <a href="deseados.php" class="nav-link ms-3 d-none d-lg-block" id="navHeart">
                    <i class="far fa-heart" style="font-size: 1.2rem; color:black;"></i>
                </a>
            <?php endif; ?>

            <div class="user-menu">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <!-- Menú de usuario -->
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
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="settings.php">Configuración</a></li>
                            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 2): ?>
                                <li><a class="dropdown-item" href="dashboard-admin.php">Dashboard</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="logout.php">Cerrar sesión</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Botones de inicio de sesión y registro -->
                    <div class="d-flex gap-3">
                        <a class="btn btn-outline-primary" href="login.php">Iniciar sesión</a>
                        <a class="btn btn-primary" href="register.php">Registrarse</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>