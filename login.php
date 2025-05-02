<?php
// Incluir la conexión a la base de datos
require_once 'conexion.php';
session_start(); // Iniciar sesión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($correo) || empty($password)) {
        $_SESSION['error'] = "Error: Todos los campos son obligatorios.";
        header("Location: login.php");
        exit;
    } else {
        try {
            // Modificar la consulta para incluir la columna rol
            $query = "SELECT id, nombre, password, rol FROM usuarios WHERE correo = :correo";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                // Guardar datos del usuario en la sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_correo'] = $correo;
                $_SESSION['usuario_rol'] = $usuario['rol']; // Guardar el rol en la sesión

                // Redirigir según el rol
                if ($usuario['rol'] == 1) {
                    $_SESSION['mensaje'] = "Inicio de sesión exitoso.";
                    header('Location: index.php');
                } else {
                    $_SESSION['mensaje'] = "Inicio de sesión exitoso.";
                    header('Location: dashboard-admin.php');
                }
                exit;
            } else {
                $_SESSION['error'] = "Error: Credenciales incorrectas.";
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al iniciar sesión: " . $e->getMessage();
            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/styles.css" />
    <link rel="shortcut icon" href="assets/img/logo.png" type="png" />
    <script src="assets/js/index.js"></script>
</head>

<body>
    <div class="form-container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card custom-card">
            <div class="card-body">
                <!-- Mostrar mensaje de error -->
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-success text-center">
                        <?= htmlspecialchars($_SESSION['mensaje']) ?>
                    </div>
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>

                <!-- Mostrar mensaje de error -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger text-center">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Logo, Título y Enlace de Registro -->
                <div class="d-flex flex-column align-items-start mb-4">
                    <img src="assets/img/logo.png" alt="Logo" class="logo-img mb-3" />
                    <h2 class="card-title mb-3">Inicia sesión en tu cuenta</h2>
                    <div class="d-flex align-items-center opacity-80 w-100">
                        <span class="text-muted me-2">¿Nuevo aquí?</span>
                        <a href="register.php" class="text-primary">Regístrate</a>
                    </div>
                </div>

                <!-- Formulario de Inicio de Sesión -->
                <form action="login.php" method="POST">
                    <!-- Campo de Correo -->
                    <div class="mb-4">
                        <label class="form-label">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input
                                type="email"
                                name="correo"
                                class="form-control"
                                placeholder="usuario@adso.dev"
                                required />
                        </div>
                    </div>

                    <!-- Campo de Contraseña -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label">Contraseña</label>
                            <a href="password.php" class="text-primary">¿Olvidaste tu contraseña?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Ingresa tu contraseña"
                                required />
                        </div>
                    </div>

                    <!-- Botón de Inicio de Sesión -->
                    <button type="submit" class="btn btn-primary w-100 mb-4">Iniciar sesión</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <body class="login-page">
</body>

</html>