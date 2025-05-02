<?php
try {
    $dsn = getenv('DATABASE_URL');
    if (!$dsn) {
        throw new Exception("DATABASE_URL no está configurado");
    }
    $pdo = new PDO($dsn, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    error_log("Conexión a la base de datos exitosa");
} catch (Exception $e) {
    $pdo = null;
    error_log("Error al conectar a la base de datos: " . $e->getMessage());
    echo "Error de conexión: " . $e->getMessage();
    exit;
}
?>