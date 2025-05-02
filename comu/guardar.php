<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO("pgsql:host=localhost;dbname=web", "postgres", "1");

        $contenido = $_POST['contenido'];
        $idusuario = $_POST['idusuario'];
        $idpublicacion = $_POST['idpublicacion'];

        $sql = "INSERT INTO comunidad (contenido, idusuario, idpublicacion)
                VALUES (:contenido, :idusuario, :idpublicacion)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':contenido' => $contenido,
            ':idusuario' => $idusuario,
            ':idpublicacion' => $idpublicacion
        ]);

        header("Location: ./comentario.php?idpublicacion=$idpublicacion");
        exit;

    } catch (PDOException $e) {
        echo "Error al guardar comentario: " . $e->getMessage();
    }
}
