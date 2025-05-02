<?php
// Incluir la conexión a la base de datos
require_once 'conexion.php';
session_start(); // Iniciar la sesión

// Forzar codificación UTF-8 en PHP
header('Content-Type: text/html; charset=UTF-8');

// Asegurarse de que los datos de entrada estén en UTF-8
if (
  !mb_check_encoding($_POST['nombre'] ?? '', 'UTF-8') ||
  !mb_check_encoding($_POST['correo'] ?? '', 'UTF-8') ||
  !mb_check_encoding($_POST['ficha'] ?? '', 'UTF-8') ||
  !mb_check_encoding($_POST['password'] ?? '', 'UTF-8')
) {
  // Convertir a UTF-8 si no es válido
  $_POST['nombre'] = mb_convert_encoding($_POST['nombre'] ?? '', 'UTF-8', 'auto');
  $_POST['correo'] = mb_convert_encoding($_POST['correo'] ?? '', 'UTF-8', 'auto');
  $_POST['ficha'] = mb_convert_encoding($_POST['ficha'] ?? '', 'UTF-8', 'auto');
  $_POST['password'] = mb_convert_encoding($_POST['password'] ?? '', 'UTF-8', 'auto');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Obtener los datos del formulario
  $nombre = trim($_POST['nombre'] ?? '');
  $identificacion = trim($_POST['identificacion'] ?? '');
  $edad = trim($_POST['edad'] ?? '');
  $ficha = trim($_POST['ficha'] ?? '');
  $correo = trim($_POST['correo'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

  // Validar que los campos no estén vacíos
  if (empty($nombre) || empty($identificacion) || empty($edad) || empty($ficha) || empty($correo) || empty($password)) {
    $_SESSION['error'] = "Error: Todos los campos son obligatorios.";
  } elseif (strlen($identificacion) < 6) {
    // Validar que la identificación tenga al menos 6 dígitos
    $_SESSION['error'] = "Error: La identificación debe tener al menos 6 dígitos.";
  } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    // Validar el formato del correo electrónico
    $_SESSION['error'] = "Error: El correo electrónico no tiene un formato válido.";
  } elseif (
    strlen($password) < 10 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/\d/', $password) ||
    !preg_match('/[@$!%*?&]/', $password)
  ) {
    // Validar la contraseña
    $_SESSION['error'] = "No cumple los requisitos de la contraseña.";
  } else {
    // Verificar el reCAPTCHA
    $recaptchaSecret = '6LdeMBorAAAAAJ0E5OtgDzvXVqG9punK7kmk6B0m'; // Reemplaza con tu clave secreta de reCAPTCHA
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaData = [
      'secret' => $recaptchaSecret,
      'response' => $recaptchaResponse,
      'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
      'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($recaptchaData)
      ]
    ];
    $context = stream_context_create($options);
    $recaptchaVerify = file_get_contents($recaptchaUrl, false, $context);
    $recaptchaResult = json_decode($recaptchaVerify, true);

    if (!$recaptchaResult['success']) {
      $_SESSION['error'] = "Error: Verificación de reCAPTCHA fallida.";
    } else {
      try {
        // Configurar codificación UTF-8 en la conexión
        $pdo->exec("SET client_encoding = 'UTF8'");

        // Verificar si el correo ya está registrado
        $queryCheck = "SELECT COUNT(*) FROM usuarios WHERE correo = :correo";
        $stmtCheck = $pdo->prepare($queryCheck);
        $stmtCheck->execute([':correo' => $correo]);
        $count = $stmtCheck->fetchColumn();

        if ($count > 0) {
          // Si el correo ya existe, mostrar mensaje de error
          $_SESSION['error'] = "Error: Usuario ya registrado.";
        } else {
          // Consulta SQL para insertar los datos
          $query = "INSERT INTO usuarios (nombre, identificacion, edad, ficha, correo, password, rol) 
                    VALUES (:nombre, :identificacion, :edad, :ficha, :correo, :password, :rol)";

          // Preparar la consulta
          $stmt = $pdo->prepare($query);

          // Ejecutar la consulta con los datos del formulario
          $stmt->execute([
            ':nombre' => $nombre,
            ':identificacion' => $identificacion,
            ':edad' => $edad,
            ':ficha' => $ficha,
            ':correo' => $correo,
            ':password' => password_hash($password, PASSWORD_BCRYPT), // Encriptar la contraseña
            ':rol' => 1 // Asignar rol 1 por defecto
          ]);

          // Mensaje de éxito
          $_SESSION['mensaje'] = "Registro exitoso.";
        }
      } catch (PDOException $e) {
        // Mensaje de error
        $_SESSION['error'] = "Error al registrar: " . $e->getMessage();
      }
    }
  }

  // Redirigir para evitar reenvío del formulario
  header("Location: register.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrarse</title>
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
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
  <div class="form-container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card custom-card">
      <div class="card-body">
        <!-- Mostrar mensaje de éxito o error -->
        <div class="container">
          <!-- Mostrar mensaje de éxito -->
          <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success text-center">
              <?= htmlspecialchars($_SESSION['mensaje']) ?>
            </div>
            <?php unset($_SESSION['mensaje']); // Eliminar el mensaje después de mostrarlo 
            ?>
          <?php endif; ?>

          <!-- Mostrar mensaje de error -->
          <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger text-center">
              <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); // Eliminar el mensaje después de mostrarlo 
            ?>
          <?php endif; ?>

          <!-- Logo, Título y Enlace de Login -->
          <div class="d-flex flex-column align-items-start mb-4">
            <img src="assets/img/logo.png" alt="Logo" class="logo-img mb-3" />
            <h2 class="card-title mb-3">Crear una cuenta</h2>
            <div class="d-flex align-items-center opacity-80 w-100">
              <span class="text-muted me-2">¿Ya estás registrado?</span>
              <a href="login.php" class="text-primary">Inicia sesión</a>
            </div>
          </div>

          <!-- Formulario de Registro -->
          <form action="register.php" method="POST">
            <!-- Campo de Nombre -->
            <div class="mb-4">
              <label class="form-label">Nombre</label>
              <input
                type="text"
                name="nombre"
                class="form-control"
                placeholder="Tu nombre"
                required />
            </div>

            <!-- Campo de Identificación -->
            <!-- Campo de Identificación -->
            <div class="mb-4">
              <label class="form-label">Identificación</label>
              <input
                type="number"
                name="identificacion"
                id="identificacion"
                class="form-control"
                placeholder="Tu identificación"
                required
                onfocus="mostrarMensajeError()"
                onblur="ocultarMensajeError()"
                oninput="validarIdentificacion()" />
              <small id="identificacion-error" class="text-danger d-none">Debe tener mínimo 6 dígitos</small>
            </div>

            <!-- Campo de Edad -->
            <div class="mb-4">
              <label class="form-label">Edad</label>
              <input
                type="number"
                name="edad"
                class="form-control"
                placeholder="Tu edad"
                required />
            </div>

            <!-- Campo de Ficha -->
            <div class="mb-4">
              <label class="form-label">Ficha</label>
              <input
                type="number"
                name="ficha"
                class="form-control"
                placeholder="Número de ficha"
                required />
            </div>

            <!-- Campo de Correo -->
            <div class="mb-4">
              <label class="form-label">Correo electrónico</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
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
              <label class="form-label">Contraseña</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" id="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" onfocus="showRequirements()" onblur="hideRequirements()" onkeyup="validatePassword()" required />
              </div>
            </div>
            <!-- Solo la parte relevante dentro de <div class="card-body"> -->
            <div class="password-requirements-container">
              <ul class="password-requirements" id="password-requirements">
                <li id="length-requirement" class="password-invalid"><i class="bi bi-x icon"></i> 10 caracteres mínimo</li>
                <li id="uppercase-requirement" class="password-invalid"><i class="bi bi-x icon"></i> Letra mayúscula</li>
                <li id="number-requirement" class="password-invalid"><i class="bi bi-x icon"></i> Al menos un número</li>
                <li id="special-char-requirement" class="password-invalid"><i class="bi bi-x icon"></i> Mínimo un carácter especial (@$!%*?&)</li>
              </ul>
            </div>

            <div class="g-recaptcha" data-sitekey="6LdeMBorAAAAAPuKlOEOmNJdVMSAz9VmG-qviTid"></div>

            <!-- Checkbox de Términos y Condiciones -->
            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" id="terms" required />
              <label class="form-check-label" for="terms">
                Acepto los
                <a href="terms.html" target="_blank">Términos y Condiciones</a>
              </label>
            </div>

            <!-- Botón de Registro -->
            <button type="submit" class="btn btn-primary w-100 mb-4">Registrar</button>
          </form>

        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/index.js"></script>
    <script src="assets/js/passwordValidation.js"></script>
    <body class="register-page">
</body>

</html>