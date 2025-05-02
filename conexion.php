<?php
// Parámetros de conexión
$host = "localhost";
$port = "5432";
$dbname = "web";
$user = "postgres";
$password = "1";

try {
    // Cadena de conexión
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
    
    // Crear conexión PDO
    $pdo = new PDO($dsn);
    
    // Configurar el modo de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Elimina o comenta esta línea para evitar el mensaje
    // echo "Conexión exitosa a la base de datos";
    
} catch (PDOException $e) {
    // Manejo de errores
    echo "Error de conexión: " . $e->getMessage();
}
$pdo = new PDO($dsn);

$pdo = new PDO("pgsql:host=localhost;dbname=web", "postgres", "1");
?>