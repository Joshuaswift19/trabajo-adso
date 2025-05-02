<?php
// ver_comentario.php
include '../conexion';

if (!isset($_GET['id'])) {
    echo "ID de comentario no especificado.";
    exit;
}

$idComentario = $_GET['id'];

$sql = "SELECT * FROM comunidad WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $idComentario, PDO::PARAM_INT);
$stmt->execute();

$comentario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comentario) {
    echo "Comentario no encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comentario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow">
        <div class="card-body">
            <h5 class="card-title">Comunidad ID: <?= htmlspecialchars($comentario['id']) ?></h5>
            <p class="card-text"><strong>Contenido:</strong> <?= nl2br(htmlspecialchars($comentario['contenido'])) ?></p>
            <p class="card-text"><strong>ID Usuario:</strong> <?= htmlspecialchars($comentario['idusuario']) ?></p>
            <p class="card-text"><strong>ID Publicación:</strong> <?= htmlspecialchars($comentario['idpublicacion']) ?></p>

            <?php if (!empty($comentario['respuesta'])): ?>
                <div class="alert alert-success mt-4">
                    <strong>Respuesta del administrador:</strong><br>
                    <?= nl2br(htmlspecialchars($comentario['respuesta'])) ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mt-4">
                    Aún no se ha respondido este comentario.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
