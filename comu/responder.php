<?php

include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idComentario = $_POST['idcomentario'];
    $respuesta = $_POST['respuesta'];

   
    $sql = "UPDATE comunidad SET respuesta = :respuesta WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':respuesta', $respuesta);
    $stmt->bindParam(':id', $idComentario);
    $stmt->execute();

    
    header("Location: ./listar.php?respondido=true");
    exit;
}
?>
