<?php

include '../conexion.php'; 

$idcomentario = $_POST['idcomentario'];

try {
    $sql = "DELETE FROM comunidad WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idcomentario]);

    echo "Comentario eliminado.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
header("Location: listar.php");
?>
