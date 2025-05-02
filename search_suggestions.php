<?php
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_GET['q']) || strlen(trim($_GET['q'])) < 3) {
    echo json_encode(['libros' => [], 'autores' => []]);
    exit;
}

$searchQuery = '%' . trim($_GET['q']) . '%';

try {
    // Buscar por título
    $queryLibros = "SELECT id, titulo, autor 
                    FROM documento 
                    WHERE titulo ILIKE :searchQuery 
                    LIMIT 3";
    $stmtLibros = $pdo->prepare($queryLibros);
    $stmtLibros->bindValue(':searchQuery', $searchQuery);
    $stmtLibros->execute();
    $libros = $stmtLibros->fetchAll(PDO::FETCH_ASSOC);

    // Buscar por autor
    $queryAutores = "SELECT DISTINCT autor 
                     FROM documento 
                     WHERE autor ILIKE :searchQuery 
                     LIMIT 3";
    $stmtAutores = $pdo->prepare($queryAutores);
    $stmtAutores->bindValue(':searchQuery', $searchQuery);
    $stmtAutores->execute();
    $autores = $stmtAutores->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'libros' => $libros,
        'autores' => array_column($autores, 'autor')
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener sugerencias: ' . $e->getMessage()]);
}
?>