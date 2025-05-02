<?php
session_start();
require_once 'conexion.php';

// Crear carpeta de uploads si no existe
$upload_dir = '/app/storage/uploads/user-photo/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];

    // Actualizar contraseña
    if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        if (empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            echo json_encode(['error' => 'Ambos campos son obligatorios']);
            exit;
        }
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            echo json_encode(['error' => 'Las contraseñas no coinciden']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            if ($stmt->execute([$password_hash, $usuario_id])) {
                echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
            } else {
                echo json_encode(['error' => 'Error al actualizar la contraseña']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error en el servidor']);
        }
        exit;
    }

    // Eliminar foto de perfil
    if (isset($_POST['action']) && $_POST['action'] === 'delete_photo') {
        try {
            $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $current_photo = $stmt->fetchColumn();
            
            if ($current_photo && file_exists($current_photo)) {
                unlink($current_photo);
            }
            
            $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = NULL WHERE id = ?");
            $stmt->execute([$usuario_id]);
            
            echo json_encode(['success' => true, 'message' => 'Foto de perfil eliminada']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al eliminar la foto']);
        }
        exit;
    }

    // Subir foto de perfil
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $file = $_FILES['foto'];

        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['error' => 'Solo se permiten imágenes JPG, PNG o WebP']);
            exit;
        }
        if ($file['size'] > $max_size) {
            echo json_encode(['error' => 'La imagen no debe exceder 2MB']);
            exit;
        }

        try {
            // Eliminar foto anterior si existe
            $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $current_photo = $stmt->fetchColumn();
            if ($current_photo && file_exists($current_photo)) {
                unlink($current_photo);
            }

            // Generar nombre único
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $usuario_id . '_' . time() . '.' . $ext;
            $destination = $upload_dir . $filename;

            // Mover archivo
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                $stmt->execute([$destination, $usuario_id]);
                echo json_encode(['success' => true, 'message' => 'Foto de perfil actualizada', 'photo_url' => $destination]);
            } else {
                echo json_encode(['error' => 'Error al mover la imagen']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error al guardar la imagen en la base de datos']);
        }
        exit;
    }

    // Actualizar perfil
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if (empty($nombre) || empty($correo)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Todos los campos son obligatorios']);
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Correo electrónico inválido']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
        $stmt->execute([$correo, $usuario_id]);
        if ($stmt->rowCount() > 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'El correo electrónico ya está en uso']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?");
        $stmt->execute([$nombre, $correo, $usuario_id]);

        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_correo'] = $correo;

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al actualizar el perfil']);
    }
    exit;
}

// Obtener foto de perfil actual
try {
    $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $foto_perfil = $stmt->fetchColumn() ?: '';
} catch (PDOException $e) {
    $foto_perfil = '';
}

