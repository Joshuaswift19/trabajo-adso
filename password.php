<?php
session_start();
require_once 'conexion.php';
require_once 'enviar-correo.php';

$mensaje = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si el campo 'correo' está presente y no está vacío
    if (!isset($_POST['correo']) || empty(trim($_POST['correo']))) {
        $_SESSION['mensaje'] = "Error: El campo de correo es obligatorio.";
        header("Location: password.php");
        exit;
    } else {
        $correo = trim($_POST['correo']);

        try {
            // Verificar si el correo existe y obtener el nombre del usuario
            $query = "SELECT id, nombre FROM usuarios WHERE correo = :correo";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                $nombreUsuario = $usuario['nombre']; // Obtener el nombre del usuario

                // Generar token y fecha de expiración
                $token = bin2hex(random_bytes(16));
                date_default_timezone_set('America/Bogota');
                $expiracion = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                // Guardar token en la base de datos
                $updateQuery = "UPDATE usuarios SET reset_token = :token, reset_token_expiration = :expiracion WHERE id = :id";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute([
                    ':token' => $token,
                    ':expiracion' => $expiracion,
                    ':id' => $usuario['id']
                ]);

                // Crear enlace de restablecimiento
                $enlace = "http://trabajo-adso-production.up.railway.app/reset-password.php?token=$token";

                // Crear contenido HTML para el correo
                $contenidoCorreo = "
                <!DOCTYPE html>
                <html lang='es'>
                  <head>
                    <meta charset='UTF-8' />
                    <meta name='viewport' content='width=device-width, initial-scale=1.0' />
                    <title>Restablecer Contraseña</title>
                    <style>
                      body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        margin: 0;
                        padding: 0;
                      }
                      .container {
                        max-width: 600px;
                        margin: 0 auto;
                        padding: 20px;
                      }
                      .logo {
                        max-width: 100px;
                        height: auto;
                        margin-bottom: 10px;
                        border-radius: 10px;
                      }
                      .divider {
                        border: none;
                        border-top: 2px solid #ccc;
                        margin: 10px 0;
                      }
                      .text {
                        font-size: 18px;
                        margin-bottom: 10px;
                      }
                      .footer {
                        font-size: 12px;
                        font-weight: bold;
                        margin-top: 10px;
                        color: #505050;
                        text-align: center;
                      }
                      .btn {
                        display: inline-block;
                        padding: 8px 20px;
                        font-size: 16px;
                        color: #fff;
                        background-color: #007bff;
                        text-decoration: none;
                        border-radius: 5px;
                      }
                      .btn:hover {
                        background-color: #0074f0;
                      }
                    </style>
                  </head>
                  <body>
                    <div class='container'>
                      <img src='https://tu-dominio.com/assets/img/logo.png' alt='logo' class='logo' />
                      <hr class='divider' />
                      <p class='text'>Hola, " . htmlspecialchars($nombreUsuario) . "</p>
                      <p>Haz clic en el siguiente botón para restablecer tu contraseña:</p>
                      <a href='$enlace' class='btn'>Restablecer Contraseña</a>
                      <p>
                        El token es válido por 5 minutos. Si no solicitaste este cambio, ignora
                        este mensaje.
                      </p>
                      <hr class='divider' />
                      <p class='footer'>Equipo de Adso</p>
                    </div>
                  </body>
                </html>";

                // Enviar correo con contenido HTML
                enviarCorreo($correo, "Restablecer contraseña", $contenidoCorreo);

                // Guardar mensaje en la sesión y redirigir
                $_SESSION['mensaje'] = "Se ha enviado un enlace de restablecimiento a tu correo.";
                session_write_close(); // Forzar la escritura de la sesión
                header("Location: password.php");
                exit;
            } else {
                $_SESSION['error'] = "Error: El correo no está registrado.";
                header("Location: password.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al procesar la solicitud: " . $e->getMessage();
            header("Location: password.php");
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
    <title>Recuperar Contraseña</title>
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
    <script src="assets/js/index.js"></script>
</head>

<body>
    <div class="form-container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card custom-card">
            <div class="card-body">
                <!-- Mostrar mensaje -->
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

                <!-- Logo, Título y Descripción -->
                <div class="d-flex flex-column align-items-start mb-4">
                    <img src="assets/img/logo.png" alt="Logo" class="logo-img mb-3" />
                    <h2 class="card-title mb-3">Recupera tu contraseña</h2>
                    <p class="text-muted">Ingresa tu correo electrónico para recuperar tu contraseña.</p>
                </div>
                <div class="d-flex align-items-center opacity-80 w-100">
                    <span class="text-muted me-2">Volver a: </span>
                    <a href="login.php" class="text-primary">Iniciar sesión</a>
                </div>

                <!-- Formulario de Recuperación -->
                <form action="password.php" method="POST">
                    <!-- Campo de Correo -->
                    <div class="mb-4">
                        <label class="form-label">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input
                                type="email"
                                name="correo"
                                class="form-control"
                                placeholder="usuario@ejemplo.com"
                                required />
                        </div>
                    </div>

                    <!-- Botón de Enviar -->
                    <button type="submit" class="btn btn-primary w-100">Enviar</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <body class="recover-page">
</body>

</html>