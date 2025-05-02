<?php
try {
    $dsn = getenv('DATABASE_URL') ?: 'pgsql:host=localhost;port=5432;dbname=web';
    $user = getenv('PGUSER') ?: 'postgres';
    $password = getenv('PGPASSWORD') ?: '1';
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Conexión a la base de datos exitosa");
} catch (PDOException $e) {
    error_log("Error al conectar a la base de datos: " . $e->getMessage());
    echo "Error de conexión: " . $e->getMessage();
}
?>