// Manejo de inactividad
$tiempo_maximo_inactividad = 600;
if (isset($_SESSION['ultima_actividad'])) {
    $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];
    if ($tiempo_inactivo > $tiempo_maximo_inactividad) {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
$_SESSION['ultima_actividad'] = time();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="shortcut icon" href="assets/img/logo.png" type="image/png">
    <style>
        .profile-icon img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        .cropper-container {
            max-width: 500px;
            margin-top: 1rem;
        }
        .alert-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            display: inline-block;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="settings-container">
        <table class="settings-table">
            <tr>
                <!-- Sidebar -->
                <td class="settings-sidebar">
                    <div class="sidebar-profile">
                        <div class="profile-icon">
                            <?php if ($foto_perfil): ?>
                                <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil" id="sidebar-photo">
                            <?php else: ?>
                                <i class="bi bi-person-circle" id="sidebar-icon"></i>
                            <?php endif; ?>
                        </div>
                        <div class="profile-info">
                            <span class="fw-bold d-block profile-name"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                        </div>
                    </div>
                    <ul class="settings-menu">
                        <li class="settings-item" data-section="profile">Mi perfil</li>
                        <li class="settings-item" data-section="photo">Foto</li>
                        <li class="settings-item" data-section="security">Seguridad de la cuenta</li>
                        <li class="settings-item" data-section="delete">Eliminar cuenta</li>
                    </ul>
                </td>

                <!-- Contenido -->
                <td class="settings-content">
                    <!-- Sección: Mi perfil -->
                    <div class="settings-section active" id="profile">
                        <h2>Mi perfil</h2>
                        <div class="um-container">
                            <form id="profileForm" method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre completo:</label>
                                    <input type="text" class="form-control" id="name" name="nombre" placeholder="Tu nombre" value="<?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>" required>
                                    <div class="invalid-feedback">Por favor, ingresa tu nombre completo.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo electrónico:</label>
                                    <input type="email" class="form-control" id="email" name="correo" placeholder="Tu correo" value="<?php echo htmlspecialchars($_SESSION['usuario_correo']); ?>" required>
                                    <div class="invalid-feedback">Por favor, ingresa un correo electrónico válido.</div>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="confirmarCambios()">Guardar cambios</button>
                            </form>
                        </div>
                    </div>

                    <!-- Sección: Foto de perfil -->
                    <div class="settings-section d-none" id="photo">
                        <h2>Foto de perfil</h2>
                        <div class="um-container">
                            <div id="photo-message"></div>
                            <form id="photoForm" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="profile-photo" class="form-label">Selecciona una foto de perfil:</label>
                                    <input type="file" class="form-control" id="profile-photo" name="foto" accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text" id="file-name">Ningún archivo seleccionado</div>
                                </div>
                                <div class="cropper-container d-none">
                                    <img id="cropper-image" alt="Recortar imagen">
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary d-none" id="crop-button">Listo</button>
                                    <button type="button" class="btn btn-primary" id="update-photo-button">Actualizar foto</button>
                                    <?php if ($foto_perfil): ?>
                                        <button type="button" class="btn btn-danger" id="delete-photo-button">Eliminar foto</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sección: Seguridad de la cuenta -->
                    <div class="settings-section d-none" id="security">
                        <h2>Seguridad de la cuenta</h2>
                        <div class="um-container">
                            <form id="securityForm" method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="new-password" class="form-label">Nueva contraseña</label>
                                    <input type="password" class="form-control" id="new-password" name="new_password" placeholder="Nueva contraseña" required onfocus="showRequirements()" onblur="hideRequirements()" onkeyup="validatePassword()">
                                    <div class="invalid-feedback">Por favor, ingresa una nueva contraseña.</div>
                                </div>
                                <div class="password-requirements-container">
                                    <ul class="password-requirements" id="password-requirements">
                                        <li id="length-requirement" class="password-invalid">10 caracteres mínimo</li>
                                        <li id="uppercase-requirement" class="password-invalid">Letra</li>
                                        <li id="number-requirement" class="password-invalid">Al menos un número</li>
                                        <li id="special-char-requirement" class="password-invalid">Mínimo un carácter especial (@$!%*?&)</li>
                                    </ul>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm-password" class="form-label">Confirma la contraseña</label>
                                    <input type="password" class="form-control" id="confirm-password" name="confirm_password" placeholder="Introducir nueva contraseña de nuevo" required onkeyup="validateConfirmPassword()">
                                    <div class="invalid-feedback">Las contraseñas no coinciden</div>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="actualizarContraseña()">Actualizar contraseña</button>
                            </form>
                        </div>
                    </div>

                    <!-- Sección: Cerrar cuenta -->
                    <div class="settings-section d-none" id="delete">
                        <h2 class="settings-delete">Eliminar cuenta</h2>
                        <div class="um-container">
                            <p>Si cierras tu cuenta, perderás acceso a todos tus datos. Esta acción no se puede deshacer.</p>
                            <button class="btn btn-danger">Cerrar cuenta</button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="assets/js/index.js"></script>
    <script src="assets/js/passwordValidation.js"></script>
</body>
</html>