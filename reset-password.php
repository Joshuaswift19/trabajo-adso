<?php
require_once 'conexion.php';

$mensaje = ''; // Variable para almacenar el mensaje

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $nuevaPassword = $_POST['password'];

    // Validar la nueva contraseña
    if (strlen($nuevaPassword) < 10 || !preg_match('/[A-Z]/', $nuevaPassword) || !preg_match('/\d/', $nuevaPassword) || !preg_match('/[@$!%*?&]/', $nuevaPassword)) {
        $mensaje = "No cumple los requisitos de la contraseña.";
    } else {
        try {
            // Verificar si el token es válido y no ha expirado
            $query = "SELECT id FROM usuarios WHERE reset_token = :token AND reset_token_expiration > NOW()";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':token' => $token]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Actualizar la contraseña del usuario
                $hashedPassword = password_hash($nuevaPassword, PASSWORD_DEFAULT);
                $updateQuery = "UPDATE usuarios SET password = :password, reset_token = NULL, reset_token_expiration = NULL WHERE id = :id";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute([
                    ':password' => $hashedPassword,
                    ':id' => $usuario['id']
                ]);

                $mensaje = "Contraseña actualizada correctamente.";
            } else {
                $mensaje = "El token es inválido o ha expirado.";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al procesar la solicitud: " . $e->getMessage();
        }
    }
} else if (isset($_GET['token'])) {
    $token = $_GET['token'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="assets/img/logo.png" type="png" />
    <link rel="stylesheet" href="assets/css/styles.css" />
    
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card p-4">
            <h2 class="mb-4">Restablecer Contraseña</h2>

            <!-- Mostrar mensaje de éxito o error -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-info text-center">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form action="reset-password.php" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" onfocus="showRequirements()" onblur="hideRequirements()" onkeyup="validatePassword()" required />
                </div>
                <div class="password-requirements-container">
                    <ul class="password-requirements" id="password-requirements">
                        <li id="length-requirement" class="password-invalid"><i class="bi bi-x icon"></i> 10 caracteres mínimo</li>
                        <li id="uppercase-requirement" class="password-invalid"><i class="bi bi-x icon"></i> Letra mayúscula</li>
                        <li id="number-requirement" class="password-invalid"><i class="bi bi-x icon"></i> Al menos un número</li>
                        <li id="special-char-requirement" class="password-invalid"><i class="bi bi-x icon"></i> Mínimo un carácter especial (@$!%*?&)</li>
                    </ul>
                </div>
                <button type="submit" class="btn btn-primary w-100">Restablecer</button>
            </form>
        </div>
    </div>
    <script src="assets/js/index.js"></script>
    <script src="assets/js/passwordValidation.js"></script>
</body>

</html